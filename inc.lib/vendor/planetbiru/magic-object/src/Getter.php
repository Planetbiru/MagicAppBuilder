<?php

namespace MagicObject;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoStringUtil;
use ReflectionClass;
use stdClass;

/**
 * Getter
 * @link https://github.com/Planetbiru/MagicObject
 */
class Getter extends stdClass
{
    const JSON = 'JSON';
    /**
     * Class parameter
     *
     * @var array
     */
    private $_classParams = array(); //NOSONAR

    /**
     * Constructor
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
     * Load data to object
     * @param stdClass|array $data Data
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
     * Get property value
     *
     * @param string $propertyName Property name
     * @return mixed|null
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : null;   
    }

    /**
     * Get value
     * @param boolean $snakeCase Flag to snake case property
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                $value->$key = $val;
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val) {
                $key2 = PicoStringUtil::snakeize($key);
                $value2->$key2 = $val;
            }
            return $value2;
        }
        return $value;
    }

    /**
     * Property list
     * @var boolean $reflectSelf Flag to reflect self
     * @var boolean $asArrayProps Flag to convert properties as array
     * @return array
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
     * Magic method called when user call any undefined method
     *
     * @param string $method Method
     * @param string $params Parameters
     * @return mixed|null
     */
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return isset($this->$var) ? $this->$var : null;
        }
        else if (strncasecmp($method, "equals", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            $value = isset($this->$var) ? $this->$var : null;
            return isset($params[0]) && $params[0] == $value;
        }
    }

    /**
     * Check if JSON naming strategy is snake case or not
     *
     * @return boolean
     */
    private function isSnake()
    {
        return isset($this->_classParams[self::JSON]) 
            && isset($this->_classParams[self::JSON]['property-naming-strategy']) 
            && strcasecmp($this->_classParams[self::JSON]['property-naming-strategy'], 'SNAKE_CASE') == 0
            ;
    }

    /**
     * Check if JSON naming strategy is snake case or not
     *
     * @return boolean
     */
    private function isPretty()
    {
        return isset($this->_classParams[self::JSON]) 
            && isset($this->_classParams[self::JSON]['prettify']) 
            && strcasecmp($this->_classParams[self::JSON]['prettify'], 'true') == 0
            ;
    }

    /**
     * Magic method to stringify object
     *
     * @return string
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