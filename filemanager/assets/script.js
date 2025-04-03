function initFileManager()
{
    // When a directory is clicked
    document.getElementById('dir-tree').addEventListener('click', function (e) {
        if (e.target) {
            // If the clicked element is a directory
            if (e.target.classList.contains('dir')) {
                const dir = e.target.dataset.dir; // Get the directory path
                const subDirLi = e.target.closest('li'); // Get the <li> that contains the subdirectory

                // Look for <ul> inside the <li> that contains subdirectories
                let subDirUl = subDirLi.querySelector('ul');
                
                // If subdirectories have not been loaded yet (i.e., no <ul> exists)
                if (!subDirUl) {
                    subDirUl = document.createElement('ul');  // Create a new <ul> for subdirectories
                    subDirLi.appendChild(subDirUl); // Append it to the <li> for the current directory

                    loadDirContent(dir, subDirUl); // Load the subdirectory content
                } else {
                    // Toggle the visibility of subdirectories
                    const isVisible = getComputedStyle(subDirUl).display !== 'none';
                    subDirUl.style.display = isVisible ? 'none' : 'block';
                }
            }
            // If the clicked element is a file
            else if (e.target.classList.contains('file')) {
                const dir = e.target; // Get the file element
                openFile(dir.dataset.file, dir.dataset.extension); // Open the file based on its data attributes
            }
        }
    });
    
    // Initial directory load
    let ulDir = document.querySelector('#dir-tree');
    loadDirContent(ulDir.dataset.baseDirectory, ulDir);
    initCodeMirror();
}

let modified = true;
let editor = null;   
let currentMode = null;
function format(){
    let totalLines = editor.lineCount();  
    editor.autoFormatRange({line:0, ch:0}, {line:totalLines});
}
let modeInput = null;

/**
 * This function loads the content of the specified directory and appends the content to the subdirectory list.
 * 
 * @param {string} dir - The directory path to load.
 * @param {HTMLElement} subDirUl - The <ul> element where the subdirectory content will be appended.
 */
function loadDirContent(dir, subDirUl) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'load-dir.php?dir=' + encodeURIComponent(dir), true); // Send GET request to the server
    xhr.onload = function () {
        if (xhr.status === 200) {
            const dirs = JSON.parse(xhr.responseText); // Parse the JSON response
            displayDirContent(dirs, subDirUl); // Display the contents of the directory
        }
    };
    xhr.send(); // Send the request
}

/**
 * This function processes the directory data and appends directories and files to the subdirectory list.
 * 
 * @param {Array} dirs - The list of directories and files to display.
 * @param {HTMLElement} subDirUl - The <ul> element where the directory content will be appended.
 */
function displayDirContent(dirs, subDirUl) {
    dirs.forEach(function (dir) {
        if (dir.type === 'dir') { // If the item is a directory
            const dirLi = document.createElement('li');
            dirLi.dataset.type = dir.type;
            const dirSpan = document.createElement('span');
            dirSpan.textContent = dir.name;
            dirSpan.dataset.dir = dir.path;
            dirSpan.classList.add('dir');

            // Create a <ul> for subdirectories, initially hidden
            const subUl = document.createElement('ul');
            subUl.style.display = 'none'; // Hide subdirectories by default
            dirLi.appendChild(dirSpan);
            subDirUl.appendChild(dirLi);

        } else if (dir.type === 'file') { // If the item is a file
            const fileLi = document.createElement('li');
            fileLi.dataset.type = dir.type;
            const fileSpan = document.createElement('span');
            fileSpan.textContent = dir.name;
            fileSpan.dataset.file = dir.path;
            fileSpan.dataset.extension = dir.extension;
            fileSpan.classList.add('file');

            fileLi.appendChild(fileSpan);
            subDirUl.appendChild(fileLi); // Append the file <li> to the subdirectory <ul>
        }
    });
    
   
    
   
}

function initCodeMirror()
{
    document.addEventListener('keydown', function(e) {
        if(e.ctrlKey && (e.which == 83)) {
          e.preventDefault();
          saveFile();
          return false;
        }
      });
    modeInput = document.getElementById('filename');
    CodeMirror.modeURL = "../lib.assets/cm/mode/%N/%N.js";
    editor = CodeMirror.fromTextArea(document.getElementById("code"), 
    {
        lineNumbers: true,
        lineWrapping: true,
        matchBrackets: true,
        indentUnit: 4,
        indentWithTabs: true
    });

    window.addEventListener('resize', function(e){
        let w = window.innerWidth - 300;
        let h = window.innerHeight - 50;
        editor.setSize(w, h);
    });
    
    
    let w = window.innerWidth - 300;
    let h = window.innerHeight - 50;
    editor.setSize(w, h);
}

function saveFile()
{
    
}

/**
 * This function handles opening a file depending on its extension. 
 * It checks if the file is a text file or a supported image type, and then displays it accordingly.
 * 
 * @param {string} file - The file path.
 * @param {string} extension - The file extension.
 */
function openFile(file, extension) {
  
    // List of non-text extensions (images, videos, audio, etc.)
    const nonTextExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'mp4', 'mp3', 'avi', 'exe', 'pdf'];

    // Check if the file extension is not for text files or supported images
    if (!nonTextExtensions.includes(extension.toLowerCase())) {
        if(currentMode === null)
        {
            changeMode(file, extension); 
        }
        // Use fetch to get the file content through server-side PHP for text files
        setDisplayMode('text');
        fetch('load-file.php?file=' + encodeURIComponent(file))
            .then(response => response.text())
            .then(text => {
                changeMode(file, extension);                
                editor.setValue(text);
            })
            .catch(error => {
            });
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(extension.toLowerCase())) {
        setDisplayMode('image');
        // For supported image extensions, use the server-side script to load the image as base64
        fetch('load-image.php?file=' + encodeURIComponent(file))
            .then(response => response.text())
            .then(base64ImageData => {
                let mediaDisplay = document.querySelector('.media-display');
                // Create an <img> element to display the image
                const img = document.createElement('img');
                img.src = base64ImageData; // Set the base64 image data as the source
                img.style.maxWidth = '100%';
                img.style.maxHeight = '400px';
                mediaDisplay.appendChild(img); // Append the image to the file content div
            })
            .catch(error => {
            });
    } else {
        // For unsupported file extensions, display an error message
        fileDiv.textContent = 'Cannot open this file.'; // Display error message for unsupported file types
    }
}

function setDisplayMode(mode)
{
    if(mode == 'text')
    {
        document.querySelector('.image-mode').style.display = 'none';
        document.querySelector('.text-mode').style.display = 'block';
    } 
    else if(mode == 'image')
    {
        document.querySelector('.text-mode').style.display = 'none';
        document.querySelector('.image-mode').style.display = 'block';
    }
}

function changeMode(filename, extension) {
    currentMode = extension;
	let val = filename;
    let mode;
    let spec;
    let m;
	if (m = /.+\.([^.]+)$/.exec(val)) 
	{
		let info = CodeMirror.findModeByExtension(m[1]);
		if (info)
		{
			mode = info.mode;
			spec = info.mime;
		}
	}
	else if (/\//.test(val))
	{
		let info = CodeMirror.findModeByMIME(val);
		if (info) 
		{
			mode = info.mode;
			spec = val;
		}
	} 
	else 
	{
		mode = spec = val;
	}
	if (mode) 
	{
		editor.setOption("mode", spec);
		CodeMirror.autoLoadMode(editor, mode);
	} 
}