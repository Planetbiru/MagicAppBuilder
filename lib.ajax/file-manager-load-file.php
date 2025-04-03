<?php

use MagicObject\Request\InputGet;

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
    $file = normalizationPath($file);

    echo file_get_contents($file);

} catch (Exception $e) {
    // Log any errors that occur
    error_log($e->getMessage());
    // do nothing
}