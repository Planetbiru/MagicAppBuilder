<?php

namespace AppBuilder\Exception;

use Exception;

/**
 * Class ConnectionException
 *
 * This exception is thrown when there is a connection-related error in the application.
 * It can be used to handle errors that occur during database connections, API requests,
 * or any other networking operations that require a stable connection.
 */
class ConnectionException extends Exception
{
    /**
     * Constructs a new ConnectionException.
     *
     * @param string $message The error message
     * @param int $code The error code (optional)
     * @param Exception|null $previous The previous exception used for exception chaining (optional)
     */
    public function __construct($message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
