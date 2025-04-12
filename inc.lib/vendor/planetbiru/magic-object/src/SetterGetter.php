<?php

namespace MagicObject;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoArrayUtil;
use MagicObject\Util\PicoStringUtil;
use ReflectionClass;
use stdClass;

/**
 * Class SetterGetter
 *
 * A dynamic object that provides getter and setter methods for properties, 
 * allowing flexible management of property values and array-like behavior. 
 * Supports annotations for property configuration and JSON serialization.
 *
 * @author Kamshory
 * @package MagicObject
 * @link https://github.com/Planetbiru/MagicObject
 */
class SetterGetter extends stdClass
{
    const JSON = 'JSON';

    /**
     * Class parameter storage.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var array
     */
    private $_classParams = array(); // NOSONAR


    /**
     * Constructor.
     *
     * Initializes the object with data and parses class annotations for 
     * property configuration.
     *
     * @param self|array|stdClass|object|null $data Initial data for the object. 
     * Can be an associative array, another SetterGetter instance, or a stdClass.
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
                $this->_classParams[$paramName] = $vals;
            }
            catch(InvalidAnnotationException $e)
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
     * Load data into the object.
     *
     * Maps the given data to the object's properties, automatically 
     * camelizing keys.
     *
     * @param mixed $data Data to load, which can be another SetterGetter instance, 
     * an array, or an object.
     * @return self Returns the current instance for method chaining.
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
     * Set a property value.
     *
     * @param string $propertyName Name of the property to set.
     * @param mixed|null $propertyValue Value to assign to the property.
     * @return self Returns the current instance for method chaining.
     */
    public function set($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        $this->{$var} = $propertyValue;
        return $this;
    }

