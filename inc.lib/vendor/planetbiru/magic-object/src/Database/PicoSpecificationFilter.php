<?php

namespace MagicObject\Database;

class PicoSpecificationFilter
{
    /**
     * Column name
     *
     * @var string
     */
    private $columnName;

    /**
     * Data type
     *
     * @var string
     */
    private $dataType;
    
    /**
     * Constructor
     *
     * @param string $columnName
     * @param string $dataType
     */
    public function __construct($columnName, $dataType)
    {
        $this->columnName = $columnName;
        $this->dataType = $dataType;
    }

    /**
     * Get value
     * 
     * @return mixed
     */
    public function valueOf($stringValue)
    {
        if($this->isNumber())
        {
            return $this->getNumber($stringValue);
        }
        else if($this->isBoolean())
        {
            return $this->getBoolean($stringValue);
        }
        else
        {
            return $stringValue;
        }
    }

    /**
     * Get number value
     * 
     * @return float|integer
     */
    private function getNumber($stringValue)
    {
        if(strpos($stringValue, ".") !== false)
        {
            return floatval($stringValue);
        }
        else
        {
            return intval($stringValue);
        }
    }

    /**
     * Get boolean value
     * 
     * @return boolean
     */
    private function getBoolean($stringValue)
    {
        return strcasecmp($stringValue, "yes") === 0 
        || strcasecmp($stringValue, "true") === 0 
        || $stringValue === "1" 
        || $stringValue === 1
        ;
    }

    /**
     * Check if data type is a number
     *
     * @return boolean
     */
    public function isNumber()
    {
        return $this->dataType == "number";
    }

    /**
     * Check if data type is full text
     *
     * @return boolean
     */
    public function isFulltext()
    {
        return $this->dataType == "fulltext";
    }

    /**
     * Check if data type is a string
     *
     * @return boolean
     */
    public function isString()
    {
        return $this->dataType == "string";
    }

    /**
     * Check if data type is a boolean
     *
     * @return boolean
     */
    public function isBoolean()
    {
        return $this->dataType == "boolean";
    }

    /**
     * Get column name
     *
     * @return  string
     */ 
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Get data type
     *
     * @return  string
     */ 
    public function getDataType()
    {
        return $this->dataType;
    }
}