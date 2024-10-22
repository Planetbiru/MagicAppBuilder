<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidQueryInputException
 *
 * Custom exception class for handling errors related to invalid query inputs.
 * This exception can be thrown when a database query receives inputs that 
 * do not conform to expected formats or constraints, such as invalid data types, 
 * missing required parameters, or malformed query structures. It is useful 
 * in scenarios involving database interactions or data retrieval processes.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class InvalidQueryInputException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for InvalidQueryInputException.
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
