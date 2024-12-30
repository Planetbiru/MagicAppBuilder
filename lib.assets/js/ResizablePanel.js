/**
 * Class representing resizable panels with a resize bar between them.
 */
class ResizablePanels {
    /**
     * Create a ResizablePanels instance.
     * @param {string} selector - The parent selector of the panels.
     * @param {string} leftPanelSelector - The selector for the left panel.
     * @param {string} rightPanelSelector - The selector for the right panel.
     * @param {string} resizeBarSelector - The selector for the resize bar.
     * @param {number} [minWidth=100] - The minimum width for the left panel.
     */
    constructor(selector, leftPanelSelector, rightPanelSelector, resizeBarSelector, minWidth = 100) {
        this.selector = selector;
        this.element = document.querySelector(this.selector);
        this.leftPanel = document.querySelector(selector + " " + leftPanelSelector);
        this.rightPanel = document.querySelector(selector + " " + rightPanelSelector);
        this.resizeBar = document.querySelector(selector + " " + resizeBarSelector);
        this.minWidth = minWidth;  // Minimum width for the left panel
        this.isResizing = false;
        this.lastDownX = 0;
        this.localStorageKey = this.selector + 'leftPanelWidth';
        this.init();
    }

    /**
     * Initialize event listeners for resizing functionality and window resizing.
     */
    init() {
        // Start resizing when clicking on the resize bar
        this.resizeBar.addEventListener('mousedown', (e) => this.startResizing(e));

        // Adjust layout when the window is resized
        window.addEventListener('resize', () => this.onWindowResize());
        this.loadPanelWidth();
    }

    

    /**
     * Handle the mouse movement during resizing.
     * @param {MouseEvent} e - The mouse move event.
     */
    handleMouseMove(e) {
        if (this.isResizing) {
            let offset = e.clientX - this.lastDownX; // Calculate the change in position
            let leftPanelWidth = this.leftPanel.offsetWidth + offset;

            // Ensure the left panel's width is within the minWidth and max bounds
            if (leftPanelWidth >= this.minWidth && leftPanelWidth <= window.innerWidth - this.minWidth) {
                let parentNode = this.leftPanel.parentNode;
                let parentWidth = parentNode.offsetWidth;
                let rightPanelWidth = parentWidth - leftPanelWidth - 10; // 10px space between panels

                // If the right panel width becomes smaller than minWidth, adjust left panel width
                if (rightPanelWidth < this.minWidth) {
                    leftPanelWidth = parentWidth - this.minWidth - 10; // Adjust left panel to maintain minWidth for right panel
                    rightPanelWidth = this.minWidth; // Set right panel to minWidth
                }

                // Set the new widths for the panels
                this.leftPanel.style.width = leftPanelWidth + 'px';
                this.rightPanel.style.width = rightPanelWidth + 'px';
                this.lastDownX = e.clientX;

                // Save the new width of the left panel in localStorage
                localStorage.setItem(this.localStorageKey, leftPanelWidth);
            }

            
        }
    }
    
    disableSelection(element)
    {
        element.style.webkitUserSelect = 'none'; // Safari
        element.style.mozUserSelect = 'none';    // Firefox
        element.style.msUserSelect = 'none';     // IE/Edge
        element.style.userSelect = 'none';       // Standard
    }
    
    enableSelection(element)
    {
        element.style.webkitUserSelect = 'auto'; // Safari
        element.style.mozUserSelect = 'auto';    // Firefox
        element.style.msUserSelect = 'auto';     // IE/Edge
        element.style.userSelect = 'auto';       // Standard
    }
    
    /**
     * Start the resizing process when the mouse is pressed down on the resize bar.
     * @param {MouseEvent} e - The mouse down event.
     */
    startResizing(e) {
        this.isResizing = true;
        this.lastDownX = e.clientX;
        this.disableSelection(this.leftPanel);
        this.disableSelection(this.rightPanel);
        this.element.addEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.element.addEventListener('mouseup', () => this.stopResizing());
    }

    /**
     * Stop the resizing process when the mouse is released.
     */
    stopResizing() {
        this.isResizing = false;
        this.element.removeEventListener('mousemove', (e) => this.handleMouseMove(e));
        this.element.removeEventListener('mouseup', () => this.stopResizing());
        editor.refreshEntities();
        this.enableSelection(this.leftPanel);
        this.enableSelection(this.rightPanel);
    }

    /**
     * Resize the panels based on the saved width for the left panel.
     * @param {string} savedLeftPanelWidth - The saved width of the left panel from localStorage.
     */
    doResize(savedLeftPanelWidth) {
        if (savedLeftPanelWidth) {
            // If a saved width exists, apply it
            let leftPanelWidth = parseInt(savedLeftPanelWidth, 10);
            let parentNode = this.leftPanel.parentNode;
            let parentWidth = parentNode.offsetWidth;
            let rightPanelWidth = parentWidth - leftPanelWidth - 10;

            // Ensure that right panel's width is not less than minWidth
            if (rightPanelWidth < this.minWidth) {
                rightPanelWidth = this.minWidth;
                leftPanelWidth = parentWidth - rightPanelWidth - 10; // Adjust left panel width accordingly
            }

            // Adjust the panels based on the saved width
            this.leftPanel.style.width = leftPanelWidth + 'px';
            this.rightPanel.style.width = rightPanelWidth + 'px';
        }
    }

    /**
     * Get the saved width of the left panel from localStorage.
     * @returns {number} The width of the left panel stored in localStorage.
     */
    getLeftPanelWidth() {
        return parseInt(localStorage.getItem(this.localStorageKey));
    }

    /**
     * Load the saved width of the left panel and apply it.
     */
    loadPanelWidth() {
        this.doResize(this.getLeftPanelWidth());
    }

    /**
     * Recalculate the panel widths based on the new window size.
     */
    onWindowResize() {
        // Recalculate the panel widths based on the new window size
        this.doResize(this.getLeftPanelWidth());
    }
}
