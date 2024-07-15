<?php

namespace AppBuilder\Util;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoEmptyParameter;

class EntityUtil
{
    public static function getTableName($entityFile)
    {
        if(file_exists($entityFile))
        {
            $content = file_get_contents($entityFile);
            // @Table(
            $tableOffset = stripos($content, '@Table');
            if($tableOffset !== false)
            {
                // find (
                $p1 = strpos($content, "(", $tableOffset + 1);
                if($p1 !== false)
                {
                    $p2 = strpos($content, ")", $p1 + 1);
                    if($p2 !== false)
                    {
                        $len = $p2 - $p1 - 1;
                        $str = substr($content, $p1+1, $len);
                        return self::parseKeyValue($str);
                    }
                }
            }
        }
        return array();
    }

    /**
     * Parse parameters. Note that all numeric attributes will be started with underscore (_). Do not use it as is
     *
     * @param string $queryString
     * @return string[]
     * @throws InvalidQueryInputException 
     */
    public static function parseKeyValue($queryString)
    {
        if(!isset($queryString) || empty($queryString) || $queryString instanceof PicoEmptyParameter)
        {
            return array();
        }
        if(!is_string($queryString))
        {
            throw new InvalidAnnotationException("Invalid query string");
        }

        // For every modification, please test regular expression with https://regex101.com/
    
        // parse attributes with quotes
        $pattern1 = '/([_\-\w+]+)\=\"([a-zA-Z0-9\-\+ _,.\(\)\{\}\`\~\!\@\#\$\%\^\*\\\|\<\>\[\]\/&%?=:;\'\t\r\n|\r|\n]+)\"/m'; // NOSONAR
        preg_match_all($pattern1, $queryString, $matches1);
        $pair1 = array_combine($matches1[1], $matches1[2]);
        
        // parse attributes without quotes
        $pattern2 = '/([_\-\w+]+)\=([a-zA-Z0-9._]+)/m'; // NOSONAR
        preg_match_all($pattern2, $queryString, $matches2);

        $pair3 = self::combineAndMerge($matches2, $pair1);
        
        // parse attributes without any value
        $pattern3 = '/([\w\=\-\_"]+)/m'; // NOSONAR
        preg_match_all($pattern3, $queryString, $matches3);
        
        $pair4 = array();
        if(isset($matches3) && isset($matches3[0]) && is_array($matches3[0]))
        {
            $keys = array_keys($pair3);
            foreach($matches3[0] as $val)
            {
                if(self::matchArgs($keys, $val))
                {
                    if(is_numeric($val))
                    {
                        // prepend attribute with underscore due unexpected array key
                        $pair4["_".$val] = true;
                    }
                    else
                    {
                        $pair4[$val] = true;
                    }
                }
            }
        }
        
        // merge $pair3 and $pair4 into result
        return array_merge($pair3, $pair4);
    }

    /**
     * Check if argument is match
     *
     * @param array $keys
     * @param string $val
     * @return boolean
     */
    public static function matchArgs($keys, $val)
    {
        return stripos($val, '=') === false && stripos($val, '"') === false && stripos($val, "'") === false && !in_array($val, $keys);
    }

    /**
     * Combine and merge array
     *
     * @param array $matches2
     * @param array $pair
     * @return array
     */
    public static function combineAndMerge($matches2, $pair)
    {
        if(isset($matches2[1]) && isset($matches2[2]) && is_array($matches2[1]) && is_array($matches2[2]))
        {
            $pair2 = array_combine($matches2[1], $matches2[2]);
            // merge $pair and $pair2 into $pair3
            return array_merge($pair, $pair2);
        }
        else
        {
            return $pair;
        }
    }
}