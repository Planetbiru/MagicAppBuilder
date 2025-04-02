<?php
/**
 * Function to determine the MIME type from the file content.
 * 
 * This function reads the first few bytes of a file to detect its MIME type 
 * based on known "magic bytes" or file signatures for common image formats.
 * 
 * @param string $file The file path to determine the MIME type for.
 * 
 * @return string The MIME type of the file (e.g., 'image/jpeg', 'image/png').
 */
function getCustomMimeType($file) {
    // Open the file in binary read mode
    $finfo = fopen($file, "rb");
    
    // Read the first 4 bytes of the file to check its signature
    $bin = fread($finfo, 4);
    
    // Close the file after reading the data
    fclose($finfo);

    // Check for specific image file signatures (magic bytes)
    if ($bin === "\xFF\xD8\xFF\xE0" || $bin === "\xFF\xD8\xFF\xE1") {
        // JPEG image signature
        return 'image/jpeg';
    } elseif ($bin === "\x89\x50\x4E\x47") {
        // PNG image signature
        return 'image/png';
    } elseif ($bin === "GIF8" || $bin === "GIF87a") {
        // GIF image signature
        return 'image/gif';
    } elseif ($bin === "\x52\x49\x46\x46" && substr(fread(fopen($file, "rb"), 4), 0, 4) === "WEBP") {
        // WEBP image signature
        return 'image/webp';
    } elseif ($bin === "\x52\x49\x46\x46" && substr(fread(fopen($file, "rb"), 4), 0, 4) === "BMP") {
        // BMP image signature
        return 'image/bmp';
    }
    
    // Return a default binary stream MIME type if no image signature is found
    return 'application/octet-stream';
}

// Ensure that a valid file is specified in the URL
if (isset($_GET['file'])) {
    $file = $_GET['file']; // Get the file parameter from the URL
    
    // Check if the file exists on the server
    if (file_exists($file)) {
        // Get the file extension (e.g., 'jpg', 'png', etc.)
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
        // Define a list of valid image extensions
        $validImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        // Check if the file extension is one of the valid image formats
        if (in_array(strtolower($extension), $validImageExtensions)) {
            // Read the image data from the file
            $imageData = file_get_contents($file);
            
            // Encode the image data to Base64 for embedding in HTML
            $base64Image = base64_encode($imageData);
            
            // Get the MIME type for the image file using the custom function
            $mimeType = getCustomMimeType($file);
            
            // Output the image as base64-encoded data with the appropriate MIME type
            echo 'data:' . $mimeType . ';base64,' . $base64Image;
        } 
    } 
}
?>
