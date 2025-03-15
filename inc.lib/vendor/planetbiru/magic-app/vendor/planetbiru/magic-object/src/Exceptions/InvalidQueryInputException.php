<?php
namespace MagicObject\Exceptions;

use Exception;
use Throwable;

/**
 * Class InvalidQueryInputException
 * 
 * Thrown when there is an error with the format of annotation attributes applied to a class, property, or method.
 * 
 * This exception is triggered when annotations provided in the code do not meet the expected format or structure.
 * Common scenarios for throwing this exception include:
 * - Malformed annotation values.
 * - Missing required parameters in annotations.
 * - Incorrect application of annotations to classes, properties, or methods that do not support them.
 * 
 * Example scenarios where this exception may be used:
 * - A class is annotated with an unsupported attribute or a malformed annotation.
 * - A property is annotated with an incorrect attribute that does not conform to the expected format.
 * - A method receives an unsupported or malformed annotation, resulting in a failure during runtime or reflection processing.
 * 
 * This exception is useful for catching annotation-related errors early in the code execution, allowing developers to quickly address any misconfigurations.
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
     * @var Throwable|null The previous exception
     */
    private $previous;

    /**
     * Constructor for InvalidQueryInputException.
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
