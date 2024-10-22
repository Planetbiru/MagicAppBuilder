<?php

namespace MagicObject\Util;

/**
 * Utility class for handling INI file operations.
 *
 * This class provides methods for reading from and writing to INI files, 
 * as well as parsing INI strings into arrays and vice versa.
 * 
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoIniUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Write an array to an INI file.
     *
     * This method converts an array into an INI format and saves it to the specified file path.
     *
     * @param array $array The array to write to the INI file.
     * @param string $path The file path where the INI file will be saved.
     * @return bool True on success, false on failure.
     */
    public static function writeIniFile($array, $path)
    {
        $arrayMulti = false;

        // Check if the input array is multidimensional.
        foreach ($array as $arrayTest) {
            if (is_array($arrayTest)) {
                $arrayMulti = true;
            }
        }

        $content = "";

        # Use categories in the INI file for multidimensional array OR use basic INI file:
        if ($arrayMulti) {
            $content = self::getContentMulti($content, $array);
        } else {
            $content = self::getContent($content, $array);
        }
        if (strlen($content) > 3) {
            file_put_contents($path, $content);
        }
        return true;
    }

    /**
     * Generate INI content from a simple array.
     *
     * @param string $content The existing content (usually empty).
     * @param array $array The array to convert to INI format.
     * @return string The formatted INI content.
     */
    private static function getContent($content, $array)
    {
        foreach ($array as $key2 => $elem2) {
            if (is_array($elem2)) {
                for ($i = 0; $i < count($elem2); $i++) {
                    $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n";
                }
            } else if ($elem2 == "") {
                $content .= $key2 . " = \n";
            } else {
                $content .= $key2 . " = \"" . $elem2 . "\"\n";
            }
        }
        return $content;
    }

    /**
     * Generate INI content from a multidimensional array.
     *
     * @param string $content The existing content (usually empty).
     * @param array $array The multidimensional array to convert to INI format.
     * @return string The formatted INI content.
     */
    private static function getContentMulti($content, $array)
    {
        foreach ($array as $key => $elem) {
            $content .= "[" . $key . "]\n";
            foreach ($elem as $key2 => $elem2) {
                if (is_array($elem2)) {
                    for ($i = 0; $i < count($elem2); $i++) {
                        $content .= $key2 . "[] = \"" . $elem2[$i] . "\"\n";
                    }
                } else if ($elem2 == "") {
                    $content .= $key2 . " = \n";
                } else {
                    $content .= $key2 . " = \"" . $elem2 . "\"\n";
                }
            }
        }
        return $content;
    }

    /**
     * Parse an INI file from the specified path.
     *
     * @param string $path The file path of the INI file to parse.
     * @return array|false The parsed INI data as an array, or false on failure.
     */
    public static function parseIniFile($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        $str = file_get_contents($path);
        if (empty($str)) {
            return false;
        }

        return self::parseIniString($str);
    }

    /**
     * Parse an INI string into an array.
     *
     * @param string $str The INI string to parse.
     * @return array|false The parsed INI data as an array, or false on failure.
     */
    public static function parseIniString($str)
    {
        $lines = explode("\n", $str);
        $ret = array();
        $inside_section = false;

        foreach ($lines as $line) {

            $line = trim($line);

            if (self::invalidLine($line)) {
                continue;
            }

            if ($line[0] == "[" && $endIdx = strpos($line, "]")) {
                $inside_section = substr($line, 1, $endIdx - 1);
                continue;
            }

            if (!strpos($line, '=')) {
                continue;
            }

            $tmp = explode("=", $line, 2);

            if ($inside_section) {

                $key = rtrim($tmp[0]);
                $value = ltrim($tmp[1]);
                $value = self::fixValue1($value);
                $value = self::fixValue2($value);
                preg_match("^\[(.*?)\]^", $key, $matches);
                if (self::matchValue($matches)) {
                    $arr_name = preg_replace('#\[(.*?)\]#is', '', $key);
                    $ret = self::fixValue3($ret, $inside_section, $arr_name, $matches, $value);

                } else {
                    $ret[$inside_section][trim($tmp[0])] = $value;
                }
            } else {
                $value = ltrim($tmp[1]);
                $value = self::fixValue1($value);
                $ret[trim($tmp[0])] = $value;
            }
        }
        return $ret;
    }

    /**
     * Check if the line is invalid (empty or a comment).
     *
     * @param string $line The line to check.
     * @return bool True if the line is invalid, false otherwise.
     */
    public static function matchValue($matches)
    {
        return !empty($matches) && isset($matches[0]);
    }

    /**
     * Check if a line is invalid.
     *
     * A line is considered invalid if it is empty or starts with a comment character (# or ;).
     *
     * @param string $line The line to check.
     * @return bool True if the line is invalid, false otherwise.
     */
    public static function invalidLine($line)
    {
        return !$line || $line[0] == "#" || $line[0] == ";";
    }

    /**
     * Remove surrounding quotes from a value.
     *
     * This method checks if the value is surrounded by double or single quotes 
     * and removes them if present.
     *
     * @param string $value The value to fix.
     * @return string The cleaned value without surrounding quotes.
     */
    public static function fixValue1($value)
    {
        if (
            PicoStringUtil::startsWith($value, '"') && PicoStringUtil::endsWith($value, '"')
            || PicoStringUtil::startsWith($value, "'") && PicoStringUtil::endsWith($value, "'")
        ) {
            $value = substr($value, 1, strlen($value) - 2);
        }
        return $value;
    }

    /**
     * Remove surrounding quotes from a value using regex.
     *
     * This method checks if the value matches the pattern of being surrounded by 
     * double or single quotes and removes them if so.
     *
     * @param string $value The value to fix.
     * @return string The cleaned value without surrounding quotes.
     */
    public static function fixValue2($value)
    {
        if (preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
            $value = mb_substr($value, 1, mb_strlen($value) - 2);
        }
        return $value;
    }

    /**
     * Fix and organize the value in the parsed result.
     *
     * This method ensures that the given array is correctly formatted 
     * based on the provided parameters, handling nested structures.
     *
     * @param array $ret The parsed result array to update.
     * @param string $inside_section The current section name.
     * @param string $arr_name The name of the array key.
     * @param array $matches Matches found during parsing.
     * @param mixed $value The value to assign.
     * @return array The updated parsed result array.
     */
    public static function fixValue3($ret, $inside_section, $arr_name, $matches, $value)
    {
        if (!isset($ret[$inside_section][$arr_name]) || !is_array($ret[$inside_section][$arr_name])) {
            $ret[$inside_section][$arr_name] = array();
        }

        if (isset($matches[1]) && !empty($matches[1])) {
            $ret[$inside_section][$arr_name][$matches[1]] = $value;
        } else {
            $ret[$inside_section][$arr_name][] = $value;
        }
        return $ret;
    }
}
