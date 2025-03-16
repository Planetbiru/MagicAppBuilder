<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Base class to construct an object with a `value` and `label`.
 *
 * This class provides a base implementation to store a value and a label, where
 * the value can be of any type and the label is a string. The class constructor
 * accepts a required `value` and an optional `label`. If no label is provided, 
 * the `value` is used as the default label.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoValueLabelConstructor extends PicoObjectToString
{
    /**
     * The value assigned to the object.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The label associated with the value.
     *
     * @var string
     */
    protected $label;

    /**
     * Constructor for initializing the object with a value and an optional label.
     *
     * This constructor accepts a required value, and an optional label. If the label is not provided, 
     * the value is used as the label by default.
     *
     * @param mixed $value The value to be assigned to the object. This could be a string, integer, or other type.
     * @param string|null $label The label associated with the value. If not provided, the value is used as the label.
     */
    public function __construct($value = null, $label = null)
    {
        // Set the value property to the provided value
        $this->value = $value;

        // If a label is provided, use it. Otherwise, use the value as the label.
        if (isset($label)) {
            $this->label = $label;
        } else {
            $this->label = $value;
        }
    }

    /**
     * Set the value assigned to the object.
     *
     * @param mixed  $value  The value assigned to the object.
     *
     * @return self Returns the current instance for method chaining.
     */ 
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the label associated with the value.
     *
     * @return string The label associated with the value.
     */ 
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label associated with the value.
     *
     * @param string $label The label associated with the value.
     *
     * @return self Returns the current instance for method chaining.
     */ 
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}
