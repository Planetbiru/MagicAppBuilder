<?php
namespace MagicObject\Util;

use stdClass;

class PicoStringUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Convert snake case to camel case
     *
     * @param string $input Input string
     * @param string $glue Glue
     * @return string
     */
    public static function camelize($input, $glue = '_')
    {
        $input = lcfirst($input);
        return lcfirst(str_replace($glue, '', ucwords(trim($input), $glue)));
    }

    /**
     * Convert snake case to upper camel case
     *
     * @param string $input Input string
     * @param string $glue Glue
     * @return string
     */
    public static function upperCamelize($input, $glue = '_')
    {
        $input = lcfirst($input);
        return ucfirst(str_replace($glue, '', ucwords($input, $glue)));
    }

    /**
     * Convert camel case to snake case
     *
     * @param string $input Input string
     * @param string $glue Glue
     * @return string
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
     * @param mixed $object Object
     * @return mixed
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
     * Convert snake case to title
     *
     * @param string $input Input
     * @param string $glue Glue
     * @return string
     */
    public static function snakeToTitle($input, $glue = '_')
    {
        $input = lcfirst($input);
        return ucwords(str_replace($glue, ' ', ucwords($input, $glue)));
    }

    /**
     * Convert camel case to title
     *
     * @param string $input Input string
     * @return string
     */
    public static function camelToTitle($input)
    {
        $snake = self::snakeize($input);
        return self::snakeToTitle($snake);
    }

    /**
     * Convert to kebap case
     *
     * @param string $input Input string
     * @return string
     */
    public static function kebapize($input)
    {
        $snake = self::snakeize($input, '-');
        return str_replace('_', '-', $snake);
    }

    /**
     * Create constant key
     *
     * @param string $input Input string
     * @return string
     */
    public function constantKey($input)
    {
        return strtoupper(self::snakeize($input, '-'));
    }

    /**
     * Check if string is starts with substring
     *
     * @param string $haystack Haystack
     * @param string $value Value
     * @param boolean $caseSensitive Flag that comparation is case sensitive
     * @return boolean
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
     * Check if string is ends with substring
     *
     * @param string $haystack Haystack
     * @param string $value Value
     * @param boolean $caseSensitive Flag that comparation is case sensitive
     * @return boolean
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
     * @param string $haystack Haystack
     * @param string $substring Substring
     * @param int $count Count
     * @return string
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
     * @param string $haystack Haystack
     * @param string $substring Substring
     * @param int $count Count
     * @return string
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
     * @param string $value Value
     * @return boolean
     */
    public static function isNotNullAndNotEmpty($value)
    {
        return isset($value) && !empty($value);
    }

    /**
     * Check if string is null or empty
     *
     * @param string $value Value
     * @return boolean
     */
    public static function isNullOrEmpty($value)
    {
        return !isset($value) || empty($value);
    }

    /**
     * Select not null value
     *
     * @param mixed $value1 Value 1
     * @param mixed $value2 Value 2
     * @return mixed
     */
    public static function selectNotNull($value1, $value2)
    {
        return isset($value1) ? $value1 : $value2;
    }

    /**
     * Fix cariage return
     *
     * @param string $input Input string
     * @return string
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