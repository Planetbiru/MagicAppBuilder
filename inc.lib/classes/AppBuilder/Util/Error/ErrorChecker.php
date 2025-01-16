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
     * @return int Returns 0 if there are no syntax errors, 1 if there are errors, and 2 if the check could not be performed.
     */
    public static function errorCheck($databaseBuilder, $path)
    {
        $normalizedPath = FileDirUtil::normalizePath($path);
        $ft = filemtime($path);
        $return_var = 1;

        try {
            // Check if cached error result exists
            $errorCache = self::getErrorCode($databaseBuilder, $normalizedPath, $ft);
            $return_var = intval($errorCache->getErrorCode());

            // If the file has been modified since last check, run a new PHP syntax check
            if (strtotime($errorCache->getModificationTime()) < $ft) {
                $phpError = self::phpTest($path);
                $return_var = $phpError->errorCode;
                ErrorChecker::saveCacheError($databaseBuilder, $normalizedPath, $ft, $phpError);
            }

        } catch (Exception $e) {
            // No record found in cache, perform syntax check
            $phpError = self::phpTest($path);
            $return_var = $phpError->errorCode;

            try {
                ErrorChecker::saveCacheError($databaseBuilder, $normalizedPath, $ft, $phpError);
            } catch (Exception $e) {
                // Do nothing if cache saving fails
            }
        }

        return $return_var;
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
        exec("php -l $path 2>&1", $output, $return_var);

        $errors = [];
        if (isset($output) && is_array($output)) {
            foreach ($output as $line) {
                $errors[] = $line . "\n";
            }
        }

        $lineNumber = -1;
        $lineNumberRaw = "";

        if ($return_var !== 0) {
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

        $phpError->errorCode = $return_var;
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
     * Save the error cache record for a given PHP file.
     *
     * This method stores the result of a PHP syntax check into the database. The cache includes error code,
     * modification time, and an optional message and line number.
     *
     * @param PicoDatabase $databaseBuilder The database connection object for managing error cache.
     * @param string $normalizedPath The normalized path of the PHP file.
     * @param int $ft File modification time.
     * @param PhpError $phpError The `PhpError` object containing the syntax check result.
     * @return void
     */
    public static function saveCacheError($databaseBuilder, $normalizedPath, $ft, $phpError)
    {
        $now = date("Y-m-d H:i:s");
        $errorCache = new PhpErrorCache(null, $databaseBuilder);
        $errorCacheId = md5($normalizedPath);
        $errorCache->setErrorCacheId($errorCacheId);
        $errorCache->setFileName(basename($normalizedPath));
        $errorCache->setFilePath($normalizedPath);
        $errorCache->setErrorCode($phpError->errorCode);
        $errorCache->setModificationTime(date("Y-m-d H:i:s", $ft));
        $errorCache->setMessage(implode("\r\n", $phpError->errors));
        $errorCache->setLineNumber($phpError->lineNumber);
        $errorCache->setTimeCreate($now);
        $errorCache->save();
    }
}
