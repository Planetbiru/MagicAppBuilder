<?php

namespace AppBuilder\Util\Error;

use AppBuilder\Util\FileDirUtil;
use Exception;
use MagicObject\Database\PicoDatabase;

/**
 * Class ErrorChecker
 *
 * A utility class for checking PHP syntax errors in files. This class provides methods to validate PHP scripts
 * by leveraging caching to avoid repetitive syntax checks. It checks the modification time of the file and compares
 * it with cached results to determine if a syntax check is necessary.
 *
 * @package AppBuilder\Util\Error
 */
class ErrorChecker
{
    /**
     * Check for syntax errors in the specified PHP file.
     *
     * This method checks if a cached error result exists for the given file. If a cached result is not found, it
     * executes a PHP linting command to check for syntax errors. The results are cached for future checks.
     *
     * @param PicoDatabase $databaseBuilder The database connection object for managing error cache.
     * @param string $path Path to the PHP file to be checked for syntax errors.
     * @param string $applicationId Application ID
     * @return PhpError Represents an error that occurs in PHP code, providing details such as the error code, line number, and associated error messages.
     */
    public static function errorCheck($databaseBuilder, $path, $applicationId)
    {
        $normalizedPath = FileDirUtil::normalizePath($path);
        $ft = filemtime($path);

        $phpError = new PhpError();

        try {
            // Check if cached error result exists
            $errorCache = self::getErrorCode($databaseBuilder, $normalizedPath, $ft);
            $phpError->restoreErrorFromCache($errorCache);

            // If the file has been modified since last check, run a new PHP syntax check
            if (strtotime($errorCache->getModificationTime()) < $ft) {
                $phpError = self::phpTest($path);
                ErrorChecker::saveCacheError($databaseBuilder, $normalizedPath, $ft, $phpError, $applicationId);
            }

        } catch (Exception $e) {
            // No record found in cache, perform syntax check
            $phpError = self::phpTest($path);

            try {
                ErrorChecker::saveCacheError($databaseBuilder, $normalizedPath, $ft, $phpError, $applicationId);
            } catch (Exception $e) {
                // Do nothing if cache saving fails
            }
        }

        return $phpError;
    }

    /**
     * Test a PHP file for syntax errors using the `php -l` command.
     *
     * This method executes the `php -l` command to check for syntax errors in the given PHP file. It parses
     * the output and returns a `PhpError` object containing the error code, line number, and error message.
     *
     * @param string $path Path to the PHP file to be checked.
     * @return PhpError Returns a PhpError object with the result of the syntax check.
     */
    public static function phpTest($path)
    {
        $phpError = new PhpError();
        exec("php -l $path 2>&1", $output, $returnVar);

        $errors = [];
        if (isset($output) && is_array($output)) {
            foreach ($output as $line) {
                $errors[] = $line . "\n";
            }
        }

        $lineNumber = -1;
        $lineNumberRaw = "";

        if ($returnVar !== 0) {
            $errorMessage = implode("\r\n", $errors);
            $lineNumber = 0;
            $p1 = stripos($errorMessage, 'PHP Parse error');
            if ($p1 !== false) {
                $p2 = stripos($errorMessage, ' on line ', $p1);
                if ($p2 !== false) {
                    $p2 += 9;
                    $lineNumberRaw = substr($errorMessage, $p2);
                    $p3 = strpos($errorMessage, "\r\n", $p2);
                    $lineNumber = intval(trim(substr($errorMessage, $p2, $p3 - $p2)));
                }
            }
        }

        $phpError->errorCode = $returnVar;
        $phpError->lineNumber = $lineNumber;
        $phpError->lineNumberRaw = $lineNumberRaw;
        $phpError->errors = $errors;

        return $phpError;
    }

    /**
     * Retrieve the error cache record for a given PHP file.
     *
     * This method checks the cache for previously stored syntax check results. If a record exists, it returns
     * the corresponding `PhpErrorCache` object.
     *
     * @param PicoDatabase $databaseBuilder The database connection object for managing error cache.
     * @param string $normalizedPath The normalized path of the PHP file.
     * @return PhpErrorCache Returns the `PhpErrorCache` object containing the cached error data.
     */
    public static function getErrorCode($databaseBuilder, $normalizedPath)
    {
        $errorCache = new PhpErrorCache(null, $databaseBuilder);
        $errorCacheId = md5($normalizedPath);
        $errorCache->findOneByErrorCacheId($errorCacheId);
        return $errorCache;
    }

    /**
     * Saves the error cache record for a given PHP file after a syntax check.
     *
     * This method stores the results of a PHP syntax check into the database. The cache includes
     * the PHP file's error code, modification time, an optional error message, and the line number
     * where the error occurred (if applicable).
     *
     * If no syntax errors are detected, the error message will be set to an empty string.
     *
     * @param PicoDatabase $databaseBuilder The database connection object used for managing the error cache.
     * @param string $normalizedPath The normalized path to the PHP file being checked.
     * @param int $ft The file modification time as a Unix timestamp.
     * @param string $applicationId Application ID
     * @param PhpError $phpError The `PhpError` object containing the result of the PHP syntax check.
     * 
     * @return void
     */
    public static function saveCacheError($databaseBuilder, $normalizedPath, $ft, $phpError, $applicationId)
    {
        $now = date("Y-m-d H:i:s");
        $errorMessage = implode("\r\n", $phpError->errors);
        if(stripos($errorMessage, 'No syntax errors detected') !== false)
        {
            $errorMessage = "";
        }
        $errorCache = new PhpErrorCache(null, $databaseBuilder);
        $errorCacheId = md5($normalizedPath);
        $errorCache->setErrorCacheId($errorCacheId);
        $errorCache->setApplicationId($applicationId);
        $errorCache->setFileName(basename($normalizedPath));
        $errorCache->setFilePath($normalizedPath);
        $errorCache->setErrorCode($phpError->errorCode);
        $errorCache->setModificationTime(date("Y-m-d H:i:s", $ft));
        $errorCache->setMessage($errorMessage);
        $errorCache->setLineNumber($phpError->lineNumber);
        $errorCache->setTimeCreate($now);
        $errorCache->save();
    }
}
