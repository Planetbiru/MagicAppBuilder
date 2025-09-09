let selectedItem = null; // To store the selected file or directory
let contentModified = true;
let currentMode = null;

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
                    subDirLi.setAttribute('data-open', 'true'); // Mark the directory as open
                } else {
                    // Toggle the visibility of subdirectories
                    subDirLi.setAttribute('data-open', subDirLi.getAttribute('data-open') === 'true' ? 'false' : 'true');
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
    
    const dirTree = document.getElementById("dir-tree");
    const contextMenu = document.getElementById("context-menu");

    dirTree.addEventListener("contextmenu", function (event) // NOSONAR
    {
        event.preventDefault();
        

        // Find the closest li element
        const target = event.target.closest("li");
        
        if (target && target.dataset && target.dataset.type) // NOSONAR
        {
          // Store the selected item for future use
          selectedItem = event.target;

          // Get the name (file or directory) from data attributes
          let itemName = '';
          let itemType = target.dataset.type; // Accessing data-type
          let fileExtension = '';

          if (itemType === 'file') {
              itemName = target.querySelector("span").dataset.file;
              fileExtension = target.querySelector("span").dataset.extension;
              fileExtension = fileExtension.toLowerCase();
          } else if (itemType === 'dir') {
              itemName = target.querySelector("span").dataset.dir;
          }

          // Show the context menu at the cursor position
          let menuX = event.pageX;
          let menuY = event.pageY;


          // Reset the context menu and set up the options
          contextMenu.className = 'context-menu'; // Reset any previous state
          const menuList = contextMenu.querySelector("ul");
          menuList.innerHTML = ""; // Clear previous items
          
          // Add appropriate menu options for file or directory
          if (itemType === 'file') {
              if(['svg'].includes(fileExtension))
              {
                menuList.innerHTML = `
                <li data-type="file" data-operation="view-image" data-file="${itemName}">View Image</li>
                <li data-type="file" data-operation="open" data-file="${itemName}">Open as Text</li>
                <li data-type="file" data-operation="rename" data-file="${itemName}">Rename File</li>
                <li data-type="file" data-operation="download" data-file="${itemName}">Download File</li>
                <li data-type="file" data-operation="delete" data-file="${itemName}">Delete File</li>
                `;
              }
              else
              {
                menuList.innerHTML = `
                <li data-type="file" data-operation="open" data-file="${itemName}">Open File</li>
                <li data-type="file" data-operation="rename" data-file="${itemName}">Rename File</li>
                <li data-type="file" data-operation="download" data-file="${itemName}">Download File</li>
                <li data-type="file" data-operation="delete" data-file="${itemName}">Delete File</li>
                `;
              }
              // If contextMenu.style.top + contextMenu.style.height > window.innerHeight, adjust the position
              if (menuY + contextMenu.offsetHeight > window.innerHeight) {
                menuY = window.innerHeight - contextMenu.offsetHeight - 10; // Adjust the top position
              }
              
              contextMenu.style.left = `${menuX}px`;
              contextMenu.style.top = `${menuY}px`;

              // Show the context menu
              contextMenu.style.display = "block";
          } else if (itemType === 'dir') {
              menuList.innerHTML = `
              <li data-type="dir" data-operation="new-file" data-dir="${itemName}">Create New File</li>
              <li data-type="dir" data-operation="new-dir" data-dir="${itemName}">Create New Directory</li>
              <li data-type="dir" data-operation="upload-file" data-dir="${itemName}">Upload Files</li>
              <li data-type="dir" data-operation="open" data-dir="${itemName}">Expand Directory</li>
              <li data-type="dir" data-operation="refresh-dir" data-dir="${itemName}">Reload Directory</li>
              <li data-type="dir" data-operation="rename" data-dir="${itemName}">Rename Directory</li>
              <li data-type="dir" data-operation="compress" data-dir="${itemName}">Download Directory</li>
              <li data-type="dir" data-operation="delete" data-dir="${itemName}">Delete Directory</li>
              `;
              
              // If contextMenu.style.top + contextMenu.style.height > window.innerHeight, adjust the position
              if (menuY + contextMenu.offsetHeight > window.innerHeight) {
                menuY = window.innerHeight - contextMenu.offsetHeight - 10; // Adjust the top position
              }
              
              contextMenu.style.left = `${menuX}px`;
              contextMenu.style.top = `${menuY}px`;

              // Show the context menu
              contextMenu.style.display = "block";
          } else {
            showRootContextMenu(event, contextMenu);
            event.preventDefault();  
            event.stopPropagation();
          }
          
        
        } else {
          showRootContextMenu(event, contextMenu);
          event.preventDefault();  
          event.stopPropagation();
        }
    });

    document.querySelector('.root-directory').addEventListener("contextmenu", function (event) {
      showRootContextMenu(event, contextMenu);
      event.stopPropagation();
      event.preventDefault();
    });     

    // Hide context menu on click outside
    document.addEventListener("click", function () {
        contextMenu.style.display = "none";
    });

    // Add functionality for context menu options
    contextMenu.addEventListener("click", function (event) {
      let target = event.target.closest("li");
      if(target != null)
      {
        const clickedOption = target.dataset.operation;

        if (clickedOption) {
        let name = '';
        const dataType = target.dataset.type;

        if (dataType === 'file') {
            name = target.dataset.file;
        } else if (dataType === 'dir') {
            name = target.dataset.dir;
        }
        
        // Action based on the clicked option
        switch (clickedOption) {
            case "view-image":
              viewImage(name); // Create a new file in the root directory
              setDisplayMode('image');
              break;
            case "root-new-file":
              createNewFile(''); // Create a new file in the root directory
              break;
            case "reset":
              resetFileManager(); // Reset the file manager content
              break;
            case "root-dowload":
              downloadFile('', 'dir'); // Download all files in the root directory
              break;
            case "new-file":
              createNewFile(name); // Create a new file in the selected directory
            break;
            case "upload-file":
              uploadFile(name); // Create a new file in the selected directory
            break;
            case "new-dir":
              createNewDirectory(name); // Create a new directory in the selected directory
            break;
            case "root-new-dir":
              createNewDirectory(''); // Create a new directory in the root directory
            break;
            case "open":
              selectedItem.click(); // Open the directory or file
            break;
            case "refresh-dir":
            refreshDirectory(name); // Refresh the directory content
            break;
            case "rename":
            renameFile(name, dataType); // Rename file or directory
            break;
            case "download":
            downloadFile(name, dataType); // Download file or directory
            break;
            case "delete":
            deleteFile(name, dataType); // Delete file or directory
            break;
            case "compress":
            compressDirectory(name); // Compress and download the directory
            break;
            default:
            break;
        }

        // Hide the context menu after selection
        contextMenu.style.display = "none";
        }
      }
    });
    
    document.querySelector('.file-path').addEventListener('change', function(e){ 
      let file = e.target.value;
      let extension = getFileExtension(file);
      if (extension) {
        changeMode(file, extension); // Call changeMode function with the file and extension
      }  
    });
    
    initCodeMirror();
    fileManagerEditor.refresh();
}

