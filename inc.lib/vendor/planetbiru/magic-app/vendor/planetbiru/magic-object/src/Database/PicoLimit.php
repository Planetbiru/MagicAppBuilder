<?php

namespace MagicObject\Database;

/**
 * Limit and offset select database records
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoLimit
{
    /**
     * Limit
     *
     * @var integer
     */
    private $limit = 0;

    /**
     * Offset
     *
     * @var integer
     */
    private $offset = 0;

    /**
     * Constructor
     *
     * @param integer $offset Offset
     * @param integer $limit Limit
     */
    public function __construct($offset = 0, $limit = 0)
    {
        if($offset < 0)
        {
            $offset = 0;
        }
        if($limit < 1)
        {
            $limit = 1;
        }

        $offset = intval($offset);
        $limit = intval($limit);

        $this->setOffset($offset);
        $this->setLimit($limit);
    }

    /**
     * Increase page number
     *
     * @return self
     */
    public function nextPage()
    {
        $this->offset += $this->limit;
        return $this;
    }

    /**
     * Decrease page number
     *
     * @return self
     */
    public function previousPage()
    {
        if($this->offset > 1)
        {
            $this->offset -= $this->limit;
        }
        if($this->offset < 0)
        {
            $this->offset = 0;
        }
        return $this;
    }

    /**
     * Get the value of limit
     *
     * @return integer
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the value of limit
     *
     * @param integer $limit Limit
     * @return self
     */
    public function setLimit($limit)
    {
        if($limit < 0)
        {
            $limit = 0;
        }
        $this->limit = $limit;
        return $this;
    }

    /**
     * Get the value of offset
     *
     * @return integer
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the value of offset
     *
     * @param integer $offset Offset
     * @return self
     */
    public function setOffset($offset)
    {
        if($offset < 0)
        {
            $offset = 0;
        }
        $this->offset = $offset;
        return $this;
    }

    /**
     * Get page
     *
     * @return PicoPage
     */
    public function getPage()
    {
        $limit = $this->limit;
        $offset = $this->offset;
        if($limit <= 0)
        {
            $limit = 1;
        }
        if($limit > 0)
        {
            $pageNumber = round(($offset + $limit) / $limit);
            if($pageNumber < 1)
            {
                $pageNumber = 1;
            }
        }
        else
        {
            $pageNumber = 1;
        }
        return new PicoPage($pageNumber, $limit);
    }

    /**
     * Magic method to debug object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(
            array(
                'limit' => $this->limit,
                'offset' => $this->offset
            )
        );
    }
}