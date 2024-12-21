

// Move init() outside of the class
function init() {
    let modalQueryTranslator = document.getElementById("queryTranslatorModal");
    let modalEntityEditor = document.getElementById("entityEditorModal");
    let closeModalButton = document.querySelectorAll(".cancel-button");
    let openModalQuertTranslatorButton = document.querySelector(".import-structure");
    let openModalEntityEditorButton = document.querySelector(".open-entity-editor");
    
    let translateButton  = document.querySelector(".translate-structure");
    let importFromEntityButton = document.querySelector('.import-from-entity');
    let clearButton  = document.querySelector(".clear");
    let original = document.querySelector('#original');
    let queryGenerated = document.querySelector('.query-generated');
    let query = document.querySelector('[name="query"]');
    let deleteCells = document.querySelectorAll('.cell-delete a');

    // Menampilkan modal saat tombol di klik
    openModalQuertTranslatorButton.onclick = function() {
        modalQueryTranslator.style.display = "block";
        original.focus();
    }

    openModalEntityEditorButton.onclick = function() {
        modalEntityEditor.style.display = "block";
        resizablePanels.loadPanelWidth();
    }
    

    
    closeModalButton.forEach(function(cancelButton) {
        cancelButton.onclick = function(e) {
            e.target.closest('.modal').style.display = "none";
        }
    });
    
    // Menutup modal saat tombol 'Close' di footer di klik
    clearButton.onclick = function() {
        original.value = "";
    }
    
    translateButton.onclick = function()
    {
        let sql = original.value;
        let type = document.querySelector('meta[name="database-type"]').getAttribute('content');
        let converted = converter.translate(sql, type);
        document.querySelector('[name="query"]').value = converted;
        modalQueryTranslator.style.display = "none";
    }
    
    importFromEntityButton.onclick = function()
    {
        let sql = queryGenerated.value;
        let type = document.querySelector('meta[name="database-type"]').getAttribute('content');
        let converted = converter.translate(sql, type);
        document.querySelector('[name="query"]').value = converted;
        modalEntityEditor.style.display = "none";
    }
    deleteCells.forEach(function(cell) {
        cell.addEventListener('click', function(event) {
            event.preventDefault();
            let schema = event.target.getAttribute('data-schema');
            let table = event.target.getAttribute('data-table');
            let primaryKey = event.target.getAttribute('data-primary-key');
            let value = event.target.getAttribute('data-value');
            let queryString = "";
            let tableName = schema == "" ? `${schema}.${table}` : table;
            queryString = `DELETE FROM ${tableName} WHERE ${primaryKey} = '${value}' `;
            query.value = queryString;

        });
    });
    window.onclick = function(event) {
        if (event.target == modalQueryTranslator) {
            modalQueryTranslator.style.display = "none";
        }
    }
}



// Instantiate the class
const converter = new SQLConverter();
let editor;
let resizablePanels;

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    
    editor = new EntityEditor('.entity-editor', 
        {
            defaultDataType: 'VARCHAR',
            defaultDataLength: 50,
            callbackLoadEntity: function(){
                let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                fetchDataFromServer(applicationId, databaseType, databaseName, databaseSchema)
            }, 
            callbackSaveEntity: function (entities){
                let applicationId = document.querySelector('meta[name="application-id"]').getAttribute('content');
                let databaseName = document.querySelector('meta[name="database-name"]').getAttribute('content');
                let databaseSchema = document.querySelector('meta[name="database-schema"]').getAttribute('content');
                let databaseType = document.querySelector('meta[name="database-type"]').getAttribute('content');
                sendToServer(applicationId, databaseType, databaseName, databaseSchema, entities); 
            }
        }
    );

    resizablePanels = new ResizablePanels('.entity-editor', '.left-panel', '.right-panel', '.resize-bar', 200);
    init();

});

/**
 * Sends data to the server using the POST method with URL-encoded format.
 * 
 * @param {string} applicationId - The application ID to be sent.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} entities - The list of entities to be sent to the server.
 */
function sendToServer(applicationId, databaseType, databaseName, databaseSchema, entities) {
    const data = {
        applicationId: applicationId,
        databaseType: databaseType,
        databaseName: databaseName,
        databaseSchema: databaseSchema,
        entities: JSON.stringify(entities)  // Converting the entities array into a JSON string
    };

    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = 'lib.ajax/entity-structure.php'; // URL endpoint on the server

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
function fetchDataFromServer(applicationId, databaseType, databaseName, databaseSchema, entities, callback) {
    const xhr = new XMLHttpRequest(); // Create a new XMLHttpRequest object
    const url = buildUrl(applicationId, databaseType, databaseName, databaseSchema, entities);
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
 * Builds the URL for the GET request by appending query parameters.
 * 
 * @param {string} applicationId - The application ID being used.
 * @param {string} databaseType - The type of database being used.
 * @param {string} databaseName - The name of the database being used.
 * @param {string} databaseSchema - The schema of the database being used.
 * @param {Array} entities - The list of entities to be included in the query string.
 * 
 * @returns {string} The URL with query parameters appended.
 */
function buildUrl(applicationId, databaseType, databaseName, databaseSchema, entities) {
    return `lib.ajax/entity-structure.php?applicationId=${encodeURIComponent(applicationId)}&databaseType=${encodeURIComponent(databaseType)}&databaseName=${encodeURIComponent(databaseName)}&databaseSchema=${encodeURIComponent(databaseSchema)}&entities=${encodeURIComponent(JSON.stringify(entities))}`;
}

