<?php

namespace MagicObject\Util;

use stdClass;

/**
 * Array util
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoArrayUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Camelize array keys
     *
     * @param array|object|stdClass $array Array contains data to be processed
     * @return array
     */
    public static function camelize($input)
    {
        if(is_array($input))
        {
            self::_camelize($input);
            return $input;
        }
        else
        {
            $array = json_decode(json_encode($input), true);
            self::_camelize($array);
            return $array;
        }
    }

    /**
     * Snakeize array keys
     *
     * @param array|object|stdClass $array Array contains data to be processed
     * @return array
     */
    public static function snakeize($input)
    {
        if(is_array($input))
        {
            self::_snakeize($input);
            return $input;
        }
        else
        {
            $array = json_decode(json_encode($input), true);
            self::_snakeize($array);
            return $array;
        }
    }

    /**
     * Camelize array keys
     *
     * @param array $array Array contains data to be processed
     * @return array|null
     */
    private static function _camelize(&$array) //NOSONAR
    {
        foreach (array_keys($array) as $key)
        {
            # Working with references here to avoid copying the value,
            # since you said your data is quite large.
            $value = &$array[$key];
            unset($array[$key]);
            # This is what you actually want to do with your keys:
            #  - remove exclamation marks at the front
            #  - camelCase to snake_case
            # $transformedKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', ltrim($key, '!')));

            $transformedKey = PicoStringUtil::camelize(str_replace("-", "_", $key));

            # Work recursively
            if (is_array($value))
            {
                self::_camelize($value);
            }
            # Store with new key
            $array[$transformedKey] = $value;
            # Do not forget to unset references!
            unset($value);
        }
    }

    /**
     * Snakeize array keys
     *
     * @param array $array Array contains data to be processed
     * @return array|null
     */
    private static function _snakeize(&$array) //NOSONAR
    {
        foreach (array_keys($array) as $key)
        {
            # Working with references here to avoid copying the value,
            # since you said your data is quite large.
            $value = &$array[$key];
            unset($array[$key]);
            # This is what you actually want to do with your keys:
            #  - remove exclamation marks at the front
            #  - camelCase to snake_case
            # $transformedKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', ltrim($key, '!')));

            $transformedKey = PicoStringUtil::snakeize($key);

            # Work recursively
            if (is_array($value))
            {
                self::_snakeize($value);
            }
            # Store with new key
            $array[$transformedKey] = $value;
            # Do not forget to unset references!
            unset($value);
        }
    }
}