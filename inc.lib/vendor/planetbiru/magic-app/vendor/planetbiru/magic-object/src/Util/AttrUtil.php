<?php

namespace MagicObject\Util;

/**
 * Class AttrUtil
 * 
 * A utility class for generating HTML attribute strings based 
 * on comparisons between two parameters. This is useful for 
 * dynamically setting attributes like `selected` and `checked` 
 * in HTML forms.
 *
 * This class is intended to be used statically and cannot 
 * be instantiated.
 *
 * @package MagicObject\Util
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicObject
 */
class AttrUtil
{
    private function __construct()
    {
        // prevent object construction from outside the class
    }
    
    /**
     * Returns ' selected="selected"' if $param1 equals $param2.
     *
     * This method is useful for setting the `selected` attribute 
     * on option elements in a dropdown when the current value 
     * matches the value of the option.
     *
     * **Example:**
     * ```php
     * <?php
     * echo AttrUtil::selected($currentValue, $optionValue);
     * // Outputs: selected="selected" if they match
     * ```
     *
     * @param mixed $param1 The first parameter to compare.
     * @param mixed $param2 The second parameter to compare.
     * @return string The attribute string if matched, empty string otherwise.
     */
    public static function selected($param1, $param2)
    {
        return $param1 == $param2 ? ' selected="selected"' : '';
    }

    /**
     * Returns ' checked="checked"' if $param1 equals $param2.
     *
     * This method is useful for setting the `checked` attribute 
     * on checkbox or radio button elements when the current value 
     * matches the value of the input.
     *
     * **Example:**
     * ```php
     * <?php
     * echo AttrUtil::checked($currentValue, $inputValue);
     * // Outputs: checked="checked" if they match
     * ```
     *
     * @param mixed $param1 The first parameter to compare.
     * @param mixed $param2 The second parameter to compare.
     * @return string The attribute string if matched, empty string otherwise.
     */
    public static function checked($param1, $param2)
    {
        return $param1 == $param2 ? ' checked="checked"' : '';
    }
}