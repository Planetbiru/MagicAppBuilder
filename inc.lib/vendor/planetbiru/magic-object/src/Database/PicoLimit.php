<?php

namespace MagicObject\Database;

/**
 * Class for limiting and offsetting select database records.
 *
 * This class provides functionality to manage pagination in database queries
 * by setting limits and offsets.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoLimit
{
    /**
     * Limit of records to retrieve.
     *
     * @var int
     */
    private $limit = 0;

    /**
     * Offset for records to skip.
     *
     * @var int
     */
    private $offset = 0;

    /**
     * Constructor
     *
     * @param int $offset Offset
     * @param int $limit Limit
     */
    public function __construct($offset = 0, $limit = 0)
    {
        $this->setOffset(max(0, intval($offset)));
        $this->setLimit(max(1, intval($limit)));
    }

    /**
     * Increase the offset for the next page.
     *
     * @return self
     */
    public function nextPage()
    {
        $this->offset += $this->limit;
        return $this;
    }

    /**
     * Decrease the offset for the previous page.
     *
     * @return self
     */
    public function previousPage()
    {
        $this->offset = max(0, $this->offset - $this->limit);
        return $this;
    }

    /**
     * Get the limit value.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the limit value.
     *
     * @param int $limit Limit
     * @return self
     */
    public function setLimit($limit)
    {
        $this->limit = max(1, intval($limit));
        return $this;
    }

    /**
     * Get the offset value.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the offset value.
     *
     * @param int $offset Offset
     * @return self
     */
    public function setOffset($offset)
    {
        $this->offset = max(0, intval($offset));
        return $this;
    }

    /**
     * Get the current page information.
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
     * Magic method to return a string representation of the object.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode([
            'limit' => $this->limit,
            'offset' => $this->offset
        ]);
    }
}
