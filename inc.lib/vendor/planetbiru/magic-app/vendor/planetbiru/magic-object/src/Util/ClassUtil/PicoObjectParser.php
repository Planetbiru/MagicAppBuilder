<?php

namespace MagicObject\Util\ClassUtil;

use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Object parser
 */
class PicoObjectParser
{
    /**
     * Parse MagicObject
     * @param MagicObject $data
     * @return MagicObject
     */
    private static function parseMagicObject($data)
    {
        $magicObject = new MagicObject();
        $values = $data->value();
        foreach ($values as $key => $value) {
            $key2 = PicoStringUtil::camelize($key);
            if(is_scalar($value))
            {
                $magicObject->set($key2, $value, true);
            }
            else
            {
                $magicObject->set($key2, self::parseRecursiveObject($value), true);
            }
        }
        return $magicObject;
    }
    
    /**
     * Parse Object
     * @param stdClass|array $data
     * @return MagicObject
     */
    private static function parseObject($data)
    {
        $magicObject = new MagicObject();
        foreach ($data as $key => $value) {
            $key2 = PicoStringUtil::camelize($key);
            if(is_scalar($value))
            {
                $magicObject->set($key2, $value, true);
            }
            else
            {
                $magicObject->set($key2, self::parseRecursiveObject($value), true);
            }
        }
        return $magicObject;
    }
    
    /**
     * Check if input is associated array
     *
     * @param array $array
     * @return boolean
     */
    private static function hasStringKeys($array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
    
    /**
     * Parse recursive
     * @param mixed $data
     * @return mixed
     */
    public static function parseRecursiveObject($data)
    {
        $result = null;
        if($data != null)
        {
            if($data instanceof MagicObject)
            {
                $result = self::parseMagicObject($data);
            }
            else if (is_array($data) || is_object($data) || $data instanceof stdClass) {
                $obj = new MagicObject();
                foreach($data as $key=>$val)
                {
                    $obj = self::updateObject($obj, $key, $val);
                }
                $result = $obj;
            }
            else
            {
                $result = $data;
            }
        }
        return $result;
    }
    
    /**
     * Update object
     *
     * @param MagicObject $obj
     * @param string $key
     * @param mixed $val
     * @return MagicObject
     */
    private static function updateObject($obj, $key, $val)
    {
        if (self::isObject($val))
        {
            $obj->set($key, self::parseRecursiveObject($val));
        }
        else if (is_array($val))
        {
            if(self::hasStringKeys($val))
            {
                $obj->set($key, self::parseRecursiveObject($val));
            }
            else
            {
                $obj->set($key, self::parseRecursiveArray($val));
            }
        }
        else
        {
            $obj->set($key, $val);
        }
        return $obj;
    }
    
    /**
     * Check if value is object
     *
     * @param [type] $value
     * @return boolean
     */
    private static function isObject($value)
    {
        if ($value instanceof stdClass || is_object($value))  
        {
            return true;
        }
        return false;
    }
    
    /**
     * Parse recursive
     * @param array $data
     */
    public static function parseRecursiveArray($data)
    {
        $result = array();
        if($data != null)
        {
            foreach($data as $val)
            {
                if (self::isObject($val))
                {
                    $result[] = self::parseRecursiveObject($val);
                }
                else if (is_array($val))
                {
                    if(self::hasStringKeys($val))
                    {
                        $result[] = self::parseRecursiveObject($val);
                    }
                    else
                    {
                        $result[] = self::parseRecursiveArray($val);
                    }
                }
                else
                {
                    $result[] = $val;
                }
            }
        }
        return $result;
    }
    
    /**
     * Parse from Yaml recursively
     * @param string $yamlString
     */
    public static function parseYamlRecursive($yamlString)
    {
        if($yamlString != null)
        {
            $data = Yaml::parse($yamlString);
            if (is_array($data) || is_object($data)) {
                return self::parseObject($data);
            }
        }
        return null;
    }
    
    /**
     * Parse from JSON recursively
     * @param mixed $data
     */
    public static function parseJsonRecursive($data) //NOSONAR
    {
        if($data == null)
        {
            return null;
        }
        if(is_scalar($data) && is_string($data))
        {
            return self::parseObject(json_decode($data));
        }
        else if (is_array($data) || is_object($data)) {
            return self::parseObject($data);
        }
        else
        {
            return $data;
        }
    }

    /**
     * Parse string
     *
     * @param string $data
     * @return mixed
     */
    public static function parseString($data) //NOSONAR
    {
        if($data == 'null')
        {
            return null;
        }
        else if($data == 'false')
        {
            return false;
        }
        else if($data == 'true')
        {
            return true;
        }
        else
        {
            return $data;
        }
    }
}