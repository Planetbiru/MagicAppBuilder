<?php

namespace MagicApp\AppDto\ResponseDto;

use stdClass;

/**
 * StandardClass extends the base stdClass and customizes the __toString method.
 * This class provides a custom string representation of the object, 
 * converting it into a JSON format that can be optionally pretty-printed 
 * depending on the `prettify` property.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class StandardClass extends stdClass
{
    /**
     * @var bool Determines if the JSON output should be pretty-printed.
     */
    private $prettify = false;

    /**
     * Converts the object to a string in JSON format.
     * 
     * This method overrides the default __toString method to return a JSON
     * representation of the object. If the `prettify` property is set to true, 
     * the JSON will be pretty-printed. Otherwise, it will be compact.
     * 
     * @return string The JSON-encoded string representation of the object.
     */
    public function __toString()
    {
        $flag = $this->prettify ? JSON_PRETTY_PRINT : 0;
        return json_encode($this, $flag);
    }

    /**
     * Set the value of the prettify property.
     *
     * This method controls whether the JSON output will be formatted with 
     * pretty-printing or be in compact form.
     *
     * @param bool $prettify Set to true to enable pretty-printing, false to disable.
     * @return self Returns the current object instance for method chaining.
     */
    public function setPrettify($prettify)
    {
        $this->prettify = $prettify;
        return $this;
    }
}
