<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Data Transfer Object (DTO) representing a column in a tabular data structure.
 * 
 * The ColumnDataDto class encapsulates the properties of a column, including its field name,
 * associated values, data types, and display characteristics. It is designed to facilitate
 * the representation and manipulation of data within a column context, allowing for 
 * additional features such as read-only status and visibility control.
 * 
 * The class extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ColumnDataDto extends ToString
{
    /**
     * The name of the field.
     *
     * @var string
     */
    public $field;

    /**
     * The label for the field, typically used in user interfaces.
     *
     * @var string
     */
    public $label;

    /**
     * The value associated with the field for display purposes.
     *
     * @var mixed
     */
    public $value;

    /**
     * The raw value associated with the field.
     *
     * @var mixed
     */
    public $valueRaw;

    /**
     * The data type of the field.
     *
     * @var string
     */
    public $type;

    /**
     * Indicates whether the field is read-only.
     *
     * @var bool
     */
    public $readonly;

    /**
     * Indicates whether the field is hidden from the user interface.
     *
     * @var bool
     */
    public $hidden;

    /**
     * The draft value associated with the field for temporary storage.
     *
     * @var mixed
     */
    public $valueDraft;

    /**
     * The raw draft value associated with the field.
     *
     * @var mixed
     */
    public $valueDraftRaw;

    /**
     * Constructor to initialize properties of the ColumnDataDto class.
     *
     * @param string $field The name of the field.
     * @param ValueDto $value The value associated with the field.
     * @param string $type The data type of the field.
     * @param string $label The label for the field.
     * @param bool $readonly Indicates if the field is read-only.
     * @param bool $hidden Indicates if the field is hidden.
     * @param ValueDto $valueDraft The draft value associated with the field.
     */
    public function __construct($field, $value, $type, $label, $readonly, $hidden, $valueDraft)
    {
        $this->field = $field;
        $this->value = $value->getDisplay();
        if($value->getRaw() != null)
        {
            $this->valueRaw = $value->getRaw();
        }
        $this->type = $type;
        $this->label = $label;
        $this->readonly = $readonly;
        $this->hidden = $hidden;
        $this->valueDraft = $valueDraft->getDisplay();
        if($valueDraft->getRaw() != null)
        {
            $this->valueDraftRaw = $valueDraft->getRaw();
        }
    }

    /**
     * Get the name of the field.
     *
     * @return string The name of the field.
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set the name of the field and return the current instance for method chaining.
     *
     * @param string $field The name of the field.
     * @return self The current instance for method chaining.
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the value associated with the field.
     *
     * @return mixed The value associated with the field.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value associated with the field and return the current instance for method chaining.
     *
     * @param mixed $value The value to associate with the field.
     * @return self The current instance for method chaining.
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the type of the field.
     *
     * @return string The type of the field.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the type of the field and return the current instance for method chaining.
     *
     * @param string $type The type to set for the field.
     * @return self The current instance for method chaining.
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the label for the field.
     *
     * @return string The label for the field.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label for the field and return the current instance for method chaining.
     *
     * @param string $label The label to set for the field.
     * @return self The current instance for method chaining.
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Check if the field is read-only.
     *
     * @return bool True if the field is read-only, otherwise false.
     */
    public function isReadonly()
    {
        return $this->readonly;
    }

    /**
     * Set the read-only status of the field and return the current instance for method chaining.
     *
     * @param bool $readonly Indicates if the field should be read-only.
     * @return self The current instance for method chaining.
     */
    public function setReadonly($readonly)
    {
        $this->readonly = $readonly;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Check if the field is hidden.
     *
     * @return bool True if the field is hidden, otherwise false.
     */
    public function isHidden()
    {
        return $this->hidden;
    }

    /**
     * Set the hidden status of the field and return the current instance for method chaining.
     *
     * @param bool $hidden Indicates if the field should be hidden.
     * @return self The current instance for method chaining.
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the draft value associated with the field.
     *
     * @return mixed The draft value associated with the field.
     */
    public function getValueDraft()
    {
        return $this->valueDraft;
    }

    /**
     * Set the draft value associated with the field and return the current instance for method chaining.
     *
     * @param mixed $valueDraft The draft value to associate with the field.
     * @return self The current instance for method chaining.
     */
    public function setValueDraft($valueDraft)
    {
        $this->valueDraft = $valueDraft;
        return $this; // Return current instance for method chaining.
    }
}
