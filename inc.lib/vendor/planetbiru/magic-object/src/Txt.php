<?php

namespace MagicObject;

/**
 * Class Txt
 *
 * A utility class that provides dynamic handling of static method calls.
 * This class allows for flexible interaction by returning the names of 
 * methods that are called statically but are not explicitly defined within 
 * the class. It can be useful for implementing dynamic behavior or 
 * creating a fluent interface.
 */
class Txt
{
    /**
     * Handles static calls to undefined methods.
     *
     * This method returns the name of the method being called.
     *
     * @param string $name The name of the method being called.
     * @param array $arguments An array of arguments passed to the method.
     * @return string|null The name of the called method, or null if no arguments are provided.
     */
    public static function __callStatic($name, $arguments)
    {
        return $name;
    }
}
