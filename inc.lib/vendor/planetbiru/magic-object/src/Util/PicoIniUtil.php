<?php

namespace MagicObject\Util;

/**
 * Ini utility
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoIniUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Write INI file
     *
     * @param array $array Array
     * @param string $path File path
     * @return boolean
     */
    public static function writeIniFile($array, $path)
    {
        $arrayMulti = false;

        # See if the array input is multidimensional.
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
     * Get INI content
     *
     * @param string $content Content
     * @param array $array Array
     * @return string
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
     * Get INI content from multiple
     *
     * @param string $content Content
     * @param array $array Array
     * @return string
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
     * Parse ini file
     *
     * @param string $path File path
     * @return array|false
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
     * Parse INI string
     *
     * @param string $str String to be parsed
     * @return array|false
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
     * check if match
     * @param array $matches Mathes
     * @return bool
     */
    public static function matchValue($matches)
    {
        return !empty($matches) && isset($matches[0]);
    }

    /**
     * Check if line is invalid
     *
     * @param string $line Line
     * @return boolean
     */
    public static function invalidLine($line)
    {
        return !$line || $line[0] == "#" || $line[0] == ";";
    }

    /**
     * Fix value
     *
     * @param string $value Value
     * @return string
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
     * Fix value
     *
     * @param string $value Value
     * @return string
     */
    public static function fixValue2($value)
    {
        if (preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
            $value = mb_substr($value, 1, mb_strlen($value) - 2);
        }
        return $value;
    }

    /**
     * Fix value
     *
     * @param array $ret Return value from previous process
     * @param string $inside_section Inside section
     * @param string $arr_name Array name
     * @param array $matches Matches
     * @param mixed $value Value
     * @return array
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
