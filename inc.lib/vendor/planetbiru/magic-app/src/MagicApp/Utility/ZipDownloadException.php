<?php

namespace MagicApp\Utility;

use Exception;
use Throwable;

/**
 * Class ZipDownloadException
 *
 * Custom exception class for handling errors related to cURL operations.
 * This exception is typically thrown when there is an issue with a cURL request,
 * such as network failure, timeouts, or invalid responses from the server.
 * 
 * The `ZipDownloadException` class allows you to capture and manage errors related to
 * cURL requests, providing detailed information about the error message, code,
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
     * Previous exception
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
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->previous = $previous;
    }

    /**
     * Get the previous exception.
     *
     * @return Throwable|null
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
