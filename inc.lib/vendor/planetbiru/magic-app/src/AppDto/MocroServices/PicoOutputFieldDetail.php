<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoOutputFieldDetail
 *
 * Represents the details of an output field in a form or data display context. 
 * This class manages the field's name, label, data type, and its current value, 
 * which is useful for displaying the field in a form or outputting it in a user interface. 
 * It also handles the value associated with the field when editing or updating a record.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoOutputFieldDetail extends PicoObjectToString
{
    /**
     * The name or identifier for the field.
     *
     * @var string
     */
    protected $field;
    
    /**
     * The label to be displayed alongside the field.
     *
     * @var string
     */
    protected $label;
    
    /**
     * The data type of the field (e.g., string, integer, date).
     *
     * @var string
     */
    protected $dataType;
    
    /**
     * The current value of the field, typically used when editing or updating a record.
     *
     * @var PicoInputFieldValue|null
     */
    protected $currentValue;
    
    /**
     * PicoOutputFieldDetail constructor.
     *
     * Initializes the properties of the field, label, data type, and current value.
     * If no current value is provided, it defaults to `null`.
     *
     * @param PicoInputField $inputField The input field object that contains the field's value and label.
     * @param string $dataType The data type of the field (e.g., string, integer, date).
     * @param PicoInputFieldValue|null $currentValue The current value of the field, used for editing/updating (optional).
     */
    public function __construct($inputField, $dataType = "string", $currentValue = null)
    {
        if (isset($inputField) && $inputField instanceof PicoInputField) {
            $this->field = $inputField->getValue();
            $this->label = $inputField->getLabel();
        }
        $this->dataType = $dataType;
        
        // Initialize current value if provided
        if ($currentValue !== null) {
            $this->currentValue = $currentValue;
        }
    }

    /**
     * Get the current value of the input field.
     *
     * Returns the value of the field, which can be used when displaying or editing the field.
     *
     * @return PicoInputFieldValue|null The current value of the input field or null if not set.
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * Set the current value of the input field.
     *
     * Allows updating the current value of the field, which is useful for editing or saving data.
     *
     * @param PicoInputFieldValue $currentValue The new current value to set for the input field.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function setCurrentValue($currentValue)
    {
        $this->currentValue = $currentValue;

        return $this;
    }
}
