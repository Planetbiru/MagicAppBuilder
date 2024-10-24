<?php

namespace MagicObject;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoObjectParser;
use MagicObject\Util\PicoGenericObject;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

/**
 * Class MagicDto
 *
 * Represents a dynamic data transfer object that allows the user to create and manipulate 
 * properties on-the-fly. It can handle various data sources including INI, YAML, JSON, and 
 * databases. Users can perform CRUD operations on database records and manipulate properties 
 * as needed.
 *
 * @author Kamshory
 * @package MagicObject
 * @link https://github.com/Planetbiru/MagicObject
 */
class MagicDto extends stdClass // NOSONAR
{
    // Format constants
    const JSON = 'JSON';
    const PRETTIFY = 'prettify';

    /**
     * Class parameters.
     *
     * @var array
     */
    private $_classParams = []; // NOSONAR

    /**
     * Data source.
     *
     * @var mixed
     */
    private $_dataSource = null;

    /**
     * Constructor.
     *
     * Initializes the object with provided data and database connection.
     *
     * @param self|array|stdClass|MagicObject|SetterGetter|SecretObject|PicoGenericObject|null $data Initial data to populate the object.
     */
    public function __construct($data = null)
    {
        $this->loadData($data);   
        $jsonAnnot = new PicoAnnotationParser(get_class($this));
        $params = $jsonAnnot->getParameters();
        foreach($params as $paramName=>$paramValue)
        {
            try
            {
                $vals = $jsonAnnot->parseKeyValue($paramValue);
                $this->_classParams[$paramName] = $vals;
            }
            catch(InvalidQueryInputException $e)
            {
                throw new InvalidAnnotationException("Invalid annotation @".$paramName);
            }
        }
    }
    
    /**
     * Loads data into the object.
     *
     * This method accepts various data types, including:
     * - An instance of the class itself
     * - An array
     * - A standard object (stdClass)
     * - Other specific object types such as MagicObject, SetterGetter, 
     *   SecretObject, and PicoGenericObject. 
     * 
     * The method processes the input data and stores it in the internal 
     * data source of the object, ensuring that only non-scalar values are 
     * handled.
     *
     * @param self|array|stdClass|MagicObject|SetterGetter|SecretObject|PicoGenericObject|null $data 
     *        The data to load, which can be one of the specified types 
     *        or null.
     * @return self Returns the current instance for method chaining.
     */
    public function loadData($data)
    {
        if (isset($data)) {
            // Check if data is not a scalar value
            if (is_object($data) || is_array($data)) {
                // Check if the data is one of the allowed object types
                if ($data instanceof self || $data instanceof MagicObject || 
                    $data instanceof SetterGetter || $data instanceof SecretObject || 
                    $data instanceof PicoGenericObject) {
                    // Directly assign the data source if it is an allowed object type
                    $this->_dataSource = $data;
                } else {
                    // Parse the object or array recursively
                    $this->_dataSource = PicoObjectParser::parseRecursiveObject($data);
                } 
            }
        }
        return $this;
    }
    
    /**
     * Get the object values
     *
     * @return stdClass An object containing the values of the properties
     */
    public function value()
    {
        $parentProps = $this->propertyList(true, true);
        $returnValue = new stdClass;

        foreach ($this as $key => $val) {
            if (!in_array($key, $parentProps)) {
                $doc = $this->getPropertyDocComment($key);
                $source = $this->extractSource($doc);
                $jsonProperty = $this->extractJsonProperty($doc);
                $var = $this->extractVar($doc);
                $propertyName = $jsonProperty ? $jsonProperty : $key;

                $objectTest = class_exists($var) ? new $var() : null;

                if ($this->isSelfInstance($var, $objectTest)) {
                    $returnValue->$propertyName = $this->handleSelfInstance($source, $var, $propertyName);
                } elseif ($this->isMagicObjectInstance($objectTest)) {
                    $returnValue->$propertyName = $this->handleMagicObject($source, $propertyName);
                } else {
                    $returnValue->$propertyName = $this->handleDefaultCase($source, $key, $propertyName);
                }
            }
        }
        return $returnValue;
    }

    /**
     * Retrieves the documentation comment for a specified property.
     *
     * @param string $key The name of the property.
     * @return string|null The documentation comment for the property, or null if not found.
     */
    private function getPropertyDocComment($key)
    {
        $propReflect = new ReflectionProperty($this, $key);
        return $propReflect->getDocComment();
    }

