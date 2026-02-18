let tabsLinkContainer;
let currentMarginLeft = 0;

// Instantiate the class
let converter = null;
let editor;
let entityRenderer;
let diagramRenderer = {};
let reservedColumns = {};
let resizablePanels;

let scrollElement = null;
const SCROLL_POSITION_KEY = 'scrollPosition.tableList';
let timeout = setTimeout('', 10000);

let tableIndex = 0;
let maxTableIndex = 0;
let exportConfig = {};
let exportTableList = [];
let fileName = '';
let downloadName = '';
let timeoutDownload = setTimeout('', 100);
let isExporting = false;

/**
 * Selects the first DOM element that matches the given CSS selector.
 *
 * @function qs
 * @param {string} selector - CSS selector string used to query an element.
 * @returns {Element|null} The first matched element, or null if no match is found.
 */
function qs(selector) {
    return document.querySelector(selector);
}

/**
 * Selects all DOM elements that match the given CSS selector.
 *
 * @function qsa
 * @param {string} selector - CSS selector string used to query elements.
 * @returns {NodeListOf<Element>} A NodeList containing all matched elements.
 */
function qsa(selector) {
    return document.querySelectorAll(selector);
}

/**
 * Creates a debounced version of the given function that delays its execution
 * until after a specified wait time has passed since the last invocation.
 *
 * @param {Function} func - The function to debounce.
 * @param {number} delay - The delay in milliseconds.
 * @returns {Function} - A debounced function.
 */
function debounce(func, delay) {
    let timeout;
    return function () {
        clearTimeout(timeout);
        timeout = setTimeout(func, delay);
    };
}

/**
 * Retrieves metadata values defined in the HTML <meta> tags.
 *
 * This function searches for specific <meta> elements in the current document
 * and extracts their `content` attribute. The values represent application
 * configuration parameters such as the application identifier, the target
 * database name, schema, and type.
 *
 * Example of expected <meta> tags in HTML:
 *   <meta name="application-id" content="my-app">
 *   <meta name="database-name" content="mydb">
 *   <meta name="database-schema" content="public">
 *   <meta name="database-type" content="postgresql">
 *
 * @function getMetaValues
 * @returns {Object} An object containing:
 * @property {string} applicationId - The application identifier.
 * @property {string} databaseName  - The database name.
 * @property {string} databaseSchema - The schema name in the database.
 * @property {string} databaseType  - The database type (e.g., mysql, postgresql, sqlite).
 *
 * @example
 * const { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
 * console.log(applicationId, databaseName, databaseSchema, databaseType);
 */
function getMetaValues() {
    return {
        applicationId: qs('meta[name="application-id"]').getAttribute('content'),
        databaseName: qs('meta[name="database-name"]').getAttribute('content'),
        databaseSchema: qs('meta[name="database-schema"]').getAttribute('content'),
        databaseType: qs('meta[name="database-type"]').getAttribute('content'),
        hash: qs('meta[name="hash"]').getAttribute('content'),
    };
}

/**
 * Sets metadata values in the HTML document for application and database configuration.
 *
 * This function updates the corresponding <meta> tags in the document head with
 * the provided values. It is typically used to expose runtime configuration or
 * environment details to client-side scripts.
 *
 * @param {string} applicationId - The unique identifier of the application.
 * @param {string} databaseName - The name of the connected database.
 * @param {string} databaseSchema - The schema used within the database.
 * @param {string} databaseType - The database engine type (e.g., MySQL, PostgreSQL).
 * @param {string} hash - The hash of the information.
 * @returns {void}
 */
function setMetaValues(applicationId, databaseName, databaseSchema, databaseType, hash)
{
    qs('meta[name="application-id"]').setAttribute('content', applicationId);
    qs('meta[name="database-name"]').setAttribute('content', databaseName);
    qs('meta[name="database-schema"]').setAttribute('content', databaseSchema);
    qs('meta[name="database-type"]').setAttribute('content', databaseType);
    qs('meta[name="hash"]').setAttribute('content', hash);
}


/**
 * Saves the vertical scroll position of the `.table-list` element to localStorage.
 */
function saveScrollPosition() {
    if (scrollElement) {
        localStorage.setItem(SCROLL_POSITION_KEY, scrollElement.scrollTop.toString());
    }
}

/**
 * Restores the vertical scroll position of the `.table-list` element
 * from the value stored in localStorage.
 */
function restoreScrollPosition() {
    scrollElement = qs('.table-list');
    if (!scrollElement) return;

    const saved = localStorage.getItem(SCROLL_POSITION_KEY);
    if (saved !== null) {
        scrollElement.scrollTop = parseInt(saved, 10);
    }
}

/**
 * Initializes scroll position persistence for the `.table-list` element.
 * Adds a debounced scroll event listener to save the scroll position
 * and restores the saved position on page load.
 */
function initTableScrollPosition() {
    scrollElement = qs('.table-list');
    if (!scrollElement) return;

    const debouncedSave = debounce(saveScrollPosition, 300); // 300ms debounce

    scrollElement.addEventListener('scroll', debouncedSave);
    restoreScrollPosition();
}

/**
 * Initializes the event listeners and sets up the modal dialogs.
 */
