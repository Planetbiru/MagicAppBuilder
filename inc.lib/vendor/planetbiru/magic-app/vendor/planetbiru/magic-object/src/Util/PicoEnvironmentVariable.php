<?php

namespace MagicObject\Util;

use stdClass;

/**
 * Class PicoEnvironmentVariable
 *
 * A utility class for handling environment variable replacements within strings and data structures.
 * 
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoEnvironmentVariable
{
    /**
     * Replace all values in a collection with other properties.
     *
     * @param array|object $values Values to process.
     * @param array $collection Collection of replacement values.
     * @param bool $recursive Flag indicating if the process should be recursive.
     * @return array|object Processed values with replacements applied.
     */
    public static function replaceValueAll($values, $collection, $recursive = false)
    {
        if(is_array($values))
        {
            return self::replaceValueAllArray($values, $collection, $recursive);
        }
        else if(is_object($values))
        {
            return self::replaceValueAllObject($values, $collection, $recursive);
        }
        return $values;
    }

    /**
     * Replace all values in an array with other properties.
     *
     * @param array $values Values to process.
     * @param array $collection Collection of replacement values.
     * @param bool $recursive Flag indicating if the process should be recursive.
     * @return array Processed array with replacements applied.
     */
    public static function replaceValueAllArray($values, $collection, $recursive = false)
    {
        foreach($values as $key=>$value)
        {
            if($recursive)
            {
                if(is_object($value) || is_array($value))
                {
                    $value = self::replaceValueAll($value, $collection, $recursive);
                }
                else
                {
                    $value = self::replaceWithOtherProperties($value, $collection);
                }
            }
            else
            {
                $value = self::replaceWithOtherProperties($value, $collection);
            }
            $values[$key] = $value;
        }
        return $values;
    }

    /**
     * Replace all values in an object with other properties.
     *
     * @param stdClass|object $values Values to process.
     * @param array $collection Collection of replacement values.
     * @param bool $recursive Flag indicating if the process should be recursive.
     * @return stdClass|object Processed object with replacements applied.
     */
    public static function replaceValueAllObject($values, $collection, $recursive = false)
    {
        foreach($values as $key=>$value)
        {
            if($recursive)
            {
                if(is_object($value) || is_array($value))
                {
                    $value = self::replaceValueAll($value, $collection, $recursive);
                }
                else
                {
                    $value = self::replaceWithOtherProperties($value, $collection);
                }
            }
            else
            {
                $value = self::replaceWithOtherProperties($value, $collection);
            }
            $values->{$key} = $value;
        }
        return $values;
    }

    /**
     * Replace strings with environment variable names from a string.
     *
     * @param string $value Value to process.
     * @param array $collection Collection of replacement values.
     * @return mixed Processed value with replacements applied.
     */
    public static function replaceWithOtherProperties($value, $collection)
    {
        if(stripos($value, '$') !== false)
        {
            $result = $value;
            $regex = '/\$\\{([^}]+)\\}/m';
            preg_match_all($regex, $value, $matches);
            $pair = array_combine($matches[0], $matches[1]);
            if(!empty($pair))
            {
                foreach($pair as $key=>$value)
                {
                    $otherValue = self::getOtherValue($value, $collection);
                    if($otherValue !== null)
                    {
                        // found
                        $result = str_replace($key, $otherValue, $result);
                        // keep data type
                        if($result == $otherValue)
                        {
                            return $otherValue;
                        }
                    }
                }
            }
            return $result;
        }
        return $value;
    }

    /**
     * Retrieve a value from the collection by its key.
     *
     * @param string $key Key name to retrieve.
     * @param array $collection Collection to search in.
     * @return mixed|null Retrieved value or null if not found.
     */
    public static function getOtherValue($key, $collection)
    {
        $keys = explode(".", trim($key, ""));
        if(count($keys) == 1 && isset($collection[$key]))
        {
            return $collection[$key];
        }
        $value = null;
        if(isset($collection[$keys[0]]))
        {
            $value = $collection[$keys[0]];
        }
        for($i = 1; $i < count($keys); $i++)
        {
            if(!isset($value[$keys[$i]]))
            {
                return null;
            }
            $value = $value[$keys[$i]];
        }
        return $value;
    }

    /**
     * Replace all values in an array with environment variable names.
     *
     * @param array $values Values to process.
     * @param bool $recursive Flag indicating if the process should be recursive.
     * @return array Processed values with replacements applied.
     */
    public static function replaceSysEnvAll($values, $recursive = false)
    {
        foreach($values as $key=>$value)
        {
            if($recursive)
            {
                if(is_object($value) || is_array($value))
                {
                    $value = self::replaceSysEnvAll($value, $recursive);
                }
                else
                {
                    $value = self::replaceWithEnvironmentVariable($value);
                }
            }
            else
            {
                $value = self::replaceWithEnvironmentVariable($value);
            }
            $values[$key] = $value;
        }
        return $values;
    }

    /**
     * Replace strings with environment variable names from a string.
     *
     * @param string $value Value to process.
     * @return string Processed value with replacements applied.
     */
    public static function replaceWithEnvironmentVariable($value)
    {
        $result = $value;
        $regex = '/\$\\{([^}]+)\\}/m';
        preg_match_all($regex, $value, $matches);
        $pair = array_combine($matches[0], $matches[1]);
        if(!empty($pair))
        {
            foreach($pair as $key=>$value)
            {
                $systemEnv = getenv($value);
                if($systemEnv === false)
                {
                    // not found
                }
                else
                {
                    // found
                    $result = str_replace($key, $systemEnv, $result);
                }
            }
        }
        return $result;
    }

    /**
     * Replace a value with its corresponding environment variable.
     *
     * @param string $value Value to process.
     * @return string Processed value with replacements applied.
     */
    public static function replaceSysEnv($value)
    {
        $vars = self::getVariables($value);
        foreach($vars as $key)
        {
            $systemEnv = getenv($key);
            $key2 = '${'.$key.'}';
            if($systemEnv !== false)
            {
                $value = str_replace($key2, $systemEnv, $value);
            }
        }
        return $value;
    }

    /**
     * Extract environment variable names from a string.
     *
     * @param string $value Value to process.
     * @return array List of variable names extracted.
     */
    public static function getVariables($value)
    {
        $result = array();
        $arr = explode('${', $value);
        if(count($arr) > 1)
        {
            $cnt = count($arr);
            for($i = 1; $i < $cnt; $i++)
            {
                if(stripos($arr[$i], "}") !== false)
                {
                    $arr2 = explode('}', $arr[$i]);
                    $result[] = trim($arr2[0]);
                }
            }
        }
        return $result;
    }
}