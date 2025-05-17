/**
 * MultiSelect Class - A custom select input with multiple selection and advanced features.
 * 
 * This class provides an enhanced multi-select dropdown with features like search, select all, 
 * displaying selected items, and custom options. It is designed to work with an HTML <select> element 
 * and replaces the standard select with a custom dropdown UI.
 * 
 * @class MultiSelect
 */
class MultiSelect {

    /**
     * Creates an instance of the MultiSelect.
     * 
     * @constructor
     * @param {HTMLElement|string} element - The DOM element or selector of the <select> element.
     * @param {Object} [options={}] - Optional configuration object.
     * @param {string} [options.placeholder='Select item(s)'] - Placeholder text when no items are selected.
     * @param {string} [options.searchPlaceholder='Search...'] - Placeholder text for the search input.
     * @param {string} [options.labelSelectAll='Select all'] - Label for the "Select All" option.
     * @param {string} [options.labelSelected='selected'] - Label for the selected items.
     * @param {number} [options.max=null] - Maximum number of items that can be selected.
     * @param {boolean} [options.search=true] - Whether to enable search functionality.
     * @param {boolean} [options.selectAll=true] - Whether to enable the "Select All" option.
     * @param {boolean} [options.listAll=true] - Whether to show all selected items in the header.
     * @param {boolean} [options.closeListOnItemSelect=false] - Whether to close the dropdown when an item is selected.
     * @param {string} [options.name=''] - The name attribute for the select element.
     * @param {string} [options.width=''] - Width of the multi-select dropdown.
     * @param {string} [options.height=''] - Height of the multi-select dropdown.
     * @param {string} [options.dropdownWidth=''] - Dropdown width override.
     * @param {string} [options.dropdownHeight=''] - Dropdown height override.
     * @param {Array} [options.data=[]] - Array of data for options, each having a value, text, and other properties.
     * @param {function} [options.onChange] - Callback function triggered on any change in selection.
     * @param {function} [options.onSelect] - Callback function triggered when an option is selected.
     * @param {function} [options.onUnselect] - Callback function triggered when an option is unselected.
     */
    constructor(element, options = {}) {
        let defaults = {
            placeholder: 'Select item(s)',
            searchPlaceholder: 'Search...',
            labelSelectAll: 'Select all',
            labelSelected: 'selected',
            max: null,
            search: true,
            selectAll: true,
            listAll: true,
            closeListOnItemSelect: false,
            name: '',
            width: '',
            height: '',
            dropdownWidth: '',
            dropdownHeight: '',
            data: [],
            onChange: function () { },
            onSelect: function () { },
            onUnselect: function () { }
        };
        this.options = Object.assign(defaults, options);
        this.selectElement = typeof element === 'string' ? document.querySelector(element) : element;
        for (const prop in this.selectElement.dataset) {
            if (this.options[prop] !== undefined) {
                this.options[prop] = this.selectElement.dataset[prop];
            }
        }
        let name = this._getName();
        this.name = name.split('[]').join('');
        if (!this.options.data.length) {
            let options = this.selectElement.querySelectorAll('option');
            for (const option of options) {
                const parentOptgroup = option.closest('optgroup');
                if (parentOptgroup) {
                    const groupLabel = parentOptgroup.label;
                    if (!this.options.data.some(item => item.groupLabel === groupLabel)) {
                        this.options.data.push({ groupLabel: groupLabel, options: [] });
                    }
                    this.options.data.find(item => item.groupLabel === groupLabel).options.push({
                        value: option.value,
                        text: option.innerHTML,
                        selected: option.selected,
                        html: option.getAttribute('data-html')
                    });
                } else {
                    this.options.data.push({
                        value: option.value,
                        text: option.innerHTML,
                        selected: option.selected,
                        html: option.getAttribute('data-html')
                    });
                }
            }            
        }
        this.element = this._template();
        this.selectElement.replaceWith(this.element);
        this._updateSelected();
        this._eventHandlers();
    }

    /**
     * Retrieves the name of the select element or generates a random name if not available.
     * 
     * @private
     * @returns {string} The name of the select element.
     */
    _getName()
    {
        return this.selectElement.getAttribute('name') ? this.selectElement.getAttribute('name') : 'multi-select-' + Math.floor(Math.random() * 1000000);
    }

