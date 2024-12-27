/**
 * Represents a column in a database table.
 * 
 * The Column class is used to define the properties of a column in a database table. 
 * This includes the column's name, type, length, nullable status, default value, 
 * primary key status, auto-increment behavior, and valid values for ENUM or SET types.
 * 
 * @class
 */
class Column {
    /**
     * Creates an instance of the Column class.
     * 
     * @param {string} name - The name of the column.
     * @param {string} [type="VARCHAR"] - The data type of the column (e.g., "VARCHAR", "INT", "ENUM", etc.).
     * @param {string} [length=""] - The length of the column for types like VARCHAR (optional).
     * @param {boolean} [nullable=false] - Whether the column can be NULL (default is false).
     * @param {string} [defaultValue=""] - The default value for the column (optional).
     * @param {boolean} [primaryKey=false] - Whether the column is a primary key (default is false).
     * @param {boolean} [autoIncrement=false] - Whether the column auto-increments (default is false).
     * @param {string} [values=""] - The valid values for ENUM or SET types, or the range of values for types like DECIMAL, NUMERIC, FLOAT, and DOUBLE (optional, comma-separated).
     */
    constructor(name, type = "VARCHAR", length = "", nullable = false, defaultValue = "", primaryKey = false, autoIncrement = false, values = "") //NOSONAR
    {
        this.name = name;
        this.type = type;
        this.length = length;
        this.nullable = nullable;
        this.default = defaultValue;
        this.primaryKey = primaryKey;
        this.autoIncrement = autoIncrement;
        this.values = values;
    }

    /**
     * Converts the column definition into a valid SQL column definition string.
     * 
     * This method generates the SQL column definition based on the column's properties such as:
     * - data type (e.g., VARCHAR, INT, ENUM, etc.)
     * - nullable status
     * - primary key status
     * - auto-increment behavior
     * - default value
     * - valid values for ENUM/SET types or range values for DECIMAL, NUMERIC, FLOAT, and DOUBLE.
     * 
     * @returns {string} The SQL column definition.
     */
    toSQL() // NOSONAR
    {
        let withValueTypes = ['ENUM', 'SET'];
        let withRangeTypes = ['NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT'];
        let numericTypes = ['BIGINT', 'INT', 'MEDIUMINT', 'SMALLINT', 'TINYINT', 'NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT'];
        let withLengthTypes = [
            'VARCHAR', 'CHAR', 
            'VARBINARY', 'BINARY',
            'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT',
            'BIT'
        ];

        let columnDef = "";
        
        if (this.hasValue(withValueTypes)) 
        {
            // Handle ENUM and SET types
            let enumList = this.values.split(',').map(val => `'${val.trim()}'`).join(', ');
            columnDef = `${this.name} ${this.type}(${enumList})`;
        }     
        else if (this.hasRange(withRangeTypes)) 
        {
            // Handle range types like NUMERIC, DECIMAL, DOUBLE, FLOAT
            let rangeList = this.values.split(',').map(val => val.trim());

            // Filter only integer values, remove non-integer values
            rangeList = rangeList.filter(val => Number.isInteger(parseFloat(val)));

            // Join the valid integer values back into a range string
            let rangeString = rangeList.join(', ');

            // Set the column definition
            if (rangeList.length < 2) {
                // If no valid integer values are present, don't set a range
                columnDef = `${this.name} ${this.type}`;
            } else {
                // If there are valid integer values, include the range in the column definition
                columnDef = `${this.name} ${this.type}(${rangeString})`;
            }
        } 
        else if (this.hasLength(withLengthTypes)) 
        {
            // Handle types that support length, like VARCHAR, CHAR, etc.
            columnDef = `${this.name} ${this.type}(${this.length})`;
        }
        else
        {
            columnDef = `${this.name} ${this.type}`;
        }
        
        if (!this.primaryKey) {
            // Nullable
            columnDef += this.nullable ? " NULL" : " NOT NULL";
        } else {
            columnDef += " NOT NULL PRIMARY KEY";
        }
        // Auto increment logic
        if (this.autoIncrement) {
            columnDef += " AUTO_INCREMENT";
        }
        // Default value logic
        if (this.hasDefault()) {
            if(this.isTypeBoolean(this.type, this.length)) {
                columnDef += ` DEFAULT ${this.toBoolean(this.default)}`; // No quotes for boolean values
            } else if (numericTypes.includes(this.type) && !isNaN(this.default)) {
                columnDef += ` DEFAULT ${this.default}`; // No quotes for numeric values
            } else {
                columnDef += ` DEFAULT '${this.default}'`; // Default is a string, so use quotes
            }
        }
        return columnDef;
    }

    /**
     * Converts a given value to a boolean-like string representation.
     * 
     * This function evaluates the input value and converts it to either 'TRUE' or 'FALSE'.
     * It interprets values such as:
     * - Any string containing 'false' (case-insensitive) will return 'FALSE'.
     * - Any non-zero integer or string 'true' (case-insensitive) will return 'TRUE'.
     * - Any other value will return 'FALSE'.
     *
     * @param {string} value - The value to be converted to a boolean-like string.
     * @returns {string} 'TRUE' or 'FALSE' based on the value.
     */
    toBoolean(value) {
        if (value.toLowerCase().indexOf('false') !== -1) {
            return 'FALSE';
        }
        return (parseInt(value) !== 0 || value.toLowerCase() === 'true') ? 'TRUE' : 'FALSE';
    }

    /**
     * Fixes the default value based on the column's type and length.
     * 
     * This method adjusts the default value depending on the column's data type:
     * - For BOOLEAN types, converts values to 'true' or 'false'.
     * - For text types, escapes single quotes.
     * - For numeric types (INTEGER and FLOAT), parses the value accordingly.
     * 
     * @param {string} defaultValue - The default value to fix.
     * @param {string} type - The type of the column.
     * @param {string} length - The length of the column.
     * @returns {string|number} The fixed default value.
     */
    fixDefaultValue(defaultValue, type, length) {
        let result = defaultValue;
    
        if (this.isTypeBoolean(type, length)) {
            result = (defaultValue !== 0 && defaultValue.toString().toLowerCase() === 'true') ? 'true' : 'false';
        } else if (this.isNativeValue(defaultValue)) {
            result = defaultValue;
        } else if (this.isTypeText(type)) {
            result = `'${defaultValue.replace(/'/g, "\\'")}'`;
        } else if (this.isTypeInteger(type)) {
            result = parseInt(defaultValue.replace(/[^\d]/g, ''), 10);
        } else if (this.isTypeFloat(type)) {
            result = parseFloat(defaultValue.replace(/[^\d.]/g, ''));
        }
    
        return result;
    }
    
