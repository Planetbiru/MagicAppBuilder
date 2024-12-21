/**
 * Class representing a column in a database table.
 * 
 * The Column class is used to define a column for a database table, including 
 * its name, type, nullable status, default value, primary key, auto-increment,
 * and specific values for ENUM or SET types.
 */
class Column {
    /**
     * Creates an instance of the Column class.
     * 
     * @param {string} name - The name of the column.
     * @param {string} [type="VARCHAR"] - The type of the column (e.g., "VARCHAR", "INT", "ENUM", etc.).
     * @param {string} [length=""] - The length of the column for types like VARCHAR (optional).
     * @param {boolean} [nullable=false] - Whether the column can be NULL (default is false).
     * @param {string} [defaultValue=""] - The default value for the column (optional).
     * @param {boolean} [primaryKey=false] - Whether the column is a primary key (default is false).
     * @param {boolean} [autoIncrement=false] - Whether the column auto-increments (default is false).
     * @param {string} [enumValues=""] - The values for ENUM or SET types, if applicable (comma-separated).
     */
    constructor(name, type = "VARCHAR", length = "", nullable = false, defaultValue = "", primaryKey = false, autoIncrement = false, enumValues = "") //NOSONAR
    {
        this.name = name;
        this.type = type;
        this.length = length;
        this.nullable = nullable;
        this.default = defaultValue;
        this.primaryKey = primaryKey;
        this.autoIncrement = autoIncrement;
        this.enumValues = enumValues;
    }

