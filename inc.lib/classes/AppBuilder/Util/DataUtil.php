<?php

namespace AppBuilder\Util;

use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\SecretObject;
use PDO;

class DataUtil
{
    /**
     * Determines if a given value represents a "true" state.
     *
     * This method checks various representations of true (like '1', true, etc.) 
     * and returns a boolean result.
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