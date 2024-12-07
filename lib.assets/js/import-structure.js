

// Move init() outside of the class
function init() {
    // Mendapatkan elemen-elemen modal dan tombol
    var modal = document.getElementById("translatorModal");
    var openModalButton = document.querySelector(".import-structure");
    var closeModalButton = document.getElementById("closeBtn");
    var cancelButton = document.getElementById("cancelBtn");
    var translateButton  = document.querySelector(".translate-structure");
    var clearButton  = document.querySelector(".clear");
    var original = document.querySelector('#original');

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
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }
}



// Instantiate the class
const converter = new SqliteConverter();

// Initialize event listeners
document.addEventListener('DOMContentLoaded', () => {
    init();
});