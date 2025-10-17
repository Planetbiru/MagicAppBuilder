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

$response = [];

try {
    // Get the base directory of the active application
    $baseDirectory = $activeApplication->getBaseApplicationDirectory();
    // Remove trailing slash if exists
    
    $baseDirectory = FileDirUtil::normalizationPath($baseDirectory);
    
    $baseDirectory = rtrim($baseDirectory, "/");

    $name = $baseDirectory . "/" . $inputPost->getName();
    $name = FileDirUtil::normalizationPath($name);
    
    $dir = dirname($name); // Get the directory name from the file path
    
    $newName = $baseDirectory . "/" . $inputPost->getNewName();
    $newName = FileDirUtil::normalizationPath($newName);
    
    
    if (file_exists($name)) {
        // Check if the new name already exists
        if (file_exists($newName)) {
            $response['success'] = false;
            $response['status'] = 'error';
            $response['message'] = 'File already exists';
        } else {
            // Rename the file
            if (rename($name, $newName)) {
                $response['success'] = true;
                $response['status'] = 'success';
                $response['message'] = 'File renamed successfully';
            } else {
                $response['success'] = false;
                $response['status'] = 'error';
                $response['message'] = 'Error renaming file';
            }
        }
    } else {
        // If the file does not exist, return a message
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
ResponseUtil::sendJSON($response);
