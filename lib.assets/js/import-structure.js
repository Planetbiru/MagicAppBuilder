

// Move init() outside of the class
function init() {
    // Mendapatkan elemen-elemen modal dan tombol
    let modal = document.getElementById("translatorModal");
    let openModalButton = document.querySelector(".import-structure");
    let closeModalButton = document.getElementById("closeBtn");
    let cancelButton = document.getElementById("cancelBtn");
    let translateButton  = document.querySelector(".translate-structure");
    let clearButton  = document.querySelector(".clear");
    let original = document.querySelector('#original');
    let query = document.querySelector('[name="query"]');
    let deleteCells = document.querySelectorAll('.cell-delete a');

    // Menampilkan modal saat tombol di klik
    openModalButton.onclick = function() {
        modal.style.display = "block";
        original.focus();
    }

    // Menutup modal saat tombol close di klik
    closeModalButton.onclick = function() {
        modal.style.display = "none";
    }

    // Menutup modal saat tombol 'Close' di footer di klik
    cancelButton.onclick = function() {
        modal.style.display = "none";
    }
    
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
        modal.style.display = "none";
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
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
}



// Instantiate the class
const converter = new SQLConverter();

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    init();
});