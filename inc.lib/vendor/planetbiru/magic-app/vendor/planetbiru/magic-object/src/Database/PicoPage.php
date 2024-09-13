<?php
namespace MagicObject\Database;

/**
 * Data page
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPage
{
    /**
     * Page number
     *
     * @var integer
     */
    private $pageNumber = 0;

    /**
     * Page size
     *
     * @var integer
     */
    private $pageSize = 1;

    /**
     * Page
     *
     * @param integer $pageNumber Page number
     * @param integer $pageSize Page size
     */
    public function __construct($pageNumber, $pageSize)
    {
        if($pageNumber < 1)
        {
            $pageNumber = 1;
        }
        if($pageSize < 1)
        {
            $pageSize = 1;
        }

        $pageNumber = intval($pageNumber);
        $pageSize = intval($pageSize);

        $this->setPageNumber($pageNumber);
        $this->setPageSize($pageSize);
    }

    /**
     * Increase page number
     *
     * @return self
     */
    public function nextPage()
    {
        $this->pageNumber++;
        return $this;
    }

    /**
     * Decrease page number
     *
     * @return self
     */
    public function previousPage()
    {
        if($this->pageNumber > 1)
        {
            $this->pageNumber--;
        }
        return $this;
    }

    /**
     * Get the value of pageNumber
     *
     * @return integer
     */
    public function getPageNumber()
    {
        return $this->pageNumber;
    }

    /**
     * Set the value of pageNumber
     *
     * @param integer $pageNumber Page number
     * @return self
     */
    public function setPageNumber($pageNumber)
    {
        if($pageNumber < 1)
        {
            $pageNumber = 1;
        }
        $this->pageNumber = $pageNumber;

        return $this;
    }

    /**
     * Get the value of pageSize
     *
     * @return integer
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * Set the value of pageSize
     *
     * @param integer $pageSize Page size
     * @return self
     */
    public function setPageSize($pageSize)
    {
        if($pageSize < 1)
        {
            $pageSize = 1;
        }
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * Get limit
     * @return PicoLimit
     */
    public function getLimit()
    {
        $limit = $this->getPageSize();
        if($limit < 0)
        {
            $limit = 0;
        }
        $offset = ($this->getPageNumber() - 1) * $limit;
        if($offset < 0)
        {
            $offset = 0;
        }
        return new PicoLimit($offset, $limit);
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
                'pageNumber' => $this->pageNumber,
                'pageSize' => $this->pageSize
            )
        );
    }
}