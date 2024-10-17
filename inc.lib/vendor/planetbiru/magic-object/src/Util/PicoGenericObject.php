<?php

namespace MagicObject\Util;

use MagicObject\MagicObject;
use stdClass;

/**
 * Class PicoGenericObject
 *
 * A generic object that allows dynamic property management.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoGenericObject extends stdClass
{
    /**
     * Constructor
     *
     * @param MagicObject|self|stdClass|array|null $data Initial data to load into the object.
     */
    public function __construct($data = null)
    {
        if(isset($data))
        {
            $this->loadData($data);
        }
    }

    /**
     * Load data into the object.
     *
     * @param stdClass|array $data Data to be loaded.
     * @return self
     */
    public function loadData($data)
    {
        if($data != null && (is_array($data) || is_object($data)))
        {
            foreach ($data as $key => $value) {
                $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                $this->set($key2, $value);
            }
        }
        return $this;
    }

    /**
     * Set a property value.
     *
     * @param string $propertyName Name of the property.
     * @param mixed $propertyValue Value to set.
     * @return self
     */
    public function set($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        $this->$var = $propertyValue;
        return $this;
    }

    /**
     * Get a property value.
     *
     * @param string $propertyName Name of the property.
     * @return mixed|null The value of the property or null if not set.
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : null;
    }

    /**
     * Magic method to set property values dynamically.
     *
     * @param string $name Name of the property.
     * @param mixed $value Value to set.
     * @return void
     */
    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Magic method to get property values dynamically.
     *
     * @param string $name Name of the property to get.
     * @return mixed The value stored in the property or null if not set.
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
     * @param string $name Name of the property.
     * @return bool True if the property is set, false otherwise.
     */
    public function __isset($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    /**
     * Unset a property value.
     *
     * @param string $name Name of the property.
     * @return self
     */
    public function __unset($name)
    {
        unset($this->$name);
        return $this;
    }

    /**
     * Magic method called when invoking undefined methods.
     *
     * This method handles dynamic method calls for property management.
     *
     * Supported methods:
     * 
     * - `isset<PropertyName>`: Checks if the property is set.
     *   - Example: `$obj->issetFoo()` returns true if property `foo` is set.
     * 
     * - `is<PropertyName>`: Checks if the property is set and equals 1 (truthy).
     *   - Example: `$obj->isFoo()` returns true if property `foo` is set and is equal to 1.
     * 
     * - `get<PropertyName>`: Retrieves the value of the property.
     *   - Example: `$value = $obj->getFoo()` gets the value of property `foo`.
     * 
     * - `set<PropertyName>`: Sets the value of the property.
     *   - Example: `$obj->setFoo($value)` sets the property `foo` to `$value`.
     * 
     * - `unset<PropertyName>`: Unsets the property.
     *   - Example: `$obj->unsetFoo()` removes the property `foo`.
     *
     * @param string $method Method name.
     * @param array $params Parameters for the method.
     * @return mixed|null The result of the method call or null if not applicable.
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
            $this->__unset($var);
            return $this;
        }
    }
}