function init() {
    converter = new SQLConverter();
    let modalQueryTranslator = document.getElementById("queryTranslatorModal");
    let modalEntityEditor = document.getElementById("entityEditorModal");
    let closeModalButton = qsa(".cancel-button");
    let openModalQuertTranslatorButton = qs(".import-structure");
    let openModalEntityEditorButton = qs(".open-entity-editor");
    let openFileButton  = qs(".open-structure");
    let translateButton  = qs(".translate-structure");
    let importFromEntityButton = qs('.import-from-entity');
    let clearButton  = qs(".clear");
    let original = qs('.original');
    let query = qs('[name="query"]');
    let deleteCells = qsa('.cell-delete a');

    initTableScrollPosition();

    if(openModalQuertTranslatorButton)
    {
        openModalQuertTranslatorButton.onclick = function() {
            modalQueryTranslator.style.display = "block";
            original.focus();
        };
    }

    if(openModalEntityEditorButton)
    {
        openModalEntityEditorButton.onclick = function() {
            modalEntityEditor.style.display = "block";
            resizablePanels.loadPanelWidth();
            editor.updateDiagram();

        };
    }

    if(closeModalButton)
    {
        closeModalButton.forEach(function(cancelButton) {
            cancelButton.onclick = function(e) {
                e.target.closest('.modal').style.display = "none";
            }
        });
    }

    if(clearButton)
    {
        clearButton.onclick = function() {
            original.value = "";
        };
    }

    if(translateButton)
    {
        translateButton.onclick = function()
        {
            let sql = original.value;
            let type = qs('meta[name="database-type"]').getAttribute('content');
            let converted = converter.translate(sql, type);
            qs('[name="query"]').value = converted;
            modalQueryTranslator.style.display = "none";
        };
    }

    if(openFileButton)
    {
        openFileButton.onclick = function()
        {
            qs('.structure-sql').click();
        }
    }

    if(importFromEntityButton)
    {
        importFromEntityButton.onclick = function()
        {
            let sql = editor.generateSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());;
            qs('[name="query"]').value = sql.join("\r\n");
            modalEntityEditor.style.display = "none";
        };
    }

    if(deleteCells && deleteCells.length > 0)
    {
        deleteCells.forEach(function(cell) {
            cell.addEventListener('click', function(event) {
                event.preventDefault();
                const el = event.target;
                let schema     = el.dataset.schema;
                let table      = el.dataset.table;
                let primaryKey = el.dataset.primaryKey;
                let value      = el.dataset.value;
                let queryString = "";
                let tableName = schema != "" ? `${schema}.${table}` : table;
                queryString = `DELETE FROM ${tableName} WHERE ${primaryKey} = '${value}';\r\n`;
                let originalQuery = query.value;
                if(originalQuery.startsWith('DELETE FROM '))
                {
                    queryString = query.value + queryString;
                }
                query.value = queryString;
            });
        });
    }

    window.onclick = function(event) {
        if (event.target == modalQueryTranslator) {
            modalQueryTranslator.style.display = "none";
        }
    };
    qs('.structure-sql').addEventListener('change', function(e){
        openStructure(this.files[0]);
    });

    document.getElementById("tableFilter").addEventListener("input", function(event) {
        // Use event.target instead of 'this' for clarity in delegation context,
        // though 'this' works here as well.
        const filter = event.target.value.toLowerCase().trim();

        // Get the parent container of the list items
        const tableList = qs(".object-container .table-list");

        // Get all the list items inside the container
        const items = tableList.querySelectorAll("li");

        items.forEach(li => {
            // Check if the <li> has a title attribute
            const title = li.getAttribute("title");

            // Check for null/undefined before calling .toLowerCase()
            const text = title ? title.toLowerCase() : "";

            li.style.display = text.includes(filter) ? "" : "none";
        });
    });

    qs('.draw-auto-relationship').addEventListener('change', function(e){
        editor.refreshEntities();
        editor.updateDiagram();
    });

    qs('.draw-fk-relationship').addEventListener('change', function(e){
        editor.refreshEntities();
        editor.updateDiagram();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('check-group-structure')) {
            const group = e.target.dataset.group;
            const checked = e.target.checked;
            qsa('.check-structure-' + group).forEach(cb => {
                cb.checked = checked;
            });
        }

        if (e.target.classList.contains('check-group-data')) {
            const group = e.target.dataset.group;
            const checked = e.target.checked;
            qsa('.check-data-' + group).forEach(cb => {
                cb.checked = checked;
            });
        }
    });

    qs(".check-all-entity-data").addEventListener('change', (event) => {
        let checked = event.target.checked;
        let allEntities = event.target.closest('table').querySelector('tbody').querySelectorAll(".selected-entity-data");

        if(allEntities)
        {
            allEntities.forEach(entity => {
                entity.checked = checked;
            })
        }
        editor.exportToSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());
    });

    qs(".right-panel .table-list-for-export").addEventListener('change', (event) => {
        if (event.target.classList.contains('selected-entity-structure') || event.target.classList.contains('selected-entity-data')) {
            editor.exportToSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());
        }

        if (event.target.classList.contains('export-structure-system')) {
            let seletion = qsa('.entity-structure-system');
            seletion.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
            editor.exportToSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());
        }

        if (event.target.classList.contains('export-structure-custom')) {
            let seletion = qsa('.entity-structure-custom');
            seletion.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
            editor.exportToSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());
        }

        if (event.target.classList.contains('export-data-system')) {
            let seletion = qsa('.entity-data-system');
            seletion.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
            editor.exportToSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());
        }

        if (event.target.classList.contains('export-data-custom')) {
            let seletion = qsa('.entity-data-custom');
            seletion.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
            editor.exportToSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());
        }
    });

    qs('.entity-selector-container').addEventListener('change', function(e){
        // Check if the changed element is a <select> inside the form
        if(e.target.tagName === 'SELECT' || e.target.tagName === 'INPUT')
        {
            setTimeout(function(){
                saveFormState(e.target.form);
            }, 400);
        }
    });
    qs('.entity-type-selector').addEventListener('change', function(e){
        setTimeout(function(){
            saveFormState(e.target.form);
        }, 400);
    });

    let { applicationId, databaseName, databaseSchema, databaseType, hash } = getMetaValues();

    loadDatabaseIndex(applicationId, hash);
    let profile = qs('.graphql-app-profile').value;
    loadGraphQlEntityFromServer(applicationId, databaseType, databaseName, databaseSchema, profile, function(data){
        editor.graphqlAppData = data;
    });
    window.addEventListener('storage', function (e) {
        if(e.key == 'graphql-app-profile')
        {
            let dataset = JSON.parse(e.newValue);
            let { applicationId, databaseName, databaseSchema, databaseType, hash } = getMetaValues();
            if(applicationId == dataset.applicationId && databaseName == dataset.databaseName && databaseSchema == dataset.databaseSchema && databaseType == dataset.databaseType && hash == dataset.hash)
            {
                loadApplicationData(dataset.applicationId, dataset.databaseName, dataset.databaseSchema, dataset.databaseType, dataset.hash);
            }
        }
    });

    window.addEventListener('resize', function () {
        // Get the updated width of the SVG container
        editor.refreshEntities();
        editor.updateDiagram();
    });

    window.addEventListener('click', function() {
        qsa('.button-container .dropdown.show').forEach(function(openDropdown) {
            openDropdown.classList.remove('show');
            let menu = openDropdown.querySelector('.dropdown-menu');
            if(menu) {
                menu.style.top = '';
                menu.style.bottom = '';
                menu.style.marginTop = '';
                menu.style.marginBottom = '';
            }
        });
    });
}

/**
 * Handle change event on database/schema selector.
 *
 * Reads metadata stored in the selected <option> dataset
 * and triggers application data loading based on the selected database.
 *
 * @param {HTMLSelectElement} select
 *        The select element containing database/schema options.
 */
function onChangeDatabase(select) {
    let selectedOption = select.options[select.selectedIndex];
    let dataset = selectedOption.dataset;
    loadApplicationData(dataset.applicationId, dataset.databaseName, dataset.databaseSchema, dataset.databaseType, dataset.hash)
}

/**
 * Load database/schema index for a specific application
 * and populate the schema selector dropdown.
 *
 * The returned data is expected to be a JSON object where
 * each key represents a schema hash and the value contains
 * database metadata (label, type, name, schema).
 *
 * @param {string} applicationId
 *        Application identifier used to load schema index.
 * @param {string} hash
 *        Currently selected schema hash (used to mark option as selected).
 */
function loadDatabaseIndex(applicationId, hash)
{
    $.ajax({
        type: 'GET',
        url: '../lib.ajax/load-entiy-index.php',
        data: {applicationId: applicationId},
        dataType: 'json',
        success: function(data) {
            let select = qs('.schema-selector');
            select.innerHTML = '';
            for(let index in data)
            {
                if(data.hasOwnProperty(index))
                {
                    let option = document.createElement('option');
                    option.value = index;
                    option.textContent = data[index].label;
                    if(index == hash)
                    {
                        option.setAttribute('selected', 'selected');
                    }
                    option.dataset.applicationId = applicationId;
                    option.dataset.databaseType = data[index].databaseType;
                    option.dataset.databaseName = data[index].databaseName;
                    option.dataset.databaseSchema = data[index].databaseSchema;
                    option.dataset.hash = index;

                    select.appendChild(option);
                }
            }
        },
        error: function(err)
        {
            console.error(err);
        }
    })
}

/**
 * Saves the current state of the GraphQL entity selector form to the server.
 *
 * This function reads the values of various form controls within the provided form element,
 * including checkboxes for entity types (custom/system), in-memory cache settings,
 * and column-level configurations for each entity (e.g., filters, primary key handling).
 * It constructs a data object with this state and sends it to the server for persistence
 * via `sendGraphQlEntityToServer`. The state is also stored locally in `editor.graphqlAppData`.
 *
 * @param {HTMLFormElement} frm - The form element containing the GraphQL generator settings.
 * @returns {void}
 */
function saveFormState(frm)
{
    let custom = frm.querySelector('.entity-type-checker[data-entity-type="custom"]').checked;
    let system = frm.querySelector('.entity-type-checker[data-entity-type="system"]').checked;
    let inMemoryCache = frm.querySelector('.in-memory-cache-checker').checked;
    let programmingLanguage = frm.querySelector('.programming-language-selector').value;
    let entitySelectorTables = frm.querySelectorAll('.entity-selector-table');
    let entityTables = frm.querySelectorAll('.entity-table');
    let entitySelector = {};
    let entities = {};

    let profile = frm.querySelector('.graphql-app-profile').value;

    entitySelectorTables.forEach(table => {
        let input = table.querySelector('.entity-selector');
        entitySelector[input.value] = input.checked;
    });

    entityTables.forEach(table => {
        let entityName = table.dataset.entity;
        entities[entityName] = {};

        table.querySelector('tbody').querySelectorAll('tr').forEach(tr => {
            let colName = tr.dataset.col;
            let columnInfo = {};
            columnInfo.checked = tr.querySelector('.check-column').checked;
            if(tr.querySelector('.filter-graphql'))
            {
                let value = tr.querySelector('.filter-graphql').value;
                columnInfo.filter = value;
            }
            if(tr.querySelector('.textarea-graphql'))
            {
                let value = tr.querySelector('.textarea-graphql').checked;
                columnInfo.textareaColumns = value;
            }
            if(tr.querySelector('.pk-value-graphql'))
            {
                let value = tr.querySelector('.pk-value-graphql').value;
                columnInfo.primaryKeyValue = value;
            }
            entities[entityName][colName] = columnInfo;
        });
    });

    let dataToSave = {
        custom: custom,
        system: system,
        inMemoryCache: inMemoryCache,
        entitySelector: entitySelector,
        entities: entities,
        programmingLanguage: programmingLanguage
    };
    editor.graphqlAppData = dataToSave;
    let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
    sendGraphQlEntityToServer(applicationId, databaseType, databaseName, databaseSchema, dataToSave, profile);
}

/**
 * Restores a form field’s value based on the given selector and input type.
 *
 * @param {HTMLFormElement} frm - The form element containing the target field.
 * @param {string} selector - A CSS selector used to locate the field within the form.
 * @param {*} value - The value to restore to the field. If undefined, the function does nothing.
 * @param {string} [type='checkbox'] - The type of field to restore. Supported types: 'checkbox', 'select'.
 *
 * @returns {void}
 */
