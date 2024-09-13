<?php

namespace MagicObject\Util;

use stdClass;

/**
 * Environment variable
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoEnvironmentVariable
{
    /**
     * Replace all values from other properties
     *
     * @param array $values Values
     * @param array $collection Collection
     * @param boolean $recursive Flag that process is recursive
     * @return array
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
     * Replace all values from other properties as array
     *
     * @param array $values Values
     * @param array $collection Collection
     * @param boolean $recursive Flag that process is recursive
     * @return array
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
     * Replace all values from other properties as object
     *
     * @param stdClass|object $values
     * @param array $collection Collection
     * @param boolean $recursive Flag that process is recursive
     * @return array|stdClass|object
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
     * Replace string with environment variable nane from a string
     *
     * @param string $value Value
     * @param array $collection Collection
     * @return mixed
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
     * Get other value
     *
     * @param string $key Key name
     * @param array $collection Collection
     * @return mixed
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
     * Replace all values with environment variable
     *
     * @param array $values Values
     * @param boolean $recursive Flag that process is recursive
     * @return array
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
     * Replace string with environment variable nane from a string
     *
     * @param string $value Value
     * @return string
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
     * Replace value with environment variable
     *
     * @param string $value Value
     * @return string
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
     * Get environment variable name from a string
     *
     * @param string $value Value
     * @return array
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