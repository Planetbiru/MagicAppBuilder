<?php

namespace MagicObject\Util;

class AttrUtil
{
    /**
     * return selected="selected" if $param1 == $param2 
     *
     * @param mixed $param1
     * @param mixed $param2
     * @return string
     */
    public static function selected($param1, $param2)
    {
        return $param1 == $param2 ? ' selected="selected"' : '';
    }
    
    /**
     * return checked="checked" if $param1 == $param2 
     *
     * @param mixed $param1
     * @param mixed $param2
     * @return string
     */
    public static function checked($param1, $param2)
    {
        return $param1 == $param2 ? ' checked="checked"' : '';
    }
}