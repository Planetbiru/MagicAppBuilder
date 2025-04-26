<?php

namespace AppBuilder\Util;

/**
 * Class FileDirUtil
 *
 * This utility class provides methods to interact with the filesystem, such as scanning directories
 * and normalizing file paths to ensure consistent formatting across different operating systems.
 */
class FileDirUtil
{
    /**
     * Scans a directory and returns an array of subdirectories found within.
     *
     * @param string $dir The directory path to scan.
     * @return array An array of subdirectory paths found within the provided directory.
     */
    public static function scanDirectory($dir)
    {
        $dirs = [];

        // Check if the given path is a valid directory
        if (is_dir($dir)) {
            // Scan the directory and get all files and directories
            $files = scandir($dir);

            // Remove the '.' and '..' entries
            $files = array_diff($files, array('.', '..'));

            // Loop through the files and check for directories
            foreach ($files as $file) {
                $filePath = $dir . DIRECTORY_SEPARATOR . $file;

                // If the entry is a directory, add it to the $dirs array
                if (is_dir($filePath)) {
                    $dirs[] = $filePath;
                }
            }
        }

        return $dirs;
    }

    /**
     * Normalizes a file path by converting all separators to the appropriate format
     * for the current operating system and removing unnecessary path elements like './' and '../'.
     *
     * @param string $path The file path to normalize.
     * @return string The normalized file path with forward slashes as the separator.
     */
    public static function normalizePath($path)
    {
        // Use the appropriate directory separator for the current operating system
        $separator = DIRECTORY_SEPARATOR;

        // Replace all slashes '/' and backslashes '\\' with the correct separator
        $path = str_replace(['/', '\\'], $separator, $path);

        // Split the path into segments and normalize
        $segments = explode($separator, $path);
        $normalizedSegments = [];

        // Loop through each segment and handle relative path components
        foreach ($segments as $segment) {
            // Ignore empty segments and current directory (.)
            if ($segment === '' || $segment === '.') {
                continue;
            }

            // If the segment is a parent directory (..), pop the last directory
            if ($segment === '..') {
                array_pop($normalizedSegments);
            } else {
                // Otherwise, add the segment to the normalized segments
                $normalizedSegments[] = $segment;
            }
        }

        // Join the normalized segments and replace the directory separator with a forward slash
        $newPath = implode($separator, $normalizedSegments);

        // Ensure the final path uses forward slashes (for consistency across systems)
        return str_replace($separator, "/", $newPath);
    }

    /**
     * Recursively copies all files and directories from the source to the destination.
     * If a directory is empty, it will still be created at the destination.
     *
     * @param string $src Source directory path.
     * @param string $dst Destination directory path.
     * @return void
     */
    public static function copyTree($src, $dst) {
        // Ensure the destination directory exists
        if (!file_exists($dst)) {
            mkdir($dst, 0777, true);
        }
        
        // Scan the contents of the source directory
        $files = scandir($src);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $srcPath = "$src/$file";
            $dstPath = "$dst/$file";
            
            if (is_dir($srcPath)) {
                // Recursively copy subdirectory
                self::copyTree($srcPath, $dstPath);
            } else {
                // Copy file to destination
                copy($srcPath, $dstPath);
            }
        }
    }
    

    /**
     * Scans the directory at the specified path with a given depth and returns the directory tree.
     * 
     * @param string $dir The directory path to scan.
     * @param string $baseDirectory The base directory that the path should be relative to.
     * @param int $depth The depth of recursion. Default is 0 (no recursion).
     * @return string A JSON-encoded string containing the directory tree with files and subdirectories.
     */
    public static function getDirTree($dir, $baseDirectory, $depth = 0) {
        // Scan the folder and retrieve files and subdirectories
        $files = scandir($dir);
        $result1 = [];  // Array to hold directories
        $result2 = [];  // Array to hold files

        // Loop through each file/directory in the scanned list
        foreach ($files as $file) {
            // Skip '.' and '..' (current and parent directory)
            if ($file != '.' && $file != '..') {
                $path = $dir . DIRECTORY_SEPARATOR . $file; // Get the full path of the file or directory
                $path = self::normalizationPath($path);  // Normalize the file path
                
                // Make the path relative to the base directory
                $relativePath = str_replace($baseDirectory . "/", "", $path);

                // If it's a directory
                if (is_dir($path)) {
                    $res = [
                        'type' => 'dir',  // Specify the type as 'dir' for directory
                        'name' => $file,  // Name of the directory
                        'path' => $relativePath   // Full path to the directory, relative to the base directory
                    ];

                    // If depth is greater than 0, recursively scan subdirectories
                    if ($depth > 0) {
                        $res['childrens'] = self::getDirTree($path, $baseDirectory, $depth - 1);
                    }
                    
                    $result1[] = $res;
                } 
                // If it's a file, get its extension and add it to the $result2 array
                else if (is_file($path)) {
                    $extension = pathinfo($file, PATHINFO_EXTENSION);  // Get the file extension
                    
                    $result2[] = [
                        'type' => 'file',        // Specify the type as 'file' for files
                        'name' => $file,         // Name of the file
                        'path' => $relativePath, // Full path to the file, relative to the base directory
                        'extension' => $extension // Add the file extension to the result
                    ];
                }
            }
        }

        // Merge directories and files arrays into one result array
        return array_merge($result1, $result2);
    }


    /**
     * Normalizes a given file path by converting backslashes to forward slashes and 
     * handling other common path inconsistencies such as multiple slashes and relative paths.
     * 
     * @param string $path The file path to normalize.
     * @return string The normalized file path.
     */
    public static function normalizationPath($path)
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
    public static function getCustomMimeType($file) {
        // Open the file in binary read mode
        $finfo = fopen($file, "rb");
        if (!$finfo) {
            return 'application/octet-stream';
        }
        
        // Read the first 512 bytes of the file
        $bin = fread($finfo, 512);
        fclose($finfo);

        $result = 'application/octet-stream';

        // Check magic bytes
        if (substr($bin, 0, 4) === "\xFF\xD8\xFF\xE0" || substr($bin, 0, 4) === "\xFF\xD8\xFF\xE1") {
            $result = 'image/jpeg';
        } elseif (substr($bin, 0, 4) === "\x89\x50\x4E\x47") {
            $result = 'image/png';
        } elseif (substr($bin, 0, 3) === "GIF") {
            $result = 'image/gif';
        } elseif (substr($bin, 0, 4) === "\x00\x00\x01\x00") {
            $result = 'image/x-icon'; // ICO format
        } elseif (substr($bin, 0, 4) === "\x52\x49\x46\x46") {
            // RIFF format, could be WEBP or BMP
            $subType = substr($bin, 8, 4);
            if ($subType === "WEBP") {
                $result = 'image/webp';
            } elseif ($subType === "BMP ") {
                $result = 'image/bmp';
            }
        } elseif (strpos($bin, '<svg') !== false || strpos($bin, '<?xml') !== false) {
            $result = 'image/svg+xml';
        }

        return $result;
    }



    
}
