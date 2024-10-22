<?php

namespace MagicObject\Util;

use MagicObject\MagicObject;
use stdClass;

/**
 * Class PicoGenericObject
 *
 * A generic object that allows dynamic property management. 
 * This class extends stdClass and provides methods to load, 
 * set, get, unset, and check properties dynamically.
 * 
 * Properties can be accessed using camelCase naming conventions.
 *
 * @package MagicObject\Util
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoGenericObject extends stdClass
{
    /**
     * Constructor
     *
     * Initializes the object with optional initial data.
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
     * Accepts either an associative array or an object, 
     * converting its keys to camelCase format before loading.
     *
     * Example:
     * ```php
     * $obj = new PicoGenericObject();
     * $obj->loadData(['first_name' => 'John', 'last_name' => 'Doe']);
     * echo $obj->get('firstName'); // Outputs: John
     * ```
     *
     * @param stdClass|array $data Data to be loaded.
     * @return self Returns the current instance for method chaining.
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
     * Converts the property name to camelCase and assigns the value.
     *
     * Example:
     * ```php
     * $obj = new PicoGenericObject();
     * $obj->set('first_name', 'John');
     * echo $obj->get('firstName'); // Outputs: John
     * ```
     *
     * @param string $propertyName Name of the property.
     * @param mixed $propertyValue Value to set.
     * @return self Returns the current instance for method chaining.
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
     * Retrieves the value of a property, returning null if not set.
     *
     * Example:
     * ```php
     * $value = $obj->get('firstName'); // Retrieves the value of 'firstName'
     * ```
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
     * This method allows setting properties without directly calling `set()`.
     *
     * Example:
     * ```php
     * $obj->firstName = 'John'; // Calls __set() internally
     * ```
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
     * This method allows getting properties without directly calling `get()`.
     *
     * Example:
     * ```php
     * $value = $obj->firstName; // Calls __get() internally
     * ```
     *
     * @param string $name Name of the property to get.
     * @return mixed|null The value stored in the property or null if not set.
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
     * Example:
     * ```php
     * if ($obj->issetFirstName()) {
     *     // Do something
     * }
     * ```
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
     * Example:
     * ```php
     * $obj->unsetFirstName(); // Removes the property 'firstName'
     * ```
     *
     * @param string $name Name of the property.
     * @return self Returns the current instance for method chaining.
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