<?php

namespace MagicObject\Language;

use MagicObject\Util\PicoStringUtil;
use stdClass;

/**
 * Language
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoLanguage
{
    /**
     * Constructor
     *
     * @param stdClass|array $data
     */
    public function __construct($data = null)
    {
        if(isset($data))
        {
            $this->loadData($data);
        }
    }

    /**
     * Load data to object
     * @param stdClass|array $data
     * @return self
     */
    public function loadData($data)
    {
        if($data != null && (is_array($data) || is_object($data)))
        {
            foreach ($data as $key => $value) {
                $key2 = PicoStringUtil::camelize($key);
                $this->set($key2, $value);
            }
        }
        return $this;
    }

    /**
     * Set property value
     *
     * @param string $propertyName
     * @param mixed|null
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
     * @param string $propertyName
     * @return mixed|null
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
     * @param mixed $value Property value
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
     * @param string $name Name of the property to get.
     * @return mixed Datas stored in property.
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
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    /**
     * Unset property value
     *
     * @param string $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->$name);
    }

    /**
     * Magic method called when user call any undefined method
     *
     * @param string $method
     * @param string $params
     * @return mixed|null
     */
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return $this->get($var);
        }
        else if (strncasecmp($method, "equals", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            $value = isset($this->$var) ? $this->$var : null;
            return isset($params[0]) && $params[0] == $value;
        }
    }
}