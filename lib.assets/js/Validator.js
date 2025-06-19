/**
 * ValidationBuilder is responsible for managing validation rules for form fields.
 * It provides methods to add, edit, delete, and render validations for each field,
 * as well as updating the UI and JSON output to reflect the current validation state.
 */
class ValidationBuilder {
    /**
     * Initializes a new instance of the ValidationBuilder class, which handles
     * the creation, editing, and management of validation rules for form fields.
     *
     * This constructor binds modal elements, event handlers, and initializes
     * the validation schema and dropdowns used for adding validation rules.
     *
     * @param {string} baseSelector - CSS selector for the main modal that allows field-level validation management.
     * @param {string} modalSelector - CSS selector for the "Add/Edit Validation" modal used to configure individual rules.
     * @param {string} jsonOutputSelector - CSS selector for the element where the current validation definition (as JSON) will be rendered.
     */
    constructor(baseSelector, modalSelector, jsonOutputSelector, rowSelector) {
        this.baseSelector = baseSelector;
        this.modalSelector = modalSelector;
        this.jsonOutputSelector = jsonOutputSelector;
        this.rowSelector = rowSelector;
        this.baseElement = document.querySelector(baseSelector);
        this.modalElement = document.querySelector(modalSelector);
        this.currentField = null;
        this.currentIndex = null;
        this.validationsPerField = {};
        this.propsContainer = this.modalElement.querySelector(".validation-props");
        this.applyInsertCheckbox = this.modalElement.querySelector(".apply-insert");
        this.applyUpdateCheckbox = this.modalElement.querySelector(".apply-update");
        this.schema = this.initSchema();
        this.bindFieldButtons();
        this.initValidationSelector();
    }

    /**
     * Closes the main validation modal.
     * 
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    closeValidationModal()
    {
        $(this.baseSelector).modal('hide');
        return this;
    }

    /**
     * Closes the modal used for adding or editing a validation rule.
     *
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    closeAddValidationModal()
    {
        new bootstrap.Modal(this.modalElement).hide();
        $(this.modalSelector).modal('hide');
        return this;
    }

    /**
     * Initializes the validation schema mapping validation types to their required properties.
     *
     * @returns {Object} The schema object.
     */
    initSchema() {
        return {
            Required: ["message"],
            NotEmpty: ["message"],
            NotBlank: ["message"],
            Min: ["value", "message"],
            Max: ["value", "message"],
            DecimalMin: ["value", "message"],
            DecimalMax: ["value", "message"],
            Range: ["min", "max", "message"],
            Size: ["min", "max", "message"],
            Length: ["min", "max", "message"],
            Digits: ["integer", "fraction", "message"],
            Positive: ["message"],
            PositiveOrZero: ["message"],
            Negative: ["message"],
            NegativeOrZero: ["message"],
            Pattern: ["regexp", "message"],
            Email: ["message"],
            Url: ["message"],
            Ip: ["message"],
            DateFormat: ["format", "message"],
            Phone: ["message"],
            NoHtml: ["message"],
            Past: ["message"],
            Future: ["message"],
            PastOrPresent: ["message"],
            FutureOrPresent: ["message"],
            AssertTrue: ["message"],

            Alpha: ["message"],
            AlphaNumeric: ["message"],
            StartsWith: ["prefix", "caseSensitive", "message"],
            EndsWith: ["suffix", "caseSensitive", "message"],
            Contains: ["substring", "caseSensitive", "message"],
            BeforeDate: ["date", "message"],
            AfterDate: ["date", "message"],

            Enum: ["allowedValues", "caseSensitive", "message"]
        };
    }

