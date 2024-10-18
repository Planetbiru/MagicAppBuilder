<?php

namespace AppBuilder\Util\Error;

use AppBuilder\Util\Cache\ErrorCacheUtil;

/**
 * Class ErrorChecker
 *
 * A utility class for checking PHP syntax errors in files. This class provides
 * methods to validate PHP scripts by leveraging caching to avoid repetitive syntax checks.
 * It checks the modification time of the file and compares it with cached results
 * to determine if a syntax check is necessary.
 */
class ErrorChecker
{
    /**
     * Check for syntax errors in the specified PHP file.
     *
     * This method checks if a cached error result exists for the given file.
     * If a cached result is not found, it executes a PHP linting command
     * to check for syntax errors. The results are cached for future checks.
     *
     * @param string $cacheDir Directory where cached error results are stored.
     * @param string $path Path to the PHP file to be checked for syntax errors.
     * @return int Returns 0 if there are no syntax errors, 1 if there are errors,
     *             and 2 if the check could not be performed.
     */
    public static function errorCheck($cacheDir, $path)
    {

        // begin check
        $ft = filemtime($path);
        
        $cachePath = $cacheDir.preg_replace("/[^a-zA-Z0-9]/", "", $path);
        $return_var = 1;
        if(file_exists($cachePath."-".$ft))
        {
            $err = ErrorCacheUtil::getCacheError($cachePath, $ft);
            if($err != 'true')
            {
                $return_var = 0;
            }
        }
        else
        {
            exec("php -l $path 2>&1", $output, $return_var);
            ErrorCacheUtil::saveCacheError($cachePath, $ft, $return_var === 0 ? 'false': 'true');
        }
        // end check
        return $return_var;
    }
}