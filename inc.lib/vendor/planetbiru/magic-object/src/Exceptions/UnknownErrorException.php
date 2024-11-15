<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class UnknownErrorException
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
class UnknownErrorException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for UnknownErrorException.
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
