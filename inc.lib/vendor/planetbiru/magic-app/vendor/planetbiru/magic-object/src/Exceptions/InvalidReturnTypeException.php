<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidReturnTypeException
 *
 * Exception thrown when an invalid return type is encountered.
 *
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class InvalidReturnTypeException extends Exception
{
    /**
     * Previous exception
     *
     * @var Throwable|null
     */
    private $previous;

    /**
     * Constructor for InvalidReturnTypeException.
     *
     * @param string $message  Exception message
     * @param int $code        Exception code (default: 0)
     * @param Throwable|null $previous Previous exception (optional)
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->previous = $previous;
    }

    /**
     * Get the previous exception.
     *
     * Returns the previous exception if it exists, or null if there is no previous exception.
     *
     * @return Throwable|null The previous exception
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