function restoreField(frm, selector, value, type = 'checkbox') {
    if (value === undefined) return;
    const el = frm.querySelector(selector);
    if (!el) return;

    if (type === 'checkbox') {
        el.checked = value;
    } else if (type === 'select') {
        el.value = value;
    }
}

/**
 * Loads the state of the GraphQL entity selector form from a data object.
 * It safely checks for the existence of each element before setting its value.
 *
 * @param {object} data - The data object containing the form state.
 * @param {boolean} data.custom - The checked state for the 'custom' entity type checkbox.
 * @param {boolean} data.system - The checked state for the 'system' entity type checkbox.
 * @param {object} data.entitySelector - An object mapping entity names to their checked state.
 * @param {object} data.entities - A nested object mapping entity and column names to their filter and primary key value settings.
 */
function loadFormState(frm, data) {

    if (!frm || !data) {
        return;
    }

    // Restore the main entity type checkers (Custom/System/InMemoryCache)
    restoreField(frm, '.entity-type-checker[data-entity-type="custom"]', data.custom, 'checkbox');
    restoreField(frm, '.entity-type-checker[data-entity-type="system"]', data.system, 'checkbox');
    restoreField(frm, '.in-memory-cache-checker', data.inMemoryCache, 'checkbox');

    // Restore programming language selector
    restoreField(frm, '.programming-language-selector', data.programmingLanguage, 'select');

    // Restore the checked state for each entity selector
    if (data.entitySelector && typeof data.entitySelector === 'object') {
        for (const entityName in data.entitySelector) {
            if (Object.hasOwnProperty.call(data.entitySelector, entityName)) {
                const entityCheckbox = frm.querySelector(`.entity-selector[value="${entityName}"]`);
                if (entityCheckbox) {
                    entityCheckbox.checked = data.entitySelector[entityName];
                }
            }
        }
    }

    // Restore values for filters and primary key settings within each entity table
    if (data.entities && typeof data.entities === 'object') {
        for (const entityName in data.entities) {
            for (const colName in data.entities[entityName]) {
                const colData = data.entities[entityName][colName];

                const tr = frm.querySelector(`table[data-entity="${entityName}"] tr[data-col="${colName}"]`);
                if (!tr) continue;

                tr.querySelector('.check-column').checked = colData.checked;

                const filterSelect = tr.querySelector(`select.filter-graphql[data-col="${colName}"]`);
                if (filterSelect && typeof colData.filter !== 'undefined') {
                    filterSelect.value = colData.filter;
                }
                const taCheckBox = tr.querySelector(`input.textarea-graphql[data-col="${colName}"]`);
                if (taCheckBox && typeof colData.textareaColumns !== 'undefined') {
                    taCheckBox.checked = colData.textareaColumns;
                }
                const pkSelect = tr.querySelector(`select.pk-value-graphql[data-col="${colName}"]`);
                if (pkSelect && typeof colData.primaryKeyValue !== 'undefined') {
                    pkSelect.value = colData.primaryKeyValue;
                }
            }
        }
    }
}

/**
 * Sends GraphQL entity data to the server via a POST request.
 *
 * @async
 * @param {string} applicationId - The unique identifier of the application.
 * @param {string} databaseType - The type of the database (e.g., mysql, postgresql, sqlite, sqlserver).
 * @param {string} databaseName - The name of the database to which the entity belongs.
 * @param {string} databaseSchema - The schema name of the database (if applicable).
 * @param {Object} dataToSave - The entity data to be sent to the server as JSON.
 * @param {string} profile - The GraphQL application profile.
 * @returns {Promise<Object|null>} A promise that resolves to the JSON response from the server, or `null` if an error occurs.
 *
 */
async function sendGraphQlEntityToServer(applicationId, databaseType, databaseName, databaseSchema, dataToSave, profile) {
    const url = buildUrl('graphql-entity', applicationId, databaseType, databaseName, databaseSchema, '', profile);
    const jsonData = JSON.stringify(dataToSave);

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json;charset=UTF-8',
                'X-Requested-With': 'xmlhttprequest'
            },
            body: jsonData
        });

        if (!response.ok) {
            console.error('An error occurred while sending data to the server:', response.status, response.statusText);
            return null;
        }

        // Optionally parse JSON response if needed
        const result = await response.json();
        return result;

    } catch (error) {
        console.error('Network error while sending data to the server:', error);
        return null;
    }
}

/**
 * Loads GraphQL entity data from the server via a GET request.
 *
 * @param {string} applicationId - The unique identifier of the application.
 * @param {string} databaseType - The type of the database (e.g., mysql, postgresql, sqlite, sqlserver).
 * @param {string} databaseName - The name of the database to retrieve entities from.
 * @param {string} databaseSchema - The schema name of the database (if applicable).
 * @param {string} profile - The GraphQL application profile.
 * @param {Function} callback - A callback function executed after data is successfully fetched.
 *                              Receives the parsed JSON data as its single argument.
 */
function loadGraphQlEntityFromServer(applicationId, databaseType, databaseName, databaseSchema, profile, callback) {
    const url = buildUrl('graphql-entity', applicationId, databaseType, databaseName, databaseSchema, '', profile);

    fetch(url, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'xmlhttprequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (typeof callback === 'function') {
            callback(data);
        }
    })
    .catch(error => {
        console.error('An error occurred while fetching data from the server:', error);
    });
}

/**
 * Opens the structure of the provided file.
 * If the file is a SQLite database, it delegates to `openSQLiteStructure`.
 * Otherwise, it reads the file as text and displays the content in `.original`.
 *
 * @param {File} file - The file to be read.
 */
function openStructure(file) {
    const reader = new FileReader();

    const headerBlob = file.slice(0, 512);
    reader.onload = function (e) {
        const buffer = new Uint8Array(e.target.result);
        if (looksLikeSQLite(buffer)) {
            openSQLiteStructure(file);
        } else {
            readAsText(file);
        }
    };

    reader.onerror = () => console.error("Failed to read file header.");
    reader.readAsArrayBuffer(headerBlob);
}

/**
 * Reads a text file and displays its content in the .original textarea.
 *
 * @param {File} file - The file to read as plain text.
 */
function readAsText(file) {
    const reader = new FileReader();
    reader.onload = function (e) {
        try {
            qs('.original').value = e.target.result;
        } catch (err) {
            console.error("Error displaying file content: " + err.message);
        }
    };
    reader.onerror = () => console.error("Failed to read file content.");
    reader.readAsText(file);
}

/**
 * Checks whether the given buffer starts with the standard SQLite file header.
 * SQLite database files begin with the following 16-byte header: "SQLite format 3\0".
 *
 * @param {Uint8Array} buffer - The byte buffer to check.
 * @returns {boolean} - Returns true if the buffer matches the SQLite header signature.
 */
function looksLikeSQLite(buffer) {
    const sqliteHeader = [
        0x53, 0x51, 0x4C, 0x69,
        0x74, 0x65, 0x20, 0x66,
        0x6F, 0x72, 0x6D, 0x61,
        0x74, 0x20, 0x33, 0x00
    ];
    return sqliteHeader.every((byte, i) => buffer[i] === byte);
}

/**
 * Reads a SQLite database file and exports its table structures as SQL CREATE TABLE statements.
 *
 * This function uses SQL.js to load and parse a `.sqlite` or `.db` file in the browser.
 * It extracts all user-defined tables and generates the corresponding CREATE TABLE syntax using PRAGMA data.
 * The result is inserted into a <textarea> with class `.original`.
 *
 * @param {File} file - The SQLite database file selected by the user.
 */
