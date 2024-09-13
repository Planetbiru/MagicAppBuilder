<?php

namespace MagicObject\Util;

/**
 * Attribute utility
 * @link https://github.com/Planetbiru/MagicObject
 */
class AttrUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * return selected="selected" if $param1 == $param2
     *
     * @param mixed $param1 Parameter 1
     * @param mixed $param2 Parameter 2
     * @return string
     */
    public static function selected($param1, $param2)
    {
        return $param1 == $param2 ? ' selected="selected"' : '';
    }

    /**
     * return checked="checked" if $param1 == $param2
     *
     * @param mixed $param1 Parameter 1
     * @param mixed $param2 Parameter 2
     * @return string
     */
    public static function checked($param1, $param2)
    {
        return $param1 == $param2 ? ' checked="checked"' : '';
    }
}