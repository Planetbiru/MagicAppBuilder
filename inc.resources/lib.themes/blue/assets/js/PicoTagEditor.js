/**
 * PicoTagEditor - A lightweight, interactive tag editor.
 * 
 * This constructor function transforms a standard input element into an advanced tag management system, 
 * allowing users to add, edit, and remove tags dynamically. The editor supports customizable settings 
 * and integrates with form submissions by storing tag values in hidden inputs.
 * 
 * Features:
 * - Converts an input field into a tag editor.
 * - Supports tag input with optional trimming and validation.
 * - Allows dynamic addition, removal, and display of tags.
 * - Provides configurable settings for width, height, and behavior.
 * - Integrates smoothly with form submissions.
 * 
 * @param {HTMLElement} input - The input element to be transformed into a tag editor.
 * @param {Object} [options={}] - Optional configuration settings.
 * @param {boolean} [options.debug=false] - Enables or disables debugging mode.
 * @param {string|number} [options.width] - The width of the tag editor container.
 * @param {string|number} [options.height] - The height of the tag editor container.
 * @param {string|number} [options.maxHeight] - The maximum height of the tag editor container.
 * @param {string|number} [options.minWidth] - The minimum width of the tag editor container.
 * @param {boolean} [options.trimInput=false] - Determines whether input values should be trimmed before being added as tags.
 * @param {boolean} [options.clearOnHide=false] - Specifies whether the input field should be cleared when the tag editor is hidden.
 * @param {Function} [callback] - A function executed after initialization, receiving the input, container, and editor instance as arguments.
 */
