<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class SelectOptionDto
 *
 * Represents an individual option within a select input element in a form. This class holds 
 * metadata related to a selection option, including the source from which the option is derived, 
 * its group (if it's part of a grouped set of options), the field it corresponds to in the data model, 
 * and the actual `OptionDto` that contains the option's display text, value, and other properties.
 * 
 * The `SelectOptionDto` is commonly used to handle structured options that come from a dynamic
 * data source, such as an API or database, allowing for proper grouping, categorization, and 
 * representation of the option in a form element.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class SelectOptionDto extends ToString
{
    /**
     * The source from which the option is derived. This could represent a data source,
     * such as an API or a database query, that provides the available options for selection.
     * 
     * Examples of source values could be "API", "Database", or a specific data path.
     *
     * @var string
     */
    protected $source;

    /**
     * The namespace where the module is located, such as "/", "/admin", "/supervisor", etc.
     *
     * @var string
     */
    protected $namespace;

    /**
     * The field that this option corresponds to. This could be the name of the property
     * or column in the underlying data model that the option represents.
     *
     * For instance, if the option represents a country, the field could be "country_id".
     *
     * @var string
     */
    protected $field;

    /**
     * The `OptionDto` object that contains the actual option's text, value, and attributes.
     * This defines a single selection option, which typically includes properties like the 
     * option's display text and value.
     *
     * @var OptionDto
     */
    protected $option;

    /**
     * Constructor for the SelectOptionDto class.
     * 
     * Initializes the `source`, `group`, `field`, and `option` properties. If no `OptionDto`
     * is provided, an empty `OptionDto` will be created to initialize the option.
     *
     * @param string $source The source from which the option is derived (e.g., API, Database).
     * @param string $namespace The namespace where the module is located, such as "/", "/admin", "/supervisor", etc.
     * @param string $field The field associated with this option (e.g., country_id, product_id).
     * @param OptionDto $option An instance of `OptionDto` that defines the option's metadata.
     */
    public function __construct($source = '', $namespace = '', $field = '', $option = null)
    {
        $this->source = $source;
        $this->namespace = $namespace;
        $this->field = $field;
        $this->option = isset($option) ? $option : new OptionDto(); // Default to an empty OptionDto if none provided
    }

    // Getter and Setter Methods

    /**
     * Gets the source from which the option is derived.
     *
     * @return string The source of the option (e.g., API, Database).
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Sets the source from which the option is derived.
     *
     * @param string $source The source to set (e.g., API, Database).
     * @return self Returns the current instance for chaining.
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Get the namespace where the module is located.
     *
     * @return string The namespace.
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the namespace where the module is located.
     *
     * @param string $namespace The namespace to set.
     * @return self The current instance for method chaining.
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Gets the field that this option corresponds to.
     *
     * @return string The field name of the option (e.g., country_id, product_id).
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Sets the field that this option corresponds to.
     *
     * @param string $field The field name to set (e.g., country_id, product_id).
     * @return self Returns the current instance for chaining.
     */
    public function setField($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Gets the `OptionDto` object that defines the option's metadata (text, value, etc.).
     *
     * @return OptionDto The option's metadata (e.g., text, value, selected state).
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Sets the `OptionDto` object that defines the option's metadata.
     *
     * @param OptionDto $option The option metadata to set.
     * @return self Returns the current instance for chaining.
     */
    public function setOption(OptionDto $option)
    {
        $this->option = $option;
        return $this;
    }
}
