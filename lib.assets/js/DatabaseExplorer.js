

// Move init() outside of the class
function init() {
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

    openModalQuertTranslatorButton.onclick = function() {
        modalQueryTranslator.style.display = "block";
        original.focus();
    };

    openModalEntityEditorButton.onclick = function() {
        modalEntityEditor.style.display = "block";
        resizablePanels.loadPanelWidth();
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
        editor.renderEntities();
    });
    
}

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

// Instantiate the class
const converter = new SQLConverter();
let editor;
let renderer;
let resizablePanels;

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {

    // Select all toggle buttons within collapsible elements
    const toggles = document.querySelectorAll('.collapsible .button-toggle');
    renderer = new EntityRenderer(".erd-svg");

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

    document.querySelector('[type="submit"][name="___export_database___"]').addEventListener('click', function(event) {
        event.preventDefault();
        showConfirmationDialog('Are you sure you want to export the data from the database?', 'Export Confirmation', 'Yes', 'No', function(isConfirmed) {
            if (isConfirmed) {
                
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = '___export_database___';
                hiddenInput.value = 'true';
                event.target.closest('form').appendChild(hiddenInput);
                
                event.target.closest('form').submit();  // Submit the form containing the button
                event.target.closest('form').removeChild(hiddenInput);
            } 
        });
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
    
    window.addEventListener('resize', function () {
        // Get the updated width of the SVG container
        editor.renderEntities();
    });
    resizablePanels = new ResizablePanels('.entity-editor', '.left-panel', '.right-panel', '.resize-bar', 200);
    init();

});

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
    const data = {
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
                    editor.entities = editor.createEntitiesFromJSON(parsedData); // Insert the received data into editor.entities
                    editor.renderEntities(); // Update the view with the fetched entities
                    if (callback) callback(null, parsedData); // Call the callback with parsed data (if provided)
                } catch (err) {
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

