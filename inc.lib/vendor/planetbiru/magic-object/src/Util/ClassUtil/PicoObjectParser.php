<?php

namespace MagicObject\Util\ClassUtil;

use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PicoObjectParser
 *
 * This class provides methods to parse various data formats (YAML, JSON, etc.) into instances of MagicObject.
 * It supports recursive parsing for nested objects and arrays, allowing for flexible data structure handling.
 *
 * @package MagicObject\Util\ClassUtil
 * @link https://github.com/Planetbiru/MagicObject
 * @author Kamshory
 */
class PicoObjectParser
{
    /**
     * Parse a MagicObject from a given data structure.
     *
     * This method converts a MagicObject instance into a new MagicObject 
     * by mapping scalar values and recursively parsing nested objects.
     *
     * @param MagicObject $data The MagicObject instance to parse.
     * @return MagicObject The parsed MagicObject.
     */
    private static function parseMagicObject($data)
    {
        $magicObject = new MagicObject();
        $values = $data->value();
        foreach ($values as $key => $value) {
            $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
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
     * Parse an object or associative array into a MagicObject.
     *
     * This method iterates over the given data structure, converting keys to camel case 
     * and mapping scalar values directly while handling nested structures recursively.
     *
     * @param stdClass|array $data The data to parse, which can be an object or an associative array.
     * @return MagicObject The resulting MagicObject after parsing.
     */
    private static function parseObject($data)
    {
        $magicObject = new MagicObject();
        foreach ($data as $key => $value) {
            $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
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
     * Check if the given array has string keys, indicating it is associative.
     *
     * @param array $array The array to check.
     * @return bool True if the array has string keys, false otherwise.
     */
    private static function hasStringKeys($array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Recursively parse data into a MagicObject.
     *
     * This method handles various data types, converting them into a MagicObject 
     * or returning them as-is when appropriate.
     *
     * @param mixed $data The data to parse, which may be a MagicObject, array, object, or scalar.
     * @return mixed The parsed MagicObject or original data.
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
     * Update a MagicObject with a key-value pair.
     *
     * This method determines if the value is an object or array and updates the MagicObject 
     * accordingly, using recursion as necessary.
     *
     * @param MagicObject $obj The MagicObject to update.
     * @param string $key The property name to set.
     * @param mixed $val The property value to assign.
     * @return MagicObject The updated MagicObject.
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
     * Check if the given value is an object.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is an object, false otherwise.
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
     * Recursively parse an array into a MagicObject.
     *
     * This method processes each element of the array, converting nested objects or arrays 
     * into MagicObject instances as needed.
     *
     * @param array $data The array to parse.
     * @return array An array of parsed MagicObject instances or original values.
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
     * Parse a YAML string recursively into a MagicObject.
     *
     * This method converts a YAML string into a structured MagicObject by first 
     * parsing the string into an array or object, then processing it.
     *
     * @param string $yamlString The YAML string to parse.
     * @return MagicObject|null The resulting MagicObject or null if parsing fails.
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
     * Parse JSON data recursively into a MagicObject.
     *
     * This method takes JSON-encoded strings or data structures and converts them into 
     * MagicObject instances, handling various data types.
     *
     * @param mixed $data The JSON data to parse, which can be a string, array, or object.
     * @return MagicObject|null The resulting MagicObject or null if the data is not valid.
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
        if (is_array($data) || is_object($data)) {
            return self::parseObject($data);
        }
        return $data;// Return the data as is if it's not an object or array
        
    }

    /**
     * Parse a string representation of a value into its appropriate type.
     *
     * This method converts specific string values ('null', 'true', 'false') into their 
     * respective PHP types while returning all other strings as-is.
     *
     * @param string $data The string data to parse.
     * @return mixed The parsed value, which may be null, boolean, or string.
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