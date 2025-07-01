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


try {
    // Get the base directory of the active application
    $baseDirectory = $activeApplication->getBaseApplicationDirectory();
    // Remove trailing slash if exists
    $baseDirectory = rtrim($baseDirectory, "/");

    // Construct the full path based on the base directory and the requested directory
    $file = $baseDirectory . "/" . $inputGet->getFile();
    $file = FileDirUtil::normalizationPath($file);
        
    // Check if the file exists on the server
    if (file_exists($file)) {
        // Get the file extension (e.g., 'jpg', 'png', etc.)
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
        // Define a list of valid image extensions
        $validImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'ico'];

        // Check if the file extension is one of the valid image formats
        if (in_array(strtolower($extension), $validImageExtensions)) {
            // Read the image data from the file
            $imageData = file_get_contents($file);
            
            // Encode the image data to Base64 for embedding in HTML
            $base64Image = base64_encode($imageData);
            
            // Get the MIME type for the image file using the custom function
            $mimeType = FileDirUtil::getCustomMimeType($file);
            
            // Output the image as base64-encoded data with the appropriate MIME type
            echo 'data:' . $mimeType . ';base64,' . $base64Image;
        } 
    }
} catch (Exception $e) {
    // Log any errors that occur
    error_log($e->getMessage());
    // do nothing
}