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
     * Page
     *
     * @var PicoPage
     */
    private $page = null;

    /**
     * Sortable
     *
     * @var PicoSortable
     */
    private $sortable = null;

    /**
     * Offset and limit
     *
     * @var PicoLimit
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
     * Get sortable
     *
     * @return PicoSortable
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * Set sortable
     *
     * @param PicoSortable $sortable Sortable
     *
     * @return self
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Add sortable
     *
     * @param string $sortBy Sort by
     * @param string $sortType Sort type
     * @return self
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
     * Create sort by
     *
     * @param PicoTableInfo $tableInfo Table information
     * @return string|null
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
     * Get page
     *
     * @return PicoPage
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set page
     *
     * @param PicoPage $page Page
     *
     * @return self
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
     * Get offset and limit
     *
     * @return PicoLimit
     */
    public function getOffsetLimit()
    {
        return $this->offsetLimit;
    }

    /**
     * Set offset and limit
     *
     * @param PicoLimit  $offsetLimit  Offset and limit
     *
     * @return self
     */
    public function setOffsetLimit($offsetLimit)
    {
        $this->offsetLimit = $offsetLimit;

        return $this;
    }

    /**
     * Magic method to debug object
     *
     * @return string
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