function openSQLiteStructure(file) {
    const reader = new FileReader();

    reader.onload = function (event) {
        try {
            const arrayBuffer = event.target.result;
            const uint8Array = new Uint8Array(arrayBuffer);

            initSqlJs({ locateFile: file => `../lib.assets/wasm/sql-wasm.wasm` }).then(SQL => {
                const db = new SQL.Database(uint8Array);

                // Fetch user-defined tables only (exclude sqlite internal tables)
                const res = db.exec("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");

                if (res.length === 0 || res[0].values.length === 0) {
                    qs('.original').value = '-- No tables found in database.';
                    return;
                }

                const tableNames = res[0].values.map(row => row[0]);
                let sqlContent = "-- SQL Structure Export\n\n";

                tableNames.forEach(tableName => {
                    const tableStructureRes = db.exec(`PRAGMA table_info(${tableName});`);
                    if (tableStructureRes.length > 0) {
                        const columns = tableStructureRes[0].values.map(col => /*NOSONAR*/{
                            const columnName = col[1];                        // Column name
                            const dataType = col[2];                          // Data type (e.g., INTEGER, TEXT)
                            const isNotNull = col[3] === 1 ? "NOT NULL" : ""; // NOT NULL constraint
                            const defaultValue = col[4] != null ? `DEFAULT ${col[4]}` : ""; // Default value
                            const primaryKey = col[5] === 1 ? "PRIMARY KEY" : ""; // Primary key

                            return `\t${columnName} ${dataType} ${isNotNull} ${defaultValue} ${primaryKey}`.replace(/\s+/g, ' ').trim();
                        }).join(",\n");

                        sqlContent += `-- Table: ${tableName}\n`;
                        sqlContent += `CREATE TABLE ${tableName} (\n${columns}\n);\n\n`;
                    }
                });

                qs('.original').value = sqlContent;
            }).catch(err => {
                console.error("SQL.js initialization error:", err);
                qs('.original').value = '-- Failed to initialize SQL.js.';
            });
        } catch (err) {
            console.error("Error processing SQLite file:", err);
            qs('.original').value = '-- Error reading SQLite database.';
        }
    };

    reader.onerror = () => {
        console.error("Failed to read SQLite file.");
        qs('.original').value = '-- Unable to read file.';
    };

    reader.readAsArrayBuffer(file); // Important: reads binary content
}

/**
 * Determines whether a given DOM element is editable.
 *
 * This function checks if the provided element is an editable field,
 * such as an <input>, <textarea>, or any element with the
 * `contenteditable` attribute set to true.
 *
 * @param {HTMLElement} element - The DOM element to check.
 * @returns {boolean} True if the element is editable, false otherwise.
 *
 * @example
 * const input = qs('input');
 * console.log(isEditableElement(input)); // true
 *
 * const div = qs('div[contenteditable="true"]');
 * console.log(isEditableElement(div)); // true
 *
 * const span = qs('span');
 * console.log(isEditableElement(span)); // false
 */
function isEditableElement(element)
{
    return element.tagName === 'INPUT' ||
        element.tagName === 'TEXTAREA' ||
        element.isContentEditable;
}

/**
 * Loads application metadata and server-side configuration.
 *
 * This function first updates the HTML document's meta tags with application and
 * database details, then fetches entity definitions and configuration data from
 * the server based on the provided parameters.
 *
 * @param {string} applicationId - The unique identifier of the application.
 * @param {string} databaseName - The name of the database to be accessed.
 * @param {string} databaseSchema - The schema within the database.
 * @param {string} databaseType - The type of database engine (e.g., MySQL, PostgreSQL).
 * @param {string} hash - The hash of the information.
 * @returns {void}
 */
function loadApplicationData(applicationId, databaseName, databaseSchema, databaseType, hash)
{
    setMetaValues(applicationId, databaseName, databaseSchema, databaseType, hash);
    fetchEntityFromServer(applicationId, databaseType, databaseName, databaseSchema);
    fetchConfigFromServer(applicationId, databaseType, databaseName, databaseSchema);
}

