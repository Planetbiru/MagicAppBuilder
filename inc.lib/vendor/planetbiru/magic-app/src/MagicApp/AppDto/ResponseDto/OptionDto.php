<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class OptionDto
 *
 * Represents an individual option within a form element, such as a dropdown, select input,
 * or similar selection component. This class stores the metadata for each option, including 
 * the text displayed to the user, the value associated with the option, whether the option 
 * is selected by default, the group to which the option belongs, and any additional HTML 
 * attributes that can be applied to the option element.
 * 
 * This is useful for dynamically generating form controls that require configurable options 
 * with custom attributes, such as dropdown menus or radio buttons.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class OptionDto extends ToString
{
    /**
     * The text displayed for the option. This is typically the label that the user will see in the UI.
     * For example, "Yes", "No", "Option 1", or "Select Country".
     *
     * @var string
     */
    public $text;

    /**
     * The value associated with the option. This value is typically sent as part of the form data 
     * when the user selects this option. It could represent an ID, code, or any other identifier 
     * related to the option.
     *
     * @var string
     */
    public $value;

    /**
     * The group name to which the option belongs. This could be used to categorize or group options.
     * For example, grouping options for a specific category like 'Countries' or 'Product Types'.
     * 
     * @var string
     */
    public $group;

    /**
     * Indicates whether the option is selected by default. If set to `true`, the option will be
     * pre-selected when the form is rendered. If set to `false`, the option will not be selected by default.
     *
     * @var bool
     */
    public $selected;

    /**
     * An associative array of HTML attributes for the option element. This allows for customization 
     * of the appearance or behavior of the option in the rendered HTML, such as adding a `disabled`, 
     * `class`, or `id` attribute.
     * 
     * @var array
     */
    public $attribute;

    /**
     * Constructor for the OptionDto class.
     * 
     * Initializes the `text`, `value`, `selected`, `group`, and `attribute` properties. By default, 
     * the `selected` flag is set to `false`, and `attribute` is an empty array.
     *
     * @param string $text The text to display for the option (e.g., "Yes", "No").
     * @param string $value The value to associate with the option (e.g., "1", "0").
     * @param bool $selected Whether the option is selected by default. Defaults to `false`.
     * @param string $group The group name to which this option belongs (e.g., 'Countries', 'Payment Methods').
     * @param array $attribute Additional HTML attributes for the option element (e.g., `class`, `disabled`).
     */
    public function __construct($text = '', $value = '', $selected = false, $group = '', $attribute = [])
    {
        $this->text = $text;
        $this->value = $value;
        $this->selected = $selected;
        $this->group = $group;
        $this->attribute = $attribute;
    }

    // Getter and Setter Methods

    /**
     * Gets the text to display for the option.
     *
     * @return string The option text.
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Sets the text to display for the option.
     *
     * @param string $text The option text to set.
     * @return self Returns the current instance for chaining.
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * Gets the value associated with the option.
     *
     * @return string The option value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value associated with the option.
     *
     * @param string $value The option value to set.
     * @return self Returns the current instance for chaining.
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Gets whether the option is selected by default.
     *
     * @return bool `true` if the option is selected by default, otherwise `false`.
     */
    public function isSelected()
    {
        return $this->selected;
    }

    /**
     * Sets whether the option should be selected by default.
     *
     * @param bool $selected Whether the option is selected by default.
     * @return self Returns the current instance for chaining.
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;
        return $this;
    }

    /**
     * Gets the group to which this option belongs.
     *
     * @return string The group name of the option.
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Sets the group to which this option belongs.
     *
     * @param string $group The group name to set.
     * @return self Returns the current instance for chaining.
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Gets the array of HTML attributes for the option element.
     *
     * @return array The option's HTML attributes (e.g., class, disabled).
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Sets the array of HTML attributes for the option element.
     *
     * @param array $attribute The attributes to set.
     * @return self Returns the current instance for chaining.
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }
}
