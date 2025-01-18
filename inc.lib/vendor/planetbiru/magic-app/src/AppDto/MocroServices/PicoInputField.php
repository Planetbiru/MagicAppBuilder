<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoInputField
 *
 * This class represents an input field, which includes a value and a label. It is used to model 
 * the structure of form fields or input elements, where each field has a value (which can be of any type)
 * and a label (which is a string for display to the user).
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoInputField extends PicoValueLabelConstructor
{
    /**
     * The value associated with the input field.
     *
     * This can be of any type, depending on the context of the field (e.g., string, integer, etc.).
     *
     * @var mixed
     */
    protected $value;
    
    /**
     * The label associated with the input field.
     *
     * This is a string used for displaying a label to the user for the input field.
     *
     * @var string
     */
    protected $label;

    /**
     * Get this can be of any type, depending on the context of the field (e.g., string, integer, etc.).
     *
     * @return mixed
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set this can be of any type, depending on the context of the field (e.g., string, integer, etc.).
     *
     * @param mixed $value This can be of any type, depending on the context of the field (e.g., string, integer, etc.).
     *
     * @return self
     */ 
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get this is a string used for displaying a label to the user for the input field.
     *
     * @return string
     */ 
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set this is a string used for displaying a label to the user for the input field.
     *
     * @param string $label This is a string used for displaying a label to the user for the input field.
     *
     * @return self
     */ 
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }
}
