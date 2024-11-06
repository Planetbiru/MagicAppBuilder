<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\InvalidParameterException;
use stdClass;

/**
 * Pageable
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPageable
{
    /**
     * Current page information.
     *
     * @var PicoPage|null
     */
    private $page = null;

    /**
     * Sortable information.
     *
     * @var PicoSortable|null
     */
    private $sortable = null;

    /**
     * Offset and limit for database queries.
     *
     * @var PicoLimit|null
     */
    private $offsetLimit = null;

    /**
     * Constructor of Pageable
     * Example:
     * 1. $pageable = new Pageable(array(1, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));
     * 2. $pageable = new Pageable(new PicoPage(1, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));
     * 3. $pageable = new Pageable(array(1, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));
     * 4. $pageable = new Pageable(new PicoPage(1, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));
     *
     * @param PicoPage|PicoLimit|array|null $page Page
     * @param PicoSortable|array|null $sortable Sortable
     */
    public function __construct($page = null, $sortable = null)
    {
        if($page != null)
        {
            if($page instanceof PicoPage)
            {
                $this->setPage($page);
            }
            if($page instanceof PicoLimit)
            {
                $this->setOffsetLimit($page);
            }
            else if(is_array($page))
            {
                // create from array
                $this->page = new PicoPage($page[0], $page[1]);
            }
        }
        if($sortable != null)
        {
            if($sortable instanceof PicoSortable)
            {
                $this->sortable = $sortable;
            }
            else if(is_array($sortable))
            {
                // create from array
                $this->sortable = new PicoSortable($sortable);
            }
        }
    }


    /**
     * Retrieves the sortable information.
     *
     * @return PicoSortable|null
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * Sets the sortable information.
     *
     * @param PicoSortable $sortable Sortable information.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Adds a sortable criterion.
     *
     * @param string $sortBy The field to sort by.
     * @param string $sortType The type of sorting (e.g., 'asc' or 'desc').
     *
     * @return self Returns the current instance for method chaining.
     * @throws InvalidParameterException If $sortBy is null or empty.
     */
    public function addSortable($sortBy, $sortType)
    {
        if (!isset($sortBy) || empty($sortBy)) {
            throw new InvalidParameterException("Sort by can not be null or empty");
        }
        if($this->sortable == null)
        {
            $this->sortable = new PicoSortable();
        }
        $this->sortable->addSortable(new PicoSort($sortBy, $sortType));
        return $this;
    }

    /**
     * Creates the ORDER BY clause based on the current sortable criteria.
     *
     * @param PicoTableInfo $tableInfo Information about the table.
     * @return string|null The ORDER BY clause or null if no sortable criteria exist.
     */
    public function createOrderBy($tableInfo)
    {
        if($this->sortable != null && $this->sortable instanceof PicoSortable)
        {
            return $this->sortable->createOrderBy($tableInfo);
        }
        return null;
    }

    /**
     * Retrieves the current page information.
     *
     * @return PicoPage|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Sets the current page information.
     *
     * @param PicoPage $page Page information.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setPage($page)
    {
        $this->page = $page;
        $offset = ($page->getPageNumber()-1) * $page->getPageSize();
        $limit = $page->getPageSize();
        $this->setOffsetLimit(new PicoLimit($offset, $limit));
        return $this;
    }

    /**
     * Retrieves the offset and limit for database queries.
     *
     * @return PicoLimit|null
     */
    public function getOffsetLimit()
    {
        return $this->offsetLimit;
    }

    /**
     * Sets the offset and limit for database queries.
     *
     * @param PicoLimit $offsetLimit Offset and limit information.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setOffsetLimit($offsetLimit)
    {
        $this->offsetLimit = $offsetLimit;

        return $this;
    }

    /**
     * Convert the object to a JSON string representation for debugging.
     *
     * This method is intended for debugging purposes only and provides 
     * a JSON representation of the object's state.
     *
     * @return string The JSON representation of the object.
     */
    public function __toString()
    {
        $stdClass = new stdClass;
        $offsetLimit = new stdClass;
        $page = new stdClass;

        $offsetLimit->limit = $this->offsetLimit->getLimit();
        $offsetLimit->offset = $this->offsetLimit->getOffset();

        $page->pageNumber = $this->page->getPageNumber();
        $page->pageSize = $this->page->getPageSize();

        $stdClass->page = $page;
        $stdClass->offsetLimit = $offsetLimit;

        return json_encode($stdClass);
    }
}