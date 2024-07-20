<?php

namespace MagicApp;

class Field
{

    /**
     * Get instance
     *
     * @return Field
     */
    public static function of()
    {
        return new Field();
    }

    /**
     * Get string from method
     *
     * @param string $value
     * @return string
     */
    public function __get($value)
    {
        return $value;
    }
}