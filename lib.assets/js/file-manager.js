/**
 * Initializes the file manager, sets up event listeners, and initializes the CodeMirror editor.
 * 
 * This function handles:
 * - Clicking on directories to load their contents.
 * - Toggling the visibility of subdirectories.
 * - Opening and saving files.
 * 
 * It sets up event listeners for directory clicks, file open/save buttons, and initializes the editor.
 */
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
    
    document.querySelector('.btn-save-file').addEventListener('click', function(e){
        let file = document.querySelector('.file-path').value;
        let content = fileManagerEditor.getValue();
        saveFile(file, content);
    });
    
    document.querySelector('.btn-open-file').addEventListener('click', function(e){
        let file = document.querySelector('.file-path').value;
        let extension = getFileExtension(file);
        openTextFile(file, extension);
    });
    
    initCodeMirror();
}

/**
 * This function returns the caller function's name using the stack trace.
 * 
 * @returns {string} - The name of the caller function, or an empty string if unavailable.
 */
function getCaller() {
    try {
        // Generate an error to get the stack trace
        throw new Error();
    } catch (e) {
        // Split the stack trace by line and extract the caller's function name
        const stackLines = e.stack.split('\n');
        
        // For most browsers, the caller's function will be in line 3
        if (stackLines.length > 2) {
            // The third line usually contains the caller's information
            const callerInfo = stackLines[2];
            // Extract the function name from the line, if possible
            const callerName = callerInfo.match(/at (\w+)/);
            return callerName ? callerName[1] : 'Unknown';
        }
        return 'Unknown';
    }
}

/**
 * Resets the file manager, clearing all contents and resetting the UI elements.
 * 
 * This function:
 * - Clears the directory tree.
 * - Clears the file editor.
 * - Disables the save button.
 * - Reloads the directory content.
 */
function resetFileManager()
{    
    let ulDir = document.querySelector('#dir-tree');
    if(fileManagerEditor)
    {
        fileManagerEditor.setValue('');
    }
    document.querySelector('.btn-save-file').disabled = true;
    document.querySelector('#dir-tree').innerHTML = '';
    document.querySelector('.file-path').value = '';
    loadDirContent('', ulDir, true);
}

/**
 * This function returns the file extension from a given file name or file path.
 * 
 * @param {string} filename - The name or path of the file (e.g., 'example.txt', 'folder/image.jpg').
 * @returns {string} - The file extension, or an empty string if no extension is found.
 */
function getFileExtension(filename) {
    // Use a regular expression to match the file extension
    const match = filename.match(/\.(\w+)$/);  // Looks for a dot followed by one or more word characters

    // If a match is found, return the file extension, otherwise return an empty string
    return match ? match[1] : '';
}


let contentModified = true;
let fileManagerEditor = null;   
let currentMode = null;
function format(){
    let totalLines = fileManagerEditor.lineCount();  
    fileManagerEditor.autoFormatRange({line:0, ch:0}, {line:totalLines});
}
let modeInput = null;

/**
 * This function loads the content of the specified directory and appends the content to the subdirectory list.
 * 
 * @param {string} dir - The directory path to load.
 * @param {HTMLElement} subDirUl - The <ul> element where the subdirectory content will be appended.
 * @param {boolean} reset - Reset content
 */
function loadDirContent(dir, subDirUl, reset) {
    // Indicate that an AJAX request is pending (if you have such a function)
    increaseAjaxPending();

    // Fetch the directory content from the server using the GET method
    fetch('lib.ajax/file-manager-load-dir.php?dir=' + encodeURIComponent(dir))
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();  // Parse the JSON response
        })
        .then(dirs => {
            // Decrease the AJAX pending counter (if you have such a function)
            decreaseAjaxPending();
            displayDirContent(dirs, subDirUl, reset);  // Display the directory content
        })
        .catch(error => {
            // Handle any errors
            console.error('There was a problem with the fetch operation:', error);
            decreaseAjaxPending();
        });
}


/**
 * This function processes the directory data and appends directories and files to the subdirectory list.
 * 
 * @param {Array} dirs - The list of directories and files to display.
 * @param {HTMLElement} subDirUl - The <ul> element where the directory content will be appended.
 * @param {boolean} reset - Reset content
 */
