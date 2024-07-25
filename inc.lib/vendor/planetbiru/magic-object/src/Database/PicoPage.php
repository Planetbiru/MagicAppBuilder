<?php
namespace MagicObject\Database;

use MagicObject\Exceptions\InvalidParameterException;

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
     * @param integer $pageNumber
     * @param integer $pageSize
     */
    public function __construct($pageNumber, $pageSize)
    {
        if (!is_int($pageNumber) || !is_int($pageSize)) {
            throw new InvalidParameterException("Page number and page size and must an integer");
        }
        if($pageNumber < 1)
        {
            $pageNumber = 1;
        }
        if($pageSize < 1)
        {
            $pageSize = 1;
        }
        $this->setPageNumber($pageNumber);
        $this->setPageSize($pageSize);
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
     * @param integer $pageNumber
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
     * @param integer $pageSize
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