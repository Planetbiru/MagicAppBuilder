<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class ErrorConnectionException
 *
 * Custom exception class for handling errors related to connection issues.
 * This exception is typically thrown when a connection attempt to a remote service,
 * database, or external resource fails. It can be used in scenarios where the system
 * expects a successful connection but encounters issues, such as timeouts, server 
 * unavailability, or invalid credentials.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class ErrorConnectionException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for ErrorConnectionException.
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
