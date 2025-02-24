<?php

namespace MagicObject\Util;

use stdClass;

/**
 * Class PicoArrayUtil
 *
 * Utility class for performing various array operations, 
 * particularly key transformations between camelCase and 
 * snake_case formats.
 * 
 * This class provides static methods and cannot be instantiated.
 *
 * @package MagicObject\Util
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoArrayUtil
{
    private function __construct()
    {
        // Prevent object construction from outside the class
    }

    /**
     * Converts the keys of an array or object to camelCase.
     *
     * This method can process both associative arrays and objects.
     * 
     * **Example:**
     * ```php
     * <?php
     * $data = ['first_name' => 'John', 'last_name' => 'Doe'];
     * $camelized = PicoArrayUtil::camelize($data);
     * // $camelized is ['firstName' => 'John', 'lastName' => 'Doe']
     * ```
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
     * Converts the keys of an array or object to snake_case.
     *
     * This method can process both associative arrays and objects.
     * 
     * **Example:**
     * ```php
     * <?php
     * $data = ['firstName' => 'John', 'lastName' => 'Doe'];
     * $snakeized = PicoArrayUtil::snakeize($data);
     * // $snakeized is ['first_name' => 'John', 'last_name' => 'Doe']
     * ```
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
     * This method operates by reference to avoid unnecessary copies.
     *
     * @param array &$array Array containing data to be processed by reference.
     * @return void
     */
    private static function _camelize(&$array) // NOSONAR
    {
        foreach (array_keys($array) as $key) {
            // Working with references to avoid copying the value.
            $value = &$array[$key];
            unset($array[$key]);

            // Transform key to camelCase
            $transformedKey = PicoStringUtil::camelize($key);

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
     * This method operates by reference to avoid unnecessary copies.
     *
     * @param array &$array Array containing data to be processed by reference.
     * @return void
     */
    private static function _snakeize(&$array) // NOSONAR
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

    /**
     * Recursively normalizes array indices by converting string integer keys 
     * into numeric sequential indices, while retaining non-integer keys.
     *
     * This method processes multi-dimensional arrays of unlimited depth.
     * If a key is a string that represents an integer (e.g., "0", "1"), 
     * it is converted into a numeric sequential index. 
     * Non-integer or non-string keys remain unchanged.
     *
     * @param array $array The input array to normalize.
     * @return array The normalized array with modified indices.
     */
    public static function normalizeArrayIndicesRecursive($array) {
        $normalizedArray = array();

        foreach ($array as $key => $value) {
            if (is_string($key) && ctype_digit($key)) {
                // If the key is a string integer, add the value to the array sequentially.
                $normalizedArray[] = is_array($value) 
                    ? self::normalizeArrayIndicesRecursive($value) // Recursively normalize if the value is an array.
                    : $value;
            } else {
                // If the key is not a string integer, retain the key and value as-is.
                $normalizedArray[$key] = is_array($value) 
                    ? self::normalizeArrayIndicesRecursive($value) // Recursively normalize if the value is an array.
                    : $value;
            }
        }

        return $normalizedArray;
    }
    
    /**
     * Recursively converts string "true"/"false"/"null" to actual boolean/null values
     * and converts numeric string keys to integer keys.
     *
     * This function will iterate over each element of the given array and perform the following:
     * 1. Converts string `"true"` to boolean `true`, string `"false"` to boolean `false`, 
     *    and string `"null"` to the `null` value.
     * 2. Converts string keys representing numbers (e.g., "0", "1", "2") into actual integers (e.g., 0, 1, 2).
     * 3. Recursively processes nested arrays if present.
     *
     * @param array &$array The input array which will be modified in-place. The function updates
     *                      the original array by converting string values and keys as described.
     *
     * @return void This function modifies the array by reference and does not return any value.
     */
    public static function normalizeArray(&$array) {
        foreach ($array as $key => &$value) {
            // Convert string keys that represent numbers into actual integers
            if (is_string($key) && is_numeric($key)) {
                $key = (int)$key;
            }

            if (is_array($value)) {
                // Recursively process nested arrays
                self::normalizeArray($value);
            } else {
                // Replace string "true" with boolean true, "false" with boolean false, and "null" with null
                if ($value === "true") {
                    $value = true;
                } elseif ($value === "false") {
                    $value = false;
                } elseif ($value === "null") {
                    $value = null;
                }
            }

            // Reassign the converted key back
            $array[$key] = $value;

            // Remove the original string key if it was a numeric string
            if (is_numeric($key) && !is_int($key)) {
                unset($array[$key]);
            }
        }
    }


}
