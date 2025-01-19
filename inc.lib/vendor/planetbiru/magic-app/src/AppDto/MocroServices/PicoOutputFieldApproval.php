<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoOutputFieldApproval
 *
 * Extends the OutputFieldDetail class to include a proposed value for approval or rejection. 
 * This class is useful in scenarios where a field's value is being reviewed or moderated, 
 * allowing users to propose changes that can later be approved or rejected.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoOutputFieldApproval extends PicoOutputFieldDetail
{
    /**
     * Draft value made by another user, awaiting approval or rejection.
     *
     * @var InputFieldValue|null
     */
    protected $proposedValue;
    
    /**
     * PicoOutputFieldApproval constructor.
     *
     * Initializes the properties of the field, label, data type, current value, and proposed value.
     * This constructor ensures that the proposed value (if provided) is assigned to the appropriate property.
     * 
     * @param InputField $inputField The input field object containing the field's value and label.
     * @param string $dataType The data type of the field (e.g., string, integer, date).
     * @param InputFieldValue|null $currentValue The current value of the field, typically used for editing or updating.
     * @param InputFieldValue|null $proposedValue The proposed value of the field, typically used for approval (optional).
     */
    public function __construct($inputField, $dataType = "string", $currentValue = null, $proposedValue = null)
    {
        parent::__construct($inputField, $dataType, $currentValue);
        
        // Initialize proposed value if provided
        if ($proposedValue !== null) {
            $this->proposedValue = $proposedValue;
        }
    }
}
