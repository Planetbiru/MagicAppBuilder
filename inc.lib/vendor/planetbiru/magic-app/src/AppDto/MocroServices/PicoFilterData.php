<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoFilterData
 *
 * Represents filter data associated with a specific entity within the Pico service. 
 * This class extends `PicoEntityData` and contains logic for managing a filter object 
 * that can be set or retrieved. The filter is represented by an instance of `InputFieldFilter`.
 *
 * The class provides getter and setter methods for the filter variable, allowing 
 * for easy access and manipulation of the associated filter data.
 *
 * Methods:
 * - getFilter(): Retrieves the current filter object.
 * - setFilter(InputFieldFilter $filter): Sets the filter object.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoFilterData extends PicoEntityData
{
    /**
     * The filter object associated with this Pico entity.
     *
     * @var InputFieldFilter
     */
    protected $filter;

    /**
     * Get the filter object.
     *
     * This method retrieves the current filter associated with this entity.
     *
     * @return  InputFieldFilter The current filter object
     */ 
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set the filter object.
     *
     * This method allows for setting the filter object for this entity.
     *
     * @param  InputFieldFilter  $filter The filter object to be set
     *
     * @return  self Returns the current instance for method chaining
     */ 
    public function setFilter($filter)
    {
        $this->filter = $filter;
        return $this;
    }
}