    /**
     * Generates the HTML template for the multi-select dropdown.
     * 
     * @private
     * @returns {HTMLElement} The generated HTML element representing the multi-select dropdown.
     */
    _template() // NOSONAR
    {
        let optionsHTML = ''; // HTML for optgroup
        let nonSelectAllOptionsHTML = '';  // HTML for non-optgroup options
        let selectAllHTML = ''; // HTML for Select All


        // Loop to process data
        let underOptgroupHTML = ''; // Initialize variable for optgroup options
        for (const group of this.data) {
            if (group.groupLabel) {
                // This is an optgroup
                optionsHTML += `<div class="multi-select-optgroup">
                    <span class="multi-select-optgroup-label">${group.groupLabel}</span>
                    <div class="multi-select-optgroup-options">`;

                // Loop through each option in the optgroup
                for (const option of group.options) {
                    underOptgroupHTML += `
                        <div class="multi-select-option-container">
                            <div class="multi-select-option${this._getSelectedClass(option.value, group.selected)}" data-value="${option.value}">
                                <span class="multi-select-option-radio"></span>
                                <span class="multi-select-option-text">${this._getText(option)}</span>
                            </div>
                        </div>
                    `;
                }
                optionsHTML += underOptgroupHTML + `</div></div>`; // End the optgroup
                underOptgroupHTML = ''; // Reset for the next option
            } else {
                // Options outside the optgroup
                nonSelectAllOptionsHTML += `
                    <div class="multi-select-option-container">
                        <div class="multi-select-option${this._getSelectedClass(group.value, group.selected)}" data-value="${group.value}">
                            <span class="multi-select-option-radio"></span>
                            <span class="multi-select-option-text">${this._getText(group)}</span>
                        </div>
                    </div>
                `;
            }
        }
        let selectedValues = [];
        if(this.selectedValues.length == 0)
        {
            for (const group of this.data) {
                if (group.selected) {
                    selectedValues.push(group.value)
                }
            }
        }
        else
        {
            selectedValues = this.selectedValues;
        }

        // If 'Select All' is enabled, add HTML for Select All
        if (this.options.selectAll === true || this.options.selectAll === 'true') {
            selectAllHTML = `<div class="multi-select-all">
                <span class="multi-select-option-radio"></span>
                <span class="multi-select-option-text">${this.options.labelSelectAll}</span>
            </div>`;
        }

        // Combine all HTML into one template

        let hidenInput = selectedValues.map(value => `<input type="hidden" name="${this.name}[]" value="${value}">`).join('\r\n');
        let template = `
            <div class="multi-select ${this.name}"${this.selectElement.id ? ' id="' + this.selectElement.id + '"' : ''} style="${this._getDimension()}">
                <div class="multi-select-hidden-input-area" style="display:none;">
                    ${hidenInput}
                </div>
                <div class="multi-select-header" style="${this._getDimension()}">
                    <span class="multi-select-header-max">${this.options.max ? this.selectedValues.length + '/' + this.options.max : ''}</span>
                    <span class="multi-select-header-placeholder">${this.placeholder}</span>
                </div>
                <div class="multi-select-options" style="${this.options.dropdownWidth ? 'width:' + this.options.dropdownWidth + ';' : ''}${this.options.dropdownHeight ? 'height:' + this.options.dropdownHeight + ';' : ''}">
                    ${this.options.search === true || this.options.search === 'true' ? `<input type="text" class="multi-select-search" placeholder="${this.options.searchPlaceholder}">` : ''}
                    ${selectAllHTML} 
                    <div class="multi-select-options-container">
                    <div class="multi-select-options-list">${nonSelectAllOptionsHTML}</div> 
                    <div class="multi-select-optgroup-list">${optionsHTML}</div> 
                    </div>
                </div>
            </div>
        `;

        let element = document.createElement('div');
        element.innerHTML = template;
        element.classList.add('multi-select-container');
        return element;
    }

    /**
     * Retrieves the text for an option, prioritizing the HTML value if available.
     * 
     * @private
     * @param {Object} option - The option data object.
     * @returns {string} The text of the option.
     */
    _getText(option)
    {
        return `${option.html ? option.html : option.text}`;
    }

    /**
     * Returns the appropriate class based on whether an option is selected.
     * 
     * @private
     * @param {string} value - The value of the option.
     * @param {Boolean} selected - The value of the option.
     * @returns {string} The class to be applied to the option.
     */
    _getSelectedClass(value, selected)
    {
        return `${this.selectedValues.includes(value) || selected ? ' multi-select-selected' : ''}`;
    }

    /**
     * Retrieves the dimension styles (width and height) for the multi-select dropdown.
     * 
     * @private
     * @returns {string} The CSS style string for the dimensions.
     */
    _getDimension()
    {
        return `${this.width ? 'width:' + this.width + ';' : ''}${this.height ? 'height:' + this.height + ';' : ''}`;
    }

