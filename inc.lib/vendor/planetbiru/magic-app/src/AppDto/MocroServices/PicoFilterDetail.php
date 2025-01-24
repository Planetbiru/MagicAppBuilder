<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoFilterDetail
 *
 * Represents a detailed filter within the Pico service. This class extends 
 * `PicoObjectToString` and contains a `PicoInputField` instance that holds 
 * the specific input field data for this filter detail.
 *
 * The class encapsulates a `PicoInputField`, which can be accessed or 
 * manipulated to define detailed filtering criteria for a Pico object.
 * This class provides getter and setter methods for managing the input field 
 * associated with the filter.
 *
 * Methods:
 * - getField(): Retrieves the input field associated with this filter detail.
 * - setField(PicoInputField $field): Sets the input field for the filter detail.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoFilterDetail extends PicoObjectToString
{
    /**
     * The input field associated with this filter detail.
     *
     * @var PicoInputField
     */
    protected $field;

    /**
     * Get the input field associated with this filter detail.
     *
     * This method retrieves the `PicoInputField` instance that is associated 
     * with this filter detail.
     *
     * @return  PicoInputField  The input field associated with this filter detail.
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set the input field associated with this filter detail.
     *
     * This method assigns a `PicoInputField` instance to this filter detail, 
     * allowing the definition or modification of the filter criteria.
     *
     * @param  PicoInputField  $field  The input field to associate with the filter detail.
     *
     * @return  self  Returns the current instance for method chaining.
     */
    public function setField(PicoInputField $field)
    {
        $this->field = $field;

        return $this;
    }
}
