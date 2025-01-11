<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Represents a key-value pair with a name and associated value.
 * 
 * The `NameValueDto` class is used to store a name and its corresponding value.
 * This can be useful for handling pairs of data, such as in API responses, form submissions, 
 * or any context where a name and its associated value are required.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class NameValueDto extends ToString
{
    /**
     * The name associated with the value.
     * 
     * @var string
     */
    protected $name;
    
    /**
     * The value associated with the name.
     * 
     * @var mixed
     */
    protected $value;
    
    /**
     * Constructor to initialize the NameValueDto object.
     * 
     * @param string $name The name to be associated with the value.
     * @param mixed $value The value to be associated with the name.
     */
    public function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
}
