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

header('Content-type: application/json; charset=utf-8');
// Set the response header to return JSON
header('Access-Control-Allow-Origin: *');

try {
    // Get the base directory of the active application
    $baseDirectory = $activeApplication->getBaseApplicationDirectory();
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    $file = $baseDirectory . "/" . $inputPost->getFile();
    $file = FileDirUtil::normalizationPath($file);
    $dir = dirname($file);
    $dir = FileDirUtil::normalizationPath($dir);
    
    $content = $inputPost->getContent();

    file_put_contents($file, $content);
    
    $response = [
        'status' => 'success',
        'message' => 'File saved successfully.',
        'file' => $file,
        'dirs' => FileDirUtil::getDirTree($dir, $baseDirectory, 1)
    ];
    // Return the response as JSON
    echo json_encode($response);

} catch (Exception $e) {
    // Log any errors that occur
    error_log($e->getMessage());
    $response = [
        'status' => 'error',
        'message' => $e->getMessage()
    ];
    // Return the error response as JSON
    echo json_encode($response);
}