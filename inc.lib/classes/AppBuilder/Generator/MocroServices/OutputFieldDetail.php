<?php

namespace AppBuilder\Generator\MocroServices;

/**
 * Class OutputFieldDetail
 *
 * Represents the details of an output field in a form or data display context. 
 * This class is used to manage the field's name, label, data type, and its current value, 
 * which is useful for displaying the field in a form or outputting it in a user interface. 
 * It also handles the value associated with the field when editing or updating a record.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class OutputFieldDetail extends DataConstructor
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
     * @var InputFieldValue
     */
    protected $currentValue;
    
    /**
     * Constructor for OutputFieldDetail.
     *
     * Initializes the properties of the field, label, dataType, and currentValue.
     * 
     * @param string $field The name or identifier for the field.
     * @param string $label The label to be displayed alongside the field.
     * @param string $dataType The data type of the field (e.g., string, integer, date).
     * @param InputFieldValue|null $currentValue The current value of the field, typically used for editing or updating.
     */
    public function __construct($field, $label, $dataType = "string", $currentValue = null)
    {
        $this->field = $field;
        $this->label = $label;
        $this->dataType = $dataType;
        
        // Initialize current value if provided
        if ($currentValue !== null) {
            $this->currentValue = $currentValue;
        }
    }

    /**
     * Get the current value of the input field.
     *
     * @return InputFieldValue The current value of the input field.
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * Set the current value of the input field.
     *
     * @param InputFieldValue $currentValue The current value to set for the input field.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function setCurrentValue($currentValue)
    {
        $this->currentValue = $currentValue;

        return $this;
    }
}