function syncDatabaseSchema()
{
    let {applicationId, databaseName, databaseSchema, databaseType, hash} = getMetaValues();
    let dataset = {applicationId:applicationId, databaseName:databaseName, databaseSchema:databaseSchema, databaseType:databaseType, hash:hash};
    dataset.time = new Date().getTime();
    window.localStorage.setItem('graphql-app-profile', JSON.stringify(dataset));
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {

    // Select all toggle buttons within collapsible elements
    const toggles = qsa('.collapsible .button-toggle');
    entityRenderer = new EntityRenderer(".erd-svg");

    // Attach event listeners to each toggle button
    toggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            // Find the closest collapsible element and toggle the 'open' class
            e.target.closest('.collapsible').classList.toggle('open');
        });
    });

    editor = new EntityEditor('.entity-editor',
        {
            defaultDataType: 'VARCHAR',
            defaultDataLength: 50,
            primaryKeyDataType: 'BIGINT',
            primaryKeyDataLength: 20,

            callbackLoadEntity: function(){
                let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                fetchEntityFromServer(applicationId, databaseType, databaseName, databaseSchema);
                fetchConfigFromServer(applicationId, databaseType, databaseName, databaseSchema);
            },
            callbackSaveEntity: function (entities){
                let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities);
                syncDatabaseSchema();
            },
            callbackSaveDiagram: function (diagrams){
                let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                sendDiagramToServer(applicationId, databaseType, databaseName, databaseSchema, diagrams);
                syncDatabaseSchema();
            },
            callbackLoadTemplate: function(){
                let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                fetchTemplateFromServer(applicationId, databaseType, databaseName, databaseSchema, null, function(data){
                    reservedColumns = data;
                });
            },
            callbackSaveTemplate: function (template){
                let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                sendTemplateToServer(applicationId, databaseType, databaseName, databaseSchema, template);
            },
            callbackSaveConfig: function (template){
                let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                sendConfigToServer(applicationId, databaseType, databaseName, databaseSchema, template);
            }
        }
    );

    document.addEventListener('paste', async function(event) {
        const target = event.target;

        editor.clearBeforeImport = false;

        // Do not intercept paste if the target is an editable element
        const isEditable = isEditableElement(target);

        // Only handle custom paste when not in an editable element and inside .entity-editor
        if (!isEditable && target && target.closest('.entity-editor')) { // NOSONAR
            event.preventDefault(); // block default paste behavior

            let parsed;
            try {
                // Use await to wait for the promise from clipboard.read() to resolve
                const clipboardItems = await navigator.clipboard.read();

                for (const item of clipboardItems) {
                    if (item.types.includes('text/html')) {
                        const htmlBlob = await item.getType('text/html');
                        const htmlText = await htmlBlob.text();

                        const div = document.createElement('div');
                        div.innerHTML = htmlText;
                        let tables = div.querySelectorAll('table');

                        if (tables && tables.length > 0) {
                            let isFromMagicAppBuilder = editor.isFromMagicAppBuilder(tables);
                            if(isFromMagicAppBuilder)
                            {
                                editor.parseHtmlTableFromDocument(tables);
                                return;
                            }
                            else
                            {
                                parsed = editor.parseHtmlToJSON(tables[0]);
                                editor.importFromData(parsed);
                                return; // Return after finding an HTML table
                            }
                            
                        }
                    }
                }

                // If there is no HTML data, try reading as plain text
                const text = await navigator.clipboard.readText();

                if (/create\s+table/i.test(text.trim()) || /insert\s+into/i.test(text.trim())) {
                    editor.parseCreateTable(text, function(entities){
                    let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                    sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities);
                });
                } else if (/[\"']__magic_signature__[\"']\s*:\s*[\"']MAGICAPPBUILDER-DB-DESIGN-V1[\"']/.test(text)) {
                    // JSON
                    try {
                        editor.importJSONData(text, function(entities, diagrams){
                        let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                        sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities);
                        sendDiagramToServer(applicationId, databaseType, databaseName, databaseSchema, diagrams);
                    }); // Import the file if it's selected
                    } catch (jsonErr) {
                        console.error("Invalid JSON format despite having signature:", jsonErr);
                    }
                } else if (editor.isMarkdownTable(text)) {
                    editor.importFromMarkdown(text, function(entities){
                        editor.renderEntities();
                        let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
                        sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities);
                    });
                } else {
                    parsed = editor.parseTextToJSON(text);
                    editor.importFromData(parsed);
                }
            } catch (error) {
                console.error('Failed to read from clipboard:', error);
            }
        }
    });

    qs('[type="submit"].execute').addEventListener('click', function(event) {
        event.preventDefault();
        showConfirmationDialog('Are you sure you want to execute the query?', 'Execute Query Confirmation', 'Yes', 'No', function(isConfirmed) {
            if (isConfirmed) {
                event.target.closest('form').submit();  // Submit the form containing the button
            }
        });
    });

    qs('[type="button"].save').addEventListener('click', function(event) {
        event.preventDefault();
        let query = qs('textarea[name="query"]').value.trim();
        if(query.length > 0)
        {
            const blob = new Blob([query], { type: "text/plain" });

            // Create a URL for the Blob
            const url = URL.createObjectURL(blob);

            // Create a temporary anchor element
            const a = document.createElement("a");
            a.href = url;
            a.download = 'query.sql'; // Set the filename to include the datetime suffix
            document.body.appendChild(a);
            a.click(); // Trigger the download by clicking the anchor
            document.body.removeChild(a); // Clean up by removing the anchor
            URL.revokeObjectURL(url); // Release the object URL
        }
    });

    let btnTableExport = qs('[type="submit"][name="___export_table___"]');
    if(btnTableExport != null)
    {
        btnTableExport.addEventListener('click', function(event) {
            let tableName = event.target.getAttribute('value');
            event.preventDefault();
            showConfirmationDialog('Are you sure you want to export the data from the table?', 'Export Confirmation', 'Yes', 'No', function(isConfirmed) {
                if (isConfirmed) {

                    let hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = '___export_table___';
                    hiddenInput.value = tableName;
                    event.target.closest('form').appendChild(hiddenInput);
                    event.target.closest('form').submit();  // Submit the form containing the button
                    event.target.closest('form').removeChild(hiddenInput);
                }
            });
        });
    }

    let btnDatabaseExport = qs('[type="submit"][name="___export_database___"]');
    if(btnDatabaseExport != null)
    {
        btnDatabaseExport.addEventListener('click', function(event) {
            let tableName = event.target.getAttribute('value');
            event.preventDefault();

            let selector = '#exportModal';
            showExportDialog(selector,
                '<div class="loading-animation"></div>',
                'Export Database', 'Yes', 'No', function(isOk) {
                if (isOk)
                {
                    let select = qs('[name="target_database_type"]');
                    disableOtherOptions(select);
                    startExportDatabase(selector, function(){
                        enableAllOptions(select);
                    });
                }
                else
                {
                    let select = qs('[name="target_database_type"]');
                    enableAllOptions(select);
                    qs('#exportModal').style.display = 'none'
                }
            });
            listTableToExport(selector, tableName);
        });
    }

    qs(".with-foreign-key").addEventListener('change', (event) => {
        editor.exportToSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());
    });
    qs(".with-index").addEventListener('change', (event) => {
        editor.exportToSQL(editor.getSelectedDialect(), editor.isGenerateForeignKey(), editor.isGenerateIndex());
    });

    qs('.add-diagram').addEventListener('click', function(e){
        e.preventDefault();
        let ul = e.target.closest('ul');
        let diagramName = editor.getNewDiagramName();
        let randomId = (new Date()).getTime();
        let id = 'diagram-'+randomId;
        editor.addDiagram(ul, diagramName, id, [], false, true);
        editor.saveDiagram();
    });

    qs('[data-id="all-entities"]').addEventListener('click', function(e){
        e.preventDefault();
        let li = e.target.parentNode;
        let diagramContainer = qs('.diagram-container');

        li.closest('ul').querySelectorAll('li.diagram-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        diagramContainer.querySelectorAll('.diagram').forEach(tab => {
            tab.classList.remove('active');
        });
        li.classList.add('active');
        let selector = 'all-entities';
        diagramContainer.querySelector('#'+selector).classList.add('active');
        qs('.entity-editor .left-panel .table-list').querySelectorAll('li').forEach(li => {
            let input = li.querySelector('input[type="checkbox"]');
            input.checked = false;
            input.disabled = true;
        });
    });

    // Listen for changes in checkboxes within the table-list
    document.addEventListener('change', function (e) {
        if (e.target.closest('.table-list input[type="checkbox"]')) {

            let diagram = qs('.diagram-container .diagram.active');
            let source = diagram.dataset.entities || '';
            let currentSelection = source.split(',');
            let selectedEntities = new Set(); // Use a Set to store selected entities

            // Iterate through checkboxes and add checked ones to the set
            e.target.closest('.table-list').querySelectorAll('input[type="checkbox"]').forEach(input => {
                let entity = input.dataset.name;
                if (input.checked) {
                    selectedEntities.add(entity);
                }
            });

            // Maintain the order based on the initial selection
            let updatedEntities = currentSelection.filter(entity => selectedEntities.has(entity));

            // Append new entities that were not in the initial selection
            selectedEntities.forEach(entity => {
                if (!updatedEntities.includes(entity)) {
                    updatedEntities.push(entity);
                }
            });

            // Update the data-entities attribute with the new order
            diagram.setAttribute('data-entities', updatedEntities.join(','));
            editor.saveDiagram();
        }

        editor.updateDiagram();
    });

    document.addEventListener('change', (event) => {
        const target = event.target;

        // Ensure the event originates from 'id1' and is user-initiated (not from a script)
        if (target.id === 'id1' && event.isTrusted) {
            const parentList = target.closest('ul');
            let lastCheckbox = null;
            if (parentList) {
                const checkboxes = parentList.querySelectorAll('input[type="checkbox"]');
                const isChecked = target.checked;

                // Get the last element in the checkbox list


                checkboxes.forEach((checkbox) => {
                    // Only change the state if necessary
                    if (checkbox.checked !== isChecked) {
                        checkbox.checked = isChecked;
                    }
                    let selector = `.selected-entity[data-name="${checkbox.dataset.name}"]`;
                    let cb = qs(selector);
                    if(cb)
                    {
                        cb.checked = checkbox.checked;
                        lastCheckbox = cb;
                    }
                });

                // After all checkboxes have been changed, only trigger the 'change' event on the last element
                if (lastCheckbox) {
                    // Create a custom event and flag it as a "simulated" event
                    const customEvent = new Event('change', { bubbles: true });
                    customEvent.isSimulated = true;
                    lastCheckbox.dispatchEvent(customEvent);
                }
            }
        }
    });


    resizablePanels = new ResizablePanels('.entity-editor', '.left-panel', '.right-panel', '.resize-bar', 200);

    tabsLinkContainer = qs('.tabs-link-container');

    qs('.tab-mover li a.move-first').addEventListener('click', function(event) {
        event.preventDefault();
        moveTabToFirst();
    });

    qs('.tab-mover li a.move-last').addEventListener('click', function(event) {
        event.preventDefault();
        moveTabToLast();
    });

    qs('.tab-mover li a.move-left').addEventListener('click', function(event) {
        event.preventDefault();
        updateMarginLeft(30);
    });

    qs('.tab-mover li a.move-right').addEventListener('click', function(event) {
        event.preventDefault();
        updateMarginLeft(-30);
    });

    tabsLinkContainer.addEventListener('wheel', (event) => {
        event.preventDefault();
        const delta = event.deltaY || event.detail || event.wheelDelta;
        const step = 30; // Adjust the step size as needed

        updateMarginLeft(delta > 0 ? -step : step);
    });

    qs('.export-data-entity').addEventListener('click', function(){
        editor.exportData();
    });

    qs('.import-data-entity').addEventListener('click', function(){
        qs('#importDataFileInput').click();
    });

    qs('.add-data-entity').addEventListener('click', function(e){
        editor.addData(true);
    });

    qs('.clear-data-entity').addEventListener('click', function(){
        editor.clearData();
    });

    qs('.save-data-entity').addEventListener('click', function(){
        editor.saveData();
    });

    qs('#importDataFileInput').addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (!file) return;

        editor.importSheetFile(file, function(fileExtension, fileName, sheetName, headers, data){
            // Populate the editor with parsed data
            data.forEach((rowData) => {
                let tr = editor.addData();
                if (tr) {
                    let snakeRow = {};
                    for (const key of Object.keys(rowData)) {
                        const snakeKey = editor.snakeize(key);
                        snakeRow[snakeKey] = rowData[key];
                    }
                    const inputCells = tr.querySelectorAll('input.entity-data-cell');
                    inputCells.forEach(inputElement => /*NOSONAR*/{
                        const columnName = inputElement.dataset.col;
                        inputElement.value = snakeRow[columnName] ?? '';
                    });
                }
            });
        })
    });

    // Dropdown toggle logic
    qsa('.button-container .dropdown-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function(event) {
            event.stopPropagation();
            let dropdown = this.parentElement;
            
            // Close other open dropdowns within the same container
            let container = dropdown.closest('.button-container');
            if(container) {
                container.querySelectorAll('.dropdown.show').forEach(function(openDropdown) {
                    if (openDropdown !== dropdown) {
                        openDropdown.classList.remove('show');
                        let menu = openDropdown.querySelector('.dropdown-menu');
                        if(menu) {
                            menu.style.top = '';
                            menu.style.bottom = '';
                            menu.style.marginTop = '';
                            menu.style.marginBottom = '';
                        }
                    }
                });
            }

            let isShown = dropdown.classList.toggle('show');
            let menu = dropdown.querySelector('.dropdown-menu');
            if(isShown && menu) {
                // Reset styles to measure correctly
                menu.style.top = '';
                menu.style.bottom = '';
                menu.style.marginTop = '';
                menu.style.marginBottom = '';
                
                let rect = menu.getBoundingClientRect();
                if (rect.bottom > window.innerHeight) {
                    menu.style.top = 'auto';
                    menu.style.bottom = '100%';
                    menu.style.marginTop = '0';
                    menu.style.marginBottom = '2px';
                }
            } else if (menu) {
                menu.style.top = '';
                menu.style.bottom = '';
                menu.style.marginTop = '';
                menu.style.marginBottom = '';
            }
        });
    });

    // Apply to both tables
    enableArrowKeyNavigation('#table-entity-editor');
    enableArrowKeyNavigation('#table-template-editor');

    init();

    if($('input[data-type="datetime"]').length)
    {
        $('input[data-type="datetime"]').datetimepicker({
            format: 'Y-m-d H:i:s'
        });
    }
    if($('input[data-type="date"]').length)
    {
        $('input[data-type="date"]').datetimepicker({
            timepicker: false,
            format: 'Y-m-d'
        });
    }
    if($('input[data-type="time"]').length)
    {
        $('input[data-type="time"]').datetimepicker({
            datepicker: false,
            format: 'H:i:s'
        });
    }

    initAllEntitiesContextMenu(qs('#all-entities .erd-svg'));
});

