<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class InputFormData
 *
 * Represents a filter or form input element in a UI, typically used in a search or filter form.
 * This class stores information about the filter's configuration, such as the label, field name, 
 * input type, default value, and other attributes that define how the filter will be displayed 
 * and interacted with in the UI.
 *
 * It supports various input types such as text, number, date, datetime, select, and button, 
 * allowing flexibility in how the filter is rendered. Additionally, it provides methods for 
 * setting and retrieving the filter properties, and supports chaining of setter methods.
 *
 * Properties:
 * - `label`: The label that will be displayed next to the filter input element.
 * - `field`: The field name in the data model to which this filter corresponds.
 * - `name`: A unique identifier for the filter in the UI.
 * - `id`: A unique identifier for the filter control, used for DOM manipulation.
 * - `element`: The type of HTML input element used for the filter (e.g., "text", "select").
 * - `attribute`: An array of additional HTML attributes to apply to the filter element (e.g., class, style).
 * - `textContent`: The text label for button elements (e.g., "Apply", "Reset").
 * - `selectOption`: The select options for select input elements, represented as a `SelectOptionDto` object.
 * - `defaultValue`: The default value for the filter input, which is pre-filled when the form is loaded.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class InputFormData extends ToString
{
    /**
     * The label that will be displayed next to the filter input element.
     * This is the text that describes the purpose of the filter field.
     *
     * @var string
     */
    public $label;

    /**
     * The field name that this filter corresponds to in the data model.
     * Typically, this is the name of the property or column being filtered in the backend.
     *
     * @var string
     */
    public $field;

    /**
     * A unique name or identifier for this filter, used to reference the filter in the user interface.
     *
     * @var string
     */
    public $name;

    /**
     * The unique identifier for the filter control element, typically used for DOM element identification.
     * This is often used in JavaScript to manipulate or target the element.
     *
     * @var string
     */
    public $id;

    /**
     * The type of HTML input element used for the filter. The available types include:
     * - "text": For text input fields.
     * - "number": For numeric input fields.
     * - "date": For date input fields.
     * - "datetime": For date-time input fields.
     * - "checkbox": For checkbox input fields.
     * - "select": For drop-down select lists.
     * - "button": For button elements, typically used for applying or resetting the filter.
     *
     * @var string
     */
    public $element;

    /**
     * An associative array of HTML attributes to apply to the filter element.
     * This can include attributes like "class", "id", "style", "placeholder", and others to customize the appearance or behavior.
     *
     * @var array
     */
    public $attribute;

    /**
     * The text content for button elements, used to define the button's label (e.g., "Apply", "Reset").
     * This is applicable when the element type is "button".
     *
     * @var string
     */
    public $textContent;

    /**
     * Select option for "select" input elements, represented by a `SelectOptionDto` object.
     * This defines the options available for the user to choose from in a select input field.
     *
     * @var SelectOptionDto
     */
    public $selectOption;

    /**
     * The default value for the filter input. This is the value that will be pre-filled or selected when the form is loaded.
     *
     * @var mixed
     */
    public $defaultValue;

    // Getter and Setter Methods

    /**
     * Get the label for the filter.
     *
     * @return string The label of the filter.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label for the filter.
     *
     * @param string $label The label to set.
     * @return self Returns the current instance for chaining.
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get the field name associated with this filter.
     *
     * @return string The field name of the filter.
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set the field name associated with this filter.
     *
     * @param string $field The field name to set.
     * @return self Returns the current instance for chaining.
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Get the unique name or identifier for this filter.
     *
     * @return string The name of the filter.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the unique name or identifier for this filter.
     *
     * @param string $name The name to set.
     * @return self Returns the current instance for chaining.
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the unique identifier for the filter control element.
     *
     * @return string The ID of the filter control.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the unique identifier for the filter control element.
     *
     * @param string $id The ID to set.
     * @return self Returns the current instance for chaining.
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the type of HTML input element used for the filter.
     *
     * @return string The input element type.
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set the type of HTML input element used for the filter.
     *
     * @param string $element The element type to set.
     * @return self Returns the current instance for chaining.
     */
    public function setElement($element)
    {
        $this->element = $element;
        return $this;
    }

    /**
     * Get the HTML attributes for the filter element.
     *
     * @return array The attributes of the filter.
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set the HTML attributes for the filter element.
     *
     * @param array $attribute The attributes to set.
     * @return self Returns the current instance for chaining.
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * Get the text content for button elements.
     *
     * @return string The text content of the button (e.g., "Apply", "Reset").
     */
    public function getTextContent()
    {
        return $this->textContent;
    }

    /**
     * Set the text content for button elements.
     *
     * @param string $textContent The button text content to set.
     * @return self Returns the current instance for chaining.
     */
    public function setTextContent($textContent)
    {
        $this->textContent = $textContent;
        return $this;
    }

    /**
     * Get the select option associated with the filter.
     *
     * @return SelectOptionDto The select option.
     */
    public function getSelectOption()
    {
        return $this->selectOption;
    }

    /**
     * Set the select option for the filter.
     *
     * @param SelectOptionDto $selectOption The select option to set.
     * @return self Returns the current instance for chaining.
     */
    public function setSelectOption(SelectOptionDto $selectOption)
    {
        $this->selectOption = $selectOption;
        return $this;
    }

    /**
     * Get the default value for the filter input.
     *
     * @return mixed The default value of the filter.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set the default value for the filter input.
     *
     * @param mixed $defaultValue The default value to set.
     * @return self Returns the current instance for chaining.
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }
}
