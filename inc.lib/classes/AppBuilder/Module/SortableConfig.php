<?php

namespace AppBuilder\Module;

class SortableConfig {
    /**
     * The column to sort by.
     *
     * @var string
     */
    public $sortBy;

    /**
     * The sort order (e.g., ASC, DESC).
     *
     * @var string
     */
    public $sortType;

    /**
     * SortableConfig constructor.
     *
     * @param array $data Configuration data for sorting.
     */
    public function __construct($data) {
        $this->sortBy = $data['sortBy'];
        $this->sortType = $data['sortType'];
    }
}