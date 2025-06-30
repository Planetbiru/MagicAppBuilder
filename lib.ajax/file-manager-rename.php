<?php

use AppBuilder\Util\FileDirUtil;
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
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    $name = $baseDirectory . "/" . $inputPost->getName();
    $name = FileDirUtil::normalizationPath($name);
    
    $dir = dirname($name); // Get the directory name from the file path
    
    $newName = $baseDirectory . "/" . $inputPost->getNewName();
    $newName = FileDirUtil::normalizationPath($newName);
    
    
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
    
    $response['dirs'] = FileDirUtil::getDirTree($dir, $baseDirectory, 0);

} catch (Exception $e) {
    // Log any errors that occur
    
    $response['status'] = 'error';
    $response['message'] = 'An unexpected error occurred';
}

// Return the response as JSON
echo json_encode($response);
