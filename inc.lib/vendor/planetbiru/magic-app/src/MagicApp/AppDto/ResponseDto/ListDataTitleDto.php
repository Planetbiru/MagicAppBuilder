<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Represents the title of a column in a table row.
 * 
 * The class extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ListDataTitleDto extends ToString
{
    /**
     * The field associated with the column title.
     *
     * @var string|null
     */
    protected $field;

    /**
     * The display value of the column title.
     *
     * @var string|null
     */
    protected $label;

    /**
     * Indicates if the column is sortable.
     *
     * @var bool
     */
    protected $sortable;

    /**
     * The current sort direction (ASC/DESC).
     *
     * @var string|null
     */
    protected $currentSort;

    /**
     * Constructor to initialize the column title.
     *
     * @param string|null $field The field associated with the column title.
     * @param string|null $label The display value of the column title.
     * @param bool $sortable Indicates if the data can be sorted by this column.
     * @param string|null $currentSort The current sort direction (ASC/DESC).
     */
    public function __construct($field = null, $label = null, $sortable = false, $currentSort = null)
    {
        $this->field = $field;
        $this->label = $label;
        $this->sortable = $sortable;
        $this->currentSort = $currentSort;
    }

    /**
     * Get the field associated with the column title.
     *
     * @return string|null
     */ 
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set the field associated with the column title.
     *
     * @param string|null $field The field associated with the column title.
     * @return self The current instance, allowing method chaining
     */ 
    public function setField($field)
    {
        $this->field = $field;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the display value of the column title.
     *
     * @return string|null
     */ 
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the display value of the column title.
     *
     * @param string|null $label The display value of the column title.
     * @return self The current instance, allowing method chaining
     */ 
    public function setLabel($label)
    {
        $this->label = $label;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Check if the column is sortable.
     *
     * @return bool
     */ 
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * Set the sortable status of the column.
     *
     * @param bool $sortable Indicates if the column is sortable.
     * @return self The current instance, allowing method chaining
     */ 
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the current sort direction (ASC/DESC).
     *
     * @return string|null
     */
    public function getCurrentSort()
    {
        return $this->currentSort;
    }

    /**
     * Set the current sort direction.
     *
     * @param string|null $currentSort The current sort direction (ASC/DESC).
     * @return self The current instance, allowing method chaining
     */
    public function setCurrentSort($currentSort)
    {
        $this->currentSort = $currentSort;
        return $this; // Returns the current instance for method chaining.
    }
}