    /**
     * Removes the hidden input element associated with the given option.
     * 
     * This method looks for an `input` element with a `value` attribute matching the 
     * `data-value` of the provided `option`. If such an input element exists, it is 
     * removed from the DOM. This is typically used to clean up the hidden inputs 
     * representing selected values in a multi-select component.
     * 
     * @param {HTMLElement} option - The option element whose associated hidden input should be removed.
     * @returns {void} This method does not return any value.
     */
    _updateOption(headerElement, option, selected)
    {
        if (this.options.listAll === false || this.options.listAll === 'false') {
            let elem = this.element.querySelector('.multi-select-header-option');
            if(elem != null)
            {
                elem.parentNode.removeChild(elem);
            }
            headerElement.insertAdjacentHTML('afterbegin', `<span class="multi-select-header-option">${this.selectedValues.length} ${this.options.labelSelected}</span>`);
        }
        if (!this.element.querySelector('.multi-select-header-placeholder')) {
            headerElement.insertAdjacentHTML('beforeend', `<span class="multi-select-header-placeholder">${this.placeholder}</span>`);
        }
        if (this.options.max) {
            this.element.querySelector('.multi-select-header-max').innerHTML = this.selectedValues.length + '/' + this.options.max;
        }
        if (this.options.search === true || this.options.search === 'true') {
            this.element.querySelector('.multi-select-search').value = '';
        }
        this.element.querySelectorAll('.multi-select-option').forEach(option => option.style.display = 'flex');
        if (this.options.closeListOnItemSelect === true || this.options.closeListOnItemSelect === 'true') {
            headerElement.classList.remove('multi-select-header-active');
        }
        this.options.onChange(option.dataset.value, option.querySelector('.multi-select-option-text').innerHTML, option);
        if (selected) {
            this.options.onSelect(option.dataset.value, option.querySelector('.multi-select-option-text').innerHTML, option);
        } else {
            this.options.onUnselect(option.dataset.value, option.querySelector('.multi-select-option-text').innerHTML, option);
        }
    }

    /**
     * Sets up event handlers for various interactions with the multi-select dropdown.
     * 
     * @private
     */
    _eventHandlers() {
        let headerElement = this.element.querySelector('.multi-select-header');
        this.element.querySelectorAll('.multi-select-option').forEach(option => {
            
            option.onclick = () => {
                let selected = true;
                
                if (!option.classList.contains('multi-select-selected')) {
                    if (this._isMax()) {
                        return;
                    }
                    option.classList.add('multi-select-selected');
                    if (this._isListAll()) {
                        if (this.element.querySelector('.multi-select-header-option')) {
                            let opt = Array.from(this.element.querySelectorAll('.multi-select-header-option')).pop();
                            opt.insertAdjacentHTML('afterend', `<span class="multi-select-header-option" data-value="${option.dataset.value}">${option.querySelector('.multi-select-option-text').innerHTML}</span>`);
                        } else {
                            headerElement.insertAdjacentHTML('afterbegin', `<span class="multi-select-header-option" data-value="${option.dataset.value}">${option.querySelector('.multi-select-option-text').innerHTML}</span>`);
                        }
                    }
                    this.element.querySelector('.multi-select .multi-select-hidden-input-area').insertAdjacentHTML('beforeend', `<input type="hidden" name="${this.name}[]" value="${option.dataset.value}">`);
                    try {
                        this.data.filter(data => data.value == option.dataset.value)[0].selected = true;
                    }
                    catch (e) {
                        // Do nothing
                    }
                } else {
                    option.classList.remove('multi-select-selected');
                    this.element.querySelectorAll('.multi-select-header-option').forEach(headerOption => headerOption.dataset.value == option.dataset.value ? headerOption.parentNode.removeChild(headerOption) : '');

                    this._removeHiddenInput(option);
                    
                    try {
                        this.data.filter(data => data.value == option.dataset.value)[0].selected = false;
                    }
                    catch (e) {

                    }
                    selected = false;
                }
                this._updateOption(headerElement, option, selected);
            };
        });

        headerElement.onclick = () => headerElement.classList.toggle('multi-select-header-active');

        if (this.options.search === true || this.options.search === 'true') {
            let search = this.element.querySelector('.multi-select-search');
            search.oninput = () => {
                this.element.querySelectorAll('.multi-select-option').forEach(option => {
                    option.parentNode.style.display = option.querySelector('.multi-select-option-text').innerHTML.toLowerCase().indexOf(search.value.toLowerCase()) > -1 ? 'flex' : 'none';
                });
            };
        }

        if (this.options.selectAll === true || this.options.selectAll === 'true') {
            let selectAllButton = this.element.querySelector('.multi-select-all');
            selectAllButton.onclick = () => {
                let allSelected = selectAllButton.classList.contains('multi-select-selected');

                // remove all hidden input
                selectAllButton.closest('.multi-select')
                const hiddenInputs = selectAllButton.closest('.multi-select').querySelectorAll('input[type="hidden"]');
                if(hiddenInputs != null)
                {
                    hiddenInputs.forEach(input => input.parentNode.removeChild(input));
                }
                
                this.element.querySelectorAll('.multi-select-option').forEach((option) => {
                    let val = this.data.find(data => data.value == option.dataset.value); // NOSONAR
                    if(!allSelected)
                    {
                        option.classList.remove('multi-select-selected');
                    }
                    option.click();
                });
                selectAllButton.classList.toggle('multi-select-selected');
            };
        }

        if (this.selectElement.id && document.querySelector('label[for="' + this.selectElement.id + '"]')) {
            document.querySelector('label[for="' + this.selectElement.id + '"]').onclick = () => {
                headerElement.classList.toggle('multi-select-header-active');
            };
        }

        document.addEventListener('click', event => {
            if (!event.target.closest('.' + this.name) && !event.target.closest('label[for="' + this.selectElement.id + '"]')) {
                headerElement.classList.remove('multi-select-header-active');
            }
        });
    }