    /**
     * Converts the column definition into a valid SQL statement.
     * 
     * This method generates the SQL column definition as a string based on the column's
     * properties such as its type, nullable status, primary key, auto-increment, default value,
     * and for ENUM/SET types, the list of valid values.
     * 
     * @returns {string} The SQL column definition.
     */
    toSQL() {
        let withValueTypes = ['ENUM', 'SET'];
        let numericTypes = ['BIGINT', 'INT', 'MEDIUMINT', 'SMALLINT', 'TINYINT', 'DOUBLE', 'DECIMAL', 'FLOAT'];

        let columnDef = `${this.name} ${this.type}`;
        
        // If the type is ENUM or SET, handle them similarly
        if ((withValueTypes.includes(this.type)) && this.enumValues) {
            const enumList = this.enumValues.split(',').map(val => `'${val.trim()}'`).join(', ');
            columnDef = `${this.name} ${this.type}(${enumList})`;
        } else if (this.length) {
            // For other types, add length if available
            columnDef += `(${this.length})`;
        }

        // Nullable logic
        if (this.nullable && !this.primaryKey) {
            columnDef += " NULL";
        } else {
            columnDef += " NOT NULL";
        }

        // Primary key logic
        if (this.primaryKey) {
            columnDef += " PRIMARY KEY";
        }

        // Auto increment logic
        if (this.autoIncrement) {
            columnDef += " AUTO_INCREMENT";
        }

        // Default value logic
        if (this.default && this.default.toLowerCase() !== 'null') {
            // Check if the type is numeric and the default value is a number
            if (numericTypes.includes(this.type) && !isNaN(this.default)) {
                columnDef += ` DEFAULT ${this.default}`; // No quotes for numeric values
            } else {
                columnDef += ` DEFAULT '${this.default}'`; // Default is a string, so use quotes
            }
        }

        return columnDef;
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
        let sql = `-- Entity: ${this.name}\r\n`;
        sql += `CREATE TABLE IF NOT EXISTS ${this.name} (\r\n`;
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
            'DOUBLE', 'DECIMAL', 'FLOAT', 
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
        this.typeWithLength = [
            'VARCHAR', 'CHAR', 
            'VARBINARY', 'BINARY',
            'TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'INTEGER', 'BIGINT'
        ];
        this.withValueTypes = ['ENUM', 'SET'];
        this.addDomListeners();
        this.callbackLoadEntity = this.setting.callbackLoadEntity;
        this.callbackSaveEntity = this.setting.callbackSaveEntity;
        this.defaultDataType = this.setting.defaultDataType + '';
        this.defaultDataLength = this.setting.defaultDataLength + '';

        this.primaryKeyDataType = this.setting.primaryKeyDataType + '';
        this.primaryKeyDataLength = this.setting.primaryKeyDataLength + '';

        if(typeof this.callbackLoadEntity == 'function')
        {
            this.callbackLoadEntity();
        }
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
        
        

        document.querySelector(this.selector+" .import-file").addEventListener("change", function () {
            const file = this.files[0]; // Get the selected file
            if (file) {
                editor.importJSON(file, function(entities){
                    let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                    let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                    let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                    let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                    sendToServer(applicationId, databaseType, databaseName, databaseSchema, entities); 
        
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
            document.querySelector(this.selector+" .columns-table-body").innerHTML = '';
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
            document.querySelector(this.selector+" .columns-table-body").innerHTML = '';
        }
        document.querySelector(this.selector+" .button-container").style.display = "none";
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
        const tableBody = document.querySelector(this.selector+" .columns-table-body");
        const row = document.createElement("tr");
        let columnLength = column.length == null ? '' : column.length.replace(/\D/g,'');
        let columnDefault = column.default == null ? '' : column.default;

        let typeSimple = column.type.split('(')[0].trim();
        row.innerHTML = `
            <td class="column-action">
                <button onclick="editor.removeColumn(this)">❌</button>
                <button onclick="editor.moveUp(this)">⬆️</button>
                <button onclick="editor.moveDown(this)">⬇️</button>    
            </td>
            <td><input type="text" class="column-name" value="${column.name}" placeholder="Column Name"></td>
            <td>
                <select class="column-type" onchange="editor.updateColumnLengthInput(this)">
                    ${this.mysqlDataTypes.map(typeOption => `<option value="${typeOption}" ${typeOption === typeSimple ? 'selected' : ''}>${typeOption}</option>`).join('')}
                </select>
            </td>
            <td><input type="text" class="column-length" value="${columnLength}" placeholder="Length" style="display: ${this.typeWithLength.includes(typeSimple) ? 'inline' : 'none'};"></td>
            <td><input type="text" class="column-enum" value="${column.enumValues}" placeholder="Values (comma separated)" style="display: ${this.withValueTypes.includes(typeSimple) ? 'inline' : 'none'};"></td>
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
        const tableBody = document.querySelector(this.selector+" .columns-table-body");
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
        const tableBody = document.querySelector(this.selector+" .columns-table-body");
        const nextRow = row.nextElementSibling;
        if (nextRow) {
            tableBody.insertBefore(nextRow, row);
        }
    }

    /**
     * Saves the current entity, either updating an existing one or creating a new one.
     */
    saveEntity() {
        const entityName = document.querySelector(this.selector+" .entity-name").value;
        const columns = [];
        const columnNames = document.querySelectorAll(this.selector+" .column-name");
        const columnTypes = document.querySelectorAll(this.selector+" .column-type");
        const columnNullables = document.querySelectorAll(this.selector+" .column-nullable");
        const columnDefaults = document.querySelectorAll(this.selector+" .column-default");
        const columnPrimaryKeys = document.querySelectorAll(this.selector+" .column-primary-key");
        const columnAutoIncrements = document.querySelectorAll(this.selector+" .column-autoIncrement");
        const columnLengths = document.querySelectorAll(this.selector+" .column-length");
        const columnEnums = document.querySelectorAll(this.selector+" .column-enum");

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
                    columnData.enumValues !== "null" ? columnData.enumValues : "",
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
     * Renders the list of entities and updates the table list in the UI.
     */
    renderEntities() {
        const container = document.querySelector(this.selector+" .entities-container");
        container.innerHTML = '';
        const selectedEntity = [];
        const selectedEntities = document.querySelectorAll(this.selector+" .selected-entity:checked");
        if(selectedEntities)
        {
            selectedEntities.forEach(checkbox => {
                selectedEntity.push(checkbox.getAttribute('data-name'));
            });
        }

        const tabelList = document.querySelector(this.selector+" .table-list");
        tabelList.innerHTML = '';
        
        this.entities.forEach((entity, index) => {
            const entityDiv = document.createElement("div");
            entityDiv.classList.add("entity");
            let columnsInfo = entity.columns.map(col => {
                if (col.length > 0) {
                    return `<li data-primary-key="${col.primaryKey ? 'true' : 'false'}">${col.name} <span class="data-type">${col.type}(${col.length})</span></li>`;
                } else {
                    return `<li data-primary-key="${col.primaryKey ? 'true' : 'false'}">${col.name} <span class="data-type">${col.type}</span></li>`;
                }
            }).join('');
            
            entityDiv.innerHTML = `
                <div class="entity-header">
                    <button onclick="editor.deleteEntity(${index})">❌</button>
                    <button onclick="editor.editEntity(${index})">✏️</button>
                    <h4>${entity.name}</h4>
                </div>
                <div class="entity-body">
                    <ul>${columnsInfo}</ul>
                </div>
            `;

            container.appendChild(entityDiv);
            
            let entityCb = document.createElement('li');
            entityCb.innerHTML = `
            <label><input type="checkbox" class="selected-entity" data-name="${entity.name}" value="${index}" />${entity.name}</label>
            `;
            
            tabelList.appendChild(entityCb);
        });

        selectedEntity.forEach(value => {
            let cb = document.querySelector(`input[data-name="${value}"]`);
            if(cb)
            {
                cb.checked = true;
            }
        });
        
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
        this.entities.splice(index, 1);
        this.renderEntities();
        this.exportToSQL();
        if(typeof this.callbackSaveEntity == 'function')
        {
            this.callbackSaveEntity(this.entities);
        }
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
        if (this.typeWithLength.includes(columnType)) {
            lengthInput.style.display = "inline";
        } else {
            lengthInput.style.display = "none";
        }

        // Show enum input for ENUM type
        if (this.withValueTypes.includes(columnType)) {
            enumInput.style.display = "inline";
        } else {
            enumInput.style.display = "none";
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
        let dateTimeSuffix = now.toISOString().replace(/[-T:\.Z]/g, '_');  // Format datetime to YYYY_MM_DD_HH_MM_SS

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
     * Triggers the import action by simulating a click on the import file element.
     * This function locates the DOM element based on the `selector` property and 
     * clicks on it to initiate the import process.
     */
    importEntities() {
        document.querySelector(this.selector + " .import-file").click();
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
    exportEntities() {
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


}
