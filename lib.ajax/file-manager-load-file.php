<?php

use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$separatorNLT = "\r\n\t";

// Exit if the application is not set up
if ($appConfig->getApplication() == null) {
    exit();
}

$inputGet = new InputGet();

header('Content-type: text/plain');

try {
    // Get the base directory of the active application
    $baseDirectory = $activeApplication->getBaseApplicationDirectory();
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    // Construct the full path based on the base directory and the requested directory
    $file = $baseDirectory . "/" . $inputGet->getFile();
    $file = FileDirUtil::normalizationPath($file);

    echo file_get_contents($file);

} catch (Exception $e) {
    // Log any errors that occur
    error_log($e->getMessage());
    // do nothing
}