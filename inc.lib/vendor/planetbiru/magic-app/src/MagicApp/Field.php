<?php

namespace MagicApp;

/**
 * Class Field
 *
 * Represents a field with dynamic retrieval of values.
 */
class Field
{
    /**
     * Get an instance of Field.
     *
     * @return Field
     */
    public static function of()
    {
        return new Field();
    }

    /**
     * Get a value dynamically using property access.
     *
     * @param string $value
     * @return string
     */
    public function __get($value)
    {
        return $value;
    }
}
