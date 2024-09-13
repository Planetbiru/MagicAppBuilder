<?php

namespace MagicObject;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoArrayUtil;
use MagicObject\Util\PicoStringUtil;
use ReflectionClass;
use stdClass;

/**
 * Setter getter
 * @link https://github.com/Planetbiru/MagicObject
 */
class SetterGetter extends stdClass
{
    const JSON = 'JSON';

    /**
     * Class parameter
     *
     * @var array
     */
    private $classParams = array();

    /**
     * Constructor
     *
     * @param self|array|stdClass|object $data Data
     */
    public function __construct($data = null)
    {
        $jsonAnnot = new PicoAnnotationParser(get_class($this));
        $params = $jsonAnnot->getParameters();
        foreach($params as $paramName=>$paramValue)
        {
            try
            {
                $vals = $jsonAnnot->parseKeyValue($paramValue);
                $this->classParams[$paramName] = $vals;
            }
            catch(InvalidQueryInputException $e)
            {
                throw new InvalidAnnotationException("Invalid annotation @".$paramName);
            }
        }
        if($data != null)
        {
            if(is_array($data))
            {
                $data = PicoArrayUtil::camelize($data);
            }
            $this->loadData($data);
        }
    }

    /**
     * Load data to object
     * @param mixed $data Data
     * @return self
     */
    public function loadData($data)
    {
        if($data != null)
        {
            if($data instanceof self)
            {
                $values = $data->value();
                foreach ($values as $key => $value) {
                    $key2 = PicoStringUtil::camelize($key);
                    $this->set($key2, $value);
                }
            }
            else if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = PicoStringUtil::camelize($key);
                    $this->set($key2, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Set property value
     *
     * @param string $propertyName Property name
     * @param mixed|null $propertyValue Property value
     * @return self
     */
    public function set($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        $this->$var = $propertyValue;
        return $this;
    }

    /**
     * Get property value
     *
     * @param string $propertyName Property name
     * @return mixed|null $propertyValue Property value
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : null;
    }

    /**
     * Stores datas in the property.
     * Example: $instance->foo = 'bar';
     *
     * @param string $name Property name
     * @param string $value Property value
     * @return void
     **/
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }


    /**
     * Gets datas from the property.
     * Example: echo $instance->foo;
     *
     * @param string $name Property name
     * @return mixed Data stored in property.
     **/
    public function __get($name)
    {
        if($this->__isset($name))
        {
            return $this->get($name);
        }
    }

    /**
     * Check if property has been set or not or has null value
     *
     * @param string $name Property name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    /**
     * Unset property value
     *
     * @param string $name Property name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->$name);
    }

    /**
     * Get value
     *
     * @var boolean $snakeCase Flag to snake case property
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val)
        {
            if(!in_array($key, $parentProps))
            {
                $value->$key = $val;
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val)
            {
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
            function($property) use($class)
            {
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );

        if($asArrayProps)
        {
            $result = array();
            foreach ($properties as $key)
            {
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
     * @param string $method Called method
     * @param string $params Parameters given
     * @return mixed|null
     */
    public function __call($method, $params) //NOSONAR
    {
        if (strncasecmp($method, "isset", 5) === 0)
        {
            $var = lcfirst(substr($method, 5));
            return isset($this->$var);
        }
        else if (strncasecmp($method, "is", 2) === 0)
        {
            $var = lcfirst(substr($method, 2));
            return isset($this->$var) ? $this->$var == 1 : false;
        }
        else if (strncasecmp($method, "get", 3) === 0)
        {
            $var = lcfirst(substr($method, 3));
            return isset($this->$var) ? $this->$var : null;
        }
        else if (strncasecmp($method, "set", 3) === 0)
        {
            $var = lcfirst(substr($method, 3));
            $this->$var = $params[0];
            return $this;
        }
        else if (strncasecmp($method, "unset", 5) === 0)
        {
            $var = lcfirst(substr($method, 5));
            $this->removeValue($var, $params[0]);
            return $this;
        }
    }

    /**
     * Check if JSON naming strategy is snake case or not
     *
     * @return boolean
     */
    private function isSnake()
    {
        return isset($this->classParams[self::JSON])
            && isset($this->classParams[self::JSON]['property-naming-strategy'])
            && strcasecmp($this->classParams[self::JSON]['property-naming-strategy'], 'SNAKE_CASE') == 0
            ;
    }

    /**
     * Check if JSON naming strategy is camel case or not
     *
     * @return boolean
     */
    protected function isCamel()
    {
        return !$this->isSnake();
    }

    /**
     * Check if JSON naming strategy is snake case or not
     *
     * @return boolean
     */
    private function isPretty()
    {
        return isset($this->classParams[self::JSON])
            && isset($this->classParams[self::JSON]['prettify'])
            && strcasecmp($this->classParams[self::JSON]['prettify'], 'true') == 0
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
