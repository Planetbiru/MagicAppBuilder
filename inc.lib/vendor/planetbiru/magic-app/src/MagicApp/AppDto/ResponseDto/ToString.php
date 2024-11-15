<?php

namespace MagicApp\AppDto\ResponseDto;

use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use ReflectionClass;

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
 * **Supported Naming Strategies:**
 * - `SNAKE_CASE`: Converts property names to snake_case (e.g., `myProperty` becomes `my_property`).
 * - `KEBAB_CASE`: Converts property names to kebab-case (e.g., `myProperty` becomes `my-property`).
 * - `TITLE_CASE`: Converts property names to Title Case (e.g., `myProperty` becomes `My Property`).
 * - `CAMEL_CASE`: The default naming convention (e.g., `my_property` becomes `myProperty`).
 * - `PASCAL_CASE`: Converts property names to PascalCase (e.g., `myProperty` becomes `MyProperty`).
 * - `CONSTANT_CASE`: Converts property names to CONSTANT_CASE (e.g., `myProperty` becomes `MY_PROPERTY`).
 * - `FLAT_CASE`: Converts property names to lowercase without any delimiters (e.g., `myProperty` becomes `myproperty`).
 * - `DOT_NOTATION`: Converts property names to dot notation (e.g., `myProperty` becomes `my.property`).
 * - `TRAIN_CASE`: Converts property names to TRAIN-CASE (e.g., `myProperty` becomes `MY-PROPERTY`).
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ToString
{
    /**
     * Naming strategy that converts property names to snake_case (e.g., `myProperty` becomes `my_property`).
     *
     * @var string
     */
    const SNAKE_CASE = 'SNAKE_CASE';

    /**
     * Naming strategy that converts property names to kebab-case (e.g., `myProperty` becomes `my-property`).
     *
     * @var string
     */
    const KEBAB_CASE = 'KEBAB_CASE';

    /**
     * Naming strategy that converts property names to title case (e.g., `myProperty` becomes `My Property`).
     *
     * @var string
     */
    const TITLE_CASE = 'TITLE_CASE';

    /**
     * Naming strategy that converts property names to camelCase (e.g., `my_property` becomes `myProperty`).
     * This is the default case.
     *
     * @var string
     */
    const CAMEL_CASE = 'CAMEL_CASE';

    /**
     * Naming strategy that converts property names to PascalCase (e.g., `myProperty` becomes `MyProperty`).
     *
     * @var string
     */
    const PASCAL_CASE = 'PASCAL_CASE';

    /**
     * Naming strategy that converts property names to CONSTANT_CASE (e.g., `myProperty` becomes `MY_PROPERTY`).
     *
     * @var string
     */
    const CONSTANT_CASE = 'CONSTANT_CASE';

    /**
     * Naming strategy that converts property names to flat case (e.g., `myProperty` becomes `myproperty`).
     *
     * @var string
     */
    const FLAT_CASE = 'FLAT_CASE';

    /**
     * Naming strategy that converts property names to dot notation (e.g., `myProperty` becomes `my.property`).
     *
     * @var string
     */
    const DOT_NOTATION = 'DOT_NOTATION';

    /**
     * Naming strategy that converts property names to train-case (e.g., `myProperty` becomes `MY-PROPERTY`).
     *
     * @var string
     */
    const TRAIN_CASE = 'TRAIN_CASE';

    /**
     * Class annotation JSON
     * 
     * @var string
     */
    const JSON = 'JSON';

    /**
     * Value 'true'
     * 
     * @var string
     */
    const VALUE_TRUE = 'true';

    /**
     * A regular expression used to match camelCase or PascalCase property names
     * to insert appropriate delimiters (like underscores or hyphens).
     *
     * This regular expression is used for converting between different case styles
     * where uppercase letters are separated from lowercase letters with a delimiter.
     *
     * @var string
     */
    const REGEX_NAMING_STRATEGY = '/([a-z])([A-Z])/';
    
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

    /**
     * Process an array of values, applying the naming strategy to each key and value.
     *
     * @param array $value The array to process.
     * @param string $namingStrategy The naming strategy to apply.
     * @return array The processed array with formatted keys and values.
     */
    protected function processArray($value, $namingStrategy)
    {
        // Array to store the processed result
        $processedArray = [];
        
        // Loop through each element in the array
        foreach ($value as $k => $v) {
            // Format the key according to the given naming strategy
            $formattedKey = $this->convertPropertyName($k, $namingStrategy);            
            if ($v instanceof ToString) {
                // Process individual elements if the value is an instance of ToString
                // If the value is a ToString object, call propertyValue to retrieve the formatted value
                $processedArray[$formattedKey] = $v->propertyValue($namingStrategy);
            } elseif ($v instanceof MagicObject) {
                // Process the element if it is an instance of MagicObject
                // If the value is a MagicObject, call the value method to get the formatted value, 
                // applying the naming strategy (snake case or camel case)
                $processedArray[$formattedKey] = $v->value($namingStrategy === self::SNAKE_CASE);
            } else {
                // If the value is not a special object, store it directly
                $processedArray[$formattedKey] = $v;
            }
        }

        // Return the processed array
        return $processedArray;
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
    protected function convertPropertyName($name, $format) //NOSONAR
    {
        switch ($format) {
            case self::SNAKE_CASE:
                return strtolower(preg_replace(self::REGEX_NAMING_STRATEGY, '$1_$2', $name));
            case self::KEBAB_CASE:
                return strtolower(preg_replace(self::REGEX_NAMING_STRATEGY, '$1-$2', $name));
            case self::TITLE_CASE:
                return ucwords(str_replace(['_', '-'], ' ', $this->convertPropertyName($name, self::SNAKE_CASE)));
            case self::CAMEL_CASE:
                return $name;
            case self::PASCAL_CASE:
                return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $name)));
            case self::CONSTANT_CASE:
                return strtoupper(preg_replace(self::REGEX_NAMING_STRATEGY, '$1_$2', $name));
            case self::FLAT_CASE:
                return strtolower(preg_replace(self::REGEX_NAMING_STRATEGY, '$1$2', $name));
            case self::DOT_NOTATION:
                return strtolower(preg_replace(self::REGEX_NAMING_STRATEGY, '$1.$2', $name));
            case self::TRAIN_CASE:
                return strtoupper(preg_replace(self::REGEX_NAMING_STRATEGY, '$1-$2', $name));
            default:
                return $name;
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
        $attr = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::JSON));
        $this->propertyNamingStrategy = $attr->getPropertyNamingStrategy();
        $this->prettify = strtolower($attr->getPrettify()) === self::VALUE_TRUE;
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
    
    /**
     * Converts the instance to a JSON string representation based on class annotations.
     *
     * This method uses the `propertyValue()` method to format the properties of the object 
     * and returns a JSON string. If the `prettify` annotation is set to true, 
     * the output will be prettified (formatted with indentation).
     *
     * @return string A JSON string representation of the instance.
     */
    public function __toString()
    {
        $flag = $this->getPrettify(get_class($this)) ? JSON_PRETTY_PRINT : 0;
        return json_encode($this->propertyValue(), $flag);
    }
}
