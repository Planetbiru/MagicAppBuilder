let tabsLinkContainer;
let currentMarginLeft = 0;

// Instantiate the class
let converter = null;
let editor;
let entityRenderer;
let diagramRenderer = {};
let resizablePanels;

let scrollElement = null;
const SCROLL_POSITION_KEY = 'scrollPosition.tableList';
let timeout = setTimeout('', 10000);

let tableIndex = 0;
let maxTableIndex = 0;
let exportConfig = {};
let exportTableList = [];
let exportFileName = '';
let timeoutDownload = setTimeout('', 100);
let isExporting = false;

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
  scrollElement = document.querySelector('.table-list');
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
  scrollElement = document.querySelector('.table-list');
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
    let closeModalButton = document.querySelectorAll(".cancel-button");
    let openModalQuertTranslatorButton = document.querySelector(".import-structure");
    let openModalEntityEditorButton = document.querySelector(".open-entity-editor"); 
    let openFileButton  = document.querySelector(".open-structure");
    let translateButton  = document.querySelector(".translate-structure");
    let importFromEntityButton = document.querySelector('.import-from-entity');
    let clearButton  = document.querySelector(".clear");
    let original = document.querySelector('.original');
    let queryGenerated = document.querySelector('.query-generated');
    let query = document.querySelector('[name="query"]');
    let deleteCells = document.querySelectorAll('.cell-delete a');

    initTableScrollPosition();

    openModalQuertTranslatorButton.onclick = function() {
        modalQueryTranslator.style.display = "block";
        original.focus();
    };

    openModalEntityEditorButton.onclick = function() {
        modalEntityEditor.style.display = "block";
        resizablePanels.loadPanelWidth();
        editor.updateDiagram();

    };
    
    closeModalButton.forEach(function(cancelButton) {
        cancelButton.onclick = function(e) {
            e.target.closest('.modal').style.display = "none";
        }
    });
    
    clearButton.onclick = function() {
        original.value = "";
    };
    
    translateButton.onclick = function()
    {
        let sql = original.value;
        let type = document.querySelector('meta[name="database-type"]').getAttribute('content');
        let converted = converter.translate(sql, type);
        document.querySelector('[name="query"]').value = converted;
        modalQueryTranslator.style.display = "none";
    };

    openFileButton.onclick = function()
    {
        document.querySelector('.structure-sql').click();
    }
    
    importFromEntityButton.onclick = function()
    {
        let sql = queryGenerated.value;
        let type = document.querySelector('meta[name="database-type"]').getAttribute('content');
        let converted = '';
        if(type.toLowerCase() != 'mysql' && type.toLowerCase() != 'mariadb')
        {
            converted = converter.translate(sql, type);
        }
        else
        {
            converted = sql;
        }
        document.querySelector('[name="query"]').value = converted;
        modalEntityEditor.style.display = "none";
    };

    deleteCells.forEach(function(cell) {
        cell.addEventListener('click', function(event) {
            event.preventDefault();
            let schema = event.target.getAttribute('data-schema');
            let table = event.target.getAttribute('data-table');
            let primaryKey = event.target.getAttribute('data-primary-key');
            let value = event.target.getAttribute('data-value');
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

    window.onclick = function(event) {
        if (event.target == modalQueryTranslator) {
            modalQueryTranslator.style.display = "none";
        }
    };
    document.querySelector('.structure-sql').addEventListener('change', function(e){
        openStructure(this.files[0]);
    });

    document.querySelector('.draw-relationship').addEventListener('change', function(e){
        editor.refreshEntities();
        editor.updateDiagram();
    });
    
}

/**
 * Opens the structure of the provided file and reads its content.
 * The content is then displayed in the element with the class 'original'.
 * 
 * @param {File} file - The file to be read.
 */
function openStructure(file)
{
    const reader = new FileReader(); // Create a FileReader instance
    reader.onload = function (e) {
        try {
            document.querySelector('.original').value = e.target.result;
            
        } catch (err) {
            console.log("Error parsing JSON: " + err.message); // Handle JSON parsing errors
        }
    };
    reader.readAsText(file); // Read the file as text
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {

    // Select all toggle buttons within collapsible elements
    const toggles = document.querySelectorAll('.collapsible .button-toggle');
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
                let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                fetchEntityFromServer(applicationId, databaseType, databaseName, databaseSchema);
                fetchConfigFromServer(applicationId, databaseType, databaseName, databaseSchema);         
            }, 
            callbackSaveEntity: function (entities){
                let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                sendEntityToServer(applicationId, databaseType, databaseName, databaseSchema, entities); 
            },
            callbackSaveDiagram: function (diagrams){
                let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                sendDiagramToServer(applicationId, databaseType, databaseName, databaseSchema, diagrams); 
            },
            callbackLoadTemplate: function(){
                let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                fetchTemplateFromServer(applicationId, databaseType, databaseName, databaseSchema);         
            }, 
            callbackSaveTemplate: function (template){
                let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                sendTemplateToServer(applicationId, databaseType, databaseName, databaseSchema, template); 
            },
            callbackSaveConfig: function (template){
                let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                sendConfigToServer(applicationId, databaseType, databaseName, databaseSchema, template); 
            }
        }
    );

    document.querySelector('[type="submit"].execute').addEventListener('click', function(event) {
        event.preventDefault();
        showConfirmationDialog('Are you sure you want to execute the query?', 'Execute Query Confirmation', 'Yes', 'No', function(isConfirmed) {
            if (isConfirmed) {
                event.target.closest('form').submit();  // Submit the form containing the button
            } 
        });
    });

    document.querySelector('[type="button"].save').addEventListener('click', function(event) {
        event.preventDefault();
        let query = document.querySelector('textarea[name="query"]').value.trim();
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

    let btnTableExport = document.querySelector('[type="submit"][name="___export_table___"]');
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

    let btnDatabaseExport = document.querySelector('[type="submit"][name="___export_database___"]');
    if(btnDatabaseExport != null)
    {
        btnDatabaseExport.addEventListener('click', function(event) {
            let tableName = event.target.getAttribute('value');
            event.preventDefault();

            let selector = '#exportModal';
            showExprtDialog(selector, 
                '<div class="loading-animation"></div>', 
                'Export Database', 'Yes', 'No', function(isOk) {
                if (isOk) 
                {
                    let select = document.querySelector('[name="target_database_type"]');
                    disableOtherOptions(select);
                    startExportDatabase(selector, function(){
                        enableAllOptions(select);
                    });
                }
                else 
                {
                    let select = document.querySelector('[name="target_database_type"]');
                    enableAllOptions(select);
                    document.querySelector('#exportModal').style.display = 'none' 
                } 
            });
            listTableToExport(selector, tableName);
        });
    }
    
    window.addEventListener('resize', function () {
        // Get the updated width of the SVG container
        editor.refreshEntities();
        editor.updateDiagram();
    });

    document.querySelector('.add-diagram').addEventListener('click', function(e){
        e.preventDefault();
        let ul = e.target.closest('ul');
        let diagramName = editor.getNewDiagramName();
        let randomId = (new Date()).getTime();
        let id = 'diagram-'+randomId;
        editor.addDiagram(ul, diagramName, id, [], false);
        editor.saveDiagram();
    });

    document.querySelector('[data-id="all-entities"]').addEventListener('click', function(e){
        e.preventDefault();
        let li = e.target.parentNode;
        let diagramContainer = document.querySelector('.diagram-container');

        li.closest('ul').querySelectorAll('li.diagram-tab').forEach((tab, index) => {
            tab.classList.remove('active');
        });
        diagramContainer.querySelectorAll('.diagram').forEach((tab, index) => {
            tab.classList.remove('active');
        });
        li.classList.add('active');
        let selector = 'all-entities';
        diagramContainer.querySelector('#'+selector).classList.add('active');
        document.querySelector('.entity-editor .left-panel .table-list').querySelectorAll('li').forEach((li, index) => {
            let input = li.querySelector('input[type="checkbox"]');
            input.checked = false;
            input.disabled = true;
        });
    });

    // Listen for changes in checkboxes within the table-list
    document.addEventListener('change', function (e) {
        if (e.target.closest('.table-list input[type="checkbox"]')) {
            
            let diagram = document.querySelector('.diagram-container .diagram.active');
            let currentSelection = diagram.getAttribute('data-entities').split(',');

            let selectedEntities = new Set(); // Use a Set to store selected entities
            
            // Iterate through checkboxes and add checked ones to the set
            e.target.closest('.table-list').querySelectorAll('input[type="checkbox"]').forEach(input => {
                let entity = input.getAttribute('data-name');
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
    

    resizablePanels = new ResizablePanels('.entity-editor', '.left-panel', '.right-panel', '.resize-bar', 200);

    tabsLinkContainer = document.querySelector('.tabs-link-container');
    

    document.querySelector('.tab-mover li a.move-left').addEventListener('click', function(event) {
        event.preventDefault();
        updateMarginLeft(-30);
    });
    
    document.querySelector('.tab-mover li a.move-right').addEventListener('click', function(event) {
        event.preventDefault();
        updateMarginLeft(30);
    });
    
    tabsLinkContainer.addEventListener('wheel', (event) => {
        event.preventDefault();
        const delta = event.deltaY || event.detail || event.wheelDelta;
        const step = 30; // Adjust the step size as needed
    
        updateMarginLeft(delta > 0 ? -step : step);
    });


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
});

/**
 * Initiates the database export process if no other export is currently running.
 *
 * @param {string} selector - A CSS selector targeting the HTML element(s) 
 *                            to export data from (e.g., a table row).
 */
function startExportDatabase(selector, onFinish)
{
    if (!isExporting)
    {
        isExporting = true;
        exportDatabase(selector, onFinish);
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
    let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
    let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
    let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
    let schemaName = document.querySelector('meta[name="database-schema"]').getAttribute('content');

    exportTableList = [];

    // Clear timeout
    clearTimeout(timeoutDownload);
    tableIndex = 0;
    
    // Generate a timestamped export filename
    exportFileName = (new Date()).getTime() + '.sql';

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
        schemaName: schemaName
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
        iframe.src = 'export-download.php?fileName=' + encodeURIComponent(exportFileName);
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
        let targetDatabaseType = document.querySelector('[name="target_database_type"]').value;

        // Send AJAX request to the server to export the current table.
        $.ajax({
            type: 'POST',
            url: 'export-database.php',
            data: {
                ...exportConfig, // Spread operator to include existing export configuration
                tableName: currentTable.tableName,
                includeStructure: currentTable.structure ? 1 : 0, // Convert boolean to 1 or 0
                includeData: currentTable.data ? 1 : 0,           // Convert boolean to 1 or 0
                fileName: exportFileName,
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
    let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
    let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
    let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
    let schemaName = document.querySelector('meta[name="database-schema"]').getAttribute('content');
    $(selector).find('.modal-body').empty().append('<div style="text-align: center;"><span class="animation-wave"><span></span></span></div>');
    $.ajax({
        type: 'GET',
        url: 'table-list.php',
        data: {
            applicationId: applicationId,
            databaseType: databaseType,
            databaseName: databaseName,
            schemaName: schemaName,
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
function showExprtDialog(selector, message, title, captionOk, captionCancel, callback) {
    const modal = document.querySelector(selector);
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
 * Downloads the currently active diagram as an SVG file.
 */
function downloadSVG()
{
    let diagramContainer = document.querySelector('.diagram-container');
    let diagram = diagramContainer.querySelector('.diagram.active');
    if(diagram)
    {
        let id = diagram.getAttribute('id');
        if(id == 'all-entities')
        {
            entityRenderer.downloadSVG();
        }
        else
        {
            diagramRenderer[id].downloadSVG();
        }
    }
}

/**
 * Downloads the currently active diagram as a PNG file.
 */
function downloadPNG()
{
    let diagramContainer = document.querySelector('.diagram-container');
    let diagram = diagramContainer.querySelector('.diagram.active');
    if(diagram)
    {
        let id = diagram.getAttribute('id');
        if(id == 'all-entities')
        {
            entityRenderer.downloadPNG();
        }
        else
        {
            diagramRenderer[id].downloadPNG();
        }
    }
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
    const modal = document.querySelector('#asyncConfirm');
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
        entities: JSON.stringify(entities)  // Converting the entities array into a JSON string
    };

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('entity', applicationId, databaseType, databaseName, databaseSchema, '');

    xhr.open('POST', url, true); // Open a POST connection to the server

    // Set the header to send data in URL-encoded format
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                // Response received successfully
            } else {
                console.log('An error occurred while sending data to the server'); // Log error if status is not 200
            }
        }
    };

    // Prepare data in URL-encoded format
    const urlEncodedData = new URLSearchParams(data).toString();

    // Send the data in URL-encoded format
    xhr.send(urlEncodedData);
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
        diagrams: JSON.stringify(diagrams)  // Converting the entities array into a JSON string
    };

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('entity', applicationId, databaseType, databaseName, databaseSchema, '');

    xhr.open('POST', url, true); // Open a POST connection to the server

    // Set the header to send data in URL-encoded format
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                // Response received successfully
            } else {
                console.log('An error occurred while sending data to the server'); // Log error if status is not 200
            }
        }
    };

    // Prepare data in URL-encoded format
    const urlEncodedData = new URLSearchParams(data).toString();

    // Send the data in URL-encoded format
    xhr.send(urlEncodedData);
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
                    let data = editor.createEntitiesFromJSON(parsedData);
                    editor.entities = data.entities || [] // Insert the received data into editor.entities
                    editor.diagrams = data.diagrams || [];
                    editor.refreshEntities();
                    editor.prepareDiagram();
                    if (callback) callback(null, parsedData); // Call the callback with parsed data (if provided)
                } catch (err) {
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
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                // Response received successfully
            } else {
                console.log('An error occurred while sending data to the server'); // Log error if status is not 200
            }
        }
    };


    // Prepare data in URL-encoded format
    const urlEncodedData = new URLSearchParams(data).toString();

    // Send the data in URL-encoded format
    xhr.send(urlEncodedData);
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
        config: JSON.stringify(config) 
    };

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl('config', applicationId, databaseType, databaseName, databaseSchema, '');

    xhr.open('POST', url, true); // Open a POST connection to the server

    // Set the header to send data in URL-encoded format
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    // Handle the server response
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {  // Check if the request is complete
            if (xhr.status === 200) {  // Check if the response is successful (status 200)
                // Response received successfully
            } else {
                console.log('An error occurred while sending data to the server'); // Log error if status is not 200
            }
        }
    };


    // Prepare data in URL-encoded format
    const urlEncodedData = new URLSearchParams(data).toString();

    // Send the data in URL-encoded format
    xhr.send(urlEncodedData);
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
 * 
 * @returns {string} The URL with query parameters appended.
 */
function buildUrl(type, applicationId, databaseType, databaseName, databaseSchema, entities) {
    if(type == 'template')
    {
        return `../lib.ajax/load-template-data.php?applicationId=${encodeURIComponent(applicationId)}&databaseType=${encodeURIComponent(databaseType)}&databaseName=${encodeURIComponent(databaseName)}&databaseSchema=${encodeURIComponent(databaseSchema)}&entities=${encodeURIComponent(JSON.stringify(entities))}`;
    }
    else if(type == 'config')
    {
        return `../lib.ajax/load-config-data.php?applicationId=${encodeURIComponent(applicationId)}&databaseType=${encodeURIComponent(databaseType)}&databaseName=${encodeURIComponent(databaseName)}&databaseSchema=${encodeURIComponent(databaseSchema)}&entities=${encodeURIComponent(JSON.stringify(entities))}`;
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
