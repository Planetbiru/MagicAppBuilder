<?php

use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\File\PicoUploadFile;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$separatorNLT = "\r\n\t";

// Exit if the application is not set up
if ($appConfig->getApplication() == null) {
    exit();
}


$inputPost = new InputPost();

// Set the response header to return JSON
header('Access-Control-Allow-Origin: *');

try {
    // Get the base directory of the active application
    $baseDirectory = $activeApplication->getBaseApplicationDirectory();
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    $dir = $baseDirectory . "/" . $inputPost->getDir();
    $dir = FileDirUtil::normalizationPath($dir);
    
    $uploadedFile = new PicoUploadFile();
    $files = $uploadedFile->files;
    foreach($files->getAll() as $key => $fileItem) {
        $fileName = $fileItem->getName();
        $filePath = $dir . "/" . $fileName;
        $filePath = FileDirUtil::normalizationPath($filePath);
        
        $temporaryName = $fileItem->getTmpName();
        $name = $fileItem->getName();
        $size = $fileItem->getSize();
        move_uploaded_file($temporaryName, $filePath);
    }
    
    $response = [
        'status' => 'success',
        'message' => 'Files uploaded successfully',
        'dirs' => FileDirUtil::getDirTree($dir, $baseDirectory, 1)
    ];
    // Return the response as JSON
    ResponseUtil::sendJSON($response);
    exit();

} catch (Exception $e) {
    // Log any errors that occur
    error_log($e->getMessage());
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    // Return the error response as JSON
    ResponseUtil::sendJSON($response);
    exit();
}