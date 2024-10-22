<?php

namespace MagicObject\Util\ClassUtil;

use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Class PicoSecretParser
 *
 * This class provides methods to parse various data formats (YAML, JSON, etc.) into instances of SecretObject.
 * It supports recursive parsing for nested objects and arrays, allowing for flexible handling of secret-related data structures.
 *
 * @author Kamshory
 * @package MagicObject\Util\ClassUtil
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSecretParser
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Parse a SecretObject from a given data structure.
     *
     * This method converts a SecretObject instance into a new SecretObject 
     * by mapping scalar values and recursively parsing nested objects.
     *
     * @param SecretObject $data The SecretObject instance to parse.
     * @return SecretObject The parsed SecretObject.
     */
    private static function parseSecretObject($data)
    {
        $secretObject = new SecretObject();
        $values = $data->value();
        foreach ($values as $key => $value) {
            $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
            if(is_scalar($value))
            {
                $secretObject->set($key2, $value);
            }
            else
            {
                $secretObject->set($key2, self::parseRecursiveObject($value));
            }
        }
        return $secretObject;
    }

    /**
     * Parse an object or associative array into a SecretObject.
     *
     * This method iterates over the given data structure, converting keys to camel case 
     * and mapping scalar values directly while handling nested structures recursively.
     *
     * @param stdClass|array $data The data to parse, which can be an object or an associative array.
     * @return SecretObject The resulting SecretObject after parsing.
     */
    private static function parseObject($data)
    {
        $secretObject = new SecretObject();
        foreach ($data as $key => $value) {
            $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
            if(is_scalar($value))
            {
                $secretObject->set($key2, $value);
            }
            else
            {
                $secretObject->set($key2, self::parseRecursiveObject($value));
            }
        }
        return $secretObject;
    }

    /**
     * Check if the input is an associative array.
     *
     * This method determines whether the provided array has string keys, indicating it is associative.
     *
     * @param array $array The array to check.
     * @return bool True if the array has string keys, false otherwise.
     */
    private static function hasStringKeys($array) {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * Recursively parse data into a SecretObject.
     *
     * This method handles various data types, converting them into a SecretObject 
     * or returning them as-is when appropriate.
     *
     * @param mixed $data The data to parse, which may be a SecretObject, array, object, or scalar.
     * @return mixed The parsed SecretObject or original data.
     */
    public static function parseRecursiveObject($data)
    {
        $result = null;
        if($data != null)
        {
            if($data instanceof SecretObject)
            {
                $result = self::parseSecretObject($data);
            }
            else if (is_array($data) || is_object($data) || $data instanceof stdClass) {
                $obj = new SecretObject();
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
     * Update a SecretObject with a key-value pair.
     *
     * This method determines if the value is an object or array and updates the SecretObject 
     * accordingly, using recursion as necessary.
     *
     * @param SecretObject $obj The SecretObject to update.
     * @param string $key The property name to set.
     * @param mixed $val The property value to assign.
     * @return SecretObject The updated SecretObject.
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
     * Recursively parse an array into a SecretObject.
     *
     * This method processes each element of the array, converting nested objects or arrays 
     * into SecretObject instances as needed.
     *
     * @param array $data The array to parse.
     * @return array An array of parsed SecretObject instances or original values.
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
     * Parse a YAML string recursively into a SecretObject.
     *
     * This method converts a YAML string into a structured SecretObject by first 
     * parsing the string into an array or object, then processing it.
     *
     * @param string $yamlString The YAML string to parse.
     * @return SecretObject|null The resulting SecretObject or null if parsing fails.
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
     * Parse JSON data recursively into a SecretObject.
     *
     * This method takes JSON-encoded strings or data structures and converts them into 
     * SecretObject instances, handling various data types.
     *
     * @param string $jsonString The JSON string to parse.
     * @return SecretObject|null The resulting SecretObject or null if the data is not valid.
     */
    public static function parseJsonRecursive($jsonString)
    {
        if($jsonString != null)
        {
            $data = json_decode($jsonString);
            if (is_array($data) || is_object($data)) {
                return self::parseObject($data);
            }
        }
        return null;
    }
}