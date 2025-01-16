<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class InputFieldValue
 *
 * Represents the value of an input field along with its display label. 
 * This class is used to manage and store the actual value of a form field 
 * (such as the value saved to a database) and its corresponding label 
 * (which is typically used for displaying the value in the user interface).
 *
 * @package AppBuilder\Generator\MocroServices
 */
class InputFieldValue extends ValueLabelConstructor
{
    /**
     * The actual value saved to the database.
     *
     * @var mixed
     */
    protected $value;
    
    /**
     * The label associated with the actual value, typically used for display purposes.
     *
     * @var string
     */
    protected $label;
}