/**
 * Displays the custom context menu for the root directory at the cursor's position.
 *
 * This function prevents the default browser context menu from appearing,
 * resets the selected item, and dynamically generates a context menu with options
 * related to file and directory operations such as creating, uploading, resetting,
 * and downloading content. It also ensures the menu is positioned within the visible
 * window bounds.
 *
 * @param {MouseEvent} event - The right-click event triggering the context menu.
 * @param {HTMLElement} contextMenu - The HTML element representing the context menu.
 */
function showRootContextMenu(event, contextMenu) {
  event.preventDefault(); // Prevent the default context menu from appearing
  selectedItem = null; // Reset selected item
  // Store the selected item for future use 
  // Show the context menu at the cursor position
  let menuX = event.pageX;
  let menuY = event.pageY;

  // Position the context menu
  
  

  // Reset the context menu and set up the options
  contextMenu.className = 'context-menu'; // Reset any previous state
  const menuList = contextMenu.querySelector("ul");
  menuList.innerHTML = `
    <li data-type="dir" data-operation="root-new-file" data-dir="">Create New File</li>
    <li data-type="dir" data-operation="root-new-dir" data-dir="">Create New Directory</li>
    <li data-type="dir" data-operation="upload-file" data-dir="">Upload Files</li>
    <li data-type="dir" data-operation="reset" data-dir="">Reset Content</li>
    <li data-type="dir" data-operation="root-dowload" data-dir="">Download All</li>
    `;
    
  // If contextMenu.style.top + contextMenu.style.height > window.innerHeight, adjust the position
  if (menuY + contextMenu.offsetHeight > window.innerHeight) {
    menuY = window.innerHeight - contextMenu.offsetHeight - 10; // Adjust the top position
  }
  
  contextMenu.style.left = `${menuX}px`;
  contextMenu.style.top = `${menuY}px`;
  
  contextMenu.style.display = "block";
}

/**
 * Uploads files to the server by creating an invisible file input element, 
 * selecting files, and sending them to the server via a POST request.
 * 
 * This function handles the entire process of:
 * 1. Triggering a file input dialog to select files.
 * 2. Sending the selected files to the server.
 * 3. Updating the UI with the uploaded files if the upload is successful.
 * 
 * @param {string} dir - The directory path where the files should be uploaded. This directory is passed 
 *                       as a parameter to the server to store the uploaded files in the appropriate location.
 */
function uploadFile(dir)
{
  // Create a file input element
  let fileInput = document.createElement('input');
  fileInput.type = 'file';
  fileInput.accept = '*'; // Accept all file types
  fileInput.multiple = true; // Allow multiple file selection
  fileInput.style.display = 'none'; // Hide the input element
  
  // Upload file when a file is selected
  fileInput.addEventListener('change', function(event) {
    let file = event.target.files; 
    if (file) {
      let formData = new FormData(); // Create a FormData object
      
      
      for (let i = 0; i < file.length; i++) // NOSONAR
      {
        formData.append('files[]', file[i]); // Append each selected file to the FormData object
      }
      
      formData.append('dir', dir); // Append the directory to the FormData object

      increaseAjaxPending();
      fetch('lib.ajax/file-manager-upload.php', {
        method: 'POST',
        body: formData // Send the FormData object as the request body
      })
      .then(response => response.json()) // Parse the JSON response
      .then(data => {
        decreaseAjaxPending();
        if (data.status == 'success') {
          
          if(selectedItem != null)
          {
            let subDirLi = selectedItem.closest('li'); // Get the <li> that contains the subdirectory
            let subDirUl = subDirLi.querySelector('ul'); // Get the <ul> that contains subdirectories
            if(subDirUl == null)
            {
              subDirUl = document.createElement('ul');  // Create a new <ul> for subdirectories
              subDirLi.appendChild(subDirUl); // Append it to the <li> for the current directory  
            }
            if(subDirLi != null)
            {
              displayDirContent(data.dirs, subDirUl, true); // Display the directory content
              subDirLi.setAttribute('data-open', 'true'); // Mark the directory as open
            }
          }
          else
          {
            resetFileManager(); // Reset the file manager content
          }
        } else {
          console.error('Error uploading file:', data.error);
        }
      })
      .catch(error => {
        decreaseAjaxPending();
        console.error('Error uploading file:', error);
      });
    }
  });
  
  fileInput.click(); // Trigger the file input click event to open the file dialog
}

