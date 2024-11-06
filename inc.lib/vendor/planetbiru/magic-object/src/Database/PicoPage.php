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
     * Initializes the page number and page size.
     *
     * @param int $pageNumber Page number (default is 1).
     * @param int $pageSize Page size (default is 1).
     */
    public function __construct($pageNumber = 1, $pageSize = 1)
    {
        $this->setPageNumber(max(1, intval($pageNumber)));
        $this->setPageSize(max(1, intval($pageSize)));
    }

    /**
     * Increase the page number by one.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function nextPage()
    {
        $this->pageNumber++;
        return $this;
    }

    /**
     * Decrease the page number by one, ensuring it doesn't go below 1.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function previousPage()
    {
        if ($this->pageNumber > 1) {
            $this->pageNumber--;
        }
        return $this;
    }

    /**
     * Retrieves the current page number.
     *
     * @return int The current page number.
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Set the page number.
     *
     * @param int $pageNumber Page number.
     * @return self Returns the current instance for method chaining.
     */
    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = max(1, intval($pageNumber));
        return $this;
    }

    /**
     * Retrieves the page size (number of items per page).
     *
     * @return int The page size.
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set the page size.
     *
     * @param int $pageSize Page size.
     * @return self Returns the current instance for method chaining.
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = max(1, intval($pageSize));
        return $this;
    }

    /**
     * Calculates the limit and offset for database queries.
     *
     * @return PicoLimit An instance of PicoLimit with the calculated offset and limit.
     */
    public function getLimit()
    {
        $limit = $this->getPageSize();
        $offset = ($this->getPageNumber() - 1) * $limit;
        
        return new PicoLimit(max(0, $offset), $limit);
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
        return json_encode([
            'pageNumber' => $this->pageNumber,
            'pageSize' => $this->pageSize
        ]);
    }
}