    /**
     * Removes the hidden input element associated with the given option.
     * 
     * This method looks for an `input` element with a `value` attribute matching the 
     * `data-value` of the provided `option`. If such an input element exists, it is 
     * removed from the DOM. This is typically used to clean up the hidden inputs 
     * representing selected values in a multi-select component.
     * 
     * @param {HTMLElement} option - The option element whose associated hidden input should be removed.
     * @returns {void} This method does not return any value.
     */
    _removeHiddenInput(option)
    {
        let elem = this.element.querySelector(`input[value="${option.dataset.value}"]`);
        if(elem != null)
        {
            elem.parentNode.removeChild(elem);
        }
    }

    /**
     * Checks if the 'listAll' option is enabled.
     * 
     * This method checks if the `listAll` option in the configuration is set to `true` or 
     * the string `'true'`. It is used to determine whether all options should be displayed
     * in the multi-select dropdown, even if they are not selected.
     * 
     * @returns {boolean} `true` if the `listAll` option is enabled, `false` otherwise.
     */
    _isListAll()
    {
        return this.options.listAll === true || this.options.listAll === 'true';
    }

    /**
     * Checks if the maximum number of selected items has been reached.
     * 
     * This method compares the current number of selected values with the 
     * `max` value defined in the options. If the number of selected items 
     * is greater than or equal to `max`, it returns `true`; otherwise, it returns `false`.
     * 
     * @returns {boolean} `true` if the number of selected items is equal to or exceeds the maximum limit, `false` otherwise.
     */
    _isMax()
    {
        return this.options.max && this.selectedValues.length >= this.options.max;
    }

    /**
     * Updates the selected options and refreshes the display in the header.
     * 
     * @private
     */
    _updateSelected() {
        const headerElement = this.element.querySelector('.multi-select-header');

        // If listAll is true, we update the selected options
        if (this.options.listAll === true || this.options.listAll === 'true') {
            // Update selected options for the header
            this.element.querySelectorAll('.multi-select-option').forEach(option => {
                if (option.classList.contains('multi-select-selected')) {
                    // Add selected options to header (it could be multiple)
                    this.element.querySelector('.multi-select-header').insertAdjacentHTML('afterbegin', `<span class="multi-select-header-option" data-value="${option.dataset.value}">${option.querySelector('.multi-select-option-text').innerHTML}</span>`);
                }
            });
        } else if (this.selectedValues.length > 0) {
            // If not showing all options, just show how many options are selected
            // Add the number of selected options to the header
            this.element.querySelector('.multi-select-header').insertAdjacentHTML('afterbegin', `<span class="multi-select-header-option">${this.selectedValues.length} ${this.options.labelSelected}</span>`);
        
        }

        // Remove all placeholders (if there are multiple) before adding the new one
        this.element.querySelectorAll('.multi-select-header-placeholder').forEach(placeholder => {
            placeholder.parentNode.removeChild(placeholder);
        });

        // Add a new placeholder if there are no selected options
        if (!this.element.querySelector('.multi-select-header-option')) {
            headerElement.insertAdjacentHTML('afterbegin', `<span class="multi-select-header-placeholder">${this.placeholder}</span>`);
        }
    }

    /**
     * Returns an array of selected values.
     * 
     * @getter
     * @returns {Array} Array of selected values from the options.
     */
     get selectedValues() {
        return this.data.flatMap(item => item.options ? item.options.filter(option => option.selected).map(option => option.value) : []);
    }

