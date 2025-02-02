<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoInputFieldOption
 *
 * Represents an individual option element for a form input, typically used in select dropdowns.
 * This class allows setting the value, label, selection state, and HTML attributes for an option.
 * It also supports adding non-standard `data-` attributes to the option element, enabling additional
 * custom data storage.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoInputFieldOption extends PicoObjectToString
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
     * @var bool|null
     */
    protected $selected;
    
    /**
     * Standard HTML attributes for the <option> element, such as class, id, etc.
     *
     * @var array|null
     */
    protected $attributes;
    
    /**
     * Non-standard HTML attributes for the <option> element that start with `data-`,
     * allowing for additional custom data storage.
     *
     * @var array|null
     */
    protected $data;

    /**
     * Factory method to create an instance of PicoInputFieldOption.
     *
     * This static method provides an alternative way to instantiate the PicoInputFieldOption class.
     * 
     * @return PicoInputFieldOption A new instance of PicoInputFieldOption.
     */
    public static function getInstance()
    {
        return new self(null, null);
    }

    /**
     * PicoInputFieldOption constructor.
     *
     * Initializes the option with a value, label, and an optional selected state.
     *
     * @param string $value The value of the option, typically submitted when the option is selected.
     * @param string $label The label displayed for the option in the user interface.
     * @param bool|null $selected Indicates whether the option is selected by default (optional).
     */
    public function __construct($value, $label, $selected = null)
    {
        $this->value = $value;
        $this->label = $label;
        $this->selected = $selected !== false ? $selected : null;
    }

    /**
     * Get the value of the option, typically submitted when the option is selected.
     *
     * @return string The value of the option.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of the option, typically submitted when the option is selected.
     *
     * @param string $value The value of the option.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setValue(string $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the label displayed for the option in the user interface.
     *
     * @return string The label of the option.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label displayed for the option in the user interface.
     *
     * @param string $label The label to display for the option.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get standard HTML attributes for the <option> element, such as class, id, etc.
     *
     * @return array|null The HTML attributes for the <option> element.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Set standard HTML attributes for the <option> element, such as class, id, etc.
     *
     * @param array|null $attributes The HTML attributes for the <option> element.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the non-standard HTML `data-` attributes for additional custom data storage.
     *
     * @return array|null The `data-` attributes for the <option> element.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the non-standard HTML `data-` attributes for additional custom data storage.
     *
     * @param array|null $data The `data-` attributes for the <option> element.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get indicates whether the option is selected by default.
     *
     * @return  bool|null
     */ 
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * Set indicates whether the option is selected by default.
     *
     * @param bool|null  $selected  Indicates whether the option is selected by default.
     *
     * @return self Returns the current instance for method chaining.
     */ 
    public function setSelected($selected)
    {
        $this->selected = $selected;

        return $this;
    }
}
