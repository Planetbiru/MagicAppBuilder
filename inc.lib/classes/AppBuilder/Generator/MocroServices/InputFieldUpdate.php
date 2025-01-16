<?php

namespace AppBuilder\Generator\MocroServices;

/**
 * Class InputFieldUpdate
 *
 * Extends the InputFieldInsert class, adding functionality for managing and updating the 
 * current value of an input field. This class is useful for cases where input fields
 * need to reflect an existing value, such as in form edit scenarios.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class InputFieldUpdate extends InputFieldInsert
{
    /**
     * The current value of the input field, typically used when editing or updating a record.
     *
     * @var InputFieldValue
     */
    protected $currentValue;
}
