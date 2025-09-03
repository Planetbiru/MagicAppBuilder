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

          if (itemType === 'file') {
              itemName = target.querySelector("span").dataset.file;
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
              menuList.innerHTML = `
              <li data-type="file" data-operation="open" data-file="${itemName}">Open File</li>
              <li data-type="file" data-operation="rename" data-file="${itemName}">Rename File</li>
              <li data-type="file" data-operation="download" data-file="${itemName}">Download File</li>
              <li data-type="file" data-operation="delete" data-file="${itemName}">Delete File</li>
              `;
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
    const nonTextExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico', 'mp4', 'mp3', 'avi', 'exe', 'pdf', 'sqlite', 'db'];

    // Check if the file extension is not for text files or supported images
    if (!nonTextExtensions.includes(extension.toLowerCase())) {
        if(currentMode === null)
        {
            changeMode(file, extension); 
        }
        setDisplayMode('text');
        openTextFile(file, extension);
    } else if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'].includes(extension.toLowerCase())) {
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
                mediaDisplay.innerHTML = '';
                mediaDisplay.appendChild(img); // Append the image to the file content div
                decreaseAjaxPending();
            })
            .catch(error => {
                decreaseAjaxPending();
            });
    } else if (['sqlite', 'db'].includes(extension.toLowerCase())) {

        // database-display

        let selctor = document.querySelector('.database-display');
        selctor.innerHTML = createDatabaseResource();
        curretDatabaseName = getFileBaseName(file);
        loadDatabaseFromUrl('lib.ajax/file-manager-load-file.php?file=' + encodeURIComponent(file));
        document.querySelector('.sqlite-file-path').textContent = file;

        setDisplayMode('database');
        
    } else {
        // For unsupported file extensions, display an error message
        fileDiv.textContent = 'Cannot open this file.'; // Display error message for unsupported file types
    }
}

let currentData = null; // To hold the currently selected data
let selectedRowIndex = null; // To keep track of the selected row index
let columnInfo = []; // To hold column information for the currently selected table
let db;
let currentSqliteTableName = null; // To hold the name of the currently selected table
let curretDatabaseName = null;
let exportType = '';
/**
 * Load an SQLite database file from a given URL and initialize the UI.
 *
 * Workflow:
 *  1. Read the database URL from the input field (#sqliteDatabaseUrl).
 *  2. Validate the URL; if empty, show an alert and stop execution.
 *  3. Fetch the database file from the server as an ArrayBuffer.
 *  4. Initialize SQL.js using the WebAssembly binary (sql-wasm.wasm).
 *  5. Load the database into memory (`db` instance).
 *  6. Query `sqlite_master` to get the list of tables.
 *  7. Populate the sidebar (#sqlite-table-list) with:
 *     - A structure link (`.sqlite-table-structure`) → shows table schema.
 *     - A content link (`.sqlite-table-content`) → shows table data.
 *  8. Enable the "Export Database to SQL" button.
 *
 * Event bindings:
 *  - Clicking on a table content link → calls `displayTableData(db, tableName)`.
 *  - Clicking on a table structure link → calls `displayTableStructure(db, tableName)`.
 *  - Both trigger `hilightTable(e)` to update UI state.
 *
 * Error handling:
 *  - Alerts the user if the URL is invalid or if the fetch/initialization fails.
 *
 * @function loadDatabaseFromUrl
 * @param sqliteDatabaseUrl SQLite file URL
 * @returns {void} This function does not return a value.
 *
 * @requires initSqlJs - SQL.js initializer function.
 * @requires displayTableData - Function to display data for a given table.
 * @requires displayTableStructure - Function to display schema for a given table.
 * @requires hilightTable - Function to highlight the selected table in the UI.
 */
