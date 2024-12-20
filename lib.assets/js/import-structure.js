

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
    
    editor = new EntityEditor('.entity-editor');
    resizablePanels = new ResizablePanels('.entity-editor', '.left-panel', '.right-panel', '.resize-bar', 200);
    init();

});