    /**
     * Binds click events to all "Add Validation" buttons for each field.
     *
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    bindFieldButtons() {
        let _this = this;
        console.log(_this.baseSelector)
        $(document).on('click', _this.rowSelector + ' .validation-button', function(e){
            let tr = $(this).closest(".validation-item")[0];
            _this.currentField = tr.dataset.fieldName;
            $('.field-to-validate').text(tr.dataset.fieldName);
            if(_this.applyInsertCheckbox)
            {
                _this.applyInsertCheckbox.disabled = true;
            }
            if(_this.applyUpdateCheckbox)
            {
                _this.applyUpdateCheckbox.disabled = true;
            }
            _this.renderValidations();
            $(_this.baseSelector).modal('show');
        });
        $(document).on('click', this.baseSelector + ' .add-validation', function(e){
            _this.currentIndex = null;
            _this.modalElement.querySelector('.validation-type').value = "";
            _this.propsContainer.innerHTML = "";
            // Reset checkboxes when opening for new validation
            if(_this.applyInsertCheckbox)
            {
                _this.applyInsertCheckbox.disabled = true;
                _this.applyInsertCheckbox.checked = false;
            }
            if(_this.applyUpdateCheckbox)
            {
                _this.applyUpdateCheckbox.disabled = true;
                _this.applyUpdateCheckbox.checked = false;
            }

            _this.updateDropDown();
            $(_this.modalSelector).modal('show');
        });
        return this;
    }

    /**
     * Updates the validation type dropdown in the modal by disabling 
     * already-used validation types for the current field,
     * except the specified mandatory one (e.g., "Required").
     *
     * @param {string} mandatoryTrue - A validation type that should remain selectable.
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    updateDropDown(mandatoryTrue)
    {
        const validation = this.validationsPerField[this.currentField];
        let validationTypes = [];
        if(validation !== 'undefined' && validation)
        {
            validation.forEach((val) => {
                validationTypes.push(val.type);
            });
        }
        const select = this.modalElement.querySelector('.validation-type');
        select.querySelectorAll('option').forEach((option) => {
            let value = option.getAttribute('value');
            let disabled = value != mandatoryTrue && validationTypes.includes(value);
            option.disabled = disabled;
        });
        return this;
    }

    /**
     * Populates the validation type dropdown with options based on the schema.
     *
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    initValidationSelector() {
        const select = this.modalElement.querySelector('.validation-type');
        for (const type in this.schema) {
            const opt = document.createElement("option");
            opt.value = type;
            opt.textContent = type;
            select.appendChild(opt);
        }
        select.addEventListener("change", () => this.renderPropsInputs());
        return this;
    }

    /**
     * Renders input fields for a validation type's properties.
     * Optionally pre-fills values if editing an existing rule.
     *
     * @param {Object} [validation={}] - The validation object to pre-fill.
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    renderPropsInputs(validation = {}) {
        const selected = this.modalElement.querySelector('.validation-type').value;
        this.propsContainer.innerHTML = "";
        (this.schema[selected] || []).forEach(prop => {
            const div = document.createElement("div");
            div.className = "mb-2";
            div.innerHTML = `<label class="form-label">${prop}</label><input type="text" class="form-control" data-prop="${prop}" placeholder="${prop}" value="${validation[prop] || ''}">`;
            this.propsContainer.appendChild(div);
        });

        // Set checkbox states if validation object is provided (for editing)
        if(this.applyInsertCheckbox)
        {
            this.applyInsertCheckbox.disabled = false;
            this.applyInsertCheckbox.checked = validation.applyInsert === true;
        }
        if(this.applyUpdateCheckbox)
        {
            this.applyUpdateCheckbox.disabled = false;
            this.applyUpdateCheckbox.checked = validation.applyUpdate === true;
        }

        // Update max length
        this.autopopulateMinMax(selected);
        return this;
    }

    /**
     * Automatically populates the "min" and "max" input fields based on the selected constraint type
     * (either "Size" or "Length") and the current field's maximum length defined in the table structure.
     *
     * @param {string} selected - The selected constraint type (e.g., "Size", "Length").
     */
    autopopulateMinMax(selected)
    {
        let _this = this;
        if(selected == 'Size' || selected == 'Length')
        {
            currentTableStructure.fields.forEach((field) => {
                if(field.column_name == _this.currentField && field.maximum_length)
                {
                    this.propsContainer.querySelector('input[data-prop="min"').value = 0;
                    this.propsContainer.querySelector('input[data-prop="max"').value = field.maximum_length;
                }
            })
            
        }
    }

    /**
     * Saves the currently configured validation rule for the selected field.
     * Determines whether to add a new rule or update an existing one.
     *
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    saveValidation() {
        const type = this.modalElement.querySelector('.validation-type').value;
        if (!type || !this.currentField) return;

        const props = {};
        this.propsContainer.querySelectorAll("input").forEach(input => {
            props[input.dataset.prop] = input.value;
        });

        const validation = {
            type,
            ...props,
            applyInsert: this.applyInsertCheckbox.checked,
            applyUpdate: this.applyUpdateCheckbox.checked
        };

        if (!this.validationsPerField[this.currentField]) {
            this.validationsPerField[this.currentField] = [];
        }

        if (this.currentIndex !== null) {
            this.validationsPerField[this.currentField][this.currentIndex] = validation;
        } else {
            this.validationsPerField[this.currentField].push(validation);
        }

        const container = this.baseElement.querySelector(".field-validations-list");
        let data = this.validationsPerField[this.currentField] || [];
        
        this.renderFieldValidations(container, this.currentField, data)
        $(this.modalSelector).modal('hide');
        return this;
    }

    /**
     * Saves validation status for all fields in the table.
     * 
     * This method collects validated fields from the current `validationsPerField`
     * object and iterates over each row in the table body. It updates the
     * `data-has-validation` attribute of each row based on whether the field
     * has any validation rules associated with it.
     *
     * @param {boolean} closeModal - Whether or not the modal should be closed after saving (passed to getValidation).
     */
    saveAllValidation(closeModal) {
        validation = this.getValidation(closeModal); // NOSONAR
        return this.markValidation();
    }

