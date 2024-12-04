<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class NoDatabaseConnectionException
 *
 * Custom exception class for handling errors related to the absence of 
 * a database connection. This exception can be thrown when an application 
 * attempts to execute a database operation but fails to establish a 
 * connection to the database, possibly due to misconfiguration, 
 * network issues, or the database server being down. It is essential 
 * for managing connection-related errors in database-driven applications.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class NoDatabaseConnectionException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for NoDatabaseConnectionException.
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