function loadDatabaseFromUrl(sqliteDatabaseUrl)
{
  // Fetch the database from the server
  fetch(sqliteDatabaseUrl)
      .then(response => {
          if (!response.ok) {
              throw new Error("Failed to load database. Please check the URL.");
          }
          return response.arrayBuffer();  // Convert the response to ArrayBuffer
      })
      .then(arrayBuffer => {
          const uint8Array = new Uint8Array(arrayBuffer);

          // Initialize SQL.js and load the database
          initSqlJs({ locateFile: file => `lib.assets/wasm/sql-wasm.wasm` }).then(SQL => {
              db = new SQL.Database(uint8Array);  // Create a new database instance

              // Get the names of all tables in the database
              let res1 = db.exec("SELECT name FROM sqlite_master WHERE type='table';");

              let tableList = document.querySelector('#sqlite-table-sidebar #sqlite-table-list'); // Get sidebar element
              tableList.innerHTML = ''; // Clear previous table names

              if(res1?.[0]?.values?.length)
              {
                res1[0].values.forEach(row => {
                  let tableListItem = document.createElement('li');
                  let tableName = row[0]; // Extract table name
                  let tableContentLink = document.createElement('a'); // Create a link for the table
                  tableContentLink.href = '#';
                  tableContentLink.innerText = tableName; // Set link text to table name
                  tableContentLink.classList.add('sqlite-table-content');
                  tableContentLink.addEventListener('click', function (e) { //NOSONAR
                      e.preventDefault(); // Prevent default link behavior
                      displayTableData(db, tableName); // Display table data on click
                      hilightTable(e);
                  });
                  let tableStructureLink = document.createElement('a'); // Create a link for the table
                  tableStructureLink.href = '#';
                  tableStructureLink.innerText = '☰'; // Symbol link for structure
                  tableStructureLink.classList.add('sqlite-table-structure');
                  tableStructureLink.addEventListener('click', function (e) { //NOSONAR
                      e.preventDefault(); // Prevent default link behavior
                      displayTableStructure(db, tableName); // Display schema on click
                      hilightTable(e);
                  });

                  tableListItem.appendChild(tableStructureLink);
                  tableListItem.appendChild(document.createTextNode(' '));
                  tableListItem.appendChild(tableContentLink);
                  tableList.appendChild(tableListItem); // Add link to sidebar
                });

                document.getElementById('sqliteDownloadAllSqlButton').disabled = false; // Enable download button for all tables
              }
              else
              {
                db = null;
              }
          });
      })
      .catch(error => {
          alert(`Error loading database from server: ${error.message}`);
      });
}


/**
 * Display the structure of a given table (columns and their metadata).
 *
 * @param {object} db - The SQLite database instance.
 * @param {string} tableName - The name of the table to inspect.
 */
function displayTableStructure(db, tableName) {
    currentSqliteTableName = tableName; // Save the active table name
    let res = db.exec(`PRAGMA table_info(${tableName});`); // Retrieve column metadata
    let output = document.getElementById('sqlite-output'); // Get output area
    output.innerHTML = ''; // Clear previous content
    exportType = 'structure';
    document.getElementById('sqliteDownloadSqlButton').disabled = false; // Enable export button

    if (res.length > 0) {
        // Build an HTML table containing column details
        let tableString = `<h3>Table Structure: ${tableName}</h3>
                           <table class="sqlite-table-data">
                               <thead>
                                   <tr>
                                       <th>Column Name</th>
                                       <th>Data Type</th>
                                       <th>Not Null</th>
                                       <th>Default</th>
                                       <th>Primary Key</th>
                                   </tr>
                               </thead>
                               <tbody>`;

        // Iterate through column definitions and append rows
        res[0].values.forEach(column => {
            tableString += '<tr>';
            tableString += `<td>${column[1]}</td>`; // Column name
            tableString += `<td>${column[2]}</td>`; // Data type
            tableString += `<td>${column[3] === 1 ? 'Yes' : 'No'}</td>`; // Not Null constraint
            tableString += `<td>${column[4] === null ? '' : column[4]}</td>`; // Default value
            tableString += `<td>${column[5] === 1 ? 'Yes' : 'No'}</td>`; // Primary Key
            tableString += '</tr>';
        });

        tableString += '</tbody></table>'; // Close table
        output.innerHTML = tableString; // Render the result
    } else {
        output.innerHTML = "No structure found."; // Display message if no structure
    }
}

/**
 * Display data from the given table.
 *
 * @param {object} db - The SQLite database instance.
 * @param {string} tableName - The name of the table to fetch data from.
 */
function displayTableData(db, tableName) {
    currentSqliteTableName = tableName; // Save the active table name
    let res = db.exec("SELECT * FROM " + tableName); // Query all table rows
    let output = document.getElementById('sqlite-output');
    output.innerHTML = '';
    exportType = 'structure+data';
    document.getElementById('sqliteDownloadSqlButton').disabled = false; // Enable export button

    // Fetch column metadata
    columnInfo = db.exec(`PRAGMA table_info(${tableName});`)[0].values;

    if (res.length > 0) {
        output.innerHTML = `<h3>Table Content: ${tableName}</h3>` + createTable(res[0]); // Render table
        currentData = res[0]; // Save for editing
    } else {
        output.innerHTML = `<h3>Table Content: ${tableName}</h3><p>No data found.</p>`;
    }
}

/**
 * Convert database query results into an HTML table.
 *
 * @param {object} data - Query result object with `columns` and `values`.
 * @returns {string} HTML representation of the table.
 */
