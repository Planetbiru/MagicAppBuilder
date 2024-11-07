<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class that defines constants used throughout the application.
 *
 * The `ConstantDto` class serves as a container for commonly used string constants that
 * are used across the application. These constants can represent field names, keys, 
 * or other values that need to be consistent throughout the codebase.
 *
 * This pattern ensures that string literals are centralized and avoids the use of "magic strings"
 * in the application code, providing easier maintenance and reducing the risk of typos or inconsistencies.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ConstantDto
{
    /**
     * Constant for the "name" key.
     * Used to refer to the name field or property.
     * 
     * @var string
     */
    const NAME = "name";
    
    /**
     * Constant for the "value" key.
     * Used to refer to the value field or property.
     * 
     * @var string
     */
    const VALUE = "value";
    
    /**
     * Constant for the "field" key.
     * Used to refer to a generic field or a placeholder for other types of fields.
     * 
     * @var string
     */
    const FIELD = "field";
}
