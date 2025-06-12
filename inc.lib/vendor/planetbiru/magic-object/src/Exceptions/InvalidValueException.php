<?php

namespace MagicObject\Exceptions;

use Exception;
use Throwable; // It's good practice to explicitly import Throwable for clarity

/**
 * Custom exception class thrown when a validation constraint is violated.
 * This exception carries information about the property that failed validation
 * and a specific message detailing the reason for the failure.
 */
class InvalidValueException extends Exception
{
    /**
     * The name of the property that caused the validation failure.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * The specific validation message associated with the failure.
     *
     * @var string
     */
    protected $message; // Redundant if parent::__construct already sets it, but kept as per original code

    /**
     * Constructor for the InvalidValueException.
     *
     * @param string $propertyName The name of the property for which validation failed.
     * @param string $message The validation error message. Defaults to an empty string.
     * @param int $code The exception code. Defaults to 0.
     * @param Throwable|null $previous The previous throwable used for the exception chaining. Defaults to null.
     */
    public function __construct($propertyName, $message = "", $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->propertyName = $propertyName;
        $this->message = $message; // Assigning to local property
    }

    /**
     * Get the name of the property that failed validation.
     *
     * @return string The name of the property.
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Get the specific validation error message.
     *
     * @return string The validation error message.
     */
    public function getValidationMessage()
    {
        return $this->message;
    }
}