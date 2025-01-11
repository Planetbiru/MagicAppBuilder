<?php

namespace MagicApp\AppDto\ResponseDto;

use MagicObject\MagicObject;
use ReflectionClass;

/**
 * Represents a key-value pair with a name and associated value.
 * 
 * The `PrimaryKeyValueDto` class is used to store a name and its corresponding value.
 * This can be useful for handling pairs of data, such as in API responses, form submissions, 
 * or any context where a name and its associated value are required.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class PrimaryKeyValueDto extends ToString
{
    /**
     * The name associated with the value.
     * 
     * @var string
     */
    protected $name;
    
    /**
     * The value associated with the name.
     * 
     * @var mixed
     */
    protected $value;
    
    /**
     * Constructor to initialize the PrimaryKeyValueDto object.
     * 
     * @param string $name The name to be associated with the value.
     * @param mixed $value The value to be associated with the name.
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    
    /**
     * Retrieves and formats the properties of the current instance according to the specified naming strategy.
     *
     * This method extracts all properties of the current object, applies the given naming strategy
     * (such as `camelCase` or `snake_case`), and processes nested objects or arrays recursively. 
     * The result is returned as an instance of `StandardClass`, with private properties excluded from 
     * the output.
     *
     * If no naming strategy is provided, the strategy is derived from class annotations, 
     * if available, or defaults to the naming convention of the class.
     * 
     * The method also supports pretty-printing of the output if the `prettify` flag is set to `true`.
     * Nested objects and arrays will have their properties or keys recursively formatted with the 
     * specified naming strategy.
     *
     * Private properties from the current class are excluded from the formatted result, 
     * but public and protected properties will be included, even if they are inherited.
     *
     * @param string|null $namingStrategy The naming strategy to apply when formatting property names.
     *                                    If `null`, the strategy is determined based on class annotations.
     * @param bool $prettify Whether to pretty-print the resulting JSON output. Default is `false`.
     * @return StandardClass An object containing the formatted property values with the specified naming strategy.
     *                       Private properties from the current class are excluded from the result.
     */
    public function propertyValue($namingStrategy = null, $prettify = false)
    {
        // Determine the naming strategy from class annotations if not provided
        if ($namingStrategy === null) {
            $namingStrategy = $this->getPropertyNamingStrategy(get_class($this));
        }

        $properties = get_object_vars($this); // Get all properties of the instance
        $formattedProperties = new StandardClass;
        $formattedProperties->setPrettify($prettify);

        // Use ReflectionClass to inspect the current class and its properties
        $reflection = new ReflectionClass($this);
        $allProperties = $reflection->getProperties(); // Get all properties including private, protected, public

        foreach ($allProperties as $property) {
            $key = $property->getName();

            // Skip private properties of the current class
            if ($property->isPrivate() && $property->getDeclaringClass()->getName() === get_class($this)) {
                continue; // Skip this property if it's private in the current class
            }
            
            $value = $properties[$key]; // Get the value of the property

            $formattedKey = $this->convertPropertyName($key, $namingStrategy);
            if($formattedKey == $this->convertPropertyName(ConstantDto::NAME, $namingStrategy) && isset($value) && is_string($value))
            {
                $value = $this->convertPropertyName($value, $namingStrategy);
            }

            // Handle different types of property values
            if ($value === null) {
                // Explicitly handle null values
                $formattedProperties->{$formattedKey} = null;
            } elseif ($value instanceof ToString) {
                // Recursively retrieve property values from other ToString objects
                $formattedProperties->{$formattedKey} = $value->propertyValue($namingStrategy);
            } elseif ($value instanceof MagicObject) {
                // Retrieve value from MagicObject, applying the naming strategy (snake case or camel case)
                $formattedProperties->{$formattedKey} = $value->value($namingStrategy === self::SNAKE_CASE);
            } elseif (is_array($value)) {
                // Process arrays recursively, applying the naming strategy to array keys
                $formattedProperties->{$formattedKey} = $this->processArray($value, $namingStrategy);
            } elseif (is_object($value)) {
                // Leave other objects as is (without changing naming strategy) in the result
                $formattedProperties->{$formattedKey} = $value;
            } else {
                // Handle primitive values (string, int, float, etc.)
                $formattedProperties->{$formattedKey} = $value;
            }
        }

        return $formattedProperties;
    }
}
