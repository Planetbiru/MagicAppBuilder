<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoInputFieldApproval
 *
 * Extends the PicoInputFieldInsert class, adding functionality for managing and updating the 
 * current value of an input field. This class is useful for cases where input fields
 * need to reflect an existing value, such as in form edit scenarios.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoInputFieldApproval extends PicoInputFieldInsert
{
    /**
     * The current value of the input field, typically used when editing or updating a record.
     *
     * @var PicoInputField
     */
    protected $currentValue;

    /**
     * The proposed value of the input field.
     *
     * @var PicoInputField
     */
    protected $proposedValue;

    /**
     * Constructor for PicoInputFieldApproval.
     * Initializes the input field properties with provided values.
     *
     * @param PicoInputField $inputField Input field
     * @param string $inputType The type of the input field.
     * @param string $dataType The data type of the input field.
     * @param string|null $optionSource Optional source for options (e.g., for a select dropdown).
     * @param InputFieldOption[]|null $map Optional array of available options for the input field.
     * @param PicoInputField $currentValue Current value
     * @param PicoInputField $proposedValue Proposed value
     */
    public function __construct(
        $inputField,
        $inputType,
        $dataType,
        $optionSource = null,
        $map = null,
        $currentValue = null,
        $proposedValue = null
    ) {
        parent::__construct($inputField, $inputType, $dataType, $optionSource, $map);
        $this->setCurrentValue($currentValue);
        $this->setProposedValue($proposedValue);
    }

    

    /**
     * Get the current value of the input field, typically used when editing or updating a record.
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    /**
     * Set the current value of the input field, typically used when editing or updating a record.
     */
    public function setCurrentValue($currentValue)
    {
        $this->currentValue = $currentValue;

        return $this;
    }

    /**
     * Get the proposed value of the input field, typically used when editing or updating a record.
     */
    public function getProposedValue()
    {
        return $this->proposedValue;
    }

    /**
     * Set the proposed value of the input field, typically used when editing or updating a record.
     */
    public function setProposedValue($proposedValue)
    {
        $this->proposedValue = $proposedValue;

        return $this;
    }
}