function PicoTagEditor(input, options, callback) {
    if (['checkbox', 'radio', 'hidden', 'file'].includes(input.getAttribute('type'))) {
        return;
    }    
    options = options || {};
    
    /**
     * Configuration settings for the tag editor.
     * These settings can be customized via the `options` parameter.
     *
     * @type {Object}
     * @property {boolean} debug - Indicates whether debug mode is enabled.
     * @property {string|number} width - The width of the tag editor.
     * @property {string|number} height - The height of the tag editor.
     * @property {string|number} minWidth - The minimum width of the tag editor.
     * @property {string|number} maxHeight - The maximum height of the tag editor.
     * @property {boolean} trimInput - Determines whether input values should be trimmed before being added as tags.
     * @property {boolean} clearOnHide - Specifies whether the input value should be cleared when the tag editor is hidden by a scheduler.
     */
    this.settings = {
        debug: false,
        width: undefined,
        height: undefined,
        minWidth: undefined,
        maxHeight: undefined,
        trimInput: false,
        ...options // Merge user-defined options
    };

    /**
     * Stores the initial values parsed from the `data-initial-value` attribute.
     * These values will be used to prepopulate the tag editor.
     *
     * @type {Array<string>}
     */
    this.initialValue = [];

    /**
     * The main container element that wraps the tag editor.
     * It holds the input field and dynamically created tag elements.
     *
     * @type {HTMLElement|null}
     */
    this.containerElement = null;

    /**
     * The name attribute for hidden input fields storing tag values.
     * Used when submitting the form.
     *
     * @type {string}
     */
    this.name = '';

    /**
     * Tracks whether the mouse is hovering over the tag editor container.
     * This helps manage focus and UI behavior.
     *
     * @type {boolean}
     */
    this.mouseEnter = false;

    /**
     * The input field used for adding new tags.
     *
     * @type {HTMLElement|null}
     */
    this.inputElement = null;

    /**
     * Stores a reference to a timeout ID, used for delaying certain UI updates.
     * This is primarily used to hide the tag editor when focus is lost.
     *
     * @type {number|null}
     */
    this.timeout = null;

    /**
     * Stores an array of tag values currently in the tag editor.
     * This list is dynamically updated as tags are added or removed.
     *
     * @type {Array<string>}
     */
    this.values = [];

    /**
     * Stores the default placeholder text for the input field.
     * If tags are added, this may be replaced with a formatted tag list.
     *
     * @type {string}
     */
    this.placeholder = '';

    /**
     * Tracks whether the input field is currently focused.
     * This helps determine when the tag editor should remain visible.
     *
     * @type {boolean}
     */
    this.focus = false;

    // Store reference to the current instance
    let _this = this;

    /**
     * Initializes the tag editor, transforming the original input field into an interactive tag manager.
     *
     * @param {HTMLElement} input - The input element to transform.
     */
    this.init = function (input) {
        // Create the tag editor container
        const tagEditorDiv = document.createElement('div');
        tagEditorDiv.classList.add('pico-tag-editor');
        tagEditorDiv.setAttribute('data-focus', 'false');
        let initialValue = input.getAttribute('data-initial-value') || '[]';
        // Clone the original input field
        const newInput = input.cloneNode(true);

        // Create a container for tags
        const tagsContainer = document.createElement('div');
        tagsContainer.classList.add('tags-container');
        tagsContainer.style.display = 'none'; // Initially hidden

        // Apply custom styles if defined in settings
        const styles = ['width', 'height', 'maxHeight', 'minWidth'];
        styles.forEach(prop => {
            if (this.settings[prop]) {
                tagsContainer.style[prop] = this.isNumber(this.settings[prop]) ? `${this.settings[prop]}px` : this.settings[prop];
            }
        });

        // Append elements to the tag editor container
        tagEditorDiv.appendChild(newInput);
        tagEditorDiv.appendChild(tagsContainer);

        // Replace the original input with the new tag editor
        input.replaceWith(tagEditorDiv);
        this.containerElement = tagEditorDiv;
        this.inputElement = newInput;
        this.name = newInput.getAttribute('name') || '';
        // Ensure the name format supports array-like inputs
        if (!this.name.includes('[')) {
            this.name += '[]';
        }
        if(!newInput.classList.contains('pico-tag-edit'))
        {
            // Ensure that the input element has the pico-tag-edit class.
            newInput.classList.add('pico-tag-edit');
        }
        newInput.removeAttribute('name')
        this.placeholder = newInput.getAttribute('placeholder') || '';

        if(initialValue == '')
        {
            initialValue = '[]';
        }
        try
        {
            this.initialValue = JSON.parse(initialValue);
        }
        catch(ex)
        {
            // Do nothing
        }
    };

    /**
     * Sets the initial values for the tag editor.
     * If there are predefined initial values, they will be added as tags.
     */
    this.setInitialValue = function() {
        if (this.initialValue && this.initialValue.length > 0) {
            for (let value of this.initialValue) {
                this.addTag(value);
            }
        }
    };

    /**
     * Checks if a given value is a number.
     *
     * @param {string|number} value - The value to check.
     * @returns {boolean} True if the value is a valid number, otherwise false.
     */
    this.isNumber = function (value) {
        return /^-?\d+(\.\d+)?$/.test(value);
    };

    /**
     * Attaches event listeners for handling tag input and interaction.
     */
    this.addEvents = function () {
        const inputField = this.containerElement.querySelector('.pico-tag-edit');
        if (!inputField) return;
    
        // Handle "Enter" keypress to add a new tag
        inputField.addEventListener('keypress', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                if(event.target.value.trim())
                {
                    _this.addTag(_this.settings.trimInput ? event.target.value.trim() : event.target.value, true);
                    event.target.value = ''; // Clear input field after adding tag
                }
            }
        });
    
        // Handle focus state changes
        inputField.addEventListener('focus', () => {
            _this.focus = true; 
            _this.containerElement.setAttribute('data-focus', 'true'); 
        });
        inputField.addEventListener('blur', () => {
            _this.focus = false; 
            _this.containerElement.setAttribute('data-focus', _this.settings.debug || _this.mouseEnter ? 'true' : 'false');
        });
    
        // Track mouse enter and leave events for focus handling
        ['mouseenter', 'mouseleave'].forEach(eventType => {
            this.containerElement.addEventListener(eventType, () => {
                _this.mouseEnter = eventType === 'mouseenter';
                _this.cancelHide();
                if (!_this.mouseEnter && !_this.focus && !_this.settings.debug) {
                    _this.waitingForHide(1500);
                }
            });
        });
    };

    /**
     * Delays hiding the tag editor container by setting a timeout.
     * After the specified delay, it sets the `data-focus` attribute of the container to `false`,
     * creating a smooth transition effect. If `clearOnHide` is enabled, it also clears the input field.
     *
     * @param {number} waitingTime - The delay time in milliseconds before hiding the container.
     */
    this.waitingForHide = function(waitingTime) {
        this.timeout = setTimeout(() => {
            // Set container focus attribute to false to trigger hiding effect
            _this.containerElement.setAttribute('data-focus', 'false');

            // Clear input field if `clearOnHide` setting is enabled
            if (_this.settings.clearOnHide) {
                _this.inputElement.value = '';
            }
        }, waitingTime);
    };

    /**
     * Cancels the scheduled hiding of the tag editor container.
     * If a timeout exists, it will be cleared to prevent the container from being hidden.
     */
    this.cancelHide = function() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }
    };

    /**
     * Adds a new tag to the tag editor.
     *
     * @param {string} tagText - The text content of the new tag.
     */
    this.addTag = function (tagText, fromUser) {
        const tagContainer = this.containerElement.querySelector('.tags-container');

        // Create a new tag element
        const tag = document.createElement('div');
        tag.classList.add('pico-tag');
        tag.textContent = tagText;

        // Create hidden input field to store the tag value
        const inputHidden = document.createElement('input');
        inputHidden.type = 'hidden';
        inputHidden.name = this.name;
        inputHidden.value = tagText;
        tag.appendChild(inputHidden);

        // Create a remove button for the tag
        const removeSpan = document.createElement('span');
        removeSpan.innerHTML = '&times;'; // "×" symbol for removing tags
        removeSpan.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            tagContainer.removeChild(tag); // Remove tag on click
            _this.containerElement.querySelector('.pico-tag-edit').focus(); // Refocus input
            _this.updateValues();
            // Hide container if no tags remain
            if (!tagContainer.querySelector('.pico-tag')) {
                tagContainer.style.display = 'none';
            }
        });

        tag.appendChild(removeSpan);
        tagContainer.appendChild(tag);
        tagContainer.style.display = ''; // Ensure the tag container is visible
        this.updateValues();
        if(fromUser)
        {
            this.cancelHide();
            this.containerElement.setAttribute('data-focus', 'true');
        }
    };

    /**
     * Updates the stored values from hidden input fields inside `.pico-tag` elements.
     * This function collects all tag values, updates the internal `values` array, 
     * and adjusts the placeholder of the `.pico-tag-edit` input accordingly.
     */
    this.updateValues = function() {
        this.values = [];
        
        // Select all hidden input fields inside `.pico-tag` elements
        let tags = this.containerElement.querySelectorAll('.pico-tag input[type="hidden"]');

        if (typeof tags !== 'undefined') {
            for (let tag of tags) {
                this.values.push(tag.value);
            }
        }

        // Update the placeholder of the `.pico-tag-edit` input
        let inputElement = this.containerElement.querySelector('.pico-tag-edit');
        inputElement.setAttribute('placeholder', this.values.length > 0 ? `[${this.values.join(', ')}]` : this.placeholder);
    };

    // Initialize the editor, apply user settings, and bind events
    this.init(input);
    if (callback) {
        callback(this.inputElement, this.containerElement, this);
    }
    this.addEvents();
    this.setInitialValue();
}
