<?php
namespace MagicObject\Util;

use stdClass;

/**
 * Class PicoStringUtil
 *
 * A utility class for performing various string manipulations and transformations.
 *
 * This class provides static methods for converting between different string case formats (snake case, camel case, kebab case),
 * validating string contents, and manipulating strings (trimming, checking for null/empty values, etc.).
 *
 * The methods are designed to be used statically, allowing for convenient access without needing to instantiate the class.
 *
 * Example usage:
 * ```
 * <?php
 * $camelCase = PicoStringUtil::camelize('example_string');
 * $kebabCase = PicoStringUtil::kebapize('exampleString');
 * $isNotEmpty = PicoStringUtil::isNotNullAndNotEmpty('Some Value');
 * ```
 * 
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoStringUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Convert snake case to camel case
     *
     * @param string $input Input string in snake case format.
     * @param string $glue Optional. The glue character used in the input string (default is '_').
     * @return string Converted string in camel case format.
     */
    public static function camelize($input, $glue = '_')
    {
        $input = lcfirst($input);
        return lcfirst(str_replace($glue, '', ucwords(trim($input), $glue)));
    }

    /**
     * Convert snake case to upper camel case
     *
     * @param string $input Input string in snake case format.
     * @param string $glue Optional. The glue character used in the input string (default is '_').
     * @return string Converted string in upper camel case format.
     */
    public static function upperCamelize($input, $glue = '_')
    {
        $input = lcfirst($input);
        return ucfirst(str_replace($glue, '', ucwords($input, $glue)));
    }

    /**
     * Convert camel case to snake case
     *
     * Converts a string from camel case (e.g., exampleString) to snake case (e.g., example_string).
     *
     * @param string $input Input string in camel case format.
     * @param string $glue Optional. The glue character used in the input string (default is '_').
     * @return string Converted string in snake case format.
     */
    public static function snakeize($input, $glue = '_') {
        return ltrim(
            preg_replace_callback('/[A-Z]/', function ($matches) use ($glue) {
                return $glue . strtolower($matches[0]);
            }, $input),
            $glue
        );
    }

    /**
     * Snakeize object
     *
     * Converts all keys of an object or an array to snake case.
     * This is useful for normalizing data structures when working with APIs or databases.
     *
     * @param mixed $object Object or array to be converted.
     * @return mixed The input object/array with keys converted to snake case.
     */
    public static function snakeizeObject($object)
    {
        if(is_array($object))
        {
            $array = array();
            foreach($object as $key=>$value)
            {
                $array[self::snakeize($key)] = self::snakeizeObject($value);
            }
            return $array;
        }
        else if($object instanceof stdClass)
        {
            $stdClass = new stdClass;
            foreach($object as $key=>$value)
            {
                $stdClass->{self::snakeize($key)} = self::snakeizeObject($value);
            }
            return $stdClass;
        }
        else
        {
            return $object;
        }
    }

    /**
     * Convert snake case to title case
     *
     * Converts a snake case string (e.g., example_string) to title case (e.g., Example String).
     * Words are separated by spaces, and the first letter of each word is capitalized.
     *
     * @param string $input Input string in snake case format.
     * @param string $glue Optional. The glue character used in the input string (default is '_').
     * @return string Converted string in title case format.
     */
    public static function snakeToTitle($input, $glue = '_')
    {
        $input = lcfirst($input);
        return ucwords(str_replace($glue, ' ', ucwords($input, $glue)));
    }

    /**
     * Convert camel case to title case
     *
     * Converts a camel case string (e.g., exampleString) to title case (e.g., Example String).
     *
     * @param string $input Input string in camel case format.
     * @return string Converted string in title case format.
     */
    public static function camelToTitle($input)
    {
        $snake = self::snakeize($input);
        return self::snakeToTitle($snake);
    }

    /**
     * Convert to kebab case
     *
     * Converts a string to kebab case (e.g., example_string becomes example-string).
     * Useful for URL slugs or CSS class names.
     *
     * @param string $input Input string in any case format.
     * @return string Converted string in kebab case format.
     */
    public static function kebapize($input)
    {
        $snake = self::snakeize($input, '-');
        return str_replace('_', '-', $snake);
    }

    /**
     * Create constant key
     *
     * Converts a string to a constant key format (e.g., example_string becomes EXAMPLE_STRING).
     *
     * @param string $input Input string in snake case format.
     * @return string Converted string in uppercase snake case format.
     */
    public function constantKey($input)
    {
        return strtoupper(self::snakeize($input, '-'));
    }

    /**
     * Check if string starts with a substring
     *
     * Determines if the given string starts with the specified substring.
     * Comparison can be case-sensitive or case-insensitive.
     *
     * @param string $haystack The string to check.
     * @param string $value The substring to look for at the start.
     * @param bool $caseSensitive Optional. Flag to indicate if the comparison is case-sensitive (default is false).
     * @return bool True if the string starts with the substring, false otherwise.
     */
    public static function startsWith($haystack, $value, $caseSensitive = false)
    {
        if($caseSensitive)
        {
            return isset($haystack) && substr($haystack, 0, strlen($value)) == $value;
        }
        else
        {
            return isset($haystack) && strtolower(substr($haystack, 0, strlen($value))) == strtolower($value);
        }
    }

    /**
     * Check if string ends with a substring
     *
     * Determines if the given string ends with the specified substring.
     * Comparison can be case-sensitive or case-insensitive.
     *
     * @param string $haystack The string to check.
     * @param string $value The substring to look for at the end.
     * @param bool $caseSensitive Optional. Flag to indicate if the comparison is case-sensitive (default is false).
     * @return bool True if the string ends with the substring, false otherwise.
     */
    public static function endsWith($haystack, $value, $caseSensitive = false)
    {
        if($caseSensitive)
        {
            return isset($haystack) && substr($haystack, strlen($haystack) - strlen($value)) == $value;
        }
        else
        {
            return isset($haystack) && strtolower(substr($haystack, strlen($haystack) - strlen($value))) == strtolower($value);
        }
    }

    /**
     * Left trim a string
     *
     * Trims the specified substring from the start of the string for a defined number of times.
     * If count is -1, it trims until the substring no longer occurs at the start.
     *
     * @param string $haystack The string to trim.
     * @param string $substring The substring to trim from the start.
     * @param int $count Optional. Number of times to trim (default is -1).
     * @return string The trimmed string.
     */
    public static function lTrim($haystack, $substring, $count = -1)
    {
        $i = 0;
        $found = false;
        do
        {
            if(PicoStringUtil::startsWith($haystack, $substring))
            {
                $haystack = trim(substr($haystack, 1));
                $found = true;
                $i++;
            }
            else
            {
                $found = false;
            }
        }
        while($found && ($count == -1 || $count > $i));
        return $haystack;
    }

    /**
     * Right trim a string
     *
     * Trims the specified substring from the end of the string for a defined number of times.
     * If count is -1, it trims until the substring no longer occurs at the end.
     *
     * @param string $haystack The string to trim.
     * @param string $substring The substring to trim from the end.
     * @param int $count Optional. Number of times to trim (default is -1).
     * @return string The trimmed string.
     */
    public static function rTrim($haystack, $substring, $count = -1)
    {
        $i = 0;
        $found = false;
        do
        {
            if(PicoStringUtil::endsWith($haystack, $substring))
            {
                $haystack = trim(substr($haystack, 0, strlen($haystack) - 1));
                $found = true;
                $i++;
            }
            else
            {
                $found = false;
            }
        }
        while($found && ($count == -1 || $count > $i));
        return $haystack;
    }

    /**
     * Check if string is not null and not empty
     *
     * Determines if the given string is neither null nor empty.
     *
     * @param string $value The string to check.
     * @return bool True if the string is not null and not empty, false otherwise.
     */
    public static function isNotNullAndNotEmpty($value)
    {
        return isset($value) && !empty($value);
    }

    /**
     * Check if string is null or empty
     *
     * Determines if the given string is either null or empty.
     *
     * @param string $value The string to check.
     * @return bool True if the string is null or empty, false otherwise.
     */
    public static function isNullOrEmpty($value)
    {
        return !isset($value) || empty($value);
    }

    /**
     * Select not null value
     *
     * Returns the first value that is not null from the two provided values.
     *
     * @param mixed $value1 The first value to check.
     * @param mixed $value2 The second value to check.
     * @return mixed The first non-null value.
     */
    public static function selectNotNull($value1, $value2)
    {
        return isset($value1) ? $value1 : $value2;
    }

    /**
     * Fix carriage returns in a string
     *
     * Normalizes line endings in a string to Windows-style carriage return line feed (CRLF).
     *
     * @param string $input The input string to fix.
     * @return string The modified string with normalized line endings.
     */
    public static function windowsCariageReturn($input)
    {
        $input = str_replace("\n", "\r\n", $input);
        $input = str_replace("\r\r\n", "\r\n", $input);
        $input = str_replace("\r", "\r\n", $input);
        $input = str_replace("\r\n\n", "\r\n", $input);
        return $input;
    }

}