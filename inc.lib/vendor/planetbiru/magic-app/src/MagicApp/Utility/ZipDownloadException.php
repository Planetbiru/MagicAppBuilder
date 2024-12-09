<?php

namespace MagicApp\Utility;

use Exception;
use Throwable;

/**
 * Class ZipDownloadException
 *
 * Custom exception class for handling errors related to ZIP download operations.
 * This exception is thrown when there is an issue with ZIP file creation or downloading.
 * 
 * The `ZipDownloadException` class allows you to capture and manage errors related to
 * ZIP file handling, providing detailed information about the error message, code,
 * and the previous exception if any. It extends the built-in `Exception` class,
 * and can be caught and handled just like any other exception in PHP.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class ZipDownloadException extends Exception
{
    /**
     * Previos exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for ZipDownloadException.
     *
     * @param string $message  Exception message
     * @param int $code        Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        // Manually passing the previous exception to the parent constructor
        parent::__construct($message, $code);

        // Manually setting the previous exception if one was provided
        if ($previous instanceof Exception) {
            $this->previous = $previous;
        } else {
            $this->previous = null;
        }
    }

    /**
     * Get the previous exception.
     *
     * @return Exception|null
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
