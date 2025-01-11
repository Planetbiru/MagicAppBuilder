<?php

namespace MagicApp;

/**
 * Class Field
 *
 * Represents a field with dynamic retrieval of values.
 * This class uses the magic method __get to return a string representation of a dynamic value.
 * It provides a static factory method `of()` to create an instance of the class.
 *
 * Example usage:
 * ```
 * $field = Field::of();
 * echo $field->name;  // Outputs "name"
 * ```
 */
class Field
{
    /**
     * Get an instance of Field.
     *
     * This static method returns a new instance of the Field class.
     *
     * @return Field A new instance of the Field class.
     */
    public static function of()
    {
        return new Field();
    }

    /**
     * Get a value dynamically using property access.
     *
     * This magic method is triggered when accessing an undefined or dynamic property.
     * It returns the name of the requested property as a string.
     *
     * @param string $value The name of the property being accessed.
     * @return string The name of the property.
     */
    public function __get($value)
    {
        return $value;
    }
}
