<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class AllowedAction
 *
 * Represents an allowed action that can be performed on a field or entity. 
 * This class manages the value and label associated with the action, 
 * which can be used to define permitted operations or actions within a system.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class AllowedAction extends ValueLabelConstructor
{
    /**
     * The actual value representing the allowed action.
     * This can be a string, integer, or other data type depending on the action.
     *
     * @var mixed
     */
    protected $value;
    
    /**
     * The label associated with the allowed action, typically used for display purposes.
     * For example, "Create", "Update", "Delete", etc.
     *
     * @var string
     */
    protected $label;
    
}
