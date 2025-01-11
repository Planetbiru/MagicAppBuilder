<?php
namespace MagicApp\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidDownloadException
 *
 * Custom exception for handling errors during download operations.
 * This exception is thrown when there are issues such as network failures, 
 * timeouts, or invalid responses during a download process.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicApp
 */
class InvalidDownloadException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for InvalidDownloadException.
     *
     * @param string $message  Exception message
     * @param int $code        Exception code
     * @param Throwable|null $previous Previous exception
     */
    public function __construct($message, $code = 0, $previous = null)
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
