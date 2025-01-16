<?php

namespace AppBuilder\Generator\MocroServices;

/**
 * Class InputFieldOption
 *
 * Represents an individual option element for a form input, typically used in select dropdowns.
 * This class allows setting the value, label, selection state, and HTML attributes for an option.
 * It also supports adding non-standard `data-` attributes to the option element.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class InputFieldOption extends ValueLabelConstructor
{
    /**
     * The value of the option, typically submitted when the option is selected.
     *
     * @var string
     */
    protected $value;
    
    /**
     * The label displayed for the option in the user interface.
     *
     * @var string
     */
    protected $label;
    
    /**
     * Indicates whether the option is selected by default.
     *
     * @var bool
     */
    protected $selected;
    
    /**
     * Standard HTML attributes for the <option> element, such as class, id, etc.
     *
     * @var array
     */
    protected $attributes;
    
    /**
     * Non-standard HTML attributes for the <option> element that start with `data-`, 
     * allowing for additional custom data storage.
     *
     * @var array
     */
    protected $data;
}
