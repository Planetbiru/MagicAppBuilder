<?php

namespace MagicApp\AppDto\MocroServices;

use MagicApp\AppLabelValueData;

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
     * @return mixed This can be of any type, depending on the context of the field (e.g., string, integer, etc.).
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
     * @return self Returns the current instance for method chaining.
     */ 
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get this is a string used for displaying a label to the user for the input field.
     *
     * @return string This is a string used for displaying a label to the user for the input field.
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
     * @return self Returns the current instance for method chaining.
     */ 
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Retrieves the selected value from a list of options.
     *
     * This method iterates over an array of AppLabelValueData objects and 
     * returns a new instance of PicoInputField with the value and label 
     * of the selected option.
     *
     * @param AppLabelValueData[] $options An array of AppLabelValueData objects.
     *
     * @return PicoInputField|null Returns a new PicoInputField instance for the selected option, or null if none is selected.
     */
    public static function getSelectedValue($options)
    {
        if (isset($options) && is_array($options)) {
            foreach ($options as $option) {
                if ($option->selected) {
                    return new PicoInputField($option->value, $option->label);
                }
            }
        }
        return null;
    }
}