    /**
     * Returns an array of selected items (options).
     * 
     * @getter
     * @returns {Array} Array of selected option objects.
     */
    get selectedItems() {
        return this.data.flatMap(item => item.options ? item.options.filter(option => option.selected) : []);
    }

    /**
     * Sets the data for the multi-select options.
     * 
     * @setter
     * @param {Array} value - The new data array to set. This array should contain objects with options.
     */
    set data(value) {
        this.options.data = value;
    }

    /**
     * Returns the current data of the multi-select options.
     * 
     * @getter
     * @returns {Array} The current options data.
     */
    get data() {
        return this.options.data;
    }

    /**
     * Sets the select element (original <select> element) that is replaced by the custom multi-select dropdown.
     * 
     * @setter
     * @param {HTMLElement} value - The new select element.
     */
    set selectElement(value) {
        this.options.selectElement = value;
    }

    /**
     * Returns the current select element that is used for the multi-select.
     * 
     * @getter
     * @returns {HTMLElement} The current select element.
     */
    get selectElement() {
        return this.options.selectElement;
    }

    /**
     * Sets the custom multi-select element (the rendered dropdown).
     * 
     * @setter
     * @param {HTMLElement} value - The new multi-select dropdown element.
     */
    set element(value) {
        this.options.element = value;
    }

    /**
     * Returns the current custom multi-select dropdown element.
     * 
     * @getter
     * @returns {HTMLElement} The custom multi-select dropdown element.
     */
    get element() {
        return this.options.element;
    }

    /**
     * Sets the placeholder text for the multi-select header when no items are selected.
     * 
     * @setter
     * @param {string} value - The new placeholder text.
     */
    set placeholder(value) {
        this.options.placeholder = value;
    }

    /**
     * Returns the current placeholder text for the multi-select header.
     * 
     * @getter
     * @returns {string} The current placeholder text.
     */
    get placeholder() {
        return this.options.placeholder;
    }

    /**
     * Sets the name attribute for the multi-select component.
     * 
     * @setter
     * @param {string} value - The new name for the multi-select component.
     */
    set name(value) {
        this.options.name = value;
    }

    /**
     * Returns the current name attribute of the multi-select component.
     * 
     * @getter
     * @returns {string} The current name attribute.
     */
    get name() {
        return this.options.name;
    }

    /**
     * Sets the width for the multi-select dropdown.
     * 
     * @setter
     * @param {string} value - The new width for the multi-select dropdown.
     */
    set width(value) {
        this.options.width = value;
    }

    /**
     * Returns the current width of the multi-select dropdown.
     * 
     * @getter
     * @returns {string} The current width of the multi-select dropdown.
     */
    get width() {
        return this.options.width;
    }

    /**
     * Sets the height for the multi-select dropdown.
     * 
     * @setter
     * @param {string} value - The new height for the multi-select dropdown.
     */
    set height(value) {
        this.options.height = value;
    }

    /**
     * Returns the current height of the multi-select dropdown.
     * 
     * @getter
     * @returns {string} The current height of the multi-select dropdown.
     */
    get height() {
        return this.options.height;
    }

    /**
     * Sets the placeholder text for the search input inside the multi-select dropdown.
     * 
     * @setter
     * @param {string} value - The new placeholder text for the search input.
     */
    set searchPlaceholder(value) {
        this.options.searchPlaceholder = value;
    }

    /**
     * Returns the current placeholder text for the search input inside the multi-select dropdown.
     * 
     * @getter
     * @returns {string} The current placeholder text for the search input.
     */
    get searchPlaceholder() {
        return this.options.searchPlaceholder;
    }

    /**
     * Sets the label for the "Select All" option in the multi-select dropdown.
     * 
     * @setter
     * @param {string} value - The new label for the "Select All" option.
     */
    set labelSelectAll(value) {
        this.options.labelSelectAll = value;
    }

    /**
     * Returns the current label for the "Select All" option.
     * 
     * @getter
     * @returns {string} The current label for the "Select All" option.
     */
    get labelSelectAll() {
        return this.options.labelSelectAll;
    }

    /**
     * Sets the label for the selected options in the multi-select dropdown.
     * 
     * @setter
     * @param {string} value - The new label for selected options.
     */
    set labelSelected(value) {
        this.options.labelSelected = value;
    }

    /**
     * Returns the current label for the selected options.
     * 
     * @getter
     * @returns {string} The current label for the selected options.
     */
    get labelSelected() {
        return this.options.labelSelected;
    }
    
}
