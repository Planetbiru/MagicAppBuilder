<?php

namespace MagicApp\AppDto\ResponseDto;

use MagicObject\MagicObject;
use ReflectionClass;

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
    protected $field;

    /**
     * The label for the field, typically used in user interfaces.
     *
     * @var string
     */
    protected $label;

    /**
     * The value associated with the field for display purposes.
     *
     * @var mixed
     */
    protected $value;

    /**
     * The raw value associated with the field.
     *
     * @var mixed
     */
    protected $valueRaw;

    /**
     * The data type of the field.
     *
     * @var string
     */
    protected $type;

    /**
     * Indicates whether the field is read-only.
     *
     * @var bool
     */
    protected $readonly;

    /**
     * Indicates whether the field is hidden from the user interface.
     *
     * @var bool
     */
    protected $hidden;

    /**
     * The draft value associated with the field for temporary storage.
     *
     * @var mixed
     */
    protected $valueDraft;

    /**
     * The raw draft value associated with the field.
     *
     * @var mixed
     */
    protected $valueDraftRaw;

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
    public function __construct($field, $value, $type, $label, $readonly = false, $hidden = false, $valueDraft = null)
    {
        $this->field = $field;
        if(isset($value))
        {
            $this->value = $value->getDisplay();
            if($value->getRaw() != null)
            {
                $this->valueRaw = $value->getRaw();
            }
        }
        $this->type = $type;
        $this->label = $label;
        $this->readonly = $readonly;
        $this->hidden = $hidden;
        if(isset($valueDraft))
        {
            $this->valueDraft = $valueDraft->getDisplay();
            if($valueDraft->getRaw() != null)
            {
                $this->valueDraftRaw = $valueDraft->getRaw();
            }
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
    
    /**
     * Retrieves and formats the properties of the current instance according to the specified naming strategy.
     *
     * This method extracts all properties of the current object, applies the given naming strategy
     * (such as `camelCase` or `snake_case`), and processes nested objects or arrays recursively. 
     * The result is returned as an instance of `StandardClass`, with private properties excluded from 
     * the output.
     *
     * If no naming strategy is provided, the strategy is derived from class annotations, 
     * if available, or defaults to the naming convention of the class.
     * 
     * The method also supports pretty-printing of the output if the `prettify` flag is set to `true`.
     * Nested objects and arrays will have their properties or keys recursively formatted with the 
     * specified naming strategy.
     *
     * Private properties from the current class are excluded from the formatted result, 
     * but public and protected properties will be included, even if they are inherited.
     *
     * @param string|null $namingStrategy The naming strategy to apply when formatting property names.
     *                                    If `null`, the strategy is determined based on class annotations.
     * @param bool $prettify Whether to pretty-print the resulting JSON output. Default is `false`.
     * @return StandardClass An object containing the formatted property values with the specified naming strategy.
     *                       Private properties from the current class are excluded from the result.
     */
    public function propertyValue($namingStrategy = null, $prettify = false)
    {
        // Determine the naming strategy from class annotations if not provided
        if ($namingStrategy === null) {
            $namingStrategy = $this->getPropertyNamingStrategy(get_class($this));
        }

        $properties = get_object_vars($this); // Get all properties of the instance
        $formattedProperties = new StandardClass;
        $formattedProperties->setPrettify($prettify);

        // Use ReflectionClass to inspect the current class and its properties
        $reflection = new ReflectionClass($this);
        $allProperties = $reflection->getProperties(); // Get all properties including private, protected, public

        foreach ($allProperties as $property) {
            $key = $property->getName();

            // Skip private properties of the current class
            if ($property->isPrivate() && $property->getDeclaringClass()->getName() === get_class($this)) {
                continue; // Skip this property if it's private in the current class
            }
            
            $value = $properties[$key]; // Get the value of the property

            $formattedKey = $this->convertPropertyName($key, $namingStrategy);
            if($formattedKey == $this->convertPropertyName(ConstantDto::FIELD, $namingStrategy) && isset($value) && is_string($value))
            {
                $value = $this->convertPropertyName($value, $namingStrategy);
            }

            // Handle different types of property values
            if ($value === null) {
                // Explicitly handle null values
                $formattedProperties->{$formattedKey} = null;
            } elseif ($value instanceof ToString) {
                // Recursively retrieve property values from other ToString objects
                $formattedProperties->{$formattedKey} = $value->propertyValue($namingStrategy);
            } elseif ($value instanceof MagicObject) {
                // Retrieve value from MagicObject, applying the naming strategy (snake case or camel case)
                $formattedProperties->{$formattedKey} = $value->value($namingStrategy === self::SNAKE_CASE);
            } elseif (is_array($value)) {
                // Process arrays recursively, applying the naming strategy to array keys
                $formattedProperties->{$formattedKey} = $this->processArray($value, $namingStrategy);
            } elseif (is_object($value)) {
                // Leave other objects as is (without changing naming strategy) in the result
                $formattedProperties->{$formattedKey} = $value;
            } else {
                // Handle primitive values (string, int, float, etc.)
                $formattedProperties->{$formattedKey} = $value;
            }
        }

        return $formattedProperties;
    }
}
