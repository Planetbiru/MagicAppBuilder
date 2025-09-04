/**
 * Displays a preview of a file in the document viewer based on its extension.
 *
 * This function determines the file type from the URL extension and calls the
 * appropriate preview function (e.g., `previewPDF`, `previewDOCX`, etc.).
 * It first updates the viewer with a "Loading..." message. If the file
 * extension is not supported, it displays an error message.
 *
 * @param {string} url The URL of the file to be previewed.
 */
function previewFile(url) {
    const ext = url.split('.').pop().toLowerCase();
    const viewer = document.querySelector("#document-viewer");
    viewer.innerHTML = "Loading...";

    if (ext === "pdf") {
        previewPDF(url);
    } else if (ext === "docx") {
        previewDOCX(url);
    } else if (["xls", "xlsx", "ods"].includes(ext)) {
        previewExcel(url);
    } else if (ext === "csv") {
        previewCSV(url);
    } else {
        viewer.innerHTML = "Unsupported format.";
    }
}



/**
 * Renders a PDF file into a canvas element using PDF.js.
 *
 * It fetches the PDF document from the given URL, gets the first page,
 * and renders it onto a new canvas element. Error handling is included
 * to display a message if the PDF fails to load.
 *
 * @param {string} url The URL of the PDF file.
 */
function previewPDF(url) {
    const viewer = document.querySelector("#document-viewer");
    const loadingTask = pdfjsLib.getDocument(url);
    loadingTask.promise.then(pdf => {
        pdf.getPage(1).then(page => {
            const scale = 1.5;
            const viewport = page.getViewport({ scale });
            const canvas = document.createElement("canvas");
            const context = canvas.getContext("2d");
            canvas.height = viewport.height;
            canvas.width = viewport.width;
            viewer.innerHTML = "";
            viewer.appendChild(canvas);
            page.render({ canvasContext: context, viewport });
        });
    }).catch(err => {
        viewer.innerHTML = "Failed to load file PDF.";
        console.error(err);
    });
}



/**
 * Renders a DOCX file into the document viewer using Mammoth.js.
 *
 * This function fetches the DOCX file as a blob and then uses Mammoth.js
 * to asynchronously render its content (text, images, etc.) into the
 * `#document-viewer` container.
 *
 * @param {string} url The URL of the DOCX file.
 */
function previewDOCX(url) {
    fetch(url)
        .then(res => res.blob())
        .then(doc => {
            if (doc != null) {
                var docxOptions = Object.assign(docx.defaultOptions, {
                    className: "docx",
                    inWrapper: true,
                    ignoreWidth: false,
                    ignoreHeight: false,
                    ignoreFonts: false,
                    breakPages: true,
                    ignoreLastRenderedPageBreak: true,
                    experimental: false,
                    trimXmlDeclaration: true,
                    useBase64URL: false,
                    useMathMLPolyfill: false,
                    debug: false,
                });
                var container = document.querySelector("#document-viewer");
                docx.renderAsync(doc, container, null, docxOptions);
            }
        });
}



/**
 * Fetches and renders an Excel file (XLS, XLSX, ODS) using SheetJS (xlsx.js).
 *
 * This function retrieves the file as an array buffer, reads it as a workbook,
 * and dynamically creates a tabbed interface for each sheet. It displays the
 * first sheet by default and allows users to switch between sheets by clicking
 * on the tabs.
 *
 * @param {string} url The URL of the Excel file.
 */
