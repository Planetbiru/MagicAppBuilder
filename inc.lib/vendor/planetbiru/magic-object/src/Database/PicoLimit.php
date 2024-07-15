<?php

namespace MagicObject\Database;

/**
 * Limit and offset select database records
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
     * @param integer $offset
     * @param integer $limit
     */
    public function __construct($offset = 0, $limit = 0)
    {
        $this->setOffset($offset);
        $this->setLimit($limit);
    }

    /**
     * Get the value of limit
     */ 
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set the value of limit
     *
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
     */ 
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set the value of offset
     *
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