<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class MandatoryTableNameException
 *
 * Custom exception class for handling errors related to missing mandatory 
 * table names in database operations. This exception can be thrown when 
 * an operation requires a table name but none is provided, leading to 
 * failures in query execution or data manipulation. It is particularly 
 * useful in database abstraction layers or ORM implementations.
 */
class MandatoryTableNameException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for MandatoryTableNameException.
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
