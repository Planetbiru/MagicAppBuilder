<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class NoColumnMatchException
 *
 * Custom exception class for handling errors when no columns match 
 * during database operations. This exception can be thrown when 
 * a query fails to find any columns that satisfy the specified criteria, 
 * indicating that the expected data structure does not align with the 
 * available columns in the database. It is particularly useful in 
 * data mapping or ORM frameworks where column mappings are critical.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class NoColumnMatchException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for NoColumnMatchException.
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
