<?php

namespace AppBuilder\Module;

/**
 * Class ModuleDataUtil
 *
 * Utility class for handling and converting data within the module configuration system.
 * Currently provides helper methods to normalize boolean-like values.
 */
class ModuleDataUtil
{
    /**
     * Determines if a given value represents a "true" state.
     *
     * This method checks various representations of true, such as:
     * - The string `'1'`
     * - The string `'true'` (case-insensitive)
     * - The boolean `true`
     * - The integer `1`
     *
     * @param mixed $value The value to check for truthiness.
     * @return bool Returns true if the value is considered true, otherwise false.
     */
    public static function isTrue($value)
    {
        return (isset($value) && is_string($value) && ($value == '1' || strtolower($value) == 'true')) || 
               ($value === 1 || $value === true);
    }
}
