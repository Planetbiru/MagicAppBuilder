let contentModified = true;
let fileManagerEditor = null;   
let currentMode = null;
let modeInput = null;


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

                    loadDirContent(dir, subDirUl, subDirLi); // Load the subdirectory content
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
    loadDirContent('', ulDir, null, true);
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



/**
 * Formats the entire content of the CodeMirror editor.
 * 
 * This function automatically formats the content of the editor by adjusting the indentation 
 * and layout of the code. It formats all lines in the editor from the first line to the last.
 * 
 * It uses CodeMirror's `autoFormatRange` method to format the content.
 */
function format() {
    let totalLines = fileManagerEditor.lineCount();  // Get the total number of lines in the editor
    fileManagerEditor.autoFormatRange({line: 0, ch: 0}, {line: totalLines});  // Format all lines
}

/**
 * This function loads the content of the specified directory and appends the content to the subdirectory list.
 * 
 * @param {string} dir - The directory path to load.
 * @param {HTMLElement} subDirUl - The <ul> element where the subdirectory content will be appended.
 * @param {HTMLElement} subdirLi - The <li> parent element of subdirUl
 * @param {boolean} reset - Reset content
 */
function loadDirContent(dir, subDirUl, subdirLi, reset) {
    if(subdirLi != null)
    {
        subdirLi.setAttribute('data-loading', 'true');
    }
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
            if(subdirLi != null)
            {
                subdirLi.removeAttribute('data-loading');
            }
            displayDirContent(dirs, subDirUl, reset);  // Display the directory content    
        })
        .catch(error => {
            // Handle any errors
            console.error('There was a problem with the fetch operation:', error);
            decreaseAjaxPending();
            if(subdirLi != null)
            {
                subdirLi.removeAttribute('data-loading');
            }
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

/**
 * Initializes the CodeMirror editor for the file manager.
 * 
 * This function sets up the CodeMirror editor with various configurations such as line numbers,
 * line wrapping, bracket matching, and indentation settings. It also adjusts the editor's size 
 * based on the window's size, ensuring the editor fits within the available space in the UI.
 * 
 * - CodeMirror's mode URL is configured to load the correct syntax mode files.
 * - The editor is initialized with the content of the textarea with ID 'code'.
 * - A resize event listener is added to dynamically adjust the editor's size when the window is resized.
 */
function initCodeMirror() {
    modeInput = document.getElementById('filename');
    CodeMirror.modeURL = "../lib.assets/cm/mode/%N/%N.js"; // Path to CodeMirror mode files
    fileManagerEditor = CodeMirror.fromTextArea(document.getElementById("code"), 
    {
        lineNumbers: true,           // Show line numbers in the editor
        lineWrapping: true,          // Enable line wrapping to prevent horizontal scrolling
        matchBrackets: true,         // Highlight matching brackets
        indentUnit: 4,               // Set the indentation unit to 4 spaces
        indentWithTabs: true         // Use tabs for indentation
    });

    // Adjust editor size when window is resized
    window.addEventListener('resize', function(e){
        let w = document.querySelector('#file-content').offsetWidth - 16;  // Adjust width
        let h = window.innerHeight - 160;  // Adjust height based on window height
        fileManagerEditor.setSize(w, h);  // Apply the new size to the editor
    });
    
    // Initial editor size adjustment
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
 * Opens a text file and loads its content into the editor.
 * 
 * This function fetches the content of the specified text file from the server using 
 * a PHP script, then loads the content into the CodeMirror editor. The file's extension
 * is used to set the appropriate syntax highlighting mode for the editor.
 * 
 * It also manages UI elements, such as disabling the open/save buttons during the loading process.
 * 
 * @param {string} file - The path to the file to be opened.
 * @param {string} extension - The extension of the file to determine the mode for the editor (e.g., 'txt', 'js', 'html').
 */
function openTextFile(file, extension) {
    // Disable open/save buttons while loading
    document.querySelector('.btn-open-file').disabled = true;
    document.querySelector('.btn-save-file').disabled = true;
    
    // Set display mode to 'text' and update the file path input
    setDisplayMode('text');
    document.querySelector('.file-path').value = file;
    
    // Display a loading message in the editor
    fileManagerEditor.setValue('Loading...');
    
    // Change the mode based on the file's extension
    changeMode('any.txt', 'txt'); 
    
    // Indicate that an AJAX request is pending
    increaseAjaxPending();
    
    // Fetch the file content from the server
    fetch('lib.ajax/file-manager-load-file.php?file=' + encodeURIComponent(file))
        .then(response => response.text())  // Parse the response as text
        .then(text => {
            // Change the editor mode based on the file's extension
            changeMode(file, extension);                
            
            // Set the file content in the editor
            fileManagerEditor.setValue(text);
            
            // Enable the open/save buttons once loading is complete
            document.querySelector('.btn-open-file').disabled = false;
            document.querySelector('.btn-save-file').disabled = false;
            
            // Indicate that the AJAX request has completed
            decreaseAjaxPending();
        })
        .catch(error => {
            // If there's an error, enable the buttons and reset the display mode
            document.querySelector('.btn-open-file').disabled = false;
            document.querySelector('.btn-save-file').disabled = false;
            setDisplayMode('');
            
            // Indicate that the AJAX request has completed
            decreaseAjaxPending();
        });
}

/**
 * Sets the display mode for the file viewer (either 'text' or 'image').
 * 
 * This function toggles between the text and image modes by changing the visibility
 * of the corresponding HTML elements.
 * 
 * @param {string} mode - The mode to set ('text' or 'image').
 */
function setDisplayMode(mode) {
    if (mode === 'text') {
        document.querySelector('.image-mode').style.display = 'none';  // Hide the image viewer
        document.querySelector('.text-mode').style.display = 'block';  // Show the text editor
    } else if (mode === 'image') {
        document.querySelector('.text-mode').style.display = 'none';  // Hide the text editor
        document.querySelector('.image-mode').style.display = 'block';  // Show the image viewer
    }
}

/**
 * Changes the mode of the CodeMirror editor based on the file's extension.
 * 
 * This function configures the syntax highlighting for the CodeMirror editor according
 * to the file extension or MIME type. It ensures that the editor uses the correct mode
 * for the opened file to provide proper syntax highlighting.
 * 
 * @param {string} filename - The name of the file, used to determine the mode.
 * @param {string} extension - The file extension, used to identify the correct mode.
 */
function changeMode(filename, extension) {
    currentMode = extension;
    let val = filename;
    let mode;
    let spec;
    let m = /.+\.([^.]+)$/.exec(val);
    
    // Check if the file has a valid extension
    if (m) {
        let info = CodeMirror.findModeByExtension(m[1]);  // Find the mode based on the extension
        if (info) {
            mode = info.mode;
            spec = info.mime;
        }
    }
    // Check if the filename is a MIME type
    else if (/\//.test(val)) {
        let info = CodeMirror.findModeByMIME(val);  // Find the mode based on the MIME type
        if (info) {
            mode = info.mode;
            spec = val;
        }
    } else {
        mode = spec = val;
    }
    
    // If a valid mode is found, set it in the editor
    if (mode) {
        fileManagerEditor.setOption("mode", spec);
        CodeMirror.autoLoadMode(fileManagerEditor, mode);  // Load the mode dynamically
    }
}
