<?php

namespace MagicObject\Database;

/**
 * Class representing a data page for pagination.
 *
 * This class provides functionality to manage page numbers and sizes,
 * and to calculate offsets for database queries.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPage
{
    /**
     * Page number.
     *
     * @var int
     */
    private $pageNumber = 1;

    /**
     * Page size (number of items per page).
     *
     * @var int
     */
    private $pageSize = 1;

    /**
     * Constructor.
     *
     * @param int $pageNumber Page number.
     * @param int $pageSize Page size.
     */
    public function __construct($pageNumber = 1, $pageSize = 1)
    {
        $this->setPageNumber(max(1, intval($pageNumber)));
        $this->setPageSize(max(1, intval($pageSize)));
    }

    /**
     * Increase the page number by one.
     *
     * @return self
     */
    public function nextPage()
    {
        $this->pageNumber++;
        return $this;
    }

    /**
     * Decrease the page number by one, ensuring it doesn't go below 1.
     *
     * @return self
     */
    public function previousPage()
    {
        if ($this->pageNumber > 1) {
            $this->pageNumber--;
        }
        return $this;
    }

    /**
     * Get the current page number.
     *
     * @return int
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Set the page number.
     *
     * @param int $pageNumber Page number.
     * @return self
     */
    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = max(1, intval($pageNumber));
        return $this;
    }

    /**
     * Get the page size (number of items per page).
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set the page size.
     *
     * @param int $pageSize Page size.
     * @return self
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = max(1, intval($pageSize));
        return $this;
    }

    /**
     * Get the limit and offset for database queries.
     *
     * @return PicoLimit
     */
    public function getLimit()
    {
        $limit = $this->getPageSize();
        $offset = ($this->getPageNumber() - 1) * $limit;
        
        return new PicoLimit(max(0, $offset), $limit);
    }

    /**
     * Magic method to return a string representation of the object.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode([
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize
        ]);
    }
}
