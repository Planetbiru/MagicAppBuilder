<?php

namespace MagicObject\Util\File;

/**
 * Class FileUtil
 *
 * A utility class for handling file operations such as retrieving files
 * from a directory and fixing file paths. This class cannot be instantiated
 * due to its private constructor.
 * 
 * @author Kamshory
 * @package MagicObject\Util\File
 * @link https://github.com/Planetbiru/MagicObject
 */
class FileUtil
{
    private function __construct()
    {
        // Prevent object construction from outside the class
    }
    
    /**
     * Retrieves all files from a given directory, optionally at specified levels
     * of depth. Returns the file paths relative to the original directory if
     * a level greater than 0 is specified.
     *
     * @param string $directory The directory to scan for files.
     * @param int $level The depth level for recursive scanning (default is 0).
     * @param string|null $origin The origin directory for relative path calculation.
     * @return array An array of file paths found within the specified directory.
     */
    public static function getFiles($directory, $level = 0, $origin = null)
    {
        if ($level == 0) {
            $origin = $directory;
        }
        $files = array_diff(scandir($directory), array('.', '..'));
        $allFiles = array();

        foreach ($files as $file) {
            $fullPath = $directory . DIRECTORY_SEPARATOR . $file;
            $fullPath = self::fixFilePath($fullPath);
            $file2add = $file;
            if ($level > 0) {
                $file2add = substr($fullPath, strlen($origin) + 1);
            }
            is_dir($fullPath) ? array_push($allFiles, ...self::getFiles($fullPath, $level + 1, $origin)) : array_push($allFiles, $file2add);
        }
        return $allFiles;
    }

    /**
     * Normalizes the file path by replacing any backslashes or forward slashes
     * with the correct directory separator for the current platform.
     *
     * @param string $filePath The file path to be normalized.
     * @return string The normalized file path.
     */
    public static function fixFilePath($filePath)
    {
        return str_replace(array("\\", "/"), DIRECTORY_SEPARATOR, $filePath);
    }
}
