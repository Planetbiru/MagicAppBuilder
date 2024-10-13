<?php

namespace MagicObject\Util;

use stdClass;

/**
 * Class PicoArrayUtil
 *
 * Utility class for array operations, including key transformations between camelCase and snake_case.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoArrayUtil
{
    private function __construct()
    {
        // Prevent object construction from outside the class
    }

    /**
     * Converts array or object keys to camelCase.
     *
     * @param array|object|stdClass $input Array or object containing data to be processed.
     * @return array Processed array with camelCase keys.
     */
    public static function camelize($input)
    {
        if (is_array($input)) {
            self::_camelize($input);
            return $input;
        } else {
            $array = json_decode(json_encode($input), true);
            self::_camelize($array);
            return $array;
        }
    }

    /**
     * Converts array or object keys to snake_case.
     *
     * @param array|object|stdClass $input Array or object containing data to be processed.
     * @return array Processed array with snake_case keys.
     */
    public static function snakeize($input)
    {
        if (is_array($input)) {
            self::_snakeize($input);
            return $input;
        } else {
            $array = json_decode(json_encode($input), true);
            self::_snakeize($array);
            return $array;
        }
    }

    /**
     * Recursively converts array keys to camelCase.
     *
     * @param array &$array Array containing data to be processed by reference.
     * @return array|null
     */
    private static function _camelize(&$array) //NOSONAR
    {
        foreach (array_keys($array) as $key) {
            // Working with references to avoid copying the value.
            $value = &$array[$key];
            unset($array[$key]);

            // Transform key to camelCase
            $transformedKey = PicoStringUtil::camelize(str_replace("-", "_", $key));

            // Work recursively
            if (is_array($value)) {
                self::_camelize($value);
            }

            // Store with new key
            $array[$transformedKey] = $value;

            // Unset reference
            unset($value);
        }
    }

    /**
     * Recursively converts array keys to snake_case.
     *
     * @param array &$array Array containing data to be processed by reference.
     * @return array|null
     */
    private static function _snakeize(&$array) //NOSONAR
    {
        foreach (array_keys($array) as $key) {
            // Working with references to avoid copying the value.
            $value = &$array[$key];
            unset($array[$key]);

            // Transform key to snake_case
            $transformedKey = PicoStringUtil::snakeize($key);

            // Work recursively
            if (is_array($value)) {
                self::_snakeize($value);
            }

            // Store with new key
            $array[$transformedKey] = $value;

            // Unset reference
            unset($value);
        }
    }
}
