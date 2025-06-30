/**
 * TabDragger is a utility class to make <li> elements draggable within a list (typically a tab navigation).
 * It supports:
 * - Preventing drag before `.all-entities`
 * - Preventing drag after `.add-tab`
 * - Dynamically initializing `.diagram-tab` elements
 * - Optional callback execution on drop
 *
 * Example usage:
 * const tabDragger = new TabDragger("#sortable-tabs", () => { console.log("Drag finished"); });
 * tabDragger.initAll();
 */
class TabDragger {
  /**
   * Initializes a new instance of the TabDragger.
   * @param {string|HTMLElement} listElementOrSelector - The CSS selector string or the HTMLElement of the list (e.g., `<ul>`) containing the draggable items.
   * @param {function} [onEnd] - An optional callback function to execute when a drag operation completes (i.e., on `drop` event).
   * @throws {Error} If the `listElementOrSelector` is not a string or an HTMLElement.
   */
  constructor(listElementOrSelector, onEnd) {
    // Accepts a CSS selector string or a direct HTMLElement
    if (typeof listElementOrSelector === "string") {
      this.list = document.querySelector(listElementOrSelector);
    } else if (listElementOrSelector instanceof HTMLElement) {
      this.list = listElementOrSelector;
    } else {
      throw new Error("TabDragger constructor expects a selector string or HTMLElement.");
    }

    /**
     * Stores the currently dragged HTMLElement.
     * @type {HTMLElement|null}
     */
    this.draggedItem = null;

    /**
     * A snapshot of the list's children before a drag operation starts.
     * Used to prevent issues with live DOM manipulation during `dragover`.
     * @type {HTMLElement[]}
     */
    this.childrenSnapshot = [];

    /**
     * Gets the current index of the fixed `.all-entities` item within the list children.
     * @returns {number} The index of the `.all-entities` element, or -1 if not found.
     */
    this.allEntitiesIndex = () =>
      Array.from(this.list.children).findIndex(li => li.classList.contains("all-entities"));

    /**
     * Gets the current index of the fixed `.add-tab` item within the list children.
     * @returns {number} The index of the `.add-tab` element, or -1 if not found.
     */
    this.addTabIndex = () =>
      Array.from(this.list.children).findIndex(li => li.classList.contains("add-tab"));

    /**
     * Callback function executed when a drag operation finishes (on drop).
     * @type {function}
     */
    if (typeof onEnd == 'function') {
      this.onEnd = onEnd;
    } else {
      // Default no-operation function if no callback is provided
      this.onEnd = function() {
        // No-op default
      }
    }
  }

  /**
   * Initializes drag behavior for a single list item.
   * Only items with the class "diagram-tab" and without "all-entities" or "add-tab" classes
   * will be made draggable.
   * @param {HTMLElement} item - The list item (<li>) to make draggable.
   */
  makeDraggable(item) {
    // Only make 'diagram-tab' items draggable
    if (!item.classList.contains("diagram-tab")) return;
    // Prevent 'all-entities' and 'add-tab' items from being draggable
    if (item.classList.contains("all-entities") || item.classList.contains("add-tab")) return;

    // Enable HTML5 drag-and-drop for the item
    item.draggable = true;

    // Event listener for when the drag operation starts on an item
    item.addEventListener("dragstart", () => {
      this.draggedItem = item; // Set the currently dragged item
      item.classList.add("dragging"); // Add a class for visual feedback during drag

      // Take a snapshot of the children before DOM changes occur during drag
      this.childrenSnapshot = Array.from(this.list.children);
    });

    // Event listener for when the drag operation ends (whether dropped or cancelled)
    item.addEventListener("dragend", () => {
      this.draggedItem = null; // Clear the dragged item
      item.classList.remove("dragging"); // Remove the dragging class
    });

    // Event listener for when a draggable item is dragged over this item
    item.addEventListener("dragover", (e) => {
      e.preventDefault(); // Prevent default to allow dropping

      // If no item is being dragged, or if the dragged item is the current target, do nothing
      if (!this.draggedItem || item === this.draggedItem) return;
      // Prevent dropping onto fixed 'all-entities' or 'add-tab' items
      if (item.classList.contains("all-entities") || item.classList.contains("add-tab")) return;

      // Use the cached snapshot of children to determine indices consistently
      const children = this.childrenSnapshot;
      const draggedIndex = children.indexOf(this.draggedItem);
      const targetIndex = children.indexOf(item);
      const allEntitiesIndex = children.findIndex(li => li.classList.contains("all-entities"));
      const addTabIndex = children.findIndex(li => li.classList.contains("add-tab"));

      // Position constraints: prevent dragging before 'all-entities' or after 'add-tab'
      if (targetIndex <= allEntitiesIndex || targetIndex >= addTabIndex) return;

      // Reorder the DOM elements based on drag direction
      // If dragging forward and the dragged item is not already next to the target
      if (draggedIndex < targetIndex && item.nextSibling !== this.draggedItem) {
        this.list.insertBefore(this.draggedItem, item.nextSibling);
      }
      // If dragging backward and the dragged item is not the same as the target
      else if (draggedIndex > targetIndex && item !== this.draggedItem) {
        this.list.insertBefore(this.draggedItem, item);
      }
    });
  }

  /**
   * Initializes all `<li>` elements within the list that have the class "diagram-tab"
   * by making them draggable and attaching drag/drop listeners.
   * Also sets up a listener on the main list to execute the `onEnd` callback when a drop occurs.
   */
  initAll() {
    // Select all potential tab elements within the list
    const tabs = this.list.querySelectorAll(".diagram-tab");
    // Apply draggable behavior to each found tab
    tabs.forEach(tab => this.makeDraggable(tab));

    // Listen for the 'drop' event on the entire list to trigger the onEnd callback
    this.list.addEventListener("drop", () => {
      this.onEnd(); // Execute the provided callback function
    });
  }
}

