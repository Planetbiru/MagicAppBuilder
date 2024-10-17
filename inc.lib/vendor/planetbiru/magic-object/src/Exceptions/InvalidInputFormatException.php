<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidInputFormatException
 *
 * Custom exception class for handling errors related to invalid input formats.
 * This exception can be thrown when input data fails validation, is improperly 
 * structured, or does not meet expected format criteria. It is useful in 
 * scenarios such as form submissions, data processing, or API requests 
 * where specific input formats are required.
 */
class InvalidInputFormatException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for InvalidInputFormatException.
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
