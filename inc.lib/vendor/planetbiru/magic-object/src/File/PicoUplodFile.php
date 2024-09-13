<?php

namespace MagicObject\File;

use MagicObject\Util\PicoStringUtil;

/**
 * Upload file tool
 * All atribute in this class is readonly
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoUplodFile
{
    private $map = array();

    /**
     * Uploaded file
     *
     * @var PicoUploadFileContainer[]
     */
    private $values = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initMap();
    }

    /**
     * Magic method
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, $arguments) //NOSONAR
    {
        if (strncasecmp($method, "get", 3) === 0) {
            $var = substr($method, 3);
            $camel = PicoStringUtil::camelize($var);
            $key = $this->map[$camel];
            return isset($this->values[$key]) ? $this->values[$key] : new PicoUploadFileContainer();
        }
    }
    
    /**
     * Get uploaded file
     *
     * @param string $name
     * @return PicoUploadFileContainer|mixed
     */
    public function get($name)
    {
        return $this->__get($name);
    }

    /**
     * Get uploaded file by key
     *
     * @param string $name
     * @return PicoUploadFileContainer|mixed
     */
    public function __get($name)
    {
        $camel = PicoStringUtil::camelize($name);
        if (isset($this->map[$camel])) {
            $key = $this->map[$camel];
            if (isset($this->values[$key])) {
                return $this->values[$key];
            }
        }
        return new PicoUploadFileContainer();
    }

    /**
     * Check if file is exists
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        $camel = PicoStringUtil::camelize($name);
        return isset($this->map[$camel]);
    }

    /**
     * Init map
     *
     * @return void
     */
    private function initMap()
    {
        $keys = array_keys($_FILES);
        foreach ($keys as $key) {
            $camel = PicoStringUtil::camelize($key);
            $this->map[$camel] = $key;
            $this->values[$key] = new PicoUploadFileContainer($_FILES[$key]);
        }
    }

    /**
     * Method to debug object
     *
     * @return string
     */
    public function __toString()
    {
        $arr = array();
        foreach($this->values as $key=>$value)
        {
            $arr[$key] = json_decode($value);
        }
        return json_encode($arr);
    }
}
