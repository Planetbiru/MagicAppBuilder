<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class NoRecordFoundException
 *
 * Custom exception class for handling scenarios where a database 
 * query does not return any records. This exception is useful in 
 * situations where a lookup operation fails to find the requested 
 * data, helping to differentiate between successful queries with 
 * no results and errors in the query itself. It can be particularly 
 * useful in data retrieval operations, ensuring that the calling 
 * code can handle the absence of records appropriately.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class NoRecordFoundException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for NoRecordFoundException.
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
