<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class DataHeader
 *
 * Represents the header information for a data table or list. This class manages the field 
 * name, its label for display purposes, and the sorting order (ASC, DESC, or null). 
 * It is used for defining the structure and sorting behavior of data headers in a user interface.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class DataHeader extends ObjectToString
{
    /**
     * The field name, which is typically used as the key for sorting or identifying the field.
     * This value can be a string or other data type depending on the field's nature.
     *
     * @var mixed
     */
    protected $value;
    
    /**
     * The label for the field, typically used for display to the user.
     * This is a human-readable string representing the field name.
     *
     * @var string
     */
    protected $label;
    
    /**
     * The sorting order for the field, which can be:
     * - `ASC` for ascending order,
     * - `DESC` for descending order,
     * - `null` if no sorting order is defined.
     *
     * @var string|null
     */
    protected $sort;

    /**
     * DataHeader constructor.
     *
     * Initializes the data header with a field name, a label, and an optional sorting order.
     *
     * @param mixed $value The field name, used as the key for sorting or identifying the field.
     * @param string $label The label for the field, typically used for display to the user.
     * @param string|null $sort The sorting order for the field (optional).
     */
    public function __construct($value, $label, $sort = null)
    {
        $this->value = $value;
        $this->label = $label;
        $this->sort = $sort;
    }

    /**
     * Get the sort order.
     *
     * This method returns the current sort order, which can be a value such as `ASC`, `DESC`, or `null`.
     *
     * @return string|null The current sort order.
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * Set the sort order.
     *
     * This method sets the sort order, which could be `ASC`, `DESC`, or any other string value indicating the sorting preference.
     *
     * @param string $sort The sort order to be set (e.g., 'ASC', 'DESC').
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }
}
