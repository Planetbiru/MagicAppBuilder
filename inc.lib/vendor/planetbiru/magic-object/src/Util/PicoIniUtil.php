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
     * @return bool true on success, false on failure.
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
        // Split the input string into lines
        $lines = explode("\n", $str);
        $ret = array(); // Initialize the result array
        $insideSection = false; // Track if we are inside a section

        // Iterate through each line of the INI string
        foreach ($lines as $line) {

            $line = trim($line); // Remove whitespace from the beginning and end

            // Skip invalid lines
            if (self::invalidLine($line)) {
                continue;
            }

            // Check for a section header
            if ($line[0] == "[" && $endIdx = strpos($line, "]")) {
                $insideSection = substr($line, 1, $endIdx - 1);
                continue;
            }

            // Skip lines without an equals sign
            if (!strpos($line, '=')) {
                continue;
            }

            // Split the line into key and value
            $tmp = explode("=", $line, 2);

            if ($insideSection) {
                // Process key-value pairs inside a section
                $key = rtrim($tmp[0]); // Trim the key
                $value = ltrim($tmp[1]); // Trim the value
                $value = self::removeSurroundingQuotes($value); // Apply any necessary value fixes
                $value = self::removeSurroundingQuotesRegex($value); // Apply additional fixes

                // Match the key format to determine if it's a sub-array
                preg_match("^\[(.*?)\]^", $key, $matches);
                if (self::matchValue($matches)) {
                    // Handle array-like keys
                    $arrName = preg_replace('#\[(.*?)\]#is', '', $key);
                    $ret = self::organizeValue($ret, $insideSection, $arrName, $matches, $value);

                } else {
                    // Standard key-value assignment
                    $ret[$insideSection][trim($tmp[0])] = $value;
                }
            } else {
                // Process key-value pairs outside of any section
                $value = ltrim($tmp[1]); // Trim the value
                $value = self::removeSurroundingQuotes($value); // Apply value fixes
                $ret[trim($tmp[0])] = $value; // Assign to the result array
            }
        }
        return $ret; // Return the final parsed array
    }

    /**
     * Check if the line is invalid (empty or a comment).
     *
     * @param string $line The line to check.
     * @return bool true if the line is invalid, false otherwise.
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
     * @return bool true if the line is invalid, false otherwise.
     */
    public static function invalidLine($line)
    {
        return !$line || $line[0] == "#" || $line[0] == ";";
    }

    /**
     * Remove surrounding quotes from a value.
     *
     * This method checks if the given value is surrounded by either double or single quotes
     * and removes those quotes if they are present.
     *
     * @param string $value The value to fix.
     * @return string The cleaned value without surrounding quotes.
     */
    public static function removeSurroundingQuotes($value)
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
     * This method checks if the given value matches the pattern of being surrounded by
     * double or single quotes and removes them if so.
     *
     * @param string $value The value to fix.
     * @return string The cleaned value without surrounding quotes.
     */
    public static function removeSurroundingQuotesRegex($value)
    {
        if (preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
            $value = mb_substr($value, 1, mb_strlen($value) - 2);
        }
        return $value;
    }

    /**
     * Fix and organize the value in the parsed result.
     *
     * This method ensures that the provided array is correctly formatted based on the
     * given parameters, handling nested structures as needed.
     *
     * @param array $ret The parsed result array to update.
     * @param string $insideSection The name of the current section.
     * @param string $arrName The name of the array key to update.
     * @param array $matches Matches found during parsing.
     * @param mixed $value The value to assign to the array.
     * @return array The updated parsed result array.
     */
    public static function organizeValue($ret, $insideSection, $arrName, $matches, $value)
    {
        if (!isset($ret[$insideSection][$arrName]) || !is_array($ret[$insideSection][$arrName])) {
            $ret[$insideSection][$arrName] = array();
        }

        if (isset($matches[1]) && !empty($matches[1])) {
            $ret[$insideSection][$arrName][$matches[1]] = $value;
        } else {
            $ret[$insideSection][$arrName][] = $value;
        }
        return $ret;
    }
}