function previewExcel(url) {
    fetch(url)
        .then(res => res.arrayBuffer())
        .then(data => {
            const viewer = document.querySelector("#document-viewer");
            viewer.innerHTML = '';

            // Buat tab container terlebih dahulu agar berada di atas sheet
            const tabContainer = document.createElement("ul");
            tabContainer.id = "sheet-tabs";
            tabContainer.className = "nav nav-tabs mt-2";
            viewer.appendChild(tabContainer); // tambahkan dulu

            // Buat #sheet-viewer di bawah tab
            let sheetViewer = document.createElement("div");
            sheetViewer.id = "sheet-viewer";
            sheetViewer.style.marginTop = "10px"; // jarak dari tab
            viewer.appendChild(sheetViewer);

            const workbook = XLSX.read(data, { type: "array" });

            // Render sheet pertama secara default
            renderExcelSheet(workbook, workbook.SheetNames[0], "#sheet-viewer");

            // Tambahkan tab untuk tiap sheet
            workbook.SheetNames.forEach((name, index) => {
                const li = document.createElement("li");
                li.className = "nav-item";

                const a = document.createElement("a");
                a.className = "nav-link" + (index === 0 ? " active" : "");
                a.href = "#";
                a.textContent = name;

                a.onclick = (e) => {
                    e.preventDefault();
                    tabContainer.querySelectorAll(".nav-link").forEach(t => t.classList.remove("active"));
                    a.classList.add("active");
                    renderExcelSheet(workbook, name, "#sheet-viewer");
                };

                li.appendChild(a);
                tabContainer.appendChild(li);
            });
        })
        .catch(err => {
            document.querySelector("#document-viewer").innerHTML = "Failed to load file Excel.";
            console.error(err);
        });
}



/**
 * Fetches and renders a CSV file using SheetJS (xlsx.js).
 *
 * Similar to `previewExcel`, this function fetches the CSV file, reads it as a
 * string to create a workbook, and then renders it as an HTML table. It also
 * provides a tabbed interface, although a CSV file typically has only one sheet.
 *
 * @param {string} url The URL of the CSV file.
 */
function previewCSV(url) {
    fetch(url)
        .then(res => res.text())
        .then(text => {
            const viewer = document.querySelector("#document-viewer");
            viewer.innerHTML = '';

            // Buat tab container terlebih dahulu
            const tabContainer = document.createElement("ul");
            tabContainer.id = "sheet-tabs";
            tabContainer.className = "nav nav-tabs mt-2";
            viewer.appendChild(tabContainer);

            // Buat #sheet-viewer di bawah tab
            const sheetViewer = document.createElement("div");
            sheetViewer.id = "sheet-viewer";
            sheetViewer.style.marginTop = "10px";
            viewer.appendChild(sheetViewer);

            const workbook = XLSX.read(text, { type: "string" });

            // Render sheet pertama
            renderExcelSheet(workbook, workbook.SheetNames[0], "#sheet-viewer");

            workbook.SheetNames.forEach((name, index) => {
                const li = document.createElement("li");
                li.className = "nav-item";

                const a = document.createElement("a");
                a.className = "nav-link" + (index === 0 ? " active" : "");
                a.href = "#";
                a.textContent = name;

                a.onclick = (e) => {
                    e.preventDefault();
                    tabContainer.querySelectorAll(".nav-link").forEach(t => t.classList.remove("active"));
                    a.classList.add("active");
                    renderExcelSheet(workbook, name, "#sheet-viewer");
                };

                li.appendChild(a);
                tabContainer.appendChild(li);
            });
        })
        .catch(err => {
            document.querySelector("#document-viewer").innerHTML = "Failed to load file CSV.";
            console.error(err);
        });
}



/**
 * Renders a specific worksheet from a workbook into an HTML table.
 *
 * This is a helper function that takes a workbook object, a sheet name, and a
 * DOM selector, then converts the specified sheet into an HTML table and
 * inserts it into the designated viewer element.
 *
 * @param {object} workbook The workbook object created by SheetJS.
 * @param {string} sheetName The name of the sheet to render.
 * @param {string} viewerSelector The CSS selector for the element where the sheet will be rendered.
 */
function renderExcelSheet(workbook, sheetName, viewerSelector) {
    const viewer = document.querySelector(viewerSelector);
    if (!viewer) return;

    // Render konten sheet
    const html = XLSX.utils.sheet_to_html(workbook.Sheets[sheetName]);
    viewer.innerHTML = html;
}