/**
 * Displays the export HTML dialog in the editor.
 *
 * This function calls the `showExportHTMLDialog()` method of the `editor`
 * object to open a dialog window that allows the user to export the
 * editor's content in HTML format.
 *
 * @returns {void} Does not return a value; only triggers the export dialog.
 */
function downloadHTML()
{
    editor.showExportHTMLDialog();
}

/**
 * Enables vertical keyboard navigation using ArrowUp and ArrowDown keys
 * for text inputs inside a table. It ensures navigation stays in the same
 * column and skips over hidden or invisible inputs.
 *
 * @param {string} tableSelector - A CSS selector for the table element.
 */
function enableArrowKeyNavigation(tableSelector) {
    qs(tableSelector)?.addEventListener('keydown', function (event) {
        // Only process text input fields
        if (!(event.target instanceof HTMLInputElement) || event.target.type !== 'text') return;

        const key = event.key;
        if (key !== 'ArrowUp' && key !== 'ArrowDown') return;

        const currentInput = event.target;
        const currentCell = currentInput.closest('td');
        const currentRow = currentCell?.parentElement;
        if (!currentRow) return;

        // Get the index of the current cell within its row
        const cellIndex = [...currentRow.children].indexOf(currentCell);

        // Determine the next row based on the arrow key pressed
        let targetRow = key === 'ArrowUp'
            ? currentRow.previousElementSibling
            : currentRow.nextElementSibling;

        // Helper function to check if an element is visible
        function isVisible(el) {
            return el && el.offsetParent !== null && window.getComputedStyle(el).visibility !== 'hidden';
        }

        // Loop upward or downward until a visible input is found in the same column
        while (targetRow) {
            const targetCell = targetRow.children[cellIndex];
            if (targetCell) {
                const input = targetCell.querySelector('input[type="text"]');
                if (isVisible(input)) {
                    event.preventDefault(); // Prevent default caret movement
                    input.focus();          // Move focus to the target input
                    input.select();         // Optionally select the text inside
                    break;
                }
            }

            // Move to the next row in the same direction
            targetRow = key === 'ArrowUp'
                ? targetRow.previousElementSibling
                : targetRow.nextElementSibling;
        }
    });
}

/**
 * Parses a single CSV line into an array of values.
 * Handles quoted values and escaped quotes (RFC 4180).
 *
 * @param {string} line - A line from the CSV file.
 * @returns {string[]} An array of parsed string values.
 */
function parseCSVLine(line) {
    const result = [];
    let current = '';
    let inQuotes = false;

    for (let i = 0; i < line.length; i++) {
        const char = line[i];
        const nextChar = line[i + 1];

        if (inQuotes) {
            if (char === '"' && nextChar === '"') {
                current += '"'; // Escaped quote
                i++; // NOSONAR
            } else if (char === '"') {
                inQuotes = false;
            } else {
                current += char;
            }
        } else {
            if (char === '"') // NOSONAR
            {
                inQuotes = true;
            } else if (char === ',') {
                result.push(current);
                current = '';
            } else {
                current += char;
            }
        }
    }

    result.push(current);
    return result;
}

/**
 * Toggles the checked state of all diagram export checkboxes within a list.
 *
 * This function prevents the default event behavior and uses the checked state
 * of the triggering checkbox (`e1.target`) to update all checkboxes with the
 * class `diagram-to-export` inside the closest `<ul>` element.
 *
 * @param {Event} e1 - The event object triggered by the checkbox interaction.
 * @returns {void} Does not return a value; updates the checked state of related checkboxes.
 *
 */
function checkAllDiagram(e1)
{
    e1.preventDefault();
    let checked = e1.target.checked;
    e1.target.closest('ul').querySelectorAll('input.diagram-to-export').forEach(cb =>{
        cb.checked = checked;
    });
}

/**
 * Initiates the database export process if no other export is currently running.
 *
 * @param {string} selector - A CSS selector targeting the HTML element(s)
 *                            to export data from (e.g., a table row).
 */
function startExportDatabase(selector, onFinishCallback)
{
    if (!isExporting)
    {
        isExporting = true;
        exportDatabase(selector, onFinishCallback);
    }
}

/**
 * Starts the export process for selected tables.
 * Collects export configuration and selected tables, then begins the export.
 *
 * @param {string} selector - The CSS selector for the table containing table rows to export.
 */
function exportDatabase(selector, onFinish) {
    // Read metadata from <meta> tags
    let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();

    exportTableList = [];

    // Clear timeout
    clearTimeout(timeoutDownload);
    tableIndex = 0;

    // Generate a timestamped export filename
    fileName = (new Date()).getTime() + '.sql';
    if(applicationId != '')
    {
        downloadName = applicationId + "-" + fileName;
    }
    else
    {
        downloadName = "magicappbuilder-" + fileName;
    }

    // Collect selected tables with structure/data checkboxes
    $(selector).find('tbody').find('tr').each(function () {
        let tableName = $(this).attr('data-table-name');
        let includeStructure = $(this).find('input[name="structure_export[]"]').is(':checked');
        let includeData = $(this).find('input[name="data_export[]"]').is(':checked');
        $(this).attr('data-status', 'none');
        if (includeStructure || includeData) {
            exportTableList.push({
                tableName: tableName,
                structure: includeStructure,
                data: includeData
            });
        }
    });

    maxTableIndex = exportTableList.length;

    // Save export configuration
    exportConfig = {
        applicationId: applicationId,
        databaseType: databaseType,
        databaseName: databaseName,
        schemaName: databaseSchema
    };

    // Begin exporting tables
    exportTable(selector, onFinish);
}

/**
 * Recursively exports each selected table (structure and/or data) to the server.
 * After all tables are processed, a download is triggered.
 *
 * @param {string} selector - The CSS selector used to find the modal or table.
 * @param {function} [onFinish] - Callback function to execute when export is complete.
 */
function exportTable(selector, onFinish) {
    // Stop if all tables have been processed
    if (tableIndex >= maxTableIndex) {
        isExporting = false;
        if (typeof onFinish === 'function') {
            onFinish();
        }

        // Trigger download using a hidden iframe to prevent page navigation.
        // This is a common method for initiating file downloads without
        // redirecting the current page.
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none'; // Make the iframe invisible
        iframe.src = 'export-download.php?fileName=' + encodeURIComponent(fileName) + '&downloadName=' + encodeURIComponent(downloadName);
        document.body.appendChild(iframe); // Append the iframe to the document body

        // Remove the iframe after a short delay to clean up the DOM.
        // The download usually initiates immediately, so the iframe is no longer needed.
        setTimeout(() => {
            document.body.removeChild(iframe);
        }, 5000); // Remove after 5 seconds
    } else {
        const currentTable = exportTableList[tableIndex];

        // Find the table row corresponding to the current table being exported
        // and update its status.
        let tr = $(selector).find('table tbody').find('tr[data-table-name="' + currentTable.tableName + '"]');
        if (tr.length) {
            tr.attr('data-status', 'in-progress');
        }

        // Get the target database type from the form input.
        let targetDatabaseType = qs('[name="target_database_type"]').value;

        // Send AJAX request to the server to export the current table.
        $.ajax({
            type: 'POST',
            url: 'export-database.php',
            data: {
                ...exportConfig, // Spread operator to include existing export configuration
                tableName: currentTable.tableName,
                includeStructure: currentTable.structure ? 1 : 0, // Convert boolean to 1 or 0
                includeData: currentTable.data ? 1 : 0,           // Convert boolean to 1 or 0
                fileName: fileName,
                targetDatabaseType: targetDatabaseType
            },
            dataType: 'json', // Expecting a JSON response from the server
            success: function (data) {
                // If the export of the current table was successful,
                // update its status and proceed to the next table.
                if (data.success) {
                    if (tr.length) {
                        tr.attr('data-status', 'finish');
                    }
                    tableIndex++; // Increment index to move to the next table
                    exportTable(selector, onFinish); // Recursively call for the next table
                } else if (tr.length) {
                    // If export failed for this table, mark its status as 'error'.
                    tr.attr('data-status', 'error');
                }
            },
            error: function () {
                // If the AJAX request itself failed (e.g., network error, server error),
                // stop the export process and mark the current table as 'error'.
                isExporting = false;
                if (tr.length) {
                    tr.attr('data-status', 'error');
                }
            }
        });
    }
}

