<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class NoPrimaryKeyDefinedException
 *
 * Custom exception class for handling errors when a database entity 
 * lacks a defined primary key. This exception can be thrown during 
 * operations that require a primary key for identifying records, 
 * such as updates or deletions. It is crucial for ensuring data integrity 
 * and consistency within database operations, especially in ORM frameworks 
 * where primary keys are essential for object mapping.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class NoPrimaryKeyDefinedException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for NoPrimaryKeyDefinedException.
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
