<?php
/**
 * Function to get the directory tree structure.
 * 
 * This function scans a given directory and returns an array of files and subdirectories.
 * The directory and file information is organized separately, and the result is returned
 * as a JSON-encoded string for use in applications such as web pages or APIs.
 * 
 * @param string $dir The directory path to scan.
 * @return string A JSON-encoded string containing the directory tree.
 */
function getDirTree($dir) {
    // Scan the folder and retrieve files and subdirectories
    $files = scandir($dir);
    $result = [];   // Array to hold the final result
    $result1 = [];  // Array to hold directories
    $result2 = [];  // Array to hold files

    // Loop through each file/directory in the scanned list
    foreach ($files as $file) {
        // Skip '.' and '..' (current and parent directory)
        if ($file != '.' && $file != '..') {
            $path = $dir . DIRECTORY_SEPARATOR . $file; // Get the full path of the file or directory
            if (is_dir($path)) {
                // If it's a directory, add it to the $result1 array
                $result1[] = [
                    'type' => 'dir',  // Specify the type as 'dir' for directory
                    'name' => $file,  // Name of the directory
                    'path' => $path   // Full path to the directory
                ];
            } else if (is_file($path)) {
                // If it's a file, get its extension
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                
                // Add the file information to the $result2 array
                $result2[] = [
                    'type' => 'file',        // Specify the type as 'file' for files
                    'name' => $file,         // Name of the file
                    'path' => $path,         // Full path to the file
                    'extension' => $extension // Add the file extension to the result
                ];
            }
        }
    }

    // Merge directories and files arrays into one result array
    $result = array_merge($result1, $result2);
    
    // Return the result as a JSON-encoded string
    return json_encode($result);
}

// Set the content type of the response to JSON
header('Content-type: application/json');

// Check if the 'dir' parameter is passed in the query string
if (isset($_GET['dir'])) {
    $dir = $_GET['dir'];  // Get the directory path from the query string
    echo getDirTree($dir); // Call the function and echo the result as JSON
}
?>
