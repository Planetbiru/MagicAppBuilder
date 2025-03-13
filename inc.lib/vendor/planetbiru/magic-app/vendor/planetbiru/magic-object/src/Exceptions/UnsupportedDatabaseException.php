<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class UnsupportedDatabaseException
 *
 * Custom exception class for handling null reference errors 
 * in the application. This exception is typically thrown when 
 * an operation is attempted on a variable that is null, 
 * indicating that the application is trying to access or modify 
 * an object or variable that has not been initialized. 
 * This exception helps in identifying issues related to null 
 * values, ensuring better debugging and error handling.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class UnsupportedDatabaseException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for UnsupportedDatabaseException.
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
     * @return Throwable|null The previous exception
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
