<?php

namespace MagicApp\AppDto\ResponseDto;

use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;

/**
 * Class PageDto
 *
 * Represents an object that handles pagination details, including the page number,
 * page size, and the data offset. This class is useful for managing pagination in 
 * collections of data. It can be initialized with various pagination-related objects 
 * such as `PicoPageable`, `PicoPage`, or another `PageDto`, or with an array containing 
 * the page number and page size.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class PageDto extends ToString
{
    /**
     * The current page number
     *
     * @var int
     */
    public $pageNumber;

    /**
     * The page size, i.e., the number of items displayed per page
     *
     * @var int
     */
    public $pageSize;

    /**
     * The data offset, which is used to calculate the starting position of data on 
     * the current page.
     *
     * @var int
     */
    public $dataOffset;

    /**
     * Constructor for the PageDto class.
     * 
     * This constructor initializes the pagination details (page number, page size, 
     * and data offset). It can accept various types of input, including:
     * - `PicoPageable` object: A paginated object that provides the current page and page size.
     * - `PicoPage` object: A simpler object that holds pagination information.
     * - `PageDto` object: An existing `PageDto` to copy pagination data from.
     * - `array`: An array where the first element is the page number and the second element is the page size.
     * 
     * If no parameter is provided, the pagination defaults to page 1 with a page size of 10.
     *
     * @param PicoPageable|PicoPage|PageDto|array|null $page An optional object or array that provides pagination 
     *                                    details (e.g., page number, page size).
     */
    public function __construct($page = null)
    {
        if (isset($page)) {
            if($page instanceof PicoPageable)
            {
                $this->pageNumber = $page->getPage()->getPageNumber();
                $this->pageSize = $page->getPage()->getPageSize();
                $this->dataOffset = ($this->pageNumber - 1) * $this->pageSize;
            }
            else if($page instanceof PicoPage)
            {
                $this->pageNumber = $page->getPageNumber();
                $this->pageSize = $page->getPageSize();
                $this->dataOffset = ($this->pageNumber - 1) * $this->pageSize;
            }
            else if($page instanceof PageDto)
            {
                $this->pageNumber = $page->getPageNumber();
                $this->pageSize = $page->getPageSize();
                $this->dataOffset = ($this->pageNumber - 1) * $this->pageSize;
            }
            else if(is_array($page))
            {
                $this->pageNumber = intval($page[0]);
                $this->pageSize = intval($page[1]);
                $this->dataOffset = ($this->pageNumber - 1) * $this->pageSize;
            }
        }
    }

    /**
     * Gets the current page number.
     *
     * @return int The current page number.
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Sets the current page number.
     *
     * @param int $pageNumber The page number to set.
     * 
     * @return self Returns the current instance for chaining.
     */
    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;

        return $this;
    }

    /**
     * Gets the page size (the number of items per page).
     *
     * @return int The page size.
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Sets the page size.
     *
     * @param int $pageSize The page size to set.
     * 
     * @return self Returns the current instance for chaining.
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * Gets the data offset (the starting position of data on the current page).
     *
     * @return int The data offset.
     */
    public function getDataOffset()
    {
        return $this->dataOffset;
    }

    /**
     * Sets the data offset.
     *
     * @param int $dataOffset The data offset to set.
     * 
     * @return self Returns the current instance for chaining.
     */
    public function setDataOffset($dataOffset)
    {
        $this->dataOffset = $dataOffset;

        return $this;
    }
}