function createTable(data) {
    let tableString = '<table class="sqlite-table-data"><thead><tr>';

    // Add column headers
    data.columns.forEach(column => {
        tableString += `<th>${column}</th>`;
    });
    tableString += '</tr></thead><tbody>';

    // Add data rows
    data.values.forEach(row => {
        tableString += '<tr>';
        row.forEach(cell => {
            tableString += `<td>${cell !== null ? cell : ''}</td>`; // Handle NULLs
        });
        tableString += '</tr>';
    });

    tableString += '</tbody></table>';
    return tableString;
}


/**
 * Retrieve the data type of a specific column.
 *
 * @param {string} columnName - Column name.
 * @returns {string} Data type (or "UNKNOWN" if not found).
 */
function getDataType(columnName) {
    const column = columnInfo.find(col => col[1] === columnName);
    return column ? column[2] : 'UNKNOWN';
}

/**
 * Highlight the currently selected table in the sidebar.
 *
 * @param {Event} e - Click event triggered on the table list item.
 */
function hilightTable(e) {
    let li = e.target.closest('li');
    const listItems = li.closest('ul').querySelectorAll('li');

    // Remove highlight from all <li> elements
    listItems.forEach(item => {
        item.classList.remove('highlight');
    });

    // Highlight the clicked <li>
    li.classList.add('highlight');
}

const SQLITE_EXPORT_BATCH_SIZE = 50; // configurable batch size

/**
 * Exports the entire SQLite database (all user-defined tables) into a single SQL file.
 * Supports batching of INSERT statements (default 50 rows per batch).
 */
function sqliteDownloadAllSql() {
  const res = db.exec("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
  if (res.length === 0 || res[0].values.length === 0) {
      return;
  }

  const tableNames = res[0].values.map(row => row[0]);
  let sqlContent = "-- SQL Export for All Tables\r\n\r\n";

  tableNames.forEach(tableName => {
      // --- Struktur tabel ---
      const tableStructureRes = db.exec(`PRAGMA table_info(${tableName});`);
      let columnTypes = {};
      if (tableStructureRes.length > 0) {
          const columns = tableStructureRes[0].values.map(col => {
              const columnName = col[1];
              const dataType = col[2];
              columnTypes[columnName] = dataType.toUpperCase();
              const isNotNull = col[3] === 1 ? "NOT NULL" : "";
              const defaultValue = col[4] ? `DEFAULT ${col[4]}` : "";
              const primaryKey = col[5] === 1 ? "PRIMARY KEY" : "";
              return `${columnName} ${dataType} ${isNotNull} ${defaultValue} ${primaryKey}`.trim();
          }).join(",\r\n  ");

          sqlContent += `-- Table: ${tableName}\r\n`;
          sqlContent += `CREATE TABLE ${tableName} (\r\n  ${columns}\r\n);\r\n\r\n`;
      }

      // --- Data tabel ---
      const tableDataRes = db.exec(`SELECT * FROM ${tableName};`);
      if (tableDataRes.length > 0) {
          const columns = tableDataRes[0].columns;
          const rows = tableDataRes[0].values;

          for (let i = 0; i < rows.length; i += SQLITE_EXPORT_BATCH_SIZE) {
              const batch = rows.slice(i, i + SQLITE_EXPORT_BATCH_SIZE);

              const valuesList = batch.map(row => {
                  const values = row.map((value, idx) => {
                      if (value === null) return "NULL";

                      const colName = columns[idx];
                      const type = columnTypes[colName] || "";

                      if (/(INT|REAL|NUM|DECIMAL|DOUBLE|FLOAT|BOOL)/.test(type)) {
                          return value;
                      }
                      return `'${value.toString().replace(/'/g, "''")}'`;
                  });
                  return `(${values.join(", ")})`;
              });

              sqlContent += `INSERT INTO ${tableName} (${columns.join(", ")}) VALUES\r\n${valuesList.join(",\r\n")}\r\n;\r\n`;
          }

          sqlContent += "\r\n";
      }
  });

  const blob = new Blob([sqlContent], { type: "text/sql" });
  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = `${curretDatabaseName}.sql`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);

  URL.revokeObjectURL(url);
}

/**
 * Exports the schema and data of the currently selected SQLite table into an SQL file.
 * Supports batching of INSERT statements (default 50 rows per batch).
 */
