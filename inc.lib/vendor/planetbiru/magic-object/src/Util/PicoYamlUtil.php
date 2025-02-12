<?php

namespace MagicObject\Util;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
    
/**
 * Class PicoYamlUtil
 * 
 * A utility class for handling YAML operations, including 
 * converting arrays to YAML strings and determining the 
 * depth of nested arrays.
 *
 * This class is intended to be used statically and cannot 
 * be instantiated.
 *
 * @author Kamshory
 * @package MagicObject\Util
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoYamlUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Get the depth of a nested array.
     *
     * This method recursively calculates how deep the given array is.
     * 
     * **Example:**
     * ```php
     * <?php
     * $array = [1, [2, [3, 4]]];
     * $depth = PicoYamlUtil::arrayDepth($array); // Returns 3
     * ```
     *
     * @param array $array The array to be checked for depth.
     * @return int The depth of the array. Returns 0 for non-array input, 1 for empty arrays.
     */
    public static function arrayDepth($array) {
        if (!is_array($array))
        {
            return 0;
        }
        if (empty($array))
        {
            return 1;
        }
        $depth = 0;
        $iteIte = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));

        foreach ($iteIte as $ite) // NOSONAR
        {
            $d = $iteIte->getDepth();
            $depth = $d > $depth ? $d : $depth;
        }

        return $depth + 1;
    }

    /**
     * Dumps a PHP value to a YAML string.
     *
     * This method converts a PHP array (or other types) into a 
     * YAML-formatted string. It allows customization of the 
     * inline representation, indentation, and flags for 
     * serialization.
     *
     * **Example:**
     * ```php
     * <?php
     * $data = ['name' => 'John', 'age' => 30];
     * $yaml = PicoYamlUtil::dump($data, null, 4, 0);
     * ```
     *
     * @param mixed $input The PHP value to be converted to YAML. 
     *                     Can be an array, object, etc.
     * @param int|null $inline The level at which to switch to inline YAML.
     *                         If NULL, the method uses the maximum value 
     *                         of the array depth.
     * @param int $indent The number of spaces to use for indentation of nested nodes.
     * @param int $flags A bit field of DUMP_* constants to customize the dumped YAML string.
     * 
     * @return string A YAML string representing the original PHP value.
     */
    public static function dump($input, $inline, $indent, $flags) // NOSONAR
    {
        if($inline == null || $inline < 0)
        {
            $inline = self::arrayDepth($input);
        }
        $yaml = new Spicy();
        return $yaml->dump($input, $indent, 0, true);
    }

    /**
     * Parse a YAML file and return the parsed data.
     *
     * This function loads a YAML file and returns the parsed content as an array or object
     * depending on the configuration of the YAML parser.
     *
     * @param string $file The path to the YAML file that needs to be parsed.
     * 
     * @return mixed The parsed YAML data, typically as an array or object.
     * 
     * @throws Exception If the file cannot be loaded or parsed.
     */
    public static function parseFile($file)
    {
        $yaml = new Spicy();
        return $yaml->loadFile($file);
    }

    /**
     * Parse raw YAML data and return the parsed content.
     *
     * This function loads a raw YAML string and returns the parsed content as an array or object.
     *
     * @param string $rawData The raw YAML string to be parsed.
     * 
     * @return mixed The parsed YAML data, typically as an array or object.
     * 
     * @throws Exception If the YAML string cannot be parsed.
     */
    public static function parse($rawData)
    {
        $yaml = new Spicy();
        return $yaml->loadString($rawData);
    }

}