<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class ButtonFormData
 *
 * A Data Transfer Object (DTO) that represents the data for an HTML button element within a form.
 * This class encapsulates various attributes commonly used for button elements in HTML forms,
 * including `id`, `class`, `value`, `type`, and additional custom attributes.
 * It provides getter and setter methods for manipulating these properties, and supports method chaining.
 * This DTO can be used to structure button data for rendering forms or handling form submissions.
 *
 * Key properties:
 * - `element`: Specifies the HTML element type (e.g., 'button', 'input').
 * - `type`: Defines the button type (e.g., 'submit', 'reset', 'button').
 * - `class`: The CSS class applied to the button.
 * - `id`: The unique identifier for the button.
 * - `name`: The name attribute of the button, typically used in form submission.
 * - `value`: The value attribute of the button, typically used in form submission.
 * - `textContent`: The text content displayed inside the button.
 * - `attribute`: Additional custom attributes to be added to the button element (e.g., 'data-*' attributes).
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ButtonFormData extends ToString
{
    /**
     * The type of the form element (e.g., 'button', 'input').
     *
     * @var string
     */
    protected $element;
    
    /**
     * The type of the button (e.g., 'submit', 'reset', 'button').
     * This property can be used for input buttons, but is not currently utilized.
     *
     * @var string
     */
    protected $type;

    /**
     * The CSS class applied to the button element.
     *
     * @var string
     */
    protected $class;

    /**
     * The unique identifier for the button.
     *
     * @var string
     */
    protected $id;

    /**
     * The name of the button, typically used in form submission.
     *
     * @var string
     */
    protected $name;

    /**
     * The value of the button, typically used in form submission.
     *
     * @var string
     */
    protected $value;

    /**
     * The text content inside the button element.
     *
     * @var string
     */
    protected $textContent;

    /**
     * An associative array of additional attributes for the button element.
     *
     * @var array
     */
    protected $attribute;

    /**
     * ButtonFormData constructor.
     *
     * Initializes the properties of the ButtonFormData object. All properties can be optionally initialized 
     * by passing values for each one. Default values are provided for each parameter if none are passed.
     *
     * @param string $element      The type of the button form element (default is 'button').
     * @param string|null $type    The type of the button (e.g., 'submit', 'reset', 'button') (default is 'button').
     * @param string|null $class   The CSS class applied to the button element (default is null).
     * @param string|null $id      The unique identifier for the button element (default is null).
     * @param string|null $name    The name attribute of the button (default is null).
     * @param string|null $value   The value attribute of the button (default is null).
     * @param string|null $textContent The text content inside the button element (default is null).
     */
    public function __construct($element = 'button', $type = 'button', $class = null, $id = null, $name = null, $value = null, $textContent = null)
    {
        $this->element = $element;
        $this->type = $type;
        $this->class = $class;
        $this->id = $id;
        $this->name = $name;
        $this->value = $value;
        $this->attribute = [];
        $this->textContent = $textContent;
    }

    /**
     * Get the element type.
     *
     * @return string The type of the form element.
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set the element type.
     *
     * @param string $element The element type (e.g., 'button', 'input').
     * @return self Returns the current instance for method chaining.
     */
    public function setElement($element)
    {
        $this->element = $element;
        return $this;
    }
    
    /**
     * Get the button type (e.g., 'submit', 'reset', 'button').
     *
     * Returns the type of the button.
     *
     * @return string|null The type of the button.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the button type (e.g., 'submit', 'reset', 'button').
     *
     * Sets the type of the button (such as 'submit', 'reset', or 'button').
     * 
     * @param string $type The button type (e.g., 'submit', 'reset', 'button').
     * @return self Returns the current instance for method chaining.
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the CSS class applied to the button.
     *
     * @return string The CSS class.
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the CSS class for the button.
     *
     * @param string $class The CSS class to apply to the button.
     * @return self Returns the current instance for method chaining.
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Get the ID of the button.
     *
     * @return string The unique ID of the button.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the ID of the button.
     *
     * @param string $id The unique identifier for the button.
     * @return self Returns the current instance for method chaining.
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the name of the button.
     *
     * @return string The name attribute of the button.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the button.
     *
     * @param string $name The name of the button.
     * @return self Returns the current instance for method chaining.
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the value of the button.
     *
     * @return string The value attribute of the button.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of the button.
     *
     * @param string $value The value to set for the button.
     * @return self Returns the current instance for method chaining.
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the text content of the button.
     *
     * @return string The text content inside the button element.
     */
    public function getTextContent()
    {
        return $this->textContent;
    }

    /**
     * Set the text content of the button.
     *
     * @param string $textContent The text content inside the button element.
     * @return self Returns the current instance for method chaining.
     */
    public function setTextContent($textContent)
    {
        $this->textContent = $textContent;
        return $this;
    }
    
    /**
     * Get the additional attributes for the button.
     *
     * @return array The associative array of additional attributes.
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set the additional attributes for the button.
     *
     * @param array $attribute The associative array of attributes to set for the button.
     * @return self Returns the current instance for method chaining.
     */
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * Add a new attribute to the button.
     * If the attribute key already exists, the value will be updated.
     *
     * @param string $key   The attribute key (e.g., 'data-toggle')
     * @param string $value The attribute value (e.g., 'modal')
     * @return self Returns the current instance for method chaining.
     */
    public function addAttribute($key, $value)
    {
        $this->attribute[] = new NameValueDto($key, $value);
        return $this;
    }
}
