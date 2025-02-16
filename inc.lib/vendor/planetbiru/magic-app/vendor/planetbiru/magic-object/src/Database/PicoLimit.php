<?php

namespace MagicObject\Database;

/**
 * Class PicoLimit
 *
 * This class provides functionality to manage pagination in database queries 
 * by setting limits and offsets for record retrieval.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoLimit
{
    /**
     * The maximum number of records to retrieve.
     *
     * @var int
     */
    private $limit = 0;

    /**
     * The number of records to skip before starting to collect the result set.
     *
     * @var int
     */
    private $offset = 0;

    /**
     * Constructor to initialize offset and limit.
     *
     * @param int $offset The number of records to skip. Default is 0.
     * @param int $limit The maximum number of records to retrieve. Default is 0.
     */
    public function __construct($offset = 0, $limit = 0)
    {
        $this->setOffset(max(0, intval($offset)));
        $this->setLimit(max(1, intval($limit)));
    }

    /**
     * Increment the offset to retrieve the next page of records.
     *
     * This method adjusts the offset based on the current limit, allowing 
     * for the retrieval of the next set of records in a paginated result.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function nextPage()
    {
        $this->offset += $this->limit;
        return $this;
    }

    /**
     * Decrement the offset to retrieve the previous page of records.
     *
     * This method adjusts the offset back, ensuring it does not fall below 
     * zero, thus allowing navigation to the previous set of records.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function previousPage()
    {
        $this->offset = max(0, $this->offset - $this->limit);
        return $this;
    }

    /**
     * Get the maximum number of records to retrieve.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the maximum number of records to retrieve.
     *
     * This method ensures that the limit is at least 1.
     *
     * @param int $limit The maximum number of records.
     * @return self Returns the current instance for method chaining.
     */
    public function setLimit($limit)
    {
        $this->limit = max(1, intval($limit));
        return $this;
    }

    /**
     * Get the current offset for record retrieval.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the number of records to skip before starting to collect the result set.
     *
     * This method ensures that the offset is not negative.
     *
     * @param int $offset The number of records to skip.
     * @return self Returns the current instance for method chaining.
     */
    public function setOffset($offset)
    {
        $this->offset = max(0, intval($offset));
        return $this;
    }

    /**
     * Get information about the current page based on the offset and limit.
     *
     * This method calculates the current page number and returns a 
     * PicoPage object containing the page number and limit.
     *
     * @return PicoPage
     */
    public function getPage()
    {
        $limit = $this->limit > 0 ? $this->limit : 1;
        $pageNumber = max(1, round(($this->offset + $limit) / $limit));
        
        return new PicoPage($pageNumber, $limit);
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
        return json_encode(array(
            'limit' => $this->limit,
            'offset' => $this->offset
        ), JSON_PRETTY_PRINT);
    }
}
