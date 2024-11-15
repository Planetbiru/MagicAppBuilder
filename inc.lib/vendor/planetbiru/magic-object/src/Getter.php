<?php

namespace MagicObject;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoStringUtil;
use ReflectionClass;
use stdClass;

/**
 * Class Getter
 *
 * This class provides dynamic property access and management for instances of 
 * the MagicObject framework. It allows for loading data into an object from 
 * various formats and supports retrieving property values, including handling 
 * naming conventions and formatting for JSON output.
 *
 * Key Features:
 * - Dynamically load data from associative arrays or objects.
 * - Retrieve property values using both explicit getter methods and dynamic method calls.
 * - Convert object properties into a structured representation suitable for JSON encoding.
 * - Support for property naming strategies (snake case and pretty formatting) for JSON output.
 *
 * @author Kamshory
 * @package MagicObject
 * @link https://github.com/Planetbiru/MagicObject
 */
class Getter extends stdClass
{
    const JSON = 'JSON';
    
    /**
     * Class parameters that configure behavior such as JSON output formatting.
     *
     * @var array
     */
    private $_classParams = array(); //NOSONAR

    /**
     * Constructor that initializes class parameters based on annotations.
     */
    public function __construct()
    {
        $jsonAnnot = new PicoAnnotationParser(get_class($this));
        $params = $jsonAnnot->getParameters();
        foreach($params as $paramName=>$paramValue)
        {
            try
            {
                $vals = $jsonAnnot->parseKeyValue($paramValue);
            }
            catch(InvalidQueryInputException $e)
            {
                throw new InvalidAnnotationException("Invalid annotation @".$paramName);
            }
            $this->_classParams[$paramName] = $vals;
        }
    }
    
    /**
     * Load data into the object.
     *
     * @param stdClass|array $data Data to load, which can be an associative array or object.
     */
    public function loadData($data)
    {
        if (is_array($data) || is_object($data)) {
            foreach ($data as $key => $value) {
                $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                $this->{$key2} = $value;
            }
        }
    }

    /**
     * Get the value of a specified property.
     *
     * @param string $propertyName Name of the property to retrieve.
     * @return mixed|null The value of the property, or null if not set.
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->{$var}) ? $this->{$var} : null;   
    }

    /**
     * Retrieve all properties as a structured object or array.
     *
     * @param bool $snakeCase If true, convert property names to snake case.
     * @return stdClass|array The object containing property values, or an array if snakeCase is true.
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                $value->{$key} = $val;
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val) {
                $key2 = PicoStringUtil::snakeize($key);
                $value2->{$key2} = $val;
            }
            return $value2;
        }
        return $value;
    }

    /**
     * List properties of the current object.
     *
     * @param bool $reflectSelf If true, include properties declared in this class.
     * @param bool $asArrayProps If true, return properties as an array of strings.
     * @return array|ReflectionProperty[] List of properties or an array of property names.
     */
    protected function propertyList($reflectSelf = false, $asArrayProps = false)
    {
        $reflectionClass = $reflectSelf ? self::class : get_called_class();
        $class = new ReflectionClass($reflectionClass);

        // filter only the calling class properties
        $properties = array_filter(
            $class->getProperties(), 
            function($property) use($class) { 
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );

        if($asArrayProps)
        {
            $result = array();
            foreach ($properties as $key) {
                $prop = $key->name;
                $result[] = $prop;
            }
            return $result;
        }
        else
        {
            return $properties;
        }
    }

    /**
     * Magic method for handling calls to undefined methods.
     *
     * @param string $method Name of the called method.
     * @param array $params Parameters passed to the method.
     * @return mixed|null The result of the method call, if applicable.
     */
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return isset($this->{$var}) ? $this->{$var} : null;
        }
        else if (strncasecmp($method, "equals", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            $value = isset($this->{$var}) ? $this->{$var} : null;
            return isset($params[0]) && $params[0] == $value;
        }
    }

    /**
     * Determine if JSON naming strategy is snake case.
     *
     * @return bool True if snake case is used, false otherwise.
     */
    private function isSnake()
    {
        return isset($this->_classParams[self::JSON]) 
            && isset($this->_classParams[self::JSON]['property-naming-strategy']) 
            && strcasecmp($this->_classParams[self::JSON]['property-naming-strategy'], 'SNAKE_CASE') == 0
            ;
    }

    /**
     * Determine if JSON output should be prettified.
     *
     * @return bool True if prettification is enabled, false otherwise.
     */
    private function isPretty()
    {
        return isset($this->_classParams[self::JSON]) 
            && isset($this->_classParams[self::JSON]['prettify']) 
            && strcasecmp($this->_classParams[self::JSON]['prettify'], 'true') == 0
            ;
    }

    /**
     * Convert the object to a JSON string representation.
     *
     * @return string A JSON representation of the object, formatted based on the naming strategy.
     */
    public function __toString()
    {
        $obj = clone $this;
        $json_flag = 0;
        if($this->isPretty())
        {
            $json_flag |= JSON_PRETTY_PRINT;
        }
        return json_encode($obj->value($this->isSnake()), $json_flag);
    }
}