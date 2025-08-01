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
header('Content-type: application/json');
try {
    // Get the base directory of the active application
    $baseDirectory = $activeApplication->getBaseApplicationDirectory();
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    // Construct the full path based on the base directory and the requested directory
    $dir = $baseDirectory . "/" . $inputGet->getDir();
    // Normalize the constructed directory path
    $dir = FileDirUtil::normalizationPath($dir);

    // Get the directory tree and output it as a JSON string
    $buffer = json_encode(FileDirUtil::getDirTree($dir, $baseDirectory, 0));
    header('Content-length: ' . strlen($buffer));
    echo $buffer;

} catch (Exception $e) {
    // Log any errors that occur
    error_log($e->getMessage());
    echo "{}";
    // do nothing
}
