<?php

namespace AppBuilder\Generator\MocroServices;

/**
 * Class OutputFieldApproval
 *
 * Extends the OutputFieldDetail class to include a proposed value for approval or rejection. 
 * This class is useful in scenarios where a field's value is being reviewed or moderated, 
 * allowing users to propose changes that can later be approved or rejected.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class OutputFieldApproval extends OutputFieldDetail
{
    /**
     * Draft value made by another user, awaiting approval or rejection.
     *
     * @var InputFieldValue
     */
    protected $proposedValue;
    
    /**
     * Constructor for OutputFieldDetail.
     *
     * Initializes the properties of the field, label, dataType, and currentValue.
     * 
     * @param string $field The name or identifier for the field.
     * @param string $label The label to be displayed alongside the field.
     * @param string $dataType The data type of the field (e.g., string, integer, date).
     * @param InputFieldValue|null $currentValue The current value of the field, typically used for editing or updating.
     * @param InputFieldValue|null $currentValue The proposed value of the field, typically used for approval.
     */
    public function __construct($field, $label, $dataType = "string", $currentValue = null, $proposedValue = null)
    {
        parent::__construct($field, $label, $dataType, $currentValue);
        
        // Initialize proposed value if provided
        if ($proposedValue !== null) {
            $this->proposedValue = $proposedValue;
        }
    }
}
