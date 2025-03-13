<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class NotNullColumnException
 *
 * Custom exception class for handling errors related to database 
 * operations where a column defined as NOT NULL is being assigned 
 * a null value. This exception can be thrown during insert or update 
 * operations, ensuring that the integrity of the database schema is 
 * maintained. It is essential for catching issues that may arise 
 * from improper data handling or validation before attempting to 
 * store records in the database.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class NotNullColumnException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for NotNullColumnException.
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
