<?php

namespace AppBuilder\Module;

/**
 * Class SortableConfig
 *
 * Represents configuration for sorting data within a module. 
 * Specifies the column to sort by and the sort direction (e.g., ASC or DESC).
 */
class SortableConfig {
    /**
     * The column name to sort by.
     *
     * @var string
     */
    public $sortBy;

    /**
     * The sort direction (e.g., "ASC" for ascending or "DESC" for descending).
     *
     * @var string
     */
    public $sortType;

    /**
     * SortableConfig constructor.
     *
     * Initializes the sortable configuration using the provided array.
     *
     * @param array $data Configuration data for sorting.
     */
    public function __construct($data) {
        $this->sortBy = $data['sortBy'];
        $this->sortType = $data['sortType'];
    }
}
