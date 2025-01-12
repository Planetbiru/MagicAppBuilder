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
}