/**
 * Fetches the list of tables to export and displays them in the export modal.
 * Also sets up select-all checkboxes for structure and data.
 *
 * @param {string} selector - The modal selector to inject the table list.
 * @param {string} tableName - Optional table name to preselect or focus on.
 */
function listTableToExport(selector, tableName) {
    let { applicationId, databaseName, databaseSchema, databaseType } = getMetaValues();
    $(selector).find('.modal-body').empty().append('<div style="text-align: center;"><span class="animation-wave"><span></span></span></div>');
    $.ajax({
        type: 'GET',
        url: 'table-list.php',
        data: {
            applicationId: applicationId,
            databaseType: databaseType,
            databaseName: databaseName,
            schemaName: databaseSchema,
            tableName: tableName
        },
        success: function(data) {
            // Inject content into modal
            $(selector).find('.modal-body').empty().append(data);

            // Enable select-all for structure checkboxes
            $('#cstructure').on('change', function() {
                let checked = $(this)[0].checked;
                $('.check-for-structure').each(function(){
                    $(this)[0].checked = checked;
                });
            });

            // Enable select-all for data checkboxes
            $('#cdata').on('change', function() {
                let checked = $(this)[0].checked;
                $('.check-for-data').each(function(){
                    $(this)[0].checked = checked;
                });
            });
        }
    });

    // Clean up files
    $.ajax({
        type: 'GET',
        url: 'export-clean-up.php',
        success: function(data){

        }
    });
}

/**
 * Displays a confirmation modal dialog before export.
 * Executes a callback function with `true` if OK is clicked, otherwise `false`.
 *
 * @param {string} selector - The selector for the modal element.
 * @param {string} message - The message to display inside the modal body.
 * @param {string} title - The modal title.
 * @param {string} captionOk - Text for the OK button.
 * @param {string} captionCancel - Text for the Cancel button.
 * @param {function} callback - A callback to execute with the user's choice.
 * @returns {HTMLElement} - The modal DOM element.
 */
function showExportDialog(selector, message, title, captionOk, captionCancel, callback) {
    const modal = qs(selector);
    const okBtn = modal.querySelector('.button-ok');
    const cancelBtn = modal.querySelector('.button-cancel');

    // Set modal content
    modal.querySelector('.modal-header h3').innerHTML = title;
    modal.querySelector('.modal-body').innerHTML = message;
    okBtn.innerHTML = captionOk;
    cancelBtn.innerHTML = captionCancel;

    // Show modal
    modal.style.display = 'block';

    // Remove previous event listeners to prevent duplicates
    okBtn.removeEventListener('click', handleOkConfig);
    cancelBtn.removeEventListener('click', handleCancelConfig);

    // Handle OK click
    function handleOkConfig() {
        callback(true);
    }

    // Handle Cancel click
    function handleCancelConfig() {
        callback(false);
    }

    // Add listeners
    okBtn.addEventListener('click', handleOkConfig);
    cancelBtn.addEventListener('click', handleCancelConfig);

    return modal;
}

/**
 * Updates the left margin of the tab list to scroll tabs horizontally.
 *
 * @param {number} step - The number of pixels to move the margin left (negative to scroll right).
 */
function updateMarginLeft(step) {
    currentMarginLeft += step;
    // Ensure the margin-left does not exceed the container's width
    let ulElement = tabsLinkContainer.querySelector('ul');
    let maxScroll = ulElement.scrollWidth - tabsLinkContainer.offsetWidth;
    currentMarginLeft = Math.max(Math.min(currentMarginLeft, 0), -maxScroll);
    ulElement.style.marginLeft = `${currentMarginLeft}px`;
}

/**
 * Move the tab list to the first position (far left).
 *
 * This function resets the margin-left of the <ul> inside tabsLinkContainer
 * so that the tab list scrolls back to the very beginning.
 * It ensures the margin-left value does not exceed the container's width
 * by clamping it between 0 and -maxScroll.
 */
function moveTabToFirst() {
    currentMarginLeft = 0;
    // Ensure the margin-left does not exceed the container's width
    let ulElement = tabsLinkContainer.querySelector('ul');
    let maxScroll = ulElement.scrollWidth - tabsLinkContainer.offsetWidth;
    currentMarginLeft = Math.max(Math.min(currentMarginLeft, 0), -maxScroll);
    ulElement.style.marginLeft = `${currentMarginLeft}px`;
}

/**
 * Move the tab list to the last position (far right).
 *
 * This function sets the margin-left of the <ul> inside tabsLinkContainer
 * so that the tab list scrolls to the very end.
 * It calculates the maximum scroll offset by subtracting the ul width
 * from the container width, then applies it as margin-left.
 */
function moveTabToLast() {
    // Ensure the margin-left does not exceed the container's width
    let ulElement = tabsLinkContainer.querySelector('ul');
    let maxScroll = tabsLinkContainer.offsetWidth - ulElement.scrollWidth;
    currentMarginLeft = maxScroll;
    ulElement.style.marginLeft = `${currentMarginLeft}px`;
}

/**
 * Downloads the currently active diagram as an SVG file.
 */
function downloadSVG() {
    editor.downloadSVG();
}

/**
 * Downloads the currently active diagram as a PNG file.
 */
function downloadPNG() {
    editor.downloadPNG();
}

/**
 * Downloads the currently active diagram as a Markdown (MD) file.
 */
function downloadMD() {
    editor.downloadMD();
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
 * @returns {void} - This function does not return a value.
 */
function showConfirmationDialog(message, title, captionOk, captionCancel, callback) {
    // Get modal and buttons
    const modal = qs('#asyncConfirm');
    let okBtn = modal.querySelector('.confirm-ok');
    let cancelBtn = modal.querySelector('.confirm-cancel');
    okBtn = removeAllEventListeners(okBtn);
    cancelBtn = removeAllEventListeners(cancelBtn);

    modal.querySelector('.modal-header h3').innerHTML = title;
    modal.querySelector('.modal-body').innerHTML = message;
    okBtn.innerHTML = captionOk;
    cancelBtn.innerHTML = captionCancel;

    // Show the modal
    modal.style.display = 'block';

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

/**
 * Removes all event listeners from the given element by replacing it with a cloned copy.
 * The new element will be an exact copy of the original element, including its children and attributes,
 * but without any event listeners attached.
 *
 * @param {HTMLElement} element - The DOM element from which event listeners will be removed.
 *
 * @returns {HTMLElement} - The cloned element that is a replacement for the original element, without event listeners attached.
 */
function removeAllEventListeners(element) {
    const newElement = element.cloneNode(true);  // clone the element with all children and attributes
    element.parentNode.replaceChild(newElement, element);  // replace the old element with the new one
    return newElement;  // return the cloned element
}

/**
 * Sends data to the server using the POST method with URL-encoded format.
 *
 * @param {string} applicationId - The application ID to be sent.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} entities - The list of entities to be sent to the server.
 */
function sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities) {
    let data = {
        applicationId: applicationId,
        databaseType: databaseType,
        databaseName: databaseName,
        databaseSchema: databaseSchema,
        entities: entities,
        saveEntity: true,
        saveDiagram: false
    };

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('entity', applicationId, databaseType, databaseName, databaseSchema, '');

    xhr.open('POST', url, true); // Open a POST connection to the server


    // Set the header to send data in URL-encoded format
    xhr.setRequestHeader('Content-Type', 'application/json');

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                // Response received successfully
                let { applicationId, databaseName, databaseSchema, databaseType, hash } = getMetaValues();
                loadDatabaseIndex(applicationId, hash);
            } else {
                console.error('An error occurred while sending data to the server'); // Log error if status is not 200
            }
        }
    };

    xhr.send(JSON.stringify(data));
}

/**
 * Sends data to the server using the POST method with URL-encoded format.
 *
 * @param {string} applicationId - The application ID to be sent.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} diagrams - The list of diagrams to be sent to the server.
 */
function sendDiagramToServer(applicationId, databaseType, databaseName, databaseSchema, diagrams) {
    let data = {
        applicationId: applicationId,
        databaseType: databaseType,
        databaseName: databaseName,
        databaseSchema: databaseSchema,
        diagrams: diagrams,
        saveDiagram: true,
        saveEntity: false
    };

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('entity', applicationId, databaseType, databaseName, databaseSchema, '');

    xhr.open('POST', url, true); // Open a POST connection to the server

    // Set the header to send data in URL-encoded format
    xhr.setRequestHeader('Content-Type', 'application/json');

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                // Response received successfully
            } else {
                console.error('An error occurred while sending data to the server'); // Log error if status is not 200
            }
        }
    };

    xhr.send(JSON.stringify(data));
}