/**
 * Creates a new file in the specified directory.
 * If no directory is provided, it creates the file in the default location.
 * 
 * @param {string} dir - The directory in which the new file will be created.
 * If an empty string is passed, the default location will be used.
 */
function createNewFile(dir)
{
  let filename = dir !== '' ? dir + '/new-file.txt' : 'new-file.txt'; // Default filename
  let extension = 'txt';
  document.querySelector('.file-path').value = filename;
  document.querySelector('.btn-save-file').disabled = false;
  fileManagerEditor.setValue('');
  changeMode(filename, extension);
  setDisplayMode('text'); // Set the display mode to text
  fileManagerEditor.focus(); // Focus on the editor
}

/**
 * Creates a new directory in the file manager system. This function handles the 
 * process of prompting the user for a new directory name, then sending a request 
 * to the server to create the directory. It also updates the UI based on the 
 * server's response, either by adding the new directory to the current directory 
 * structure or resetting the file manager content.
 * 
 * @param {string} dir - The directory where the new directory should be created. 
 *                       If an empty string is passed, the new directory will be 
 *                       created at the root level. 
 */
function createNewDirectory(dir)
{
  let newDir = 'new-directory'; // Default directory name
  if(dir != '')
  {
    newDir = dir + '/' + newDir; // Append the directory path
  }
  asyncPrompt('Enter new directory name', 'Create Directory', [{
    'caption': 'OK',
    'fn': function (newDirName) {
      let subDirLi = null;
      let subDirUl = null;
      let dirToLoad = '/'; // Default directory path
      if(selectedItem != null)
      {
        subDirLi = selectedItem.closest('li'); // Get the <li> that contains the subdirectory
        subDirUl = selectedItem.closest('li').querySelector('ul'); // Get the <ul> that contains subdirectories
        dirToLoad = subDirLi ? subDirLi.querySelector('span').dataset.dir : '/'; // Get the directory path      
      }
      increaseAjaxPending();
      
      fetch('lib.ajax/file-manager-create-directory.php', {
        method: 'POST', 
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          name: newDirName,
          type: 'dir',
          dir: dirToLoad
        })
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json(); // Parse the JSON response
        })
        .then(response => {
          // Decrease the AJAX pending counter (if you have such a function)
          decreaseAjaxPending();
          if (subDirLi != null) {
            subDirLi.removeAttribute('data-loading');
          }
            // Wait for a moment before reloading the directory content
            if(response.dirs)
            {
              if(subDirLi != null && subDirUl != null) 
              {
                displayDirContent(response.dirs, subDirUl, true);
              }
              else
              {
                resetFileManager();
              }
            }
            else if(dirToLoad == '/')
            {
              resetFileManager();
            }
            else
            {
              loadDirContent(dirToLoad, subDirUl, subDirLi, true); // Display the directory content
            }
            
          
        })
        .catch(error => {
          // Handle any errors
          decreaseAjaxPending();
          if (subDirLi != null) {
            subDirLi.removeAttribute('data-loading');
          }
        });
    },
    'class': 'btn-primary'
  }, {
    'caption': 'Cancel',
    'fn': function () { },
    'class': 'btn-secondary'
  }], newDir, function () {
    // Callback function after the prompt is closed
    // You can add any additional logic here if needed
  });
}

/**
 * Renames an existing file and updates the display accordingly.
 * Prompts the user to enter a new name and submits the change to the server.
 * 
 * @param {string} name - The current name of the file to be renamed.
 * @param {string} dataType - The type of the file (e.g., text, image, etc.).
 */
function renameFile(name, dataType) {
  asyncPrompt('Enter new name for ' + name, 'Rename', [{
    'caption': 'OK',
    'fn': function (newName) {
      let subDirLi = selectedItem.closest('ul').closest('li'); // Get the <li> that contains the subdirectory
      let subDirUl = selectedItem.closest('ul'); // Get the <ul> that contains subdirectories
      let dirToLoad = subDirLi ? subDirLi.querySelector('span').dataset.dir : '/'; // Get the directory path
      increaseAjaxPending();

      fetch('lib.ajax/file-manager-rename.php', {
        method: 'POST', 
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded' // Atau 'application/json' jika Anda mengirim JSON
        },
        body: new URLSearchParams({
          name: name,
          newName: newName,
          type: dataType
        })
      })
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json(); // Parse the JSON response
        })
        .then(response => {
          // Decrease the AJAX pending counter (if you have such a function)
          decreaseAjaxPending();
          if (subDirLi != null) {
            subDirLi.removeAttribute('data-loading');
          }
            // Wait for a moment before reloading the directory content
            if(response.dirs)
            {
              displayDirContent(response.dirs, subDirUl, true);
            }
            else if(dirToLoad == '/')
            {
              resetFileManager();
            }
            else
            {
              loadDirContent(dirToLoad, subDirUl, subDirLi, true); // Display the directory content
            }
        })
        .catch(error => {
          // Handle any errors
          decreaseAjaxPending();
          if (subDirLi != null) {
            subDirLi.removeAttribute('data-loading');
          }
        });
    },
    'class': 'btn-primary'

  }, {
    'caption': 'Cancel',
    'fn': function () { },
    'class': 'btn-secondary'
  }], name, function (newName) { });
}

