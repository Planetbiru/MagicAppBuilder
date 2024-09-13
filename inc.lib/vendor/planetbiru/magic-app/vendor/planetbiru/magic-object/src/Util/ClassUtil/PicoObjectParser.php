<?php

namespace MagicObject\Util\ClassUtil;

use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Object parser
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoObjectParser
{
    /**
     * Parse MagicObject
     * @param MagicObject $data Data
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
     * @param stdClass|array $data Data
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
     * @param array $array Array
     * @return boolean
     */
    private static function hasStringKeys($array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Parse recursive
     * @param mixed $data Data
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
     * @param MagicObject $obj Magic object
     * @param string $key Property name
     * @param mixed $val Property value
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
     * @param mixed $value Value to be checked
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
     * @param array $data Data to be parsed
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
     * @param string $yamlString YAML string
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
     * @param mixed $data Data to be parsed
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
     * @param string $data Data
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