<?php

use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputPost;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php"; // Load authentication script

// Retrieve data from the POST request
$inputPost = new InputPost();
$directory = FileDirUtil::normalizePath($inputPost->getDirectory()); // Normalize the directory path
$failedIfExists = $inputPost->getFailedIfExists() == 'true'; // Check if the request should fail if the directory exists

// If the input is a file, get its parent directory
if ($inputPost->getIsfile() == 'true') {
    $directory = dirname($directory);
}

/**
 * Check if the given path has a subdirectory
 * 
 * @param string $directory The directory path to check
 * @return bool True if it has a subdirectory, false otherwise
 */
function hasSubdirectory($directory) {
    $directory = str_replace("\\", "/", $directory); // Normalize directory separators
    $arr = explode("/", $directory); // Split the path into an array
    return count($arr) > 2; // Return true if the path has more than two segments
}

/**
 * Check if the given path is a root directory
 *
 * @param string $path
 * @return bool
 */
function isRootDirectory($path) {
    $path = str_replace("\\", "/", $path);       // Normalisasi separator
    $path = rtrim($path, "/");                   // Hilangkan trailing slash

    // Hitung jumlah segmen path
    $segments = explode("/", $path);

    // Root biasanya hanya punya 1 segmen (misal: "C:", atau "")
    return count($segments) === 1;
}

$writeable = false; // Default: Directory $directory is not writable
$permissions = 0; // Default permissions
$message = null; // Default message

// Check if the directory is null
if ($directory == null) {
    $writeable = false;
    $message = "Directory cannot be null";
} 
// Check if the directory is empty
else if (empty($directory)) {
    $writeable = false;
    $message = "Directory cannot be empty";
} 
else {
    // Check if the directory exists
    if (file_exists($directory)) {
        if ($failedIfExists) {
            $writeable = false;
            $message = "Path already exists";
        }
        // Check if the path is not a directory
        else if (!is_dir($directory)) {
            $writeable = false;
            $message = "Path is not a directory";
        } 
        else {
            // Check if the directory is writable
            $writeable = is_writable($directory);
            if ($writeable) {
                $message = "Directory $directory is writable"; // NOSONAR
            } else {
                $message = "Directory $directory is not writable"; // NOSONAR
            }
            $permissions = fileperms($directory); // Get directory permissions
        }
    } 
    else {
        // If the directory does not exist, check its parent directories
        $directoryToCheck = $directory;
        while (!file_exists($directoryToCheck) && hasSubdirectory($directoryToCheck)) {
            $directoryToCheck = dirname($directoryToCheck);
        }

        // Check if the parent directory exists
        if (file_exists($directoryToCheck)) {
            $permissions = fileperms($directoryToCheck); // Get parent directory permissions
            $writeable = is_writable($directoryToCheck); // Check if it is writable
            if ($writeable) {
                $message = "Directory $directory is writable"; // NOSONAR
            } else {
                $message = "Directory $directory is not writable"; // NOSONAR
            }
        } 
        else {
            $writeable = is_writable(dirname($directoryToCheck));
            if ($writeable) {
                $message = "Directory $directory is writable"; // NOSONAR
            } else {
                // try to create directory
                $parentDir = dirname($directoryToCheck);
                if (isRootDirectory($parentDir)) {
                    if (@mkdir($directoryToCheck, $permissions, true)) {
                        $writeable = true;
                        $message = "Directory $directory is writable"; // NOSONAR
                        @rmdir($directoryToCheck);
                    } else {
                        $writeable = false;
                        $message = "Directory $directory is not writable"; // NOSONAR
                    }
                }
            }
        }
    }
}

// Send the JSON response with the directory status
PicoResponse::sendJSON([
    'writeable' => $writeable, 
    'permissions' => $permissions,
    'message' => $message
]);