/**
 * Downloads a file from the server.
 * 
 * @param {string} name - The name of the file to download.
 * @param {string} dataType - The type of the file to download (e.g., text, image, etc.).
 */
function downloadFile(name, dataType) {
  // Construct the URL with query parameters for the GET request
  const url = `lib.ajax/file-manager-download.php?name=${encodeURIComponent(name)}&type=${encodeURIComponent(dataType)}`;

  // Create an anchor element to trigger the download
  const link = document.createElement('a');
  link.href = url;
  link.download = name; // This sets the name of the file when downloading

  // Programmatically click the link to start the download
  link.click();
}


/**
 * Deletes a file from the server and updates the display accordingly.
 * Prompts the user for confirmation before deleting.
 * 
 * @param {string} name - The name of the file to delete.
 * @param {string} dataType - The type of the file (e.g., text, image, etc.).
 */
function deleteFile(name, dataType) {
  asyncAlert(
    'Do you want to delete ' + name + '?',
    'Confirmation',
    [
      {
        'caption': 'Yes',
        'fn': () => {
          let subDirLi = selectedItem.closest('li'); // Get the <li> that contains the subdirectory
          increaseAjaxPending();

          fetch('lib.ajax/file-manager-delete.php', {
            method: 'POST', 
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
              name: name,
              type: dataType
            })
          })
            .then(response => {
              if (!response.ok) {
                throw new Error('Network response was not ok');
              }
              return response.json(); // Parse the JSON response
            })
            .then(dirs => {
              // Decrease the AJAX pending counter (if you have such a function)
              decreaseAjaxPending();
              if(subDirLi != null)
              {
                subDirLi.parentNode.removeChild(subDirLi); // Remove the <li> element from the DOM                
              }
            })
            .catch(error => {
              // Handle any errors
              decreaseAjaxPending();
              if (subDirLi != null) {
                subDirLi.removeAttribute('data-loading');
              }
            });
        },
        'class': 'btn-primary'
      },
      {
        'caption': 'No',
        'fn': () => { },
        'class': 'btn-secondary'
      }
    ]
  );
}

/**
 * Prompts the user to confirm if they want to compress a given directory, and if confirmed,
 * triggers the download of the compressed directory.
 *
 * This function constructs a URL to send a GET request to the server to download the directory 
 * as a compressed file. It dynamically creates an anchor (`<a>`) element, sets its `href` to the 
 * server's download URL, and programmatically clicks the link to initiate the download.
 *
 * @param {string} name - The name of the directory to compress and download.
 */
function compressDirectory(name) {
  asyncAlert('Do you want to compress ' + name + '?', 'Confirmation', [
    {
      'caption': 'Yes',
      'fn': () => {
        // Construct the URL with query parameters for the GET request
        const url = `lib.ajax/file-manager-download.php?name=${encodeURIComponent(name)}&type=dir`;

        // Create an anchor element to trigger the download
        const link = document.createElement('a');
        link.href = url;
        link.download = name; // This sets the name of the file when downloading

        // Programmatically click the link to start the download
        link.click();
      },
      'class': 'btn-primary'
    },
    {
      'caption': 'No',
      'fn': () => { },
      'class': 'btn-secondary'
    }
  ]);
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
    let dirUl = document.querySelector('#dir-tree');
    if(fileManagerEditor)
    {
        fileManagerEditor.setValue('');
    }
    document.querySelector('.btn-save-file').disabled = true;
    document.querySelector('#dir-tree').innerHTML = '';
    document.querySelector('.file-path').value = '';
    loadDirContent('', dirUl, null, true);
}

/**
 * This function returns the file extension from a given file name or file path.
 * 
 * @param {string} filename - The name or path of the file (e.g., 'example.txt', 'folder/image.jpg').
 * @returns {string} - The file extension, or an empty string if no extension is found.
 */
function getFileExtension(filename) {
  const regex = /\.(\w+)$/;
  const match = regex.exec(filename);

  return match ? match[1] : '';
}

/**
 * This function returns the base name of a file without its extension
 * from a given file name or file path.
 * 
 * @param {string} filename - The name or path of the file (e.g., 'example.txt', 'folder/image.jpg').
 * @returns {string} - The file base name without extension (e.g., 'example', 'image').
 */
