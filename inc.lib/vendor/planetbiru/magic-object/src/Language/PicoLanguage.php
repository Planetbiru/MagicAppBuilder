<?php

namespace MagicObject\Language;

use MagicObject\Util\PicoStringUtil;
use stdClass;

/**
 * Language Class
 *
 * This class represents a language object, allowing for dynamic property
 * management and loading data from arrays or objects.
 * 
 * @author Kamshory
 * @package MagicObject\Language
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoLanguage
{
    /**
     * Constructor
     *
     * @param stdClass|array|null $data Optional data to initialize the object.
     */
    public function __construct($data = null)
    {
        if (isset($data)) {
            $this->loadData($data);
        }
    }

    /**
     * Load data into the object.
     *
     * @param stdClass|array $data Data to be loaded into the object.
     * @return self Returns the current instance for method chaining.
     */
    public function loadData($data)
    {
        if ($data != null && (is_array($data) || is_object($data))) {
            foreach ($data as $key => $value) {
                $key2 = PicoStringUtil::camelize($key);
                $this->set($key2, $value);
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
     * Get a property value.
     *
     * @param string $propertyName Name of the property to retrieve.
     * @return mixed|null The value of the property or null if not set.
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->{$var}) ? $this->{$var} : null;
    }

    /**
     * Magic method to set property values.
     * 
     * **Example:** 
     * ```php
     * <?php
     * $instance->foo = 'bar';
     * ```
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
     * Magic method to get property values.
     * 
     * **Example:** 
     * ```php
     * <?php
     * echo $instance->foo;
     * ```
     *
     * @param string $name Name of the property to get.
     * @return mixed The value stored in the property.
     */
    public function __get($name)
    {
        if ($this->__isset($name)) {
            return $this->get($name);
        }
    }

    /**
     * Check if a property is set.
     *
     * @param string $name Name of the property to check.
     * @return bool true if the property is set, false otherwise.
     */
    public function __isset($name)
    {
        return isset($this->{$name});
    }

    /**
     * Unset a property value.
     *
     * @param string $name Name of the property to unset.
     * @return void
     */
    public function __unset($name)
    {
        unset($this->{$name});
    }

    /**
     * Magic method called when an undefined method is invoked.
     *
     * This method provides dynamic handling for method calls that are not explicitly defined in the class.
     * It specifically supports two types of method calls:
     * 
     * 1. **Getter Methods**:
     *    - When a method name starts with "get", it retrieves the corresponding property value.
     *    - For example, calling `$obj->getAge()` will invoke this method and call `$this->get('age')`.
     * 
     * 2. **Equality Check Methods**:
     *    - When a method name starts with "equals", it checks if the provided parameter is equal to
     *      the corresponding property value.
     *    - For example, calling `$obj->equalsAge($someValue)` will check if `$someValue` is equal to
     *      the value of the `age` property.
     *
     * If the method does not start with "get" or "equals", this method will return null.
     *
     * @param string $method Name of the method being called. It should start with "get" or "equals".
     * @param array $params Parameters passed to the method; for equality checks, it typically contains the value to compare.
     * @return mixed|null The return value of the getter method or the result of the equality check (true or false), or null if the method is not recognized.
     */
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return $this->get($var);
        } elseif (strncasecmp($method, "equals", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            $value = isset($this->{$var}) ? $this->{$var} : null;
            return isset($params[0]) && $params[0] == $value;
        }
    }
}
