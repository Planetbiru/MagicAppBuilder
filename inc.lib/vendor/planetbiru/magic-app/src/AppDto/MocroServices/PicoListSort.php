<?php

namespace MagicApp\AppDto\MocroServices;

use MagicObject\Database\PicoSort;

/**
 * Data Transfer Object (DTO) for representing a single sort criterion.
 *
 * This class holds the field name to sort by and the direction of sorting
 * (e.g., ascending or descending), based on a {@see PicoSort} object.
 */
class PicoListSort extends PicoObjectToString
{
    /**
     * The field name to sort by.
     *
     * @var string
     */
    protected $sortBy;

    /**
     * The type of sort direction, typically 'asc' or 'desc'.
     *
     * @var string
     */
    protected $sortType;

    /**
     * Constructs a new PicoListSort from a PicoSort object.
     *
     * @param PicoSort $sort The sort definition source.
     */
    public function __construct($sort)
    {
        $this->sortBy = $sort->getSortBy();
        $this->sortType = $sort->getSortType();
    }
}
