<?php

/**
 * Class UserFormFilterList
 *
 * Represents a list of filters to be displayed above a data list in a user form. 
 * This class holds a collection of `InputFieldFilter` objects, each representing 
 * an individual filter field that can be applied to the data list. Filters are 
 * added to the list using the constructor or the `addFilter` method.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class UserFormFilterList
{
    /**
     * An array of filter fields to be applied to the data list.
     * Each filter is represented by an `InputFieldFilter` object.
     *
     * @var InputFieldFilter[]
     */
    protected $filters;
    
    /**
     * Constructor for initializing the filter list.
     *
     * This constructor initializes an empty filter list and optionally adds a 
     * filter if provided. The filter is an instance of the `InputFieldFilter` class.
     *
     * @param InputFieldFilter|null $filter An optional filter to add to the list.
     */
    public function __construct($filter = null)
    {
        $this->filters = [];
        if (isset($filter)) {
            $this->filters[] = $filter;
        }
    }
    
    /**
     * Add a filter to the filter list.
     *
     * This method allows for adding an `InputFieldFilter` object to the list of filters.
     *
     * @param InputFieldFilter $filter The filter to be added to the list.
     */
    public function addFilter($filter)
    {
        $this->filters[] = $filter;
    }
}