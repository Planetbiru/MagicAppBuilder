<?php

namespace MagicObject\Util;

use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Yaml\Yaml;

/**
 * Yaml utility
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoYamlUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Get array depth
     *
     * @param array $array Array to be checked
     * @return integer
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

        foreach ($iteIte as $ite) //NOSONAR
        {
            $d = $iteIte->getDepth();
            $depth = $d > $depth ? $d : $depth;
        }

        return $depth + 1;
    }

    /**
     * Dumps a PHP value to a YAML string.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.
     *
     * @param int|null $inline The level where you switch to inline YAML. If $inline set to NULL, MagicObject will use maximum value of array depth
     * @param int $indent The amount of spaces to use for indentation of nested nodes
     * @param int $flags A bit field of DUMP_* constants to customize the dumped YAML string
     *
     * @return string A YAML string representing the original PHP value
     */
    public static function dump($input, $inline, $indent, $flags)
    {
        if($inline == null || $inline < 0)
        {
            $inline = self::arrayDepth($input);
        }
        return Yaml::dump($input, $inline, $indent, $flags);
    }
}