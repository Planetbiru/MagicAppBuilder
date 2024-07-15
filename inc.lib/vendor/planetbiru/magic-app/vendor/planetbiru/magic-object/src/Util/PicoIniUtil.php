<?php

namespace MagicObject\Util;

class PicoIniUtil
{
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
     * @param string $content
     * @param array $array
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
     * @param string $content
     * @param array $array
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
     * @param string $path
     * @return array|false
     */
    public static function parseIniFile($path) // NOSONAR
    {
        if (!file_exists($path)) {
            return false;
        }
        $str = file_get_contents($path);
        if (empty($str)) {
            return false;
        }

        $lines = explode("\n", $str);
        $ret = array();
        $inside_section = false;

        foreach ($lines as $line) {

            $line = trim($line);

            if (!$line || $line[0] == "#" || $line[0] == ";") {
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
                if (
                    PicoStringUtil::startsWith($value, '"') && PicoStringUtil::endsWith($value, '"')
                    || PicoStringUtil::startsWith($value, "'") && PicoStringUtil::endsWith($value, "'")
                ) {
                    $value = substr($value, 1, strlen($value) - 2);
                }

                if (preg_match("/^\".*\"$/", $value) || preg_match("/^'.*'$/", $value)) {
                    $value = mb_substr($value, 1, mb_strlen($value) - 2);
                }

                preg_match("^\[(.*?)\]^", $key, $matches);
                if (!empty($matches) && isset($matches[0])) {

                    $arr_name = preg_replace('#\[(.*?)\]#is', '', $key);

                    if (!isset($ret[$inside_section][$arr_name]) || !is_array($ret[$inside_section][$arr_name])) {
                        $ret[$inside_section][$arr_name] = array();
                    }

                    if (isset($matches[1]) && !empty($matches[1])) {
                        $ret[$inside_section][$arr_name][$matches[1]] = $value;
                    } else {
                        $ret[$inside_section][$arr_name][] = $value;
                    }
                } else {
                    $ret[$inside_section][trim($tmp[0])] = $value;
                }
            } else {
                $value = ltrim($tmp[1]);
                if (
                    PicoStringUtil::startsWith($value, '"') && PicoStringUtil::endsWith($value, '"')
                    || PicoStringUtil::startsWith($value, "'") && PicoStringUtil::endsWith($value, "'")
                ) {
                    $value = substr($value, 1, strlen($value) - 2);
                }
                $ret[trim($tmp[0])] = $value;
            }
        }
        return $ret;
    }
}
