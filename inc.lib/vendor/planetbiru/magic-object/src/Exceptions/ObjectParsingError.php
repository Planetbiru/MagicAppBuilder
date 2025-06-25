<?php

namespace MagicObject\Exceptions;

use Exception;
use Throwable; 

/**
 * Exception thrown when an object encounters an error during parsing or data conversion.
 *
 * This exception indicates that a process intended to parse or convert data into
 * an object (or from an object into another format) has failed. It can encapsulate
 * a previous exception that caused the parsing error, providing more context for
 * debugging and error handling.
 */
class ObjectParsingError extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for ObjectParsingError.
     *
     * @param string $message Exception message
     * @param int $code Exception code
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