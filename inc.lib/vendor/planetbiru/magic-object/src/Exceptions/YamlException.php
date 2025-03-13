<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class YamlException
 *
 * Custom exception class for handling errors related to YAML parsing or processing.
 * This class extends the base Exception class to provide additional context for 
 * YAML-specific errors and maintains a reference to a previous exception if available.
 * 
 * It is useful for scenarios where detailed error handling and chaining of exceptions 
 * are required during YAML processing.
 * 
 * @author Kamshory
 * @package MagicObject\Exceptions
 * @link https://github.com/Planetbiru/MagicObject
 */
class YamlException extends Exception
{
    /**
     * Previous exception in the chain (if any).
     *
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for YamlException.
     *
     * @param string $message  The exception message describing the error.
     * @param int $code        The exception code (default is 0).
     * @param Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct($message, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->previous = $previous;
    }

    /**
     * Get the previous exception in the chain.
     *
     * @return Throwable|null The previous exception if available, or null.
     */
    public function getPreviousException()
    {
        return $this->previous;
    }
}
