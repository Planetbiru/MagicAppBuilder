<?php

namespace MagicApp\AppDto\ResponseDto;

use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use ReflectionClass;
use stdClass;

/**
 * Base class that provides a `__toString` method for derived classes.
 * 
 * This class allows converting objects into a string representation (typically JSON), 
 * with customizable property naming strategies (e.g., snake_case, camelCase).
 * 
 * It is designed to be extended by other Data Transfer Object (DTO) classes 
 * to provide consistent string output across the application.
 * 
 * **Features:**
 * - Retrieves properties of the current instance, applying specified naming strategies (e.g., snake_case, camelCase).
 * - Correctly formats nested objects and arrays according to the naming conventions.
 * - Uses reflection to read class annotations for dynamic property naming strategy.
 * - Implements the `__toString` method to output a JSON representation of the object.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ToString
{
    /**
     * Check if $propertyNamingStrategy and $prettify are set
     *
     * @var bool
     */
    private $propertySet;
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $propertyNamingStrategy;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    private $prettify;

    /**
     * Retrieves the properties of the current instance formatted according to the specified naming strategy.
     *
     * This method retrieves all properties of the current instance and applies the appropriate naming strategy 
     * to properties that are objects or arrays. The formatted properties are returned as an `stdClass` object.
     *
     * If no naming strategy is provided, the strategy will be determined from class annotations.
     *
     * @param string|null $namingStrategy The naming strategy to use for formatting property names.
     *                                     If null, the strategy will be determined from class annotations.
     * @return stdClass An object containing the formatted property values, excluding private properties from the current class.
     */
    public function getPropertyValue($namingStrategy = null)
    {
        $properties = get_object_vars($this); // Get all properties of the instance
        $formattedProperties = new stdClass;

        // Determine the naming strategy from class annotations if not provided
        if ($namingStrategy === null) {
            $namingStrategy = $this->getPropertyNamingStrategy(get_class($this));
        }

        // Use ReflectionClass to inspect the current class and its properties
        $reflection = new ReflectionClass($this);
        $allProperties = $reflection->getProperties(); // Get all properties including private, protected, public

        foreach ($allProperties as $property) {
            $key = $property->getName();
            $value = $properties[$key]; // Get the value of the property
            
            // Skip private properties of the current class
            if ($property->isPrivate() && $property->getDeclaringClass()->getName() === get_class($this)) {
                continue; // Skip this property if it's private in the current class
            }

            // Apply the naming strategy only for object or array properties
            if ($value instanceof ToString) {
                $formattedProperties->{$this->convertPropertyName($key, $namingStrategy)} = $value->getPropertyValue($namingStrategy);
            } elseif ($value instanceof MagicObject) {
                $formattedProperties->{$this->convertPropertyName($key, $namingStrategy)} = $value->value($namingStrategy === 'SNAKE_CASE');
            } elseif (is_array($value)) {
                $formattedProperties->{$this->convertPropertyName($key, $namingStrategy)} = [];
                foreach ($value as $k => $v) {
                    if ($v instanceof ToString) {
                        $formattedProperties->{$this->convertPropertyName($key, $namingStrategy)}[$this->convertPropertyName($k, $namingStrategy)] = $v->getPropertyValue($namingStrategy);
                    } elseif ($v instanceof MagicObject) {
                        $formattedProperties->{$this->convertPropertyName($key, $namingStrategy)}[$this->convertPropertyName($k, $namingStrategy)] = $v->value($namingStrategy === 'CAMEL_CASE');
                    } else {
                        $formattedProperties->{$this->convertPropertyName($key, $namingStrategy)}[$this->convertPropertyName($k, $namingStrategy)] = $v;
                    }
                }
            } elseif (is_object($value)) {
                $formattedProperties->{$this->convertPropertyName($key, $namingStrategy)} = $value;
            } else {
                $formattedProperties->{$this->convertPropertyName($key, $namingStrategy)} = $value;
            }
        }

        return $formattedProperties;
    }

    /**
     * Converts the instance to a JSON string representation based on class annotations.
     *
     * This method uses the `getPropertyValue()` method to format the properties of the object 
     * and returns a JSON string. If the `prettify` annotation is set to true, 
     * the output will be prettified (formatted with indentation).
     *
     * @return string A JSON string representation of the instance.
     */
    public function __toString()
    {
        $flag = $this->getPrettify(get_class($this)) ? JSON_PRETTY_PRINT : 0;
        return json_encode($this->getPropertyValue(), $flag);
    }

    /**
     * Converts the property name to the desired format based on the specified naming convention.
     *
     * The supported naming conventions are:
     * - SNAKE_CASE
     * - KEBAB_CASE
     * - TITLE_CASE
     * - CAMEL_CASE (default)
     * - PASCAL_CASE
     * - CONSTANT_CASE
     * - FLAT_CASE
     * - DOT_NOTATION
     * - TRAIN_CASE
     *
     * @param string $name The original property name.
     * @param string $format The desired naming format.
     * @return string The converted property name.
     */
    private function convertPropertyName($name, $format)
    {
        switch ($format) {
            case 'SNAKE_CASE':
                return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name));
            case 'KEBAB_CASE':
                return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name));
            case 'TITLE_CASE':
                return ucwords(str_replace(['_', '-'], ' ', $name));
            case 'CAMEL_CASE':
                return $name; // Default to camelCase
            case 'PASCAL_CASE':
                return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name))); // UpperCamelCase
            case 'CONSTANT_CASE':
                return strtoupper(preg_replace('/([a-z])([A-Z])/', '$1_$2', $name)); // ALL_UPPER_CASE
            case 'FLAT_CASE':
                return strtolower(preg_replace('/([a-z])([A-Z])/', '$1$2', $name)); // alllowercase
            case 'DOT_NOTATION':
                return strtolower(preg_replace('/([a-z])([A-Z])/', '$1.$2', $name)); // this.is.dot.notation
            case 'TRAIN_CASE':
                return strtoupper(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name)); // THIS-IS-TRAIN-CASE
            default:
                return $name; // Fallback to original name
        }
    }

    /**
     * Parses the annotations in the class doc comment to retrieve the `property-naming-strategy` 
     * and `prettify` values and sets them to the current instance.
     *
     * This method uses the `PicoAnnotationParser` to extract and parse the `@JSON` annotation 
     * from the class doc comment. It retrieves the `property-naming-strategy` and `prettify` 
     * values and stores them in the instance for later use.
     *
     * @param string $className The fully qualified name of the class to inspect.
     * @return self Returns the current instance for method chaining.
     */
    private function parseAnnotation($className)
    {
        $reflexClass = new PicoAnnotationParser($className);
        $attr = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter("JSON"));
        $this->propertyNamingStrategy = $attr->getPropertyNamingStrategy();
        $this->prettify = strtolower($attr->getPrettify()) === 'true';
        $this->propertySet = true;
        return $this;
    }

    /**
     * Retrieves the `property-naming-strategy` value from the class annotations.
     *
     * This method checks if the `property-naming-strategy` annotation has been parsed already.
     * If not, it calls `parseAnnotation()` to parse and set the required values. Once set, 
     * it returns the `property-naming-strategy` value, which determines how property names 
     * should be formatted (e.g., camelCase, snake_case).
     *
     * @param string $className The fully qualified name of the class to inspect.
     * @return string|null The value of the `property-naming-strategy` annotation or null if not found.
     */
    public function getPropertyNamingStrategy($className)
    {
        if (!isset($this->propertySet)) {
            $this->parseAnnotation($className);
        }

        return $this->propertyNamingStrategy; // Returns the value of the property-naming-strategy
    }

    /**
     * Retrieves the `prettify` value from the class annotations.
     *
     * This method checks if the `prettify` annotation has been parsed already.
     * If not, it calls `parseAnnotation()` to parse and set the required values. Once set, 
     * it returns the `prettify` value, which determines if the output should be formatted
     * as pretty (i.e., indented) JSON.
     *
     * @param string $className The fully qualified name of the class to inspect.
     * @return bool Returns true if the `prettify` annotation is set to "true", false otherwise.
     */
    public function getPrettify($className)
    {
        if (!isset($this->propertySet)) {
            $this->parseAnnotation($className);
        }
        return $this->prettify;
    }
}
