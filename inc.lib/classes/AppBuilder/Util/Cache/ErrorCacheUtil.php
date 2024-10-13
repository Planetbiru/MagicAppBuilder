<?php

namespace AppBuilder\Util\Cache;

/**
 * Class ErrorCacheUtil
 *
 * Utility class for managing error cache files. This class provides methods to save, 
 * retrieve, and delete cached error messages associated with specific file paths.
 */
class ErrorCacheUtil
{
    /**
     * Get the directory for caching errors.
     *
     * @return string The path to the cache directory.
     */
    public static function getCacheDir()
    {
        return dirname(__DIR__) . "/tmp/";
    }

    /**
     * Save an error message to the cache.
     *
     * @param string $path The base path for the error file.
     * @param string $ft   A suffix or identifier for the file type (e.g., "log", "txt").
     * @param string $err  The error message to cache.
     *
     * @return void
     */
    public static function saveCacheError($path, $ft, $err)
    {
        $dir = dirname($path);
        // Create the directory if it doesn't exist
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        // Delete any existing error files for this path
        self::deleteCacheError($path);
        // Save the new error message
        file_put_contents($path . "-" . $ft, $err);
    }

    /**
     * Retrieve a cached error message.
     *
     * @param string $path The base path for the error file.
     * @param string $ft   A suffix or identifier for the file type (e.g., "log", "txt").
     *
     * @return string|false The cached error message, or false if the file does not exist.
     */
    public static function getCacheError($path, $ft)
    {
        return file_get_contents($path . "-" . $ft);
    }

    /**
     * Delete cached error messages associated with a given path.
     *
     * @param string $path The base path for the error files.
     *
     * @return void
     */
    public static function deleteCacheError($path)
    {
        $files = glob($path . '-*');
        foreach ($files as $file) {
            if (is_file($file) && file_exists($file)) {
                @unlink($file);
            }
        }
    }
}