    /**
     * Checks if the given type is a boolean type in MySQL.
     * 
     * @param {string} type - The type to check.
     * @param {string} length - The length of the column (used for TINYINT with length 1).
     * @returns {boolean} True if the type is BOOLEAN, BIT, or TINYINT(1), false otherwise.
     */
    isTypeBoolean(type, length) {
        return type.toLowerCase() === 'boolean' || type.toLowerCase() === 'bool' || type.toLowerCase() === 'bit' || (type.toLowerCase() === 'tinyint' && length == 1);
    }
    
    /**
     * Checks if the given value is a native value (true, false, or null).
     *
     * This function checks if the provided `defaultValue` is a string representing
     * one of the native values: "true", "false", or "null".
     *
     * @param {string} defaultValue The value to check.
     * @return {boolean} True if the value is "true", "false", or "null", false otherwise.
     */
    isNativeValue(defaultValue) {
        return defaultValue.toLowerCase() === 'true' || defaultValue.toLowerCase() === 'false' || defaultValue.toLowerCase() === 'null';
    }

    /**
     * Checks if the given type is a text/string type in MySQL.
     * This includes all text-related types like CHAR, VARCHAR, TEXT, etc.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a text type, false otherwise.
     */
    isTypeText(type) {
        const textTypes = ['char', 'varchar', 'text', 'tinytext', 'mediumtext', 'longtext', 'enum', 'set'];
        return textTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the given type is a numeric/integer type in MySQL.
     * This includes all integer-like types such as TINYINT, SMALLINT, INT, BIGINT, etc.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a numeric type, false otherwise.
     */
    isTypeInteger(type) {
        const integerTypes = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'integer'];
        return integerTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the given type is a floating-point type in MySQL.
     * This includes types like FLOAT, DOUBLE, and DECIMAL.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a floating-point type, false otherwise.
     */
    isTypeFloat(type) {
        const floatTypes = ['float', 'double', 'decimal', 'numeric'];
        return floatTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the given type is a date/time type in MySQL.
     * This includes types like DATE, DATETIME, TIMESTAMP, TIME, and YEAR.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a date/time type, false otherwise.
     */
    isTypeDate(type) {
        const dateTypes = ['date', 'datetime', 'timestamp', 'time', 'year'];
        return dateTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the given type is a binary/blob type in MySQL.
     * This includes types like BLOB, TINYBLOB, MEDIUMBLOB, LONGBLOB.
     *
     * @param {string} type The type to check.
     * @return {boolean} True if the type is a binary/blob type, false otherwise.
     */
    isTypeBinary(type) {
        const binaryTypes = ['blob', 'tinyblob', 'mediumblob', 'longblob'];
        return binaryTypes.includes(type.toLowerCase());
    }

    /**
     * Checks if the column type is one of the range types like NUMERIC, DECIMAL, DOUBLE, FLOAT, and has a value.
     * 
     * @param {Array} withRangeTypes - The list of types that support range values (e.g., NUMERIC, DECIMAL, etc.).
     * @returns {boolean} True if the column type is one of the range types and has a value.
     */
    hasRange(withRangeTypes) {
        return withRangeTypes.includes(this.type) && this.values;
    }

    /**
     * Checks if the column type is one of the value types like ENUM or SET, and has a value.
     * 
     * @param {Array} withValueTypes - The list of types that support specific values (e.g., ENUM, SET).
     * @returns {boolean} True if the column type is one of the value types and has a value.
     */
    hasValue(withValueTypes) {
        return withValueTypes.includes(this.type) && this.values;
    }

    /**
     * Checks if the column type supports length (e.g., VARCHAR, CHAR, etc.) and has a defined length.
     * 
     * @param {Array} withLengthTypes - The list of types that support length (e.g., VARCHAR, CHAR).
     * @returns {boolean} True if the column type supports length and a length is defined.
     */
    hasLength(withLengthTypes) {
        return this.length && withLengthTypes.includes(this.type);
    }

    /**
     * Checks if the column has a valid default value.
     * 
     * @returns {boolean} True if the column has a default value that is not 'null'.
     */
    hasDefault() {
        return this.default && this.default.toLowerCase() !== 'null';
    }
}

/**
 * Class representing an entity (table) in a database.
 * 
 * The Entity class is used to define a database table, its name, and the columns 
 * that belong to that table. It allows for adding, removing, and converting 
 * the entity (with its columns) into a valid SQL `CREATE TABLE` statement.
 */
class Entity {
    /**
     * Creates an instance of the Entity class.
     * 
     * @param {string} name - The name of the entity (table).
     */
    constructor(name) {
        this.name = name;
        this.columns = [];
    }

    /**
     * Adds a column to the entity.
     * 
     * @param {Column} column - An instance of the Column class to be added to the entity.
     */
    addColumn(column) {
        this.columns.push(column);
    }

    /**
     * Removes a column from the entity.
     * 
     * @param {number} index - The index of the column to be removed from the entity's column list.
     */
    removeColumn(index) {
        this.columns.splice(index, 1);
    }

    /**
     * Converts the entity (with its columns) into a valid SQL `CREATE TABLE` statement.
     * 
     * This method generates the SQL statement for creating a table, including the
     * table's name and each of its columns' definitions as provided by the `Column` class.
     * 
     * @returns {string} The SQL statement for creating the entity (table).
     */
    toSQL() {
        let sql = `CREATE TABLE IF NOT EXISTS ${this.name} (\r\n`;
        this.columns.forEach(col => {
            sql += `\t${col.toSQL()},\r\n`;
        });
        sql = sql.slice(0, -3); // Remove last comma
        sql += "\r\n);\r\n\r\n";
        return sql;
    }
}

/**
 * Class to manage the creation, editing, and deletion of database entities (tables),
 * as well as generating SQL statements for the entities.
 * 
 * The EntityEditor class allows users to create new database tables (entities),
 * add or remove columns, modify column properties, and export the generated SQL 
 * statements for creating the tables in MySQL.
 */
class EntityEditor {

    /**
     * Creates an instance of the EntityEditor class.
     * 
     * @param {string} selector - The CSS selector that identifies the target element for the entity editor.
     * @param {Object} [options={}] - Optional configuration settings for the entity editor.
     */
    constructor(selector, options = {}) {
        this.setting = {
            defaultDataType: 'VARCHAR',
            defaultDataLength: '48',
            primaryKeyDataType: 'VARCHAR',
            primaryKeyDataLength: '40'
        };

        // Copy properties from options to setting object
        Object.assign(this.setting, options);

        this.selector = selector;
        this.entities = [];
        this.currentEntityIndex = -1;
        this.mysqlDataTypes = [
            'BIGINT', 'INT', 'MEDIUMINT', 'SMALLINT', 'TINYINT',
            'NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT', 
            'BIT',
            'DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'YEAR',
            'LONGTEXT', 'MEDIUMTEXT', 'TEXT', 'TINYTEXT', 'VARCHAR', 'CHAR',
            'ENUM', 'SET', 
            'LONGBLOB', 'MEDIUMBLOB', 'BLOB', 'TINYBLOB',
            'UUID', 
            'VARBINARY', 'BINARY',
            'POLYGON', 'LINESTRING', 'POINT', 'GEOMETRY',
            'JSON',
        ];
        this.withLengthTypes = [
            'VARCHAR', 'CHAR', 
            'VARBINARY', 'BINARY',
            'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT',
            'BIT'
        ];
        this.withValueTypes = ['ENUM', 'SET'];
        this.withRangeTypes = ['NUMERIC', 'DECIMAL', 'DOUBLE', 'FLOAT'];
        this.defaultLength = {
            'BIGINT'    : 20,
            'INT'       : 11,
            'MEDIUMINT' : 8,
            'SMALLINT'  : 5,
            'TINYINT'   : 4
        };
        this.addDomListeners();

        this.callbackLoadEntity = this.setting.callbackLoadEntity;
        this.callbackSaveEntity = this.setting.callbackSaveEntity;
        this.callbackLoadTemplate = this.setting.callbackLoadTemplate;
        this.callbackSaveTemplate = this.setting.callbackSaveTemplate;
        this.callbackSaveConfig = this.setting.callbackSaveConfig;
 
        this.defaultDataType = this.setting.defaultDataType + '';
        this.defaultDataLength = this.setting.defaultDataLength + '';
        this.primaryKeyDataType = this.setting.primaryKeyDataType + '';
        this.primaryKeyDataLength = this.setting.primaryKeyDataLength + '';

        if(typeof this.callbackLoadEntity == 'function')
        {
            this.callbackLoadEntity();
        }
        if(typeof this.callbackLoadTemplate == 'function')
        {
            this.callbackLoadTemplate();
        }

        
            
        this.template = {columns: []};
    }

    /**
     * Adds event listeners to checkboxes for selecting and deselecting entities.
     */
    addDomListeners() {
        document.querySelector(this.selector+" .check-all-entity").addEventListener('change', (event) => {
            let checked = event.target.checked;
            let allEntities = document.querySelectorAll(this.selector+" .selected-entity");
            if(allEntities)
            {
                allEntities.forEach((entity, index) => {
                    entity.checked = checked;
                })
            }
            this.exportToSQL();
        });
        document.querySelector(this.selector+" .table-list").addEventListener('change', (event) => {
            if (event.target.classList.contains('selected-entity')) {
                this.exportToSQL();
            }
        });
        let _this = this;
        document.addEventListener('change', function (event) {
            if (event.target.classList.contains('column-primary-key')) {
                const isChecked = event.target.checked;
                if(isChecked)
                {
                    const tr = event.target.closest('tr');
                    tr.querySelector('.column-type').value = _this.primaryKeyDataType;
                    _this.updateColumnLengthInput(tr.querySelector('.column-type'));
                    tr.querySelector('.column-length').value = _this.primaryKeyDataLength;
                }
            }
        });

        document.querySelector(this.selector+" .import-file-json").addEventListener("change", function () {
            const file = this.files[0]; // Get the selected file
            if (file) {
                editor.importJSON(file, function(entities){
                    let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                    let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                    let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                    let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                    sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities); 
        
                }); // Import the file if it's selected
    
                
            } else {
                console.log("Please select a JSON file first.");
            }
        });

        document.querySelector(this.selector+" .import-file-sql").addEventListener("change", function () {
            const file = this.files[0]; // Get the selected file
            if (file) {
                editor.importSQLFile(file, function(entities){
                    let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                    let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                    let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                    let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                    sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities); 
        
                }); // Import the file if it's selected
    
                
            } else {
                console.log("Please select a JSON file first.");
            }
        });
    }

    /**
     * Shows the entity editor with the columns of an existing entity or prepares
     * a new entity for editing.
     * 
     * @param {number} entityIndex - The index of the entity to be edited. If not provided, a new entity is created.
     */
    showEditor(entityIndex = -1) {
        if (entityIndex >= 0) {
            this.currentEntityIndex = entityIndex;
            const entity = this.entities[entityIndex];
            document.querySelector(this.selector+" .entity-name").value = entity.name;
            document.querySelector(this.selector+" .entity-columns-table-body").innerHTML = '';
            entity.columns.forEach(col => this.addColumnToTable(col));
        } else {
            this.currentEntityIndex = -1;
            let newTableName = 'new_table';
            for (let i in this.entities) {
                let duplicated = false;
                for (let j in this.entities) {
                    newTableName = `new_table_${parseInt(i) + 2}`;
                    if (newTableName.toLowerCase() == this.entities[j].name.toLowerCase()) {
                        duplicated = true;
                    }
                }
                if (!duplicated) {
                    break;
                }
            }
            document.querySelector(this.selector+" .entity-name").value = newTableName;
            document.querySelector(this.selector+" .entity-columns-table-body").innerHTML = '';
        }
        document.querySelector(this.selector+" .button-container").style.display = "none";
        document.querySelector(this.selector+" .entity-container").style.display = "block";
        document.querySelector(this.selector+" .template-container").style.display = "none";
        document.querySelector(this.selector+" .editor-form").style.display = "block";
        if(entityIndex == -1)
        {
            document.querySelector(this.selector+" .entity-name").select();
        }
    }

    /**
     * Adds a column to the columns table for editing.
     * 
     * @param {Column} column - The column to add.
     * @param {boolean} [focus=false] - Whether to focus on the new column's name input.
     */
    addColumnToTable(column, focus = false) {
        const tableBody = document.querySelector(this.selector+" .entity-columns-table-body");
        const row = document.createElement("tr");
        let columnLength = column.length == null ? '' : column.length.replace(/\D/g,'');
        let columnDefault = column.default == null ? '' : column.default;
        let typeSimple = column.type.split('(')[0].trim();
        row.innerHTML = `
            <td class="column-action">
                <button onclick="editor.removeColumn(this)" class="icon-delete"></button>
                <button onclick="editor.moveUp(this)" class="icon-move-up"></button>
                <button onclick="editor.moveDown(this)" class="icon-move-down"></button>    
            </td>
            <td><input type="text" class="column-name" value="${column.name}" placeholder="Column Name"></td>
            <td>
                <select class="column-type" onchange="editor.updateColumnLengthInput(this)">
                    ${this.mysqlDataTypes.map(typeOption => `<option value="${typeOption}" ${typeOption === typeSimple ? 'selected' : ''}>${typeOption}</option>`).join('')}
                </select>
            </td>
            <td><input type="text" class="column-length" value="${columnLength}" placeholder="Length" style="display: ${this.withLengthTypes.includes(typeSimple) ? 'inline' : 'none'};"></td>
            <td><input type="text" class="column-enum" value="${column.values}" placeholder="Values (comma separated)" style="display: ${this.withValueTypes.includes(typeSimple) || this.withRangeTypes.includes(typeSimple) ? 'inline' : 'none'};"></td>
            <td><input type="text" class="column-default" value="${columnDefault}" placeholder="Default Value"></td>
            <td class="column-nl"><input type="checkbox" class="column-nullable" ${column.nullable ? 'checked' : ''}></td>
            <td class="column-pk"><input type="checkbox" class="column-primary-key" ${column.primaryKey ? 'checked' : ''}></td>
            <td class="column-ai"><input type="checkbox" class="column-autoIncrement" ${column.autoIncrement ? 'checked' : ''}></td>
        `;

        tableBody.appendChild(row);
        if(focus)
        {
            row.querySelector('.column-name').select();
        }
    }

    /**
     * Adds a new column to the entity being edited.
     * 
     * @param {boolean} [focus=false] - Whether to focus on the new column's name input.
     */
    addColumn(focus = false) {
        let entityName = document.querySelector(this.selector+" .entity-name").value;
        let count = document.querySelectorAll(this.selector+" .column-name").length;
        let countStr = count <= 0 ? '' : count + 1;
        let columnName = count == 0 ? `${entityName}_id` : `${entityName}_col${countStr}`;
        let column = new Column(columnName, this.defaultDataType, this.defaultDataLength);
        this.addColumnToTable(column, focus);
        const element = document.querySelector(this.selector+' .entity-container .table-container');
        element.scrollTop = element.scrollHeight;

    }

    /**
     * Saves the current entity, either updating an existing one or creating a new one.
     */
    saveEntity() {
        const entityName = document.querySelector(this.selector+" .entity-name").value;
        const columns = [];
        const columnNames = document.querySelectorAll(this.selector+" #table-entity-editor .column-name");
        const columnTypes = document.querySelectorAll(this.selector+" #table-entity-editor .column-type");
        const columnNullables = document.querySelectorAll(this.selector+" #table-entity-editor .column-nullable");
        const columnDefaults = document.querySelectorAll(this.selector+" #table-entity-editor .column-default");
        const columnPrimaryKeys = document.querySelectorAll(this.selector+" #table-entity-editor .column-primary-key");
        const columnAutoIncrements = document.querySelectorAll(this.selector+" #table-entity-editor .column-autoIncrement");
        const columnLengths = document.querySelectorAll(this.selector+" #table-entity-editor .column-length");
        const columnEnums = document.querySelectorAll(this.selector+" #table-entity-editor .column-enum");

        for (let i = 0; i < columnNames.length; i++) {
            let column = new Column(
                columnNames[i].value,
                columnTypes[i].value,
                columnLengths[i].value || null,
                columnNullables[i].checked,
                columnDefaults[i].value || null,
                columnPrimaryKeys[i].checked,
                columnAutoIncrements[i].checked,
                columnEnums[i].value || null,
            );
            columns.push(column);
        }

        if (this.currentEntityIndex >= 0) {
            // Update existing entity
            this.entities[this.currentEntityIndex].name = entityName;
            this.entities[this.currentEntityIndex].columns = columns;
        } else {
            // Add a new entity
            const newEntity = new Entity(entityName);
            columns.forEach(col => newEntity.addColumn(col));
            this.entities.push(newEntity);
        }

        this.renderEntities();
        this.cancelEdit();
        this.exportToSQL();
        if(typeof this.callbackSaveEntity == 'function')
        {
            this.callbackSaveEntity(this.entities);
        }
    }

    /**
     * Displays the template editor, hides the entity editor, and shows the appropriate containers.
     */
    showEditorTemplate() {
        this.currentEntityIndex = -2;
        document.querySelector(this.selector + " .template-columns-table-body").innerHTML = '';
        this.template.columns.forEach(col => this.addColumnToTemplate(col));
        document.querySelector(this.selector + " .button-container").style.display = "none";
        document.querySelector(this.selector + " .entity-container").style.display = "none";
        document.querySelector(this.selector + " .template-container").style.display = "block";
        document.querySelector(this.selector + " .editor-form").style.display = "block";
    }

    /**
     * Adds a new column template to the editor with default values.
     * 
     * @param {boolean} focus - Whether to focus on the newly added column input field.
     */
    addColumnTemplate(focus) {
        let column = {
            "name": "",
            "type": "VARCHAR",
            "length": "50",
            "nullable": true,
            "default": null,
            "values": ""
        }
        this.addColumnToTemplate(column, focus);
        const element = document.querySelector(this.selector + ' .template-container .table-container');
        element.scrollTop = element.scrollHeight;
    }

    /**
     * Adds a new column to the template editor.
     * 
     * @param {Object} column - The column data to add (name, type, length, nullable, default, values).
     * @param {boolean} focus - Whether to focus on the column input field after adding it.
     */
    addColumnToTemplate(column, focus) {
        const tableBody = document.querySelector(this.selector + " .template-columns-table-body");
        const row = document.createElement("tr");
        let columnLength = column.length == null ? '' : column.length.replace(/\D/g, '');
        let columnDefault = column.default == null ? '' : column.default;
        let typeSimple = column.type.split('(')[0].trim();
        row.innerHTML = `
            <td class="column-action">
                <button onclick="editor.removeColumn(this)" class="icon-delete"></button>
                <button onclick="editor.moveUp(this)" class="icon-move-up"></button>
                <button onclick="editor.moveDown(this)" class="icon-move-down"></button>    
            </td>
            <td><input type="text" class="column-name" value="${column.name}" placeholder="Column Name"></td>
            <td>
                <select class="column-type" onchange="editor.updateColumnLengthInput(this)">
                    ${this.mysqlDataTypes.map(typeOption => `<option value="${typeOption}" ${typeOption === typeSimple ? 'selected' : ''}>${typeOption}</option>`).join('')}
                </select>
            </td>
            <td><input type="text" class="column-length" value="${columnLength}" placeholder="Length" style="display: ${this.withLengthTypes.includes(typeSimple) ? 'inline' : 'none'};"></td>
            <td><input type="text" class="column-enum" value="${column.values}" placeholder="Values (comma separated)" style="display: ${this.withValueTypes.includes(typeSimple) || this.withRangeTypes.includes(typeSimple) ? 'inline' : 'none'};"></td>
            <td><input type="text" class="column-default" value="${columnDefault}" placeholder="Default Value"></td>
            <td class="column-nl"><input type="checkbox" class="column-nullable" ${column.nullable ? 'checked' : ''}></td>
        `;
        tableBody.appendChild(row);
        if (focus) {
            row.querySelector('.column-name').select();
        }
    }

    /**
     * Saves the column template by collecting the values from the form and updating the template.
     */
    saveTemplate() {
        const columns = [];
        const columnNames = document.querySelectorAll(this.selector + " #table-template-editor .column-name");
        const columnTypes = document.querySelectorAll(this.selector + " #table-template-editor .column-type");
        const columnNullables = document.querySelectorAll(this.selector + " #table-template-editor .column-nullable");
        const columnDefaults = document.querySelectorAll(this.selector + " #table-template-editor .column-default");
        const columnLengths = document.querySelectorAll(this.selector + " #table-template-editor .column-length");
        const columnEnums = document.querySelectorAll(this.selector + " #table-template-editor .column-enum");

        for (let i = 0; i < columnNames.length; i++) {
            let column = new Column(
                columnNames[i].value,
                columnTypes[i].value,
                columnLengths[i].value || null,
                columnNullables[i].checked,
                columnDefaults[i].value || null,
                columnEnums[i].value || null,
            );
            columns.push(column);
        }
        this.template.columns = columns;
        document.querySelector(this.selector + " .entity-container").style.display = "block";
        document.querySelector(this.selector + " .template-container").style.display = "none";
        if(typeof this.callbackSaveTemplate == 'function')
        {
            this.callbackSaveTemplate(this.template)
        }
    }

    /**
     * Cancels the template editing process and reverts to the entity editor view.
     */
    cancelEditTemplate() {
        document.querySelector(this.selector + " .entity-container").style.display = "block";
        document.querySelector(this.selector + " .template-container").style.display = "none";
    }

    /**
     * Adds columns from the template to the table, ensuring that only columns not already present are added.
     */
    addColumnFromTemplate() {
        const existingColumnNames = [];
        const columnNames = document.querySelectorAll(this.selector + " #table-entity-editor .column-name");
        for (const columnName of columnNames) {
            existingColumnNames.push(columnName.value);
        }
        this.template.columns.forEach(column => {
            if (!existingColumnNames.includes(column.name)) {
                this.addColumnToTable(column, focus);
            }
        });
        const element = document.querySelector(this.selector+' .entity-container .table-container');
        element.scrollTop = element.scrollHeight;
    }

    /**
     * Removes the selected column from the entity.
     * 
     * @param {HTMLElement} button - The button that was clicked to remove the column.
     */
    removeColumn(button) {
        const row = button.closest("tr");
        row.remove();
    }

    /**
     * Moves a column up in the columns table.
     * 
     * @param {HTMLElement} button - The button that was clicked to move the column up.
     */
    moveUp(button) {
        const row = button.closest("tr");
        const tableBody = row.closest('tbody');
        const previousRow = row.previousElementSibling;
        if (previousRow) {
            tableBody.insertBefore(row, previousRow);
        }
    }

    /**
     * Moves a column down in the columns table.
     * 
     * @param {HTMLElement} button - The button that was clicked to move the column down.
     */
    moveDown(button) {
        const row = button.closest("tr");
        const tableBody = row.closest('tbody');
        const nextRow = row.nextElementSibling;
        if (nextRow) {
            tableBody.insertBefore(nextRow, row);
        }
    }

    /**
     * Converts a JSON array to an array of Entity objects.
     * 
     * @param {Array} jsonData - The JSON array containing entities and their columns.
     * @returns {Array} - An array of Entity objects.
     */
    createEntitiesFromJSON(jsonData) {
        const entities = [];

        // Iterate over each entity in the JSON data
        jsonData.forEach(entityData => {
            // Create a new Entity instance
            const entity = new Entity(entityData.name);
            
            // Iterate over each column in the entity's columns array
            entityData.columns.forEach(columnData => {
                // Create a new Column instance
                const column = new Column(
                    columnData.name,
                    columnData.type,
                    columnData.length,
                    columnData.nullable,
                    columnData.default,
                    columnData.primaryKey,
                    columnData.autoIncrement,
                    columnData.values !== "null" ? columnData.values : "",
                );
                
                // Add the column to the entity
                entity.addColumn(column);
            });

            // Add the entity to the entities array
            entities.push(entity);
        });

        return entities;
    }

    /**
     * Converts a JSON array to an array of Entity objects.
     * 
     * @param {Array} jsonData - The JSON array containing entities and their columns.
     * @returns {Array} - An array of Entity objects.
     */
    createTemplateFromJSON(jsonData) {
        const columns = [];
        // Iterate over each column in the entity's columns array
        jsonData.columns.forEach(columnData => {
            // Create a new Column instance
            const column = new Column(
                columnData.name,
                columnData.type,
                columnData.length,
                columnData.nullable,
                columnData.default,
                columnData.values !== "null" ? columnData.values : "",
            );
            columns.push(column);
        });
        return {columns: columns};
    }

    /**
     * Creates an array of Entity instances from the given SQL table data.
     * 
     * This method takes in an array of tables, each containing information about table columns, and converts that 
     * data into Entity and Column objects. It then returns an array of the created entities.
     *
     * @param {Array} tables - An array of tables (each table being an object) with column data to convert into entities.
     * Each table should contain a `tableName` and a `columns` array where each column object contains metadata about the column (e.g., Field, Type, Length, Nullable, etc.).
     * 
     * @returns {Array} entities - An array of Entity objects, each containing Column objects based on the provided table data.
     */
    createEntitiesFromSQL(tables) {
        const entities = [];

        // Iterate over each entity in the JSON data
        tables.forEach(table => {
            // Create a new Entity instance
            const entity = new Entity(table.tableName);
            
            // Iterate over each column in the entity's columns array
            table.columns.forEach(columnData => {
                // Create a new Column instance
                const column = new Column(
                    columnData.Field,
                    columnData.Type,
                    columnData.Length,
                    columnData.Nullable,
                    columnData.Default,
                    columnData.Key,
                    columnData.AutoIncrement,
                    (columnData.EnumValues != null && typeof columnData.EnumValues == 'object') ? columnData.EnumValues.join(', ') : null,
                );
                
                // Add the column to the entity
                entity.addColumn(column);
            });

            // Add the entity to the entities array
            entities.push(entity);
        });

        return entities;
    }
    
    /**
     * Renders the entities to the DOM.
     * This method updates the UI by rendering a list of entities as checkboxes,
     * allowing users to select or deselect entities. It also ensures that previously
     * selected entities are checked when the list is re-rendered.
     * 
     * The method also updates the width of the ERD (Entity-Relationship Diagram) based on 
     * the available space in the container and re-renders the ERD with the updated width.
     */
    renderEntities() {
        // Get the container element for the entities
        const container = document.querySelector(this.selector+" .entities-container");

        // Create an array to hold the names of selected entities
        const selectedEntity = [];

        // Get all selected entity checkboxes (those that are checked)
        const selectedEntities = document.querySelectorAll(this.selector+" .selected-entity:checked");

        // If there are selected checkboxes, add their data-name to the selectedEntity array
        if (selectedEntities) {
            selectedEntities.forEach(checkbox => {
                selectedEntity.push(checkbox.getAttribute('data-name'));
            });
        }

        // Get the list element where the entities will be rendered
        const tabelList = document.querySelector(this.selector+" .table-list");
        let drawRelationship = document.querySelector(this.selector+" .draw-relationship").checked;


        // Clear any existing content in the table list
        tabelList.innerHTML = '';

        // Iterate over the entities and create a checkbox for each entity
        this.entities.forEach((entity, index) => {
            // Create a new list item for each entity
            let entityCb = document.createElement('li');
            entityCb.innerHTML = `
            <label><input type="checkbox" class="selected-entity" data-name="${entity.name}" value="${index}" />${entity.name}</label>
            `;
            
            // Append the created list item to the table list
            tabelList.appendChild(entityCb);
        });

        // Ensure that previously selected entities are checked
        selectedEntity.forEach(value => {
            // Find the checkbox corresponding to the selected entity name
            let cb = document.querySelector(`input[data-name="${value}"]`);
            if (cb) {
                // Check the checkbox if found
                cb.checked = true;
            }
        });

        // Get the SVG element for the ERD (Entity-Relationship Diagram)
        let svg = container.querySelector(".erd-svg");

        // Calculate the updated width of the SVG container
        let updatedWidth = svg.parentNode.parentNode.offsetWidth;

        // If the width is 0 (meaning it's not set), fallback to the left panel width
        if (updatedWidth == 0) {
            updatedWidth = resizablePanels.getLeftPanelWidth();
        }

        // Re-render the ERD with the updated width (subtracting 40 for padding/margin)
        renderer.createERD(editor.getData(), updatedWidth - 40, drawRelationship);
    }

    /**
     * Moves an entity up in the list of entities.
     * This method swaps the selected entity with the one before it in the array.
     * 
     * @param {number} index - The index of the entity to move up.
     */
    moveEntityUp(index) {
        editor.cancelEdit();
        // Ensure the index is valid and it's not the last element
        if (index < this.entities.length - 1) {
            // Swap the entity at 'index' with the one after it (at 'index + 1')
            const temp = this.entities[index];
            this.entities[index] = this.entities[index + 1];
            this.entities[index + 1] = temp;

            // Re-render the entities after the change
            this.renderEntities();
            this.exportToSQL();
            
            if(typeof this.callbackSaveEntity == 'function')
            {
                this.callbackSaveEntity(this.entities);
            }
        }
    }

    /**
     * Moves an entity down in the list of entities.
     * This method swaps the selected entity with the one after it in the array.
     * 
     * @param {number} index - The index of the entity to move down.
     */
    moveEntityDown(index) {
        editor.cancelEdit();
        // Ensure the index is valid and it's not the first element
        if (index > 0) {
            // Swap the entity at 'index' with the one before it (at 'index - 1')
            const temp = this.entities[index];
            this.entities[index] = this.entities[index - 1];
            this.entities[index - 1] = temp;

            // Re-render the entities after the change
            this.renderEntities();
            this.exportToSQL();
            
            if(typeof this.callbackSaveEntity == 'function')
            {
                this.callbackSaveEntity(this.entities);
            }
        }
    }

    /**
     * Sorts the entities alphabetically based on the 'name' property.
     * This method sorts the entities in ascending order (A-Z) and then calls 
     * `renderEntities()` to re-render the sorted list of entities in the UI.
     */
    sortEntities() {
        // Sort the entities array alphabetically by the 'name' property
        this.entities.sort((a, b) => {
            if (a.name > b.name) {
                return 1;  // a comes after b in alphabetical order
            }
            if (a.name < b.name) {
                return -1;  // a comes before b in alphabetical order
            }
            return 0;  // a and b are equal, no change
        });

        // Re-render the sorted list of entities
        this.renderEntities();
        this.exportToSQL();
        if(typeof this.callbackSaveEntity == 'function')
        {
            this.callbackSaveEntity(this.entities);
        }
    }


    /**
     * Edits the specified entity based on its index in the entities array.
     * 
     * @param {number} index - The index of the entity to edit.
     */
    editEntity(index) {
        this.currentEntityIndex = index;
        this.showEditor(index);
    }

    /**
     * Deletes the specified entity based on its index in the entities array.
     * 
     * @param {number} index - The index of the entity to delete.
     */
    deleteEntity(index) {
        let _this = this;
        let entityName = _this.entities[index].name;
        _this.showConfirmationDialog(`<p>Are you sure you want to delete the entity &quot;${entityName}&quot;?</p>`, 'Delete Confirmation', 'Yes', 'No', function(isConfirmed) {
            if (isConfirmed) {
                _this.entities.splice(index, 1);
                _this.renderEntities();
                _this.exportToSQL();
                if(typeof _this.callbackSaveEntity == 'function')
                {
                    _this.callbackSaveEntity(_this.entities);
                }
            } 
        });
    }

    /**
     * Cancels the entity editing process and hides the editor form.
     */
    cancelEdit() {
        document.querySelector(this.selector+" .editor-form").style.display = "none";
        document.querySelector(this.selector+" .button-container").style.display = "block";
    }

    /**
     * Updates the length and enum fields based on the selected column type.
     * 
     * @param {HTMLElement} selectElement - The select element for the column type.
     */
    updateColumnLengthInput(selectElement) {
        const row = selectElement.closest("tr");
        const columnType = selectElement.value;
        const lengthInput = row.querySelector(".column-length");
        const enumInput = row.querySelector(".column-enum");

        // Show length input for specific types
        if (this.withLengthTypes.includes(columnType)) {
            lengthInput.style.display = "inline";
        } else {
            lengthInput.style.display = "none";
        }

        // Show enum input for ENUM type
        if (this.withValueTypes.includes(columnType) || this.withRangeTypes.includes(columnType)) {
            enumInput.style.display = "inline";
        } else {
            enumInput.style.display = "none";
        }
        if(typeof this.defaultLength[columnType] != 'undefined')
        {
            let defaultLength = this.defaultLength[columnType];
            lengthInput.value = defaultLength;
        }
    }

    /**
     * Exports the selected entities as a MySQL SQL statement for creating the tables.
     */
    exportToSQL() {
        let sql = [];       
        const selectedEntities = document.querySelectorAll(this.selector+" .selected-entity:checked");  
        selectedEntities.forEach((checkbox, index) => {
            const entityIndex = parseInt(checkbox.value); 
            const entity = this.entities[entityIndex]; 
            if (entity) {
                sql.push(entity.toSQL());
            }
        });
        document.querySelector(this.selector+" .query-generated").value = sql.join("\r\n");
    }

    /**
     * Exports the given data object as a JSON file.
     * 
     * This function converts the provided JavaScript object into a JSON string, creates a Blob object from
     * the string, and then triggers a download of the Blob as a `.json` file using a temporary anchor element.
     * The filename will include a datetime suffix to avoid overwriting files, and the last underscore
     * will be removed from the datetime part of the filename.
     * 
     * @param {Object} data - The JavaScript object to be exported as JSON.
     */
    exportJSON(data) {
        // Get the base filename from the data object
        const fileName = `${data.databaseName}-${data.databaseSchema}`;

        // Get current date and time in the format YYYY-MM-DD_HH-MM-SS
        const now = new Date();
        let dateTimeSuffix = now.toISOString().replace(/[-T:\.Z]/g, '_');  // NOSONAR

        // Remove the last underscore if present
        if (dateTimeSuffix.endsWith('_')) {
            dateTimeSuffix = dateTimeSuffix.slice(0, -1);  // Remove the last underscore
        }

        // Create the filename with the datetime suffix
        const finalFileName = `${fileName}_${dateTimeSuffix}.json`;

        // Convert the object to a JSON string
        const jsonData = JSON.stringify(data); // Indented JSON for readability

        // Create a Blob object from the JSON string
        const blob = new Blob([jsonData], { type: "application/json" });

        // Create a URL for the Blob
        const url = URL.createObjectURL(blob);

        // Create a temporary anchor element
        const a = document.createElement("a");
        a.href = url;
        a.download = finalFileName; // Set the filename to include the datetime suffix
        document.body.appendChild(a);
        a.click(); // Trigger the download by clicking the anchor
        document.body.removeChild(a); // Clean up by removing the anchor
        URL.revokeObjectURL(url); // Release the object URL
    }

    /**
     * Downloads a SQL file containing database information.
     * 
     * This function collects metadata about the database from the document, constructs a data object, 
     * and generates a `.sql` file for download. The filename will include a datetime suffix to avoid 
     * overwriting files.
     * 
     * @returns {void}
     */
    downloadSQL() {
        let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
        let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
        let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
        let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
        
        const data = {
            applicationId: applicationId,
            databaseType: databaseType,
            databaseName: databaseName,
            databaseSchema: databaseSchema,
        };

        // Get the base filename from the data object
        const fileName = `${data.databaseName}-${data.databaseSchema}`;

        // Get current date and time in the format YYYY-MM-DD_HH-MM-SS
        const now = new Date();
        let dateTimeSuffix = now.toISOString().replace(/[-T:\.Z]/g, '_');  // NOSONAR

        // Remove the last underscore if present
        if (dateTimeSuffix.endsWith('_')) {
            dateTimeSuffix = dateTimeSuffix.slice(0, -1);  // Remove the last underscore
        }

        // Create the filename with the datetime suffix
        const finalFileName = `${fileName}_${dateTimeSuffix}.sql`;

        // Convert the object to a JSON string

        // Create a Blob object from the JSON string
        const blob = new Blob([document.querySelector(this.selector+' .query-generated').value], { type: "text/plain" });

        // Create a URL for the Blob
        const url = URL.createObjectURL(blob);

        // Create a temporary anchor element
        const a = document.createElement("a");
        a.href = url;
        a.download = finalFileName; // Set the filename to include the datetime suffix
        document.body.appendChild(a);
        a.click(); // Trigger the download by clicking the anchor
        document.body.removeChild(a); // Clean up by removing the anchor
        URL.revokeObjectURL(url); // Release the object URL
    }

    /**
     * Imports JSON data from a file and processes it.
     * 
     * This function accepts a file object, reads its contents as text using a FileReader, then parses the JSON
     * content and updates the editor's entities with the parsed data. It also triggers a re-render of the entities.
     * After the import, a callback function is invoked with the imported entities, if provided.
     * 
     * @param {File} file - The file object containing the JSON data to be imported.
     * @param {Function} [callback] - Optional callback function to be executed after the entities are updated. 
     *                                The callback will receive the updated entities as its argument.
     */
    importJSON(file, callback) {
        let _this = this;
        const reader = new FileReader(); // Create a FileReader instance
        reader.onload = function (e) {
            const contents = e.target.result; // Get the content of the file
            try {
                let raw = JSON.parse(contents);  // Parse the JSON content
                _this.entities = editor.createEntitiesFromJSON(raw.entities); // Insert the received data into editor.entities
                _this.renderEntities(); // Update the view with the fetched entities
                if (typeof callback === 'function') {
                    callback(_this.entities); // Execute callback with the updated entities
                }
            } catch (err) {
                console.log("Error parsing JSON: " + err.message); // Handle JSON parsing errors
            }
        };
        reader.readAsText(file); // Read the file as text
    }

    /**
     * Imports an SQL file and processes its content.
     * 
     * This function accepts an SQL file, reads its contents as text using a FileReader, then parses it 
     * using a `TableParser` and updates the editor's entities with the parsed data. After the import, 
     * a callback function is invoked with the updated entities, if provided.
     * 
     * @param {File} file - The SQL file object to be imported.
     * @param {Function} [callback] - Optional callback function to be executed after the entities are updated. 
     *                                The callback will receive the updated entities as its argument.
     * @returns {void}
     */
    importSQLFile(file, callback) {
        let _this = this;
        const reader = new FileReader(); // Create a FileReader instance
        reader.onload = function (e) {
            let contents = e.target.result; // Get the content of the file
            try {
                let translator = new SQLConverter();
                contents = translator.translate(contents, 'mysql').split('`').join('');
                let parser = new TableParser(contents);
                _this.entities = editor.createEntitiesFromSQL(parser.tableInfo); // Insert the received data into editor.entities            
                _this.renderEntities(); // Update the view with the fetched entities
                if (typeof callback === 'function') {
                    callback(_this.entities); // Execute callback with the updated entities
                }
                
            } catch (err) {
                console.log("Error parsing JSON: " + err.message); // Handle JSON parsing errors
            }
        };
        reader.readAsText(file); // Read the file as text
    }

    /**
     * Triggers the import action by simulating a click on the import file element.
     * This function locates the DOM element based on the `selector` property and 
     * clicks on it to initiate the import process.
     */
    uploadEntities() {
        document.querySelector(this.selector + " .import-file-json").click();
    }

    /**
     * Triggers the import action by simulating a click on the import file element.
     * This function locates the DOM element based on the `selector` property and 
     * clicks on it to initiate the import process.
     */
    importSQL() {
        document.querySelector(this.selector + " .import-file-sql").click();
    }

    /**
     * Gathers metadata from the HTML document and exports the entities data as a JSON file.
     * The function retrieves application-specific details such as `application-id`, 
     * `database-name`, `database-schema`, and `database-type` from the meta tags in the document.
     * Then, it constructs a data object containing these values and the current list of entities from
     * the editor, and passes it to the `exportJSON` method to export the data as a JSON file.
     * 
     * @returns {void} 
     */
    downloadEntities() {
        let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
        let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
        let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
        let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
        
        const data = {
            applicationId: applicationId,
            databaseType: databaseType,
            databaseName: databaseName,
            databaseSchema: databaseSchema,
            entities: this.entities  // Converting the entities array into a JSON string
        };
        
        this.exportJSON(data); // Export the sample object to a JSON file
    }

    /**
     * Retrieves the data containing the entities.
     *
     * This method returns an object with a property `entities`, which contains
     * the current entities stored in the class.
     *
     * @returns {Object} An object containing the `entities` property.
     * @property {Array} entities - The array of entities in the current instance.
     */
    getData() {
        return {entities: this.entities};
    }

    /**
     * Displays a confirmation dialog with OK and Cancel buttons.
     * Executes the provided callback with `true` if OK is clicked, or `false` if Cancel is clicked.
     * 
     * @param {string} message - The message to display in the dialog.
     * @param {string} title - The title of the dialog.
     * @param {string} captionOk - The label for the OK button.
     * @param {string} captionCancel - The label for the Cancel button.
     * @param {Function} callback - The callback function to be called with the result (`true` or `false`).
     * 
     * @returns {void}
     */
    showConfirmationDialog(message, title, captionOk, captionCancel, callback) {
        // Get modal and buttons
        const modal = document.querySelector('#asyncConfirm');
        const okBtn = modal.querySelector('.confirm-ok');
        const cancelBtn = modal.querySelector('.confirm-cancel');

        modal.querySelector('.modal-header h3').innerHTML = title;
        modal.querySelector('.modal-body').innerHTML = message;
        okBtn.innerHTML = captionOk;
        cancelBtn.innerHTML = captionCancel;

        // Show the modal
        modal.style.display = 'block';

        // Remove existing event listeners to prevent duplicates
        okBtn.removeEventListener('click', handleOkClick);
        cancelBtn.removeEventListener('click', handleCancelClick);

        // Define the event listener for OK button
        function handleOkClick() {
            modal.style.display = 'none';
            callback(true);  // Execute callback with 'true' if OK is clicked
        }

        // Define the event listener for Cancel button
        function handleCancelClick() {
            modal.style.display = 'none';
            callback(false);  // Execute callback with 'false' if Cancel is clicked
        }

        // Add event listeners for OK and Cancel buttons
        okBtn.addEventListener('click', handleOkClick);
        cancelBtn.addEventListener('click', handleCancelClick);
    }

    createDataTypeOption(name)
    {
        let html = '';
        html += `<select name="${name}">\r\n`;
        this.mysqlDataTypes.forEach((type, index) => {
            html += `<option value="${type}">${type}</option>\r\n`;
        });
        html += `</select>`;
        return html;
    }

    preference()
    {
        let _this = this;
        _this.showSettingDialog(`
            <table class="two-side-table">
                <tbody>
                    <tr>
                        <td>Primary Key Type</td>
                        <td>${_this.createDataTypeOption('primary_key_type')}</td>
                    </tr>
                    <tr>
                        <td>Primary Key Length</td>
                        <td><input type="text" name="primary_key_length" value=""></td>
                    </tr>
                    <tr>
                        <td>Column Type</td>
                        <td>${_this.createDataTypeOption('column_type')}</td>
                    </tr>
                    <tr>
                        <td>Column Length</td>
                        <td><input type="text" name="column_length" value=""></td>
                    </tr>
                </tbody>
            </table>
            `, 'Preferences', 'OK', 'Cancel', function(isConfirmed) {
            if (isConfirmed) {
                _this.primaryKeyDataType = document.querySelector('[name="primary_key_type"]').value;
                _this.primaryKeyDataLength = document.querySelector('[name="primary_key_length"]').value;
                _this.defaultDataType = document.querySelector('[name="column_type"]').value;
                _this.defaultDataLength = document.querySelector('[name="column_length"]').value;    
                if(typeof _this.callbackSaveConfig == 'function')
                {
                    _this.callbackSaveConfig({
                        primaryKeyDataType: _this.primaryKeyDataType,
                        primaryKeyDataLength: _this.primaryKeyDataLength,
                        defaultDataType: _this.defaultDataType,
                        defaultDataLength: _this.defaultDataLength,
                    });
                }
            } 
        });

        document.querySelector('[name="primary_key_type"]').value = _this.primaryKeyDataType;
        document.querySelector('[name="primary_key_length"]').value = _this.primaryKeyDataLength;
        document.querySelector('[name="column_type"]').value = _this.defaultDataType;
        document.querySelector('[name="column_length"]').value = _this.defaultDataLength;

    }

    showSettingDialog(message, title, captionOk, captionCancel, callback) {
        // Get modal and buttons
        const modal = document.querySelector('#settingModal');
        const okBtn = modal.querySelector('.confirm-ok');
        const cancelBtn = modal.querySelector('.confirm-cancel');

        modal.querySelector('.modal-header h3').innerHTML = title;
        modal.querySelector('.modal-body').innerHTML = message;
        okBtn.innerHTML = captionOk;
        cancelBtn.innerHTML = captionCancel;

        // Show the modal
        modal.style.display = 'block';

        // Remove existing event listeners to prevent duplicates
        okBtn.removeEventListener('click', handleOkConfig);
        cancelBtn.removeEventListener('click', handleCancelConfig);

        // Define the event listener for OK button
        function handleOkConfig() {
            modal.style.display = 'none';
            callback(true);  // Execute callback with 'true' if OK is clicked
        }

        // Define the event listener for Cancel button
        function handleCancelConfig() {
            modal.style.display = 'none';
            callback(false);  // Execute callback with 'false' if Cancel is clicked
        }

        // Add event listeners for OK and Cancel buttons
        okBtn.addEventListener('click', handleOkConfig);
        cancelBtn.addEventListener('click', handleCancelConfig);
    }

}
