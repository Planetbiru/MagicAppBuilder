<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class NoInsertableColumnException
 *
 * Custom exception class for handling errors when there are no columns 
 * available for insertion during a database operation. This exception 
 * can be thrown when an attempt is made to insert data into a database 
 * table but the specified columns are either not defined or not 
 * allowed for insertion, possibly due to misconfiguration or 
 * constraints on the database schema. It is essential for ensuring 
 * that data integrity is maintained during insert operations.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class NoInsertableColumnException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for NoInsertableColumnException.
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
