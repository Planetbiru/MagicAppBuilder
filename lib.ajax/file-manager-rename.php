<?php

use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

/**
 * Normalizes a given file path by converting backslashes to forward slashes and 
 * handling other common path inconsistencies such as multiple slashes and relative paths.
 * 
 * @param string $path The file path to normalize.
 * @return string The normalized file path.
 */
function normalizationPath($path)
{
    // Normalize backslashes to forward slashes
    $path = str_replace("\\", "/", $path);
    
    // Replace any occurrence of multiple slashes (// or more) with a single slash
    $path = preg_replace('/\/+/', '/', $path);

    // Remove relative paths like '../' and './' that may appear in the path
    $path = str_replace("../", "/", $path);
    $path = str_replace("./", "/", $path);

    return $path;
}


/**
 * Scans the directory at the specified path with a given depth and returns the directory tree.
 * 
 * @param string $dir The directory path to scan.
 * @param string $baseDirectory The base directory that the path should be relative to.
 * @param int $depth The depth of recursion. Default is 0 (no recursion).
 * @return string A JSON-encoded string containing the directory tree with files and subdirectories.
 */
function getDirTree($dir, $baseDirectory, $depth = 0) {
    // Scan the folder and retrieve files and subdirectories
    $files = scandir($dir);
    $result1 = [];  // Array to hold directories
    $result2 = [];  // Array to hold files

    // Loop through each file/directory in the scanned list
    foreach ($files as $file) {
        // Skip '.' and '..' (current and parent directory)
        if ($file != '.' && $file != '..') {
            $path = $dir . DIRECTORY_SEPARATOR . $file; // Get the full path of the file or directory
            $path = normalizationPath($path);  // Normalize the file path
            
            // Make the path relative to the base directory
            $relativePath = str_replace($baseDirectory . "/", "", $path);

            // If it's a directory
            if (is_dir($path)) {
                $res = [
                    'type' => 'dir',  // Specify the type as 'dir' for directory
                    'name' => $file,  // Name of the directory
                    'path' => $relativePath   // Full path to the directory, relative to the base directory
                ];

                // If depth is greater than 0, recursively scan subdirectories
                if ($depth > 0) {
                    $res['childrens'] = getDirTree($path, $baseDirectory, $depth - 1);
                }
                
                $result1[] = $res;
            } 
            // If it's a file, get its extension and add it to the $result2 array
            else if (is_file($path)) {
                $extension = pathinfo($file, PATHINFO_EXTENSION);  // Get the file extension
                
                $result2[] = [
                    'type' => 'file',        // Specify the type as 'file' for files
                    'name' => $file,         // Name of the file
                    'path' => $relativePath, // Full path to the file, relative to the base directory
                    'extension' => $extension // Add the file extension to the result
                ];
            }
        }
    }

    // Merge directories and files arrays into one result array
    return array_merge($result1, $result2);
}

$separatorNLT = "\r\n\t";

// Exit if the application is not set up
if ($appConfig->getApplication() == null) {
    exit();
}

$inputPost = new InputPost();

// Set the response header to return JSON
header('Content-Type: application/json');

$response = [];

try {
    // Get the base directory of the active application
    $baseDirectory = $activeApplication->getBaseApplicationDirectory();
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    $name = $baseDirectory . "/" . $inputPost->getName();
    $name = normalizationPath($name);
    
    $dir = dirname($name); // Get the directory name from the file path
    
    $newName = $baseDirectory . "/" . $inputPost->getNewName();
    $newName = normalizationPath($newName);
    
    
    if (file_exists($name)) {
        // Check if the new name already exists
        if (file_exists($newName)) {
            $response['status'] = 'error';
            $response['message'] = 'File already exists';
        } else {
            // Rename the file
            if (rename($name, $newName)) {
                $response['status'] = 'success';
                $response['message'] = 'File renamed successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error renaming file';
            }
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'File not found';
    }
    
    $response['dirs'] = getDirTree($dir, $baseDirectory, 0);

} catch (Exception $e) {
    // Log any errors that occur
    
    $response['status'] = 'error';
    $response['message'] = 'An unexpected error occurred';
}

// Return the response as JSON
echo json_encode($response);
