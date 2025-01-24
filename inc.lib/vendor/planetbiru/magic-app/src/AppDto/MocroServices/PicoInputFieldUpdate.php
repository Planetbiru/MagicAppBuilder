<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoInputFieldUpdate
 *
 * Extends the PicoInputFieldInsert class, adding functionality for managing and updating the 
 * current value of an input field. This class is useful for cases where input fields
 * need to reflect an existing value, such as in form edit scenarios.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoInputFieldUpdate extends PicoInputFieldInsert
{
    /**
     * The current value of the input field, typically used when editing or updating a record.
     *
     * @var InputFieldValue
     */
    protected $currentValue;

    /**
     * Constructor for PicoInputFieldUpdate.
     * Initializes the input field properties with provided values.
     *
     * @param InputField $inputField Input field
     * @param string $inputType The type of the input field.
     * @param string $dataType The data type of the input field.
     * @param string|null $optionSource Optional source for options (e.g., for a select dropdown).
     * @param InputFieldOption[]|null $map Optional array of available options for the input field.
     * @param string|null $pattern Optional regular expression pattern for validation.
     */
    public function __construct(
        $inputField,
        $inputType,
        $dataType,
        $optionSource = null,
        $map = null,
        $pattern = null,
        $currentValue = null
    ) {
        parent::__construct($inputField, $inputType, $dataType, $optionSource, $map, $pattern);
        $this->setCurrentValue($currentValue);
    }

    /**
     * Get the current value of the input field, typically used when editing or updating a record.
     *
     * @return InputFieldValue
     */ 
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * Set the current value of the input field, typically used when editing or updating a record.
     *
     * @param InputFieldValue  $currentValue  The current value of the input field, typically used when editing or updating a record.
     *
     * @return self Returns the current instance for method chaining.
     */ 
    public function setCurrentValue($currentValue)
    {
        $this->currentValue = $currentValue;

        return $this;
    }
}