    /**
     * Add an element to an array property.
     *
     * Initializes the property as an array if it is not already set.
     *
     * @param string $propertyName Name of the property to push to.
     * @param mixed $propertyValue Value to add to the property array.
     * @return self Returns the current instance for method chaining.
     */
    public function push($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->{$var}))
        {
            $this->{$var} = array();
        }
        array_push($this->{$var}, $propertyValue);
        return $this;
    }

    /**
     * Remove the last element from an array property.
     *
     * @param string $propertyName Name of the property to pop from.
     * @return mixed|null Returns the removed value or null if the property is not an array.
     */
    public function pop($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->{$var}) && is_array($this->{$var}))
        {
            return array_pop($this->{$var});
        }
        return null;
    }

    /**
     * Get a property value.
     *
     * @param string $propertyName Name of the property to retrieve.
     * @return mixed|null Returns the property value or null if not set.
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->{$var}) ? $this->{$var} : null;
    }

    /**
     * Magic setter method.
     *
     * Enables setting properties using object syntax, e.g., `$instance->foo = 'bar';`.
     *
     * @param string $name Name of the property to set.
     * @param mixed $value Value to assign to the property.
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }


    /**
     * Magic getter method.
     *
     * Enables retrieving properties using object syntax, e.g., `echo $instance->foo;`.
     *
     * @param string $name Name of the property to retrieve.
     * @return mixed|null Returns the value of the property or null if not set.
     */
    public function __get($name)
    {
        if($this->__isset($name))
        {
            return $this->get($name);
        }
    }

    /**
     * Check if a property is set.
     *
     * @param string $name Name of the property to check.
     * @return bool Returns true if the property is set, false otherwise.
     */
    public function __isset($name)
    {
        return isset($this->{$name}) ? $this->{$name} : null;
    }

    /**
     * Get values of the properties.
     *
     * Optionally converts property names to snake_case for the returned object.
     *
     * @param bool $snakeCase Flag to determine if property names should be converted to snake_case.
     * @return stdClass Returns an object with property values.
     */
    public function __unset($name)
    {
        unset($this->{$name});
    }

    /**
     * Get values of the properties.
     *
     * Optionally converts property names to snake_case for the returned object.
     *
     * @param bool $snakeCase Flag to determine if property names should be converted to snake_case.
     * @return stdClass Returns an object with property values.
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val)
        {
            if(!in_array($key, $parentProps))
            {
                $value->{$key} = $val;
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val)
            {
                $key2 = PicoStringUtil::snakeize($key);
                $value2->{$key2} = $val;
            }
            return $value2;
        }
        return $value;
    }

    /**
     * Get a list of properties.
     *
     * @param bool $reflectSelf Flag to determine if only properties of the current class should be included.
     * @param bool $asArrayProps Flag to convert the properties to an array.
     * @return array|ReflectionProperty[] Returns an array of ReflectionProperty objects or property names based on $asArrayProps.
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
     * Magic method called when invoking undefined methods.
     *
     * This method dynamically handles method calls for property management.
     *
     * Supported dynamic methods:
     *
     * - `isset<PropertyName>`: Checks if the specified property is set.
     *   - Returns true if the property exists and is not null.
     *   - Example: `$obj->issetFoo()` checks if the property `foo` is set.
     *
     * - `is<PropertyName>`: Checks if the specified property is set and equals 1 (truthy).
     *   - Returns true if the property exists and its value is equal to 1.
     *   - Example: `$obj->isFoo()` checks if `foo` is set to 1.
     *
     * - `get<PropertyName>`: Retrieves the value of the specified property.
     *   - Returns the property value or null if it doesn't exist.
     *   - Example: `$value = $obj->getFoo()` gets the value of property `foo`.
     *
     * - `set<PropertyName>`: Sets the value of the specified property.
     *   - Accepts a single parameter which is the value to be assigned to the property.
     *   - Example: `$obj->setFoo($value)` sets the property `foo` to `$value`.
     *
     * - `unset<PropertyName>`: Removes the specified property from the object.
     *   - Example: `$obj->unsetFoo()` deletes the property `foo`.
     *
     * - `push<PropertyName>`: Pushes a value onto an array property.
     *   - If the property is not already an array, it initializes it as an empty array.
     *   - Example: `$obj->pushFoo($value)` adds `$value` to the array property `foo`.
     *
     * - `pop<PropertyName>`: Pops a value from an array property.
     *   - Returns the last value from the array property or null if it doesn't exist.
     *   - Example: `$value = $obj->popFoo()` removes and returns the last value from the array property `foo`.
     *
     * @param string $method Method name that was called.
     * @param array $params Parameters passed to the method.
     * @return mixed|null The result of the method call or null if the method does not return a value.
     */
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "isset", 5) === 0)
        {
            $var = PicoStringUtil::camelize(substr($method, 5));
            return isset($this->{$var});
        }
        else if (strncasecmp($method, "is", 2) === 0)
        {
            $var = PicoStringUtil::camelize(substr($method, 2));
            return isset($this->{$var}) ? $this->{$var} == 1 : false;
        }
        else if (strncasecmp($method, "get", 3) === 0)
        {
            $var = PicoStringUtil::camelize(substr($method, 3));
            return isset($this->{$var}) ? $this->{$var} : null;
        }
        else if (strncasecmp($method, "set", 3) === 0)
        {
            $var = PicoStringUtil::camelize(substr($method, 3));
            $this->set($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "unset", 5) === 0)
        {
            $var = PicoStringUtil::camelize(substr($method, 5));
            unset($this->{$var});
            return $this;
        }
        else if (strncasecmp($method, "push", 4) === 0) 
        {
            $var = PicoStringUtil::camelize(substr($method, 4));
            if(!isset($this->{$var}))
            {
                $this->{$var} = array();
            }
            if(is_array($this->{$var}))
            {
                array_push($this->{$var}, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
            }
            return $this;
        }
        else if (strncasecmp($method, "pop", 3) === 0) 
        {
            $var = PicoStringUtil::camelize(substr($method, 3));
            if(isset($this->{$var}) && is_array($this->{$var}))
            {
                return array_pop($this->{$var});
            }
            return null;
        }
    }

    /**
     * Check if the JSON naming strategy is snake case.
     *
     * @return bool true if the naming strategy is snake case, false otherwise.
     */
    private function isSnake()
    {
        return isset($this->_classParams[self::JSON])
            && (
                isset($this->_classParams[self::JSON]['property-naming-strategy']) 
                || isset($this->_classParams[self::JSON]['propertyNamingStrategy'])
            )
            && (
                strcasecmp($this->_classParams[self::JSON]['property-naming-strategy'], 'SNAKE_CASE') == 0 
                || strcasecmp($this->_classParams[self::JSON]['propertyNamingStrategy'], 'SNAKE_CASE') == 0
            );
    }

    /**
     * Check if the JSON naming strategy is camel case.
     *
     * @return bool true if the naming strategy is camel case, false otherwise.
     */
    protected function isCamel()
    {
        return !$this->isSnake();
    }

    /**
     * Check if the JSON should be prettified.
     *
     * @return bool true if prettification is enabled, false otherwise.
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
     * This method serializes the object to JSON format, with options for pretty printing
     * based on the configuration. It uses the appropriate naming strategy for properties
     * as specified in the class parameters.
     *
     * @return string The JSON string representation of the object.
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
