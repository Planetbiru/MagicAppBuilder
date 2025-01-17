<?php

namespace MagicApp\AppDto\MocroServices;

use MagicObject\Util\PicoStringUtil;
use stdClass;

/**
 * Class ObjectToString
 *
 * This class provides functionality to convert an object into a JSON string representation. 
 * It overrides the `__toString()` method to return a JSON-encoded string of the object. 
 * The `toArray()` method is used to convert the object into an associative array, 
 * excluding any properties with a `null` value and ignoring the `__caseFormat` property. 
 * If any properties are objects themselves, they are recursively converted into arrays 
 * (by calling their `toArray()` method) before being included in the final JSON string.
 * Additionally, this class supports converting property names between camelCase and snake_case.
 * 
 * @package MagicApp\AppDto\MocroServices
 */
class ObjectToString
{
    /**
     * @var string Flag to determine the case format (camelCase or snake_case).
     */
    protected $__caseFormat = 'camelCase'; //NOSONAR

    /**
     * Sets the case format to camelCase.
     * This method allows switching the format for property names to camelCase.
     * @return self
     */
    public function toCamelCase()
    {
        $this->__caseFormat = 'camelCase';
        return $this;
    }

    /**
     * Sets the case format to snake_case.
     * This method allows switching the format for property names to snake_case.
     * @return self
     */
    public function toSnakeCase()
    {
        $this->__caseFormat = 'snakeCase';
        return $this;
    }
    
    /**
     * Sets the case format to specified format.
     * This method allows switching the format for property names to specified format.
     * @return self
     */
    public function switchCaseTo($caseFormat)
    {
        $this->__caseFormat = $caseFormat;
        return $this;
    }

    /**
     * Converts a string to camelCase.
     *
     * @param string $string The string to convert.
     * @return string The converted string in camelCase format.
     */
    private function toCamelCaseString($string)
    {
        return PicoStringUtil::camelize($string);
    }

    /**
     * Converts a string to snake_case.
     *
     * @param string $string The string to convert.
     * @return string The converted string in snake_case format.
     */
    private function toSnakeCaseString($string)
    {
        return PicoStringUtil::snakeize($string);
    }
    
    /**
     * Helper method to determine if a property should be skipped in the conversion.
     * This method skips the `__caseFormat` property and any property with a null value.
     *
     * @param string $key The property name.
     * @param mixed $value The property value.
     * @return bool True if the property should be skipped, false otherwise.
     */
    private function shouldBeSkipped($key, $value)
    {
        return $key === '__caseFormat' || $value === null;
    }

    /**
     * Converts the object to an associative array, ignoring properties with a null value.
     * This method iterates over all public and protected properties of the object, excluding private properties,
     * and checks each property to exclude those with a value of `null`. 
     * If the property is an array, it recursively converts any objects within the array to arrays using their `toArray()` method.
     * If the property is an object, the `toArray()` method of the object will be called if it exists.
     * 
     * @return array An associative array representation of the object, excluding null values and private properties.
     */
    public function toArray()
    {
        $data = [];

        // Get public and protected properties, excluding private properties
        $properties = get_object_vars($this);

        // Iterate through all properties to handle parent properties first
        foreach ($properties as $key => $value) {
            // Skip the __caseFormat property as well as properties with a null value
            if ($this->shouldBeSkipped($key, $value)) {
                continue;
            }

            // Check if the property belongs to the parent class
            $isParentProperty = property_exists(get_parent_class($this), $key);
            if ($isParentProperty) {
                // If the property is from the parent, process it separately
                $data = $this->appendData($data, $key, $value);
            }
        }

        // Now iterate through properties to handle own class properties
        foreach ($properties as $key => $value) {
            // Skip the __caseFormat property as well as properties with a null value
            if ($this->shouldBeSkipped($key, $value)) {
                continue;
            }

            // Check if the property belongs to the parent class
            $isParentProperty = property_exists(get_parent_class($this), $key);
            if (!$isParentProperty) {
                // If the property is not from the parent, process it for the current class
                $data = $this->appendData($data, $key, $value);
            }
        }

        return $data;
    }

    /**
     * Appends the property data to the associative array, handling array and object properties.
     * This method checks if the property value is an array or object and processes it accordingly.
     *
     * @param array $data The existing array to append data to.
     * @param string $key The property name.
     * @param mixed $value The property value.
     * @return array The updated array with the appended property data.
     */
    private function appendData($data, $key, $value)
    {
        // If the property is an array, process each item inside it
        if (is_array($value)) {
            // If an item in the array is an object, call toArray() on each object
            $data[$this->applyCaseFormat($key)] = array_map(function ($item) {
                if (is_object($item) && $item instanceof self) {
                    $item->switchCaseTo($this->__caseFormat);
                    return $this->toObject($item->toArray(), $item);
                } else {
                    return $item;
                }
            }, $value);
        }
        // If the property is an object, call toArray() on that object
        elseif (is_object($value)) {
            // If the object has a toArray method, call it
            $value->switchCaseTo($this->__caseFormat);
            $data[$this->applyCaseFormat($key)] = method_exists($value, 'toArray') ? $value->toArray() : $this->toObject($value, $value);
        } else {
            // If not an object or array, store the value directly
            $data[$this->applyCaseFormat($key)] = $value;
        }

        return $data;
    }
    
    /**
     * Converts an object to a standard PHP object if necessary, handling objects of type `self`.
     *
     * @param mixed $value The value to convert.
     * @param object $obj The object to check.
     * @return mixed The converted value.
     */
    public function toObject($value, $obj)
    {
        // If the object is of type 'self' and contains no data, return an empty stdClass object
        if (is_object($obj) && $obj instanceof self && (string)$obj == "[]") {
            return new stdClass;
        }
        // If the value is an instance of 'self', return an empty stdClass object
        if ($value instanceof self) {
            return new stdClass;
        }

        return $value;
    }

    /**
     * Apply the chosen case format (camelCase or snake_case) to the given property name.
     * This method applies the selected format (camelCase or snake_case) to the property name.
     *
     * @param string $key The property name.
     * @return string The property name in the selected case format.
     */
    private function applyCaseFormat($key)
    {
        if ($this->__caseFormat === 'snakeCase' || $this->__caseFormat === 'snake_case') {
            return $this->toSnakeCaseString($key);
        }
        else if($this->__caseFormat === 'camelCase') {
            return $this->toCamelCaseString($key);
        }

        return $key; // Default, if no case format set
    }

    /**
     * Overrides the __toString method to serialize the object into a JSON string.
     * 
     * This method calls the `toArray()` method to convert the object into an array, 
     * and then converts that array into a pretty-printed JSON string.
     * If the object contains nested objects, their `toArray()` method is called recursively 
     * to ensure they are properly serialized into the JSON string.
     *
     * @return string A JSON string representation of the object.
     */
    public function __toString()
    {
        // Call the toArray() method to convert the object into an array
        $data = $this->toArray();

        // Convert the object array into a JSON string with pretty print formatting
        return json_encode($data, JSON_PRETTY_PRINT);
    }
}