    /**
     * Extracts the source from the documentation comment.
     *
     * @param string $doc The documentation comment containing the source.
     * @return string|null The extracted source or null if not found.
     */
    private function extractSource($doc)
    {
        preg_match('/@Source\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    /**
     * Extracts the JSON property name from the documentation comment.
     *
     * @param string $doc The documentation comment containing the JSON property.
     * @return string|null The extracted JSON property name or null if not found.
     */
    private function extractJsonProperty($doc)
    {
        preg_match('/@JsonProperty\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    /**
     * Extracts the variable type from the documentation comment.
     *
     * @param string $doc The documentation comment containing the variable type.
     * @return string|null The extracted variable type or null if not found.
     */
    private function extractVar($doc)
    {
        preg_match('/@var\s+(\S+)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }
    
    /**
     * Extracts the label from the documentation comment.
     *
     * @param string $doc The documentation comment containing the label.
     * @return string|null The extracted label or null if not found.
     */
    private function extractLabel($doc)
    {
        preg_match('/@Label\("([^"]+)"\)/', $doc, $matches);
        return !empty($matches[1]) ? $matches[1] : null;
    }

    /**
     * Checks if the given variable is a self-instance.
     *
     * @param string $var The variable name.
     * @param mixed $objectTest The object to test against.
     * @return bool True if it's a self-instance, otherwise false.
     */
    private function isSelfInstance($var, $objectTest)
    {
        return strtolower($var) != 'stdclass' && $objectTest instanceof self;
    }

    /**
     * Handles the case where the property is a self-instance.
     *
     * @param string|null $source The source to extract the value from.
     * @param string $var The variable type.
     * @param string $propertyName The name of the property.
     * @return mixed The handled value for the self-instance.
     */
    private function handleSelfInstance($source, $var, $propertyName)
    {
        if (strpos($source, "->") === false) {
            $value = isset($source) ? $this->_dataSource->get($source) : $this->_dataSource->get($propertyName);
            $objectValid = new $var($value);
            return $objectValid->value();
        } else {
            return $this->getNestedValue($source);
        }
    }

    /**
     * Checks if the given object is an instance of MagicObject or its derivatives.
     *
     * @param mixed $objectTest The object to test.
     * @return bool True if it is a MagicObject instance, otherwise false.
     */
    private function isMagicObjectInstance($objectTest)
    {
        return $objectTest instanceof MagicObject || 
            $objectTest instanceof SetterGetter || 
            $objectTest instanceof SecretObject || 
            $objectTest instanceof PicoGenericObject;
    }

    /**
     * Handles the case where the property is an instance of MagicObject.
     *
     * @param string|null $source The source to extract the value from.
     * @param string $propertyName The name of the property.
     * @return mixed The handled value for the MagicObject instance.
     */
    private function handleMagicObject($source, $propertyName)
    {
        if (strpos($source, "->") === false) {
            $value = isset($source) ? $this->_dataSource->get($source) : $this->_dataSource->get($propertyName);
            return ($value instanceof MagicObject || $value instanceof SetterGetter || 
                    $value instanceof SecretObject || $value instanceof PicoGenericObject) 
                ? $value->value() 
                : json_decode(json_encode($value));
        } else {
            return $this->getNestedValue($source);
        }
    }

    /**
     * Handles the default case when retrieving property values.
     *
     * @param string|null $source The source to extract the value from.
     * @param string $key The key of the property.
     * @param string $propertyName The name of the property.
     * @return mixed The handled default value.
     */
    private function handleDefaultCase($source, $key, $propertyName)
    {
        if (strpos($source, "->") === false) {
            return isset($source) ? $this->_dataSource->get($source) : $this->_dataSource->get($key);
        } else {
            return $this->getNestedValue($source);
        }
    }

    /**
     * Retrieves nested values from the data source based on a specified source string.
     *
     * @param string $source The source string indicating the path to the value.
     * @return mixed The nested value retrieved from the data source.
     */
    private function getNestedValue($source)
    {
        $currentVal = null;
        $arr = explode("->", $source);
        $fullKey = $arr[0];
        $currentVal = $this->_dataSource->get($fullKey);
        for ($i = 1; $i < count($arr); $i++) {
            if (isset($currentVal) && $currentVal->get($arr[$i]) != null) {
                $currentVal = $currentVal->get($arr[$i]);
            } else {
                break;
            }
        }
        return $currentVal;
    }

    /**
     * Get the object value as a specified format
     *
     * @return stdClass An object representing the value of the instance
     */
    public function valueObject()
    {
        $obj = clone $this;
        foreach($obj as $key=>$value)
        {
            if($value instanceof self)
            {
                $value = $this->stringifyObject($value);
                $obj->{$key} = $value;
            }
        }
        return $obj->value();
    }

    /**
     * Get the object value as an associative array
     *
     * @return array An associative array representing the object values
     */
    public function valueArray()
    {
        $value = $this->value();
        return json_decode(json_encode($value), true);
    }

    /**
     * Get the object value as an associative array with the first letter of each key in upper camel case
     *
     * @return array An associative array with keys in upper camel case
     */
    public function valueArrayUpperCamel()
    {
        $obj = clone $this;
        $array = (array) $obj->value();
        $renameMap = array();
        $keys = array_keys($array);
        foreach($keys as $key)
        {
            $renameMap[$key] = ucfirst($key);
        }
        $array = array_combine(array_map(function($el) use ($renameMap) {
            return $renameMap[$el];
        }, array_keys($array)), array_values($array));
        return $array;
    }

    /**
     * Check if the JSON output should be prettified
     *
     * @return bool True if JSON output is set to be prettified; otherwise, false
     */
    protected function _pretty()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PRETTIFY])
            && strcasecmp($this->_classParams[self::JSON][self::PRETTIFY], 'true') == 0
            ;
    }

    /**
     * Get a list of properties
     *
     * @param bool $reflectSelf Flag indicating whether to reflect properties of the current class
     * @param bool $asArrayProps Flag indicating whether to return properties as an array
     * @return array An array of property names or ReflectionProperty objects
     */
    protected function propertyList($reflectSelf = false, $asArrayProps = false)
    {
        $reflectionClass = $reflectSelf ? self::class : get_called_class();
        $class = new ReflectionClass($reflectionClass);

        // filter only the calling class properties
        // skip parent properties
        $properties = array_filter(
            $class->getProperties(),
            function($property) use($class) {
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );
        if($asArrayProps)
        {
            $result = array();
            $index = 0;
            foreach ($properties as $key) {
                $prop = $key->name;
                $result[$index] = $prop;

                $index++;
            }
            return $result;
        }
        else
        {
            return $properties;
        }
    }

    /**
     * Recursively stringify an object or array of objects.
     *
     * @param self $value The object to stringify.
     * @return mixed The stringified object or array.
     */
    private function stringifyObject($value)
    {
        if(is_array($value))
        {
            foreach($value as $key2=>$val2)
            {
                if($val2 instanceof self)
                {
                    $value[$key2] = $val2->stringifyObject($val2);
                }
            }
        }
        else if(is_object($value))
        {
            foreach($value as $key2=>$val2)
            {
                if($val2 instanceof self)
                {

                    $value->{$key2} = $val2->stringifyObject($val2);
                }
            }
        }
        return $value->value();
    }

    /**
     * Magic method to convert the object to a JSON string representation.
     *
     * This method recursively converts the object's properties into a JSON format. 
     * If any property is an instance of the same class, it will be stringified 
     * as well. The output can be formatted for readability based on the 
     * `_pretty()` method's return value.
     *
     * @return string A JSON representation of the object, possibly pretty-printed.
     */
    public function __toString()
    {
        $pretty = $this->_pretty();
        $flag = $pretty ? JSON_PRETTY_PRINT : 0;
        $obj = clone $this;
        foreach($obj as $key=>$value)
        {
            if($value instanceof self)
            {
                $value = $this->stringifyObject($value);
                $obj->{$key} = $value;
            }
        }
        return json_encode($obj->value(), $flag);
    }

    /**
     * Convert the object to a string.
     *
     * This method returns the string representation of the object by calling 
     * the magic `__toString()` method. It's useful for obtaining the 
     * JSON representation directly as a string.
     *
     * @return string The string representation of the object.
     */
    public function toString()
    {
        return (string) $this;
    }

    /**
     * Convert the object to a JSON object.
     *
     * This method decodes the JSON string representation of the object 
     * (produced by the `__toString()` method) and returns it as a PHP 
     * object. This is useful for working with the data in a more 
     * structured format rather than as a JSON string.
     *
     * @return object|null A PHP object representation of the JSON data, or null if decoding fails.
     */
    public function toJson()
    {
        return json_decode((string) $this);
    }

    /**
     * Convert the object to an associative array.
     *
     * This method decodes the JSON string representation of the object 
     * (produced by the `__toString()` method) and returns it as an 
     * associative array. This is useful for accessing the object's 
     * properties in a more straightforward array format.
     *
     * @return array|null An associative array representation of the JSON data, or null if decoding fails.
     */
    public function toArray()
    {
        return json_decode((string) $this, true);
    }
}