/**
 * Fetches data from the server using the GET method with the provided parameters.
 *
 * @param {string} applicationId - The application ID being used.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} entities - The list of entities to be fetched.
 * @param {Function} callback - The callback function that will be called after the data is fetched.
 */
function fetchEntityFromServer(applicationId, databaseType, databaseName, databaseSchema, entities, callback) {
    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('entity', applicationId, databaseType, databaseName, databaseSchema, '');
    // Construct the URL with query parameters

    xhr.open('GET', url, true); // Open a GET connection to the server

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                const response = xhr.responseText;  // Get the response from the server
                try {
                    const parsedData = JSON.parse(response);  // Try to parse the JSON response
                    if(parsedData && parsedData.entities)
                    {
                        let data = editor.createEntitiesFromJSON(parsedData);
                        if(data)
                        {
                            editor.entities = data.entities || [] // Insert the received data into editor.entities
                            editor.diagrams = data.diagrams || [];
                        }
                        else
                        {
                            editor.entities = [];
                            editor.diagrams = [];
                        }
                        editor.refreshEntities();
                        editor.clearDiagrams();
                        editor.clearGeneratedQuery();
                        editor.prepareDiagram();
                        if (callback) callback(null, parsedData); // Call the callback with parsed data (if provided)
                    }
                    else
                    {
                        editor.entities = [];
                        editor.diagrams = [];
                        editor.refreshEntities();
                        editor.clearDiagrams();
                        editor.clearGeneratedQuery();
                        editor.prepareDiagram();
                    }
                } catch (err) {
                    editor.entities = [];
                    editor.diagrams = [];
                    editor.refreshEntities();
                    editor.clearDiagrams();
                    editor.clearGeneratedQuery();
                    editor.prepareDiagram();
                    console.error("Error parsing JSON from fetchEntityFromServer:", err.message, response);
                }
            } else {
                console.error('An error occurred while fetching data from the server. Status:', xhr.status); // Log error if status is not 200
            }
        }
    };

    // Send the GET request to the server
    xhr.send();
}

/**
 * Sends data to the server using the POST method with URL-encoded format.
 *
 * @param {string} applicationId - The application ID to be sent.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} template - The list of entities to be sent to the server.
 */
function sendTemplateToServer(applicationId, databaseType, databaseName, databaseSchema, template) {
    const data = {
        applicationId: applicationId,
        databaseType: databaseType,
        databaseName: databaseName,
        databaseSchema: databaseSchema,
        template: JSON.stringify(template)
    };

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('template', applicationId, databaseType, databaseName, databaseSchema, '');

    xhr.open('POST', url, true); // Open a POST connection to the server

    // Set the header to send data in URL-encoded format
    xhr.setRequestHeader('Content-Type', 'application/json');

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                // Response received successfully
            } else {
                console.error('An error occurred while sending data to the server'); // Log error if status is not 200
            }
        }
    };

    xhr.send(JSON.stringify(data));
}

/**
 * Fetches data from the server using the GET method with the provided parameters.
 *
 * @param {string} applicationId - The application ID being used.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} template - The list of entities to be fetched.
 * @param {Function} callback - The callback function that will be called after the data is fetched.
 */
function fetchTemplateFromServer(applicationId, databaseType, databaseName, databaseSchema, template, callback) {

    if(applicationId != '')
    {
        const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
        const url = buildUrl('template', applicationId, databaseType, databaseName, databaseSchema, '');
        // Construct the URL with query parameters

        xhr.open('GET', url, true); // Open a GET connection to the server
        // Handle the server response
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {  // Check if the request is complete
                if (xhr.status === 200) {  // Check if the response is successful (status 200)
                    const response = xhr.responseText;  // Get the response from the server
                    try {
                        const parsedData = JSON.parse(response);  // Try to parse the JSON response
                        editor.template = editor.createTemplateFromJSON(parsedData); // Insert the received data into editor.entities
                        if(typeof callback == 'function')
                        {
                            callback(parsedData);
                        }
                    } catch (err) {
                        console.error(err)
                    }
                } else {
                    console.error('An error occurred while fetching data from the server. Status:', xhr.status); // Log error if status is not 200
                }
            }
        };

        // Send the GET request to the server
        xhr.send();
    }
}

/**
 * Sends data to the server using the POST method with URL-encoded format.
 *
 * @param {string} applicationId - The application ID to be sent.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} config - The list of entities to be sent to the server.
 */
function sendConfigToServer(applicationId, databaseType, databaseName, databaseSchema, config) {
    const data = {
        applicationId: applicationId,
        databaseType: databaseType,
        databaseName: databaseName,
        databaseSchema: databaseSchema,
        config: config
    };

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('config', applicationId, databaseType, databaseName, databaseSchema, '');

    xhr.open('POST', url, true); // Open a POST connection to the server

    // Set the header to send data in URL-encoded format
    xhr.setRequestHeader('Content-Type', 'application/json');

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                // Response received successfully
            } else {
                console.error('An error occurred while sending data to the server'); // Log error if status is not 200
            }
        }
    };

    xhr.send(JSON.stringify(data));
}

/**
 * Fetches data from the server using the GET method with the provided parameters.
 *
 * @param {string} applicationId - The application ID being used.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} config - The list of entities to be fetched.
 * @param {Function} callback - The callback function that will be called after the data is fetched.
 */
function fetchConfigFromServer(applicationId, databaseType, databaseName, databaseSchema, config, callback) {

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('config', applicationId, databaseType, databaseName, databaseSchema, '');
    // Construct the URL with query parameters

    xhr.open('GET', url, true); // Open a GET connection to the server
    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                const response = xhr.responseText;  // Get the response from the server
                try {
                    const parsedData = JSON.parse(response);  // Try to parse the JSON response

                    editor.defaultDataType = parsedData.defaultDataType + '';
                    editor.defaultDataLength = parsedData.defaultDataLength + '';
                    editor.primaryKeyDataType = parsedData.primaryKeyDataType + '';
                    editor.primaryKeyDataLength = parsedData.primaryKeyDataLength + '';
                } catch (err) {
                    console.error(err)
                }
            } else {
                console.error('An error occurred while fetching data from the server. Status:', xhr.status); // Log error if status is not 200
            }
        }
    };

    // Send the GET request to the server
    xhr.send();
}

/**
 * Builds the URL for the GET request by appending query parameters.
 *
 * @param {string} type - 'entity', 'config' or 'template'
 * @param {string} applicationId - The application ID being used.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} entities - The list of entities to be included in the query string.
 * @param {string} profile - The GraphQL application profile.
 *
 * @returns {string} The URL with query parameters appended.
 */
function buildUrl(type, applicationId, databaseType, databaseName, databaseSchema, entities, profile) {
    if(type == 'template')
    {
        return `../lib.ajax/load-template-data.php?applicationId=${encodeURIComponent(applicationId)}&databaseType=${encodeURIComponent(databaseType)}&databaseName=${encodeURIComponent(databaseName)}&databaseSchema=${encodeURIComponent(databaseSchema)}&entities=${encodeURIComponent(JSON.stringify(entities))}`;
    }
    else if(type == 'config')
    {
        return `../lib.ajax/load-config-data.php?applicationId=${encodeURIComponent(applicationId)}&databaseType=${encodeURIComponent(databaseType)}&databaseName=${encodeURIComponent(databaseName)}&databaseSchema=${encodeURIComponent(databaseSchema)}&entities=${encodeURIComponent(JSON.stringify(entities))}`;
    }
    else if(type == 'graphql-entity')
    {
        return `../lib.ajax/load-graphql-entity-data.php?applicationId=${encodeURIComponent(applicationId)}&databaseType=${encodeURIComponent(databaseType)}&databaseName=${encodeURIComponent(databaseName)}&databaseSchema=${encodeURIComponent(databaseSchema)}&entities=${encodeURIComponent(JSON.stringify(entities))}&profile=${profile}`;
    }
    else
    {
        return `../lib.ajax/load-entity-data.php?applicationId=${encodeURIComponent(applicationId)}&databaseType=${encodeURIComponent(databaseType)}&databaseName=${encodeURIComponent(databaseName)}&databaseSchema=${encodeURIComponent(databaseSchema)}&entities=${encodeURIComponent(JSON.stringify(entities))}`;
    }
}

/**
 * Disables all options in a <select> element except the currently selected one.
 *
 * @param {HTMLSelectElement} selectElement - The select element whose options will be disabled.
 */
function disableOtherOptions(selectElement) {
    const selectedValue = selectElement.value;
    for (let option of selectElement.options) {
        option.disabled = option.value !== selectedValue;
    }
}

/**
 * Enables all options in a <select> element.
 *
 * @param {HTMLSelectElement} selectElement - The select element whose options will be enabled.
 */
function enableAllOptions(selectElement) {
    for (let option of selectElement.options) {
        option.disabled = false;
    }
}
