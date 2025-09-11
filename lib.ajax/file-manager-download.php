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

// Set the response header to return JSON

try {
    // Get the base directory of the active application
    $baseDirectory = $activeApplication->getBaseApplicationDirectory();
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    $name = $baseDirectory . "/" . $inputGet->getName();
    $name = FileDirUtil::normalizationPath($name);
    
    $type = $inputGet->getType();
    if(class_exists('ZipArchive') === false) {
        throw new Exception("ZipArchive class is not available. Please ensure the PHP zip extension is installed and enabled.");
    }
    
    if (file_exists($name)) {
        if($type == "file") {
            // If it's a file, set the response to download the file
            header('Content-Disposition: attachment; filename="' . basename($name) . '"');
            header('Content-Length: ' . filesize($name)); // NOSONAR
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Expires: 0'); // NOSONAR
            header('Content-Type: application/octet-stream');
            readfile($name);
            exit();
        } else if ($type == "dir") {
            // If it's a directory, set the response to show the directory contents
            header('Content-Disposition: attachment; filename="' . basename($name) . '.zip"');
            header('Content-Type: application/zip');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Expires: 0');
            
            // Create a zip file and add the directory contents to it
            $zip = new ZipArchive();
            // Create temporary zip file name
            $zipFileName = tempnam(sys_get_temp_dir(), 'zip');
            // Open the zip file for writing
            // Use ZipArchive::OVERWRITE to overwrite the file if it already exists
            // Use ZipArchive::CREATE to create the file if it doesn't exist
            // Use ZipArchive::CREATE | ZipArchive::OVERWRITE to create and overwrite the file
            if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($name),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($name) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
                
                $zip->close();
                
                // Output the zip file
                header('Content-Length: ' . filesize($zipFileName));
                readfile($zipFileName);
                
                // Delete the zip file after download
                unlink($zipFileName);
                exit();
            } else {
                echo "Failed to create zip file.";
                exit();
            }
            
        } 
    } else {
        // If the file or directory does not exist, return an error message
        header('Content-Type: application/json');
        header('HTTP/1.1 404 Not Found');
        header('Content-Length: ' . strlen("File or directory not found."));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        // Return a JSON response indicating failure
        http_response_code(404);
        echo json_encode(array(
            "success" => false,
            "message" => "File or directory not found."
        ));
        exit();
    }
    

} catch (Exception $e) {
    // Log any errors that occur
    header('Content-Type: application/json');
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Length: ' . strlen($e->getMessage()));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    // Return a JSON response indicating failure
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => $e->getMessage()
    ));
    error_log($e->getMessage());
    error_log($e->getTraceAsString());
    exit();
}
