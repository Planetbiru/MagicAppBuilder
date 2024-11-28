<?php

namespace MagicObject\Database;

use InvalidArgumentException;
use stdClass;

/**
 * Class PicoSqlJson
 * 
 * This class handles the encoding and validation of JSON data. 
 * It accepts an object, array, or a valid JSON string and ensures that it is properly 
 * encoded as a JSON string. If a string is provided, it checks if the string is a 
 * valid JSON format before encoding it. If the string is not valid JSON, an exception 
 * will be thrown.
 * 
 * @package MagicObject\Database
 */
class PicoSqlJson
{
    /**
     * The JSON encoded value
     *
     * @var string
     */
    private $value;

    /**
     * Constructor for PicoSqlJson class
     * 
     * Accepts an array, object, or a valid JSON string, and encodes it to JSON.
     * If a string is provided, it checks whether it's a valid JSON string. 
     * If valid, it is encoded to JSON; otherwise, an exception is thrown.
     *
     * @param mixed $value The value to encode. Can be an array, object, or JSON string.
     * 
     * @throws InvalidArgumentException If the string provided is not valid JSON.
     */
    public function __construct($value = null) {
        if (isset($value)) {
            // If $value is an object or array, encode it to JSON
            if ($value instanceof stdClass || is_array($value)) {
                $this->value = json_encode($value);
            } 
            // If $value is a string, check if it's a valid JSON string
            else if (is_string($value)) {
                // Try to decode the JSON string
                $decoded = json_decode($value);
                
                // Check if the decoding was successful (not null) and there were no JSON errors
                if (json_last_error() === JSON_ERROR_NONE) {
                    // If it's valid JSON, set $this->value
                    $this->value = json_encode($decoded);
                } else {
                    // If it's not valid JSON, handle the error (e.g., by throwing an exception)
                    throw new InvalidArgumentException("The provided string is not valid JSON.");
                }
            }
        }
    }
    
    /**
     * Converts the object to a string.
     *
     * This method returns the JSON-encoded value of the object when the object is
     * treated as a string (e.g., when echoing or concatenating the object).
     *
     * @return string The JSON string representation of the object.
     */
    public function __toString()
    {
        return $this->value;
    }
}
