<?php

use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";


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
    // Normalize the base directory path
    $baseDirectory = FileDirUtil::normalizationPath($baseDirectory);
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    $name = $baseDirectory . "/" . $inputPost->getName();
    $name = FileDirUtil::normalizationPath($name);
    
    $dir = dirname($name); // Get the directory name from the file path
    
    $type = $inputPost->getType();
    
    if (file_exists($name)) {
        // Check if the file already exists and can be deleted
        if ($type == "file") {
            // If it's a file, delete it
            if (unlink($name)) {
                $response['success'] = true;
                $response['status'] = 'success';
                $response['message'] = 'File deleted successfully';
            } else {
                $response['success'] = false;
                $response['status'] = 'error';
                $response['message'] = 'Error deleting file';
            }
        } else if ($type == "dir") {
            // If it's a directory, delete it
            if (rmdir($name)) {
                $response['success'] = true;
                $response['status'] = 'success';
                $response['message'] = 'Directory deleted successfully';
            } else {
                $response['success'] = false;
                $response['status'] = 'error';
                $response['message'] = 'Error deleting directory';
            }
        }
    } else {
        // If the file or directory does not exist, return a message
        $response['success'] = false;
        $response['status'] = 'error';
        $response['message'] = 'File not found';
    }
    
    $response['dirs'] = FileDirUtil::getDirTree($dir, $baseDirectory, 0);

} catch (Exception $e) {
    // Log any errors that occur
    
    $response['success'] = false;
    $response['status'] = 'error';
    $response['message'] = 'An unexpected error occurred';
}

// Return the response as JSON
echo ResponseUtil::sendJSON($response);
