<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class ListFilterControl
 *
 * Represents a filter control element for use in a list filter UI. This class holds the
 * metadata and attributes for the filter input element, including its label, field name, 
 * and the associated HTML attributes and text content.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ListFilterControl extends ToString
{
    /**
     * The label that will be displayed next to the filter input element.
     *
     * @var string
     */
    protected $label;

    /**
     * The field name that this filter corresponds to in the data model.
     * This is typically the name of the property or column being filtered.
     *
     * @var string
     */
    protected $field;

    /**
     * A unique name or identifier for this filter, used to reference the filter in the UI.
     *
     * @var string
     */
    protected $name;

    /**
     * The unique identifier for the filter control, typically used for DOM element identification.
     *
     * @var string
     */
    protected $id;

    /**
     * The type of HTML input element used for the filter. This can be one of the following types:
     * - "text": For text input fields.
     * - "number": For numeric input fields.
     * - "date": For date input fields.
     * - "datetime": For date-time input fields.
     * - "checkbox": For checkbox input fields.
     * - "select": For drop-down select lists.
     * - "button": For button elements, typically used for submitting or resetting the filter.
     *
     * @var string
     */
    protected $element;

    /**
     * An associative array of HTML attributes to apply to the filter element. 
     * This can include attributes like "class", "id", "style", "placeholder", etc.
     *
     * @var array
     */
    protected $attribute;

    /**
     * The text content for button elements, used to define the button's label (e.g., "Apply", "Reset").
     *
     * @var string
     */
    protected $textContent;

    /**
     * Select option
     *
     * @var SelectOptionDto
     */
    protected $selectOption;

    // Getter and Setter Methods

    /**
     * Gets the label of the filter input element.
     *
     * @return string The label.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the label of the filter input element.
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
     * Gets the field name associated with the filter.
     *
     * @return string The field name.
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Sets the field name associated with the filter.
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
     * Gets the unique name for the filter.
     *
     * @return string The unique name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the unique name for the filter.
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
     * Gets the unique identifier for the filter control.
     *
     * @return string The ID.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the unique identifier for the filter control.
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
     * Gets the type of HTML element used for the filter input.
     *
     * @return string The input element type (e.g., "text", "number").
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Sets the type of HTML element used for the filter input.
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
     * Gets the HTML attributes associated with the filter element.
     *
     * @return array The array of HTML attributes.
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Sets the HTML attributes associated with the filter element.
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
     * Gets the text content for button elements.
     *
     * @return string The text content for button elements.
     */
    public function getTextContent()
    {
        return $this->textContent;
    }

    /**
     * Sets the text content for button elements.
     *
     * @param string $textContent The text content to set.
     * @return self Returns the current instance for chaining.
     */
    public function setTextContent($textContent)
    {
        $this->textContent = $textContent;
        return $this;
    }
}
