<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class NoColumnUpdatedException
 *
 * Custom exception class for handling errors when no columns have been 
 * updated during a database operation. This exception can be thrown 
 * when an update query executes successfully but does not modify any 
 * records, indicating that the specified criteria did not match any 
 * existing entries. It is particularly useful in scenarios where 
 * data integrity and confirmation of changes are critical.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class NoColumnUpdatedException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for NoColumnUpdatedException.
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
