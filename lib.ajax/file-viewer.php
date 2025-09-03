<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Universal File Previewer</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    #controls { margin-bottom: 10px; }
    #viewer { border: 1px solid #ccc; padding: 10px; min-height: 400px; overflow: auto; }
    canvas { border: 1px solid #aaa; }
    #sheetSelector { margin: 10px 0; }
    .hidden { display: none; }
  </style>
</head>
<body>
  <h1>File Previewer</h1>

  <div id="controls">
    <input type="text" id="fileUrl" placeholder="Enter file URL (docx, xls, xlsx, ods, csv, pdf)" style="width:70%">
    <button onclick="previewFile()">Preview</button>
  </div>

  <div id="viewer"></div>

  <!-- Loaders -->
<script src="../buffer.min.js"></script>
<script src="../xlsx.full.min.js"></script>
<script src="../docx-preview.min.js"></script>
<script src="../pdf.min.js"></script>
<script src="../papaparse.min.js"></script>


  <script>
    async function previewFile() {
      const url = document.getElementById("fileUrl").value.trim();
      if (!url) return;
      const ext = url.split('.').pop().toLowerCase();
      const viewer = document.getElementById("viewer");
      viewer.innerHTML = "Loading...";

      if (["docx"].includes(ext)) {
        await previewDocx(url, viewer);
      } else if (["xls","xlsx","ods","csv"].includes(ext)) {
        await previewExcel(url, viewer);
      } else if (ext === "pdf") {
        await previewPdf(url, viewer);
      } else {
        viewer.innerHTML = "Unsupported file type.";
      }
    }

async function previewDocx(url) {
  try {
    // Fetch file dari URL lokal
    const resp = await fetch(url);
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
    
    const arrayBuffer = await resp.arrayBuffer();
    
    // Convert ArrayBuffer → Node-style Buffer untuk docx-preview
    const buf = Buffer.from(arrayBuffer);

    const container = document.getElementById("viewer");
    container.innerHTML = "";
    
    // Render DOCX
    window.docx.renderAsync(buf, container, null, { inWrapper: false });
  } catch(e) {
    document.getElementById("viewer").innerHTML = "Error loading DOCX: " + e.message;
  }
}



    // -------- Excel/CSV --------
    async function previewExcel(url, container) {
      try {
        const resp = await fetch(url);
        const buf = await resp.arrayBuffer();
        const wb = XLSX.read(buf, { type: "array" });

        container.innerHTML = "";

        // Sheet selector
        const sheetSelector = document.createElement("select");
        sheetSelector.id = "sheetSelector";
        wb.SheetNames.forEach(name => {
          const opt = document.createElement("option");
          opt.value = name;
          opt.textContent = name;
          sheetSelector.appendChild(opt);
        });
        container.appendChild(sheetSelector);

        const tableContainer = document.createElement("div");
        container.appendChild(tableContainer);

        function renderSheet(name) {
          const html = XLSX.utils.sheet_to_html(wb.Sheets[name]);
          tableContainer.innerHTML = html;
        }
        renderSheet(wb.SheetNames[0]);

        sheetSelector.addEventListener("change", e => {
          renderSheet(e.target.value);
        });
      } catch (e) {
        container.innerHTML = "Error loading Excel/CSV: " + e.message;
      }
    }

    // -------- PDF --------
    async function previewPdf(url, container) {
      try {
        container.innerHTML = `
          <div>
            <button id="prevPage">Prev</button>
            <button id="nextPage">Next</button>
            <span>Page: <span id="pageNum">1</span> / <span id="pageCount">?</span></span>
            <button id="zoomIn">+</button>
            <button id="zoomOut">-</button>
          </div>
          <canvas id="pdfCanvas"></canvas>
        `;

        const pdfjsLib = window['pdfjs-dist/build/pdf'];
        pdfjsLib.GlobalWorkerOptions.workerSrc =
          "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js";

        const pdf = await pdfjsLib.getDocument(url).promise;
        let pageNum = 1;
        let scale = 1.0;
        const canvas = document.getElementById("pdfCanvas");
        const ctx = canvas.getContext("2d");

        document.getElementById("pageCount").textContent = pdf.numPages;

        async function renderPage(num) {
          const page = await pdf.getPage(num);
          const viewport = page.getViewport({ scale });
          canvas.height = viewport.height;
          canvas.width = viewport.width;
          await page.render({ canvasContext: ctx, viewport }).promise;
          document.getElementById("pageNum").textContent = num;
        }

        renderPage(pageNum);

        document.getElementById("prevPage").onclick = () => {
          if (pageNum <= 1) return;
          pageNum--;
          renderPage(pageNum);
        };
        document.getElementById("nextPage").onclick = () => {
          if (pageNum >= pdf.numPages) return;
          pageNum++;
          renderPage(pageNum);
        };
        document.getElementById("zoomIn").onclick = () => {
          scale += 0.2;
          renderPage(pageNum);
        };
        document.getElementById("zoomOut").onclick = () => {
          if (scale > 0.4) {
            scale -= 0.2;
            renderPage(pageNum);
          }
        };
      } catch (e) {
        container.innerHTML = "Error loading PDF: " + e.message;
      }
    }
  </script>
</body>
</html>