function getFileBaseName(filename) {
  const base = filename.split(/[\\/]/).pop(); // ambil nama file terakhir
  const lastDotIndex = base.lastIndexOf('.');

  if (lastDotIndex === -1) return base; // tidak ada ekstensi
  return base.substring(0, lastDotIndex);
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
 * Refreshes the content of a specified directory and updates the DOM structure to reflect
 * the current content of the directory.
 *
 * This function attempts to refresh the list of files and subdirectories within the specified
 * directory. If the directory's subdirectories are not already loaded in the DOM, it creates
 * a new `<ul>` element to display them.
 *
 * @param {string} name - The name or path of the directory whose content needs to be refreshed.
 */
function refreshDirectory(name) {
  let subdirLi = selectedItem.closest('li'); // Get the <li> that contains the subdirectory
  let subDirUl = subdirLi.querySelector('ul'); // Get the <ul> that contains subdirectories
  if(subDirUl == null)
  {
    subDirUl = document.createElement('ul');  // Create a new <ul> for subdirectories
    subdirLi.appendChild(subDirUl); // Append it to the <li> for the current directory    
  }
  loadDirContent(name, subDirUl, subdirLi, true)
  if(subdirLi != null)
  {
    subdirLi.setAttribute('data-open', 'true');
  }
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
    $.ajax({
        url: 'lib.ajax/file-manager-load-dir.php',
        method: 'GET',
        data: { dir: dir },  
        dataType: 'json',
        success: function(dirs) {
            decreaseAjaxPending();
            if (subdirLi != null) {
                subdirLi.removeAttribute('data-loading');
            }
            displayDirContent(dirs, subDirUl, reset);
        },
        error: function(xhr, status, error) {
            decreaseAjaxPending();
            if (subdirLi != null) {
                subdirLi.removeAttribute('data-loading');
            }
            console.error('AJAX Error:', status, error);
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
    if(dirs?.length)
    {
      dirs.forEach(function (dir) {
        if (dir.type === 'dir') { // If the item is a directory
            const dirLi = document.createElement('li');
            dirLi.dataset.type = dir.type;
            const dirSpan = document.createElement('span');
            dirSpan.textContent = dir.name;
            dirSpan.dataset.dir = dir.path;
            dirSpan.classList.add('dir');

            // Create a <ul> for subdirectories, initially hidden
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
}

/**
 * Gets the full directory path (excluding the file name) from a given path.
 *
 * @param {string} path - The full path string (e.g., "/home/user/docs/file.txt").
 * @returns {string} The directory path without the trailing slash and without the file name.
 *
 * @example
 * getDirectoryName('/home/user/docs/file.txt'); // Returns: "home/user/docs"
 * getDirectoryName('/var/log/');               // Returns: "var/log"
 * getDirectoryName('file.txt');                // Returns: ""
 * getDirectoryName('/');                       // Returns: ""
 */
function getDirectoryName(path) {
  // Safeguard: ensure input is a valid string
  if (typeof path !== 'string' || path.trim() === '') {
    console.warn('Invalid path provided');
    return '';
  }

  // Trim and remove trailing slash
  path = path.trim().replace(/\/+$/, '');

  // Find last slash position
  const lastSlashIndex = path.lastIndexOf('/');
  if (lastSlashIndex <= 0) return '';

  // Return the directory path without leading slash
  return path.substring(0, lastSlashIndex).replace(/^\/+/, '');
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
    .then(response => response.json())  // Parse the JSON response
    .then(response => // NOSONAR
      {
        // Successfully received response from the server
        decreaseAjaxPending();
        let subDirUl = null;
        let subDirLi = null;

        let path = document.querySelector('.file-path').value;
        let dir = getDirectoryName(path);
        if(dir.length > 0)
        {
          let dirSelector = `span[data-dir="${dir}"]`;
          subDirLi = document.querySelector(dirSelector).closest('li'); 
        }

        if(selectedItem != null)
        {
          // When directory is open by context menu
          subDirLi = selectedItem.closest('li'); // Get the <li> that contains the subdirectory
          subDirUl = selectedItem.closest('li').querySelector('ul'); // Get the <ul> that contains subdirectories
          if(subDirUl == null)
          {
              subDirUl = document.createElement('ul');  // Create a new <ul> for subdirectories
              subDirLi.appendChild(subDirUl); // Append it to the <li> for the current directory
          }
          if(response.dirs)
          {
              subDirLi.removeAttribute('data-loading');
              displayDirContent(response.dirs, subDirUl, true); // Display the directory content
              if(subDirLi != null)
              {
                  subDirLi.setAttribute('data-open', 'true'); // Mark the directory as open
              }
          }
          else
          {
            let dirUl = document.querySelector('#dir-tree');
            loadDirContent('', dirUl, null, true);
          }
        }
        else if(subDirLi != null)
        {
          // When directory is open by click
          if(response.dirs)
          {
              subDirLi.removeAttribute('data-loading');
              displayDirContent(response.dirs, subDirUl, true); // Display the directory content
              if(subDirLi != null)
              {
                  subDirLi.setAttribute('data-open', 'true'); // Mark the directory as open
              }
          }
          else
          {
            let dirUl = document.querySelector('#dir-tree');
            loadDirContent('', dirUl, null, true);
          }
        }
        else
        {
          // DO directory open
          let dirUl = document.querySelector('#dir-tree');
          loadDirContent('', dirUl, null, true);
        }
    })
    .catch(error => {
        // Handle any errors that occurred during the request
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
    if(!extension)
    {
      extension = getFileExtension(file); // Get the file extension if not provided 
    }
    // List of non-text extensions (images, videos, audio, etc.)


    const nonTextExtensions = [
      'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico', // image
      'pdf', 'xls', 'xlsx', 'ods', 'csv', 'docx', // document
      'sqlite', 'db', // SQLite database
      'ttf', 'otf', 'woff', 'woff2', 'eot', // font
      'mp3', 'wav', 'flac', 'm4a', // audio
      'mp4', 'ogg', 'webm', 'avi', 'mov', 'wmv', 'flv', 'mkv', '3gp' // video
    ];

    let lowerExtension = extension.toLowerCase();

    // Check if the file extension is not for text files or supported images
    if (!nonTextExtensions.includes(lowerExtension)) {
        if(currentMode === null)
        {
            changeMode(file, extension); 
        }
        openTextFile(file, extension);
        setDisplayMode('text');
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'ico'].includes(lowerExtension)) {
        // For supported image extensions, use the server-side script to load the image as base64
        viewImage(file);
        setDisplayMode('image');
    } else if (['sqlite', 'db'].includes(lowerExtension)) {

        // database-display

        initDatabaseOnce(file);
        setDisplayMode('database');
        
    } else if (['pdf', 'xls', 'xlsx', 'csv', 'ods', 'docx'].includes(lowerExtension)) {
        // database-display
        initDocumentViewerOnce(file);
        setDisplayMode('document');
        
    } else if (['ttf', 'otf', 'woff', 'woff2', 'eot'].includes(lowerExtension)) {
        // font-display
        initFontViewerOnce(file, 'font-frame');
        setDisplayMode('frame');
    } else if(['mp4', 'ogg', 'webm', 'avi', 'mov', 'wmv', 'flv', 'mkv', '3gp'].includes(lowerExtension) ) { 
        // video-display
        initFrameViewerOnce(file, 'video-frame');
        setDisplayMode('frame');
    } else if(['mp3', 'wav', 'flac', 'm4a'].includes(lowerExtension) ) { 
        // video-display
        initFrameViewerOnce(file, 'audio-frame');
        setDisplayMode('frame');
    } else {
        // For unsupported file extensions, display an error message
        fileDiv.textContent = 'Cannot open this file.'; // Display error message for unsupported file types
    }
    
}

/**
 * Display an image in the media display area by fetching it from the server.
 *
 * This function sends a request to the server to load an image file as
 * Base64-encoded data, then dynamically creates an <img> element and injects
 * it into the `.media-display` container.
 *
 * It also manages AJAX pending counters using `increaseAjaxPending()` and
 * `decreaseAjaxPending()`.
 *
 * @function viewImage
 * @param {string} file - The relative or absolute path of the image file to load.
 * @returns {void}
 */
function viewImage(file) {
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
      mediaDisplay.innerHTML = '';
      mediaDisplay.appendChild(img); // Append the image to the display
      decreaseAjaxPending();
    })
    .catch(error => {
      decreaseAjaxPending();
    });
}

/**
 * Initialize the frame viewer once with the given file.
 * 
 * Loads a file into the frame viewer element (#frame-viewer) by embedding
 * it in an iframe. The file will be rendered by the file manager loader.
 *
 * @param {string} file - Path of the file to display in the frame viewer.
 * @param {string} className - CSS class name(s) to apply to the iframe element.
 */
function initFrameViewerOnce(file, className) {
  $('#frame-viewer').html(
    `<iframe class="${className}" src="lib.ajax/file-manager-load-file.php?file=${encodeURIComponent(file)}" frameborder="0"></iframe>`
  );
}

/**
 * Initialize the font viewer once with the given font file.
 * 
 * Loads a font file into the frame viewer element (#frame-viewer) by embedding
 * it in an iframe. The file will be rendered by the font viewer handler.
 *
 * @param {string} file - Path of the font file to display in the font viewer.
 * @param {string} className - CSS class name(s) to apply to the iframe element.
 */
function initFontViewerOnce(file, className) {
  $('#frame-viewer').html(
    `<iframe class="${className}" src="lib.ajax/file-managet-font-viewer.php?file=${encodeURIComponent(file)}" frameborder="0"></iframe>`
  );
}


/**
 * Initialize the database view once by ensuring all SQLite-related scripts are loaded only once.
 *
 * This function finds all <script> elements with the class `script-for-sqlite-viewer`
 * and loads each script from its `data-src` attribute if it has not been loaded yet.
 * It waits until all required scripts are fully loaded before running the database initialization.
 *
 * @param {string} file - The file path of the SQLite database to load.
 */
function initDatabaseOnce(file) {
    const scriptTags = Array.from(document.querySelectorAll('.script-for-sqlite-viewer'));

    if (scriptTags.length === 0) {
        console.error("No <script class='script-for-sqlite-viewer'> elements found.");
        return;
    }

    // Check if all scripts are already loaded
    const allLoaded = scriptTags.every(el => el.dataset.loaded === "true");
    if (allLoaded) {
        runDatabaseInit(file);
        return;
    }

    // Load each script if not loaded yet
    let remaining = scriptTags.filter(el => el.dataset.loaded !== "true").length;

    scriptTags.forEach(scriptTag => {
        if (scriptTag.dataset.loaded === "true") return;

        const url = scriptTag.getAttribute('data-src');
        if (!url) {
            console.warn("Missing data-src for script:", scriptTag);
            remaining--;
            return;
        }

        const newScript = document.createElement('script');
        newScript.src = url;

        newScript.onload = function() {
            // Mark script as loaded
            scriptTag.dataset.loaded = "true";
            remaining--;

            // When all scripts have finished loading, run initialization
            if (remaining === 0) {
                runDatabaseInit(file);
            }
        };

        newScript.onerror = function() {
            console.error("Failed to load script:", url);
            remaining--;
        };

        document.body.appendChild(newScript);
    });
}

/**
 * Run the database initialization logic after all SQLite scripts have been loaded.
 *
 * This function updates the database display, sets the current database name,
 * loads database content from the server, updates the file path display,
 * and switches the UI into "database" display mode.
 *
 * @param {string} file - The file path of the SQLite database being displayed.
 */
function runDatabaseInit(file) {
    // Update the database display content
    let selctor = document.querySelector('.database-display');
    selctor.innerHTML = createDatabaseResource();

    // Set the current database name from the file name
    curretDatabaseName = getFileBaseName(file);

    // Load the database content from the server
    loadDatabaseFromUrl('lib.ajax/file-manager-load-file.php?file=' + encodeURIComponent(file));

    // Show the SQLite file path in the UI
    document.querySelector('.sqlite-file-path').textContent = file;

    // Switch UI mode to "database"
    setDisplayMode('database');
}

/**
 * Initialize the document viewer once by ensuring all related scripts are loaded only once.
 *
 * This function finds all <script> elements with the class `script-for-document-viewer`
 * and loads each script from its `data-src` attribute if it has not been loaded yet.
 * It waits until all required scripts are fully loaded before running the document viewer initialization.
 *
 * @param {string} file - The URL or file path of the document to preview.
 */
function initDocumentViewerOnce(file) {
    const scriptTags = Array.from(document.querySelectorAll('.script-for-document-viewer'));

    if (scriptTags.length === 0) {
        console.error("No <script class='script-for-document-viewer'> elements found.");
        return;
    }

    // Check if all scripts are already loaded
    const allLoaded = scriptTags.every(el => el.dataset.loaded === "true");
    if (allLoaded) {
        runDocumentViewerInit(file);
        return;
    }

    // Load each script if not loaded yet
    let remaining = scriptTags.filter(el => el.dataset.loaded !== "true").length;

    scriptTags.forEach(scriptTag => {
        if (scriptTag.dataset.loaded === "true") return;

        const url = scriptTag.getAttribute('data-src');
        if (!url) {
            console.warn("Missing data-src for script:", scriptTag);
            remaining--;
            return;
        }

        const newScript = document.createElement('script');
        newScript.src = url;

        newScript.onload = function() {
            // Mark script as loaded
            scriptTag.dataset.loaded = "true";
            remaining--;

            // When all scripts have finished loading, run initialization
            if (remaining === 0) {
                runDocumentViewerInit(file);
            }
        };

        newScript.onerror = function() {
            console.error("Failed to load script:", url);
            remaining--;
        };

        document.body.appendChild(newScript);
    });
}

/**
 * Run the document viewer initialization logic after all required scripts have been loaded.
 *
 * This function previews the document file, updates the UI, and sets the display mode.
 *
 * @param {string} file - The URL or file path of the document being displayed.
 */
function runDocumentViewerInit(file) {
    previewFile('lib.ajax/file-manager-load-file.php?file=' + encodeURIComponent(file));
    setDisplayMode('document');
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
    const modes = ['text', 'image', 'database', 'document', 'frame'];
    modes.forEach(m => {
      const el = document.querySelector(`.${m}-mode`);
      if (el) el.style.display = m === mode ? 'block' : 'none';
    });
    if(['text', 'image', 'database', 'document'].includes(mode))
    {
      // Clear frame
      // Important to stop video or audio
      $('#frame-viewer').html('');
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
 * - Crucially, 'indentWithTabs' is set to false, and a 'Smart Mode' function is introduced
 * to handle specific indentation rules for file types like YAML.
 */

// --- Centralized Mode and Indentation Mapping ---
// This object serves as the single source of truth for file extension to CodeMirror mode mappings
// and associated indentation preferences.
const editorModeMap = {
    // Key: file extension (or array of extensions)
    // Value: { mode: CodeMirror_MIME_Type, indentUnit: number, indentWithTabs: boolean (optional, default false) }
    // General web/config files (2 spaces)
    'yaml': { mode: "text/x-yaml", indentUnit: 2 },
    'yml': { mode: "text/x-yaml", indentUnit: 2 }, // YAML alias
    'js': { mode: "text/javascript", indentUnit: 2 },
    'json': { mode: "application/json", indentUnit: 2 },
    'css': { mode: "text/css", indentUnit: 2 },
    'xml': { mode: "application/xml", indentUnit: 2 },
    'html': { mode: "text/html", indentUnit: 2 },
    'htm': { mode: "text/html", indentUnit: 2 }, // HTML alias
    'md': { mode: "text/x-markdown", indentUnit: 2 },
    'markdown': { mode: "text/x-markdown", indentUnit: 2 }, // Markdown alias
    'ts': { mode: "text/typescript", indentUnit: 2 },
    'tsx': { mode: "text/typescript", indentUnit: 2 }, // TypeScript alias
    'jsx': { mode: "text/jsx", indentUnit: 2 },
    'vue': { mode: "text/x-vue", indentUnit: 2 },
    'rb': { mode: "text/x-ruby", indentUnit: 2 }, // Ruby
    'sh': { mode: "text/x-sh", indentUnit: 2 }, // Shell Script
    'conf': { mode: "text/x-properties", indentUnit: 2 }, // Generic config
    'cfg': { mode: "text/x-properties", indentUnit: 2 }, // Generic config
    'properties': { mode: "text/x-properties", indentUnit: 2 }, // Properties file
    'ini': { mode: "text/x-properties", indentUnit: 2 }, // INI file
    'env': { mode: "text/x-properties", indentUnit: 2 }, // .env file

    // Languages commonly using 4 spaces
    'php': { mode: "application/x-httpd-php", indentUnit: 4 },
    'py': { mode: "text/x-python", indentUnit: 4 },
    'java': { mode: "text/x-java", indentUnit: 4 },
    'c': { mode: "text/x-csrc", indentUnit: 4 },
    'cpp': { mode: "text/x-csrc", indentUnit: 4 },
    'h': { mode: "text/x-csrc", indentUnit: 4 }, // C/C++ Header
    'go': { mode: "text/x-go", indentUnit: 4 },
    'sql': { mode: "text/x-sql", indentUnit: 4 },

    // Plain text types (no specific highlighting, simple indentation)
    'log': { mode: "text/plain", indentUnit: 2 },
    'txt': { mode: "text/plain", indentUnit: 2 },
    'csv': { mode: "text/plain", indentUnit: 2 },
};

function initCodeMirror() {
    // Ensure CodeMirror library is loaded
    if (typeof CodeMirror === 'undefined') {
        console.error("CodeMirror library not found. Please ensure codemirror.js is loaded.");
        return;
    }

    // Get the filename element and set the modeURL
    modeInput = document.getElementById('filename'); // NOSONAR
    CodeMirror.modeURL = "lib.assets/cm/mode/%N/%N.js"; // Path to CodeMirror mode files

    fileManagerEditor = CodeMirror.fromTextArea(document.getElementById("code"), // NOSONAR
    {
        lineNumbers: true,           // Show line numbers in the editor
        lineWrapping: true,          // Enable line wrapping to prevent horizontal scrolling
        matchBrackets: true,         // Highlight matching brackets
        indentUnit: 4,               // Default indentation unit (can be overridden by mode map)
        indentWithTabs: false,       // Default to spaces (crucial for YAML)
        // Optional: Set keymap for Vim, Emacs, or Sublime if desired
        // keyMap: "sublime",
        // extraKeys: {"Ctrl-Space": "autocomplete"}
    });

    // --- Function to Set Mode and Indentation based on File Extension ---
    /**
     * Sets the CodeMirror mode and indentation preferences based on the file extension.
     * This ensures correct highlighting and indentation for different file types.
     * It uses a centralized `editorModeMap` for consistent configuration.
     * @param {string} filename The name of the file being edited (e.g., 'config.yaml').
     */
    window.setEditorModeByFilename = function(filename) {
        let currentMode = "text/plain"; // Default CodeMirror MIME type
        let currentIndentUnit = 2;      // Default indentation unit
        let currentIndentWithTabs = false; // Default to spaces

        const lastDotIndex = filename.lastIndexOf('.');
        const extension = lastDotIndex > -1 ? filename.substring(lastDotIndex + 1).toLowerCase() : '';

        // 1. Try to find configuration in our custom map first
        const config = editorModeMap[extension];

        if (config) {
            currentMode = config.mode;
            currentIndentUnit = config.indentUnit;
            // Only override indentWithTabs if explicitly defined in config, otherwise use default (false)
            if (typeof config.indentWithTabs !== 'undefined') {
                currentIndentWithTabs = config.indentWithTabs;
            }
        } else if (extension) {
            // 2. If not in our custom map, try CodeMirror's built-in extension finder
            const info = CodeMirror.findModeByExtension(extension);
            if (info) {
                currentMode = info.mime;
                // CodeMirror's built-in modes usually default to spaces unless specified
                // We'll stick to our default indentUnit and indentWithTabs unless overridden
            }
        }
        // If no extension or no info found, it remains "text/plain" and default indentation

        fileManagerEditor.setOption("mode", currentMode);
        fileManagerEditor.setOption("indentWithTabs", currentIndentWithTabs);
        fileManagerEditor.setOption("indentUnit", currentIndentUnit);

        // Load mode if not already loaded (important for dynamic mode changes)
        // Use info.mode if available, otherwise just use currentMode (which could be the MIME type directly)
        const modeName = CodeMirror.findModeByMIME(currentMode)?.mode || currentMode;
        CodeMirror.autoLoadMode(fileManagerEditor, modeName);

        console.log(`Editor mode set to: ${currentMode} for file: ${filename} (Indent: ${currentIndentUnit} spaces, Tabs: ${!currentIndentWithTabs ? 'No' : 'Yes'})`);
    };

    // --- Initial Initialization and Size Adjustment ---
    // Adjust editor size when window is resized
    window.addEventListener('resize', function(e){
        let w = document.querySelector('#file-content').offsetWidth - 16;  // Adjust width
        let h = document.innerHeight - 160;  // Adjust height based on window height
        fileManagerEditor.setSize(w, h);  // Apply the new size to the editor
    });

    // Initial editor size adjustment
    let w = document.querySelector('#file-content').offsetWidth - 16;
    let h = document.innerHeight - 160;
    fileManagerEditor.setSize(w, h);
}