function displayDirContent(dirs, subDirUl, reset) {
    subDirUl.innerHTML = '';
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
    modeInput = document.getElementById('filename');
    CodeMirror.modeURL = "../lib.assets/cm/mode/%N/%N.js";
    fileManagerEditor = CodeMirror.fromTextArea(document.getElementById("code"), 
    {
        lineNumbers: true,
        lineWrapping: true,
        matchBrackets: true,
        indentUnit: 4,
        indentWithTabs: true
    });

    window.addEventListener('resize', function(e){
        let w = document.querySelector('#file-content').offsetWidth - 16;
        let h = window.innerHeight - 160;
        fileManagerEditor.setSize(w, h);
    });
    
    
    let w = document.querySelector('#file-content').offsetWidth - 16;
    let h = window.innerHeight - 160;
    fileManagerEditor.setSize(w, h);
}

/**
 * Function to send file name and content to the server using a POST request.
 * 
 * @param {string} file - The name of the file to save.
 * @param {string} content - The content to save in the file.
 */
function saveFile(file, content) {
    // Create a new FormData object to send data with a POST request
    let formData = new FormData();

    // Append file name and content to FormData
    formData.append('file', file);
    formData.append('content', content);
    increaseAjaxPending();
    // Send the data to the server using Fetch API with a POST request
    fetch('lib.ajax/file-manager-save-file.php', {
        method: 'POST',  // HTTP method is POST
        body: formData   // The FormData object contains the file name and content
    })
    .then(response => response.text())  // Convert the response into text
    .then(data => {
        // Successfully received response from the server
        console.log('File saved successfully:', data);
        decreaseAjaxPending();
    })
    .catch(error => {
        // Handle any errors that occurred during the request
        console.error('Error saving file:', error);
        decreaseAjaxPending();
    });
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
        openTextFile(file, extension);
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'].includes(extension.toLowerCase())) {
        setDisplayMode('image');
        // For supported image extensions, use the server-side script to load the image as base64
        increaseAjaxPending();
        fetch('lib.ajax/file-manager-load-image.php?file=' + encodeURIComponent(file))
            .then(response => response.text())
            .then(base64ImageData => {
                let mediaDisplay = document.querySelector('.media-display');
                // Create an <img> element to display the image
                const img = document.createElement('img');
                img.src = base64ImageData; // Set the base64 image data as the source
                img.style.maxWidth = '100%';
                img.style.maxHeight = '400px';
                mediaDisplay.appendChild(img); // Append the image to the file content div
                decreaseAjaxPending();
            })
            .catch(error => {
                decreaseAjaxPending();
            });
    } else {
        // For unsupported file extensions, display an error message
        fileDiv.textContent = 'Cannot open this file.'; // Display error message for unsupported file types
    }
}

/**
 * Open text file
 * 
 * @param {string} file - The file path.
 * @param {string} extension - The file extension.
 */
function openTextFile(file, extension)
{
    // Use fetch to get the file content through server-side PHP for text files
    document.querySelector('.btn-open-file').disabled = true;
    document.querySelector('.btn-save-file').disabled = true;
    setDisplayMode('text');
    document.querySelector('.file-path').value = file;
    fileManagerEditor.setValue('Loading...');
    changeMode('any.txt', 'txt'); 
    increaseAjaxPending();
    fetch('lib.ajax/file-manager-load-file.php?file=' + encodeURIComponent(file))
        .then(response => response.text())
        .then(text => {
            changeMode(file, extension);                
            fileManagerEditor.setValue(text);
            document.querySelector('.btn-open-file').disabled = false;
            document.querySelector('.btn-save-file').disabled = false;
            decreaseAjaxPending();
        })
        .catch(error => {
            document.querySelector('.btn-open-file').disabled = false;
            document.querySelector('.btn-save-file').disabled = false;
            setDisplayMode('');
            decreaseAjaxPending();
        });
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
		fileManagerEditor.setOption("mode", spec);
		CodeMirror.autoLoadMode(fileManagerEditor, mode);
	} 
}