function sqliteDownloadSql() {
  const res = db.exec("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
  if (res.length === 0 || res[0].values.length === 0) {
      return;
  }

  let tableName = currentSqliteTableName;
  if (!tableName) {
      return;
  }
  let sqlContent = `-- SQL Export for Table ${tableName}\r\n\r\n`;

  const tableStructureRes = db.exec(`PRAGMA table_info(${tableName});`);
  let columnTypes = {};

  if(exportType.indexOf('structure') != -1)
  {
    if (tableStructureRes.length > 0) {
        const columns = tableStructureRes[0].values.map(col => {
            const columnName = col[1];
            const dataType = col[2];
            columnTypes[columnName] = dataType.toUpperCase();
            const isNotNull = col[3] === 1 ? "NOT NULL" : "";
            const defaultValue = col[4] ? `DEFAULT ${col[4]}` : "";
            const primaryKey = col[5] === 1 ? "PRIMARY KEY" : "";
            return `${columnName} ${dataType} ${isNotNull} ${defaultValue} ${primaryKey}`.trim();
        }).join(",\r\n  ");

        sqlContent += `-- Table: ${tableName}\r\n`;
        sqlContent += `CREATE TABLE ${tableName} (\r\n  ${columns}\r\n);\r\n\r\n`;
    }
  }
  if(exportType.indexOf('data') != -1)
  {
    const tableDataRes = db.exec(`SELECT * FROM ${tableName};`);
    if (tableDataRes.length > 0) {
        const columns = tableDataRes[0].columns;
        const rows = tableDataRes[0].values;

        for (let i = 0; i < rows.length; i += SQLITE_EXPORT_BATCH_SIZE) {
            const batch = rows.slice(i, i + SQLITE_EXPORT_BATCH_SIZE);

            const valuesList = batch.map(row => {
                const values = row.map((value, idx) => {
                    if (value === null) return "NULL";

                    const colName = columns[idx];
                    const colType = columnTypes[colName] || "";

                    if (/(INT|REAL|NUM|DECIMAL|DOUBLE|FLOAT|BOOL)/.test(colType)) {
                        return value;
                    }
                    return `'${value.toString().replace(/'/g, "''")}'`;
                });
                return `(${values.join(", ")})`;
            });

            sqlContent += `INSERT INTO ${tableName} (${columns.join(", ")}) VALUES\r\n${valuesList.join(",\r\n")}\r\n;\r\n`;
        }

        sqlContent += "\r\n";
    }
  }

  const blob = new Blob([sqlContent], { type: "text/sql" });
  const url = URL.createObjectURL(blob);

  const a = document.createElement("a");
  a.href = url;
  a.download = `${tableName}.sql`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);

  URL.revokeObjectURL(url);
}


/**
 * Create the HTML structure for the SQLite database resource interface.
 *
 * The layout consists of three main sections:
 *  - Sidebar (sqlite-table-sidebar): Displays the list of tables.
 *  - Main (sqlite-table-main): Provides input controls (upload file, load from server)
 *    and an output area for displaying table data.
 *
 * Structure:
 *  <div id="sqlite-app-container">
 *    <aside id="sqlite-table-sidebar">...</aside>
 *    <main id="sqlite-table-main">...</main>
 *  </div>
 *
 * @function createDatabaseResource
 * @returns {string} HTML string representing the database resource UI layout.
 */
function createDatabaseResource() {
  return `
    <div id="sqlite-app-container">
      <aside id="sqlite-table-sidebar" class="sqlite-sidebar">
        <div class="sqlite-header-section">Tables</div>
        <div id="sqlite-table-container">
          <ul id="sqlite-table-list"></ul>
        </div>
      </aside>

      <main id="sqlite-table-main" class="sqlite-main">
        <div class="sqlite-input-area sqlite-header-section">
          <button class="btn btn-primary" id="sqliteDownloadSqlButton" onclick="sqliteDownloadSql()" disabled>Export Table to SQL</button>
          <button class="btn btn-primary" id="sqliteDownloadAllSqlButton" onclick="sqliteDownloadAllSql()" disabled>Export Database to SQL</button>
          <span class="sql-file-source btn btn-secondary"><span class="sqlite-file-path"></span></span>
        </div>
        <div id="sqlite-output"></div>
      </main>
    </div>
  `;
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
        document.querySelector('.database-mode').style.display = 'none';  // Hide the database viewer
        document.querySelector('.text-mode').style.display = 'block';  // Show the text editor
    } else if (mode === 'image') {
        document.querySelector('.text-mode').style.display = 'none';  // Hide the text editor
        document.querySelector('.database-mode').style.display = 'none';  // Hide the database viewer
        document.querySelector('.image-mode').style.display = 'block';  // Show the image viewer
    } else if (mode === 'database') {
        document.querySelector('.text-mode').style.display = 'none';  // Hide the text editor
        document.querySelector('.image-mode').style.display = 'none';  // Hide the image viewer
        document.querySelector('.database-mode').style.display = 'block';  // Show the database viewer
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