    /**
     * Marks each table row with a validation status.
     * 
     * This method iterates through all rows in the table body, compares each row's
     * associated field name with the list of validated fields, and updates the
     * `data-has-validation` attribute accordingly.
     * 
     * This can be used to visually or logically differentiate which fields
     * have validation rules defined in `validationsPerField`.
     *
     * @returns {this} Returns the current instance for method chaining.
     */
    markValidation()
    {
        const validated = Object.keys(this.validationsPerField);

        const rows = document.querySelectorAll(this.rowSelector);
        rows.forEach((row) => {
            const field = row.dataset.fieldName;
            row.dataset.hasValidation = validated.includes(field) ? 'true' : 'false';
        });
        return this;
    }

    /**
     * Renders validations for the currently selected field.
     *
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    renderValidations() {
        const field = this.currentField;
        const container = this.baseElement.querySelector(".field-validations-list");
        let data = this.validationsPerField[field] || [];
        return this.renderFieldValidations(container, field, data);
    }

    /**
     * Renders a list of validations for a specific field inside a container element.
     *
     * @param {HTMLElement} container - The container element.
     * @param {string} field - The field name.
     * @param {Object[]} data - The array of validation objects for the field.
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    renderFieldValidations(container, field, data) {
        container.innerHTML = "";
        (data || []).forEach((val, idx) => {
            const propsStr = Object.entries(val)
                .filter(([k]) => k !== "type" && k !== "applyInsert" && k !== "applyUpdate") // Exclude these keys
                .map(([k, v]) => `${k}="${v}"`).join(", ");

            let applyCheckboxes = '';
            applyCheckboxes += `<div class="validation-targets">`;
            applyCheckboxes += `<div class="form-check form-check-inline">`;
            applyCheckboxes += `<input class="form-check-input " type="checkbox" disabled ${val.applyInsert ? 'checked' : ''}>`;
            applyCheckboxes += `<label class="form-check-label">Insert</label>`;
            applyCheckboxes += `</div>`;
            applyCheckboxes += `<div class="form-check form-check-inline">`;
            applyCheckboxes += `<input class="form-check-input" type="checkbox" disabled ${val.applyUpdate ? 'checked' : ''}>`;
            applyCheckboxes += `<label class="form-check-label">Update</label>`;
            applyCheckboxes += `</div>`;
            applyCheckboxes += `</div>`;

            const div = document.createElement("div");
            div.className = "field-validations d-flex justify-content-between align-items-center mb-2";
            div.innerHTML = `
            <div>
              <span>@${val.type}(${propsStr})</span>
              ${applyCheckboxes}
            </div>
            <div>
              <button type="button" class="btn btn-sm btn-primary me-1" onclick="validatorBuilder.editValidation('${field}', ${idx})"><i class="fa-solid fa-pencil"></i></button>
              <button type="button" class="btn btn-sm btn-danger" onclick="validatorBuilder.deleteValidation('${field}', ${idx})"><i class="fa-solid fa-trash-can"></i></button>
            </div>`;
            container.appendChild(div);
        });
        document.querySelector(this.jsonOutputSelector).textContent = JSON.stringify(this.validationsPerField, null, 2);
        return this;
    }

    /**
     * Deletes a validation rule at the specified index for a given field.
     *
     * @param {string} field - The field name.
     * @param {number} index - The index of the validation to delete.
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    deleteValidation(field, index) {
        this.validationsPerField[field].splice(index, 1);
        if (this.validationsPerField[field].length === 0) 
        {
            delete this.validationsPerField[field];
        }
        this.renderValidations();
        return this;
    }

    /**
     * Opens the modal to edit an existing validation rule for a given field and index.
     *
     * @param {string} field - The field name.
     * @param {number} index - The index of the validation to edit.
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    editValidation(field, index) {
        this.currentField = field;
        this.currentIndex = index;
        const validation = this.validationsPerField[field][index];
        this.modalElement.querySelector('.validation-type').value = validation.type;
        this.renderPropsInputs(validation); // Pass the validation object to pre-fill props and checkboxes
        this.updateDropDown(validation.type);
        $(this.modalSelector).modal('show');
    }

    /**
     * Retrieves the validation data for all fields.
     * Optionally closes the main validation modal.
     *
     * @param {boolean} [closeModal=false] - Whether to close the modal after retrieval.
     * @returns {Object} The validation data object.
     */
    getValidation(closeModal)
    {
        if(closeModal)
        {
            $(this.baseSelector).modal('hide');
        }
        return this.validationsPerField;
    }

    /**
     * Sets the validation data to be used in the builder.
     *
     * @param {Object} validation - The object representing all validation rules per field.
     * @returns {ValidationBuilder} The current instance for chaining.
     */
    setValidation(validation)
    {
        this.validationsPerField = validation;
        return this;
    }
}