<?php

namespace MagicObject\Database;

/**
 * Specification filter
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSpecificationFilter
{
    const DATA_TYPE_NUMBER = "number";
    const DATA_TYPE_STRING = "string";
    const DATA_TYPE_BOOLEAN = "boolean";
    const DATA_TYPE_ARRAY_NUMBER = "number[]";
    const DATA_TYPE_ARRAY_STRING = "string[]";
    const DATA_TYPE_ARRAY_BOOLEAN = "boolean[]";
    const DATA_TYPE_FULLTEXT = "fulltext";

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
     * @param string $columnName Column name
     * @param string $dataType Data type
     */
    public function __construct($columnName, $dataType)
    {
        $this->columnName = $columnName;
        $this->dataType = $dataType;
    }

    /**
     * Magic method to debug object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(array(
            'columnName' => $this->columnName,
            'dataType' => $this->dataType,
        ));
    }

    /**
     * Get value
     *
     * @param mixed $stringValue Value given
     * @return mixed
     */
    public function valueOf($stringValue)
    {
        $result = null;
        if($this->isArrayNumber())
        {
            $result = $this->getArrayNumber($stringValue);
        }
        else if($this->isArrayBoolean())
        {
            $result = $this->getArrayBoolean($stringValue);
        }
        else if($this->isNumber())
        {
            $result = $this->getNumber($stringValue);
        }
        else if($this->isBoolean())
        {
            $result = $this->getBoolean($stringValue);
        }
        else
        {
            $result = $stringValue;
        }
        return $result;
    }

    /**
     * Get number values
     *
     * @param mixed $stringValue Value given
     * @return float[]|integer[]
     */
    private function getArrayNumber($stringValue)
    {
        if(is_array($stringValue))
        {
            $values = array();
            foreach($stringValue as $value)
            {
                $values[] = $this->getNumber($value);
            }
            return $values;
        }
        else
        {
            return $stringValue;
        }
    }

    /**
     * Get boolean values
     *
     * @param mixed $stringValue Value given
     * @return boolean[]
     */
    private function getArrayBoolean($stringValue)
    {
        if(is_array($stringValue))
        {
            $values = array();
            foreach($stringValue as $value)
            {
                $values[] = $this->getBoolean($value);
            }
            return $values;
        }
        else
        {
            return $stringValue;
        }
    }

    /**
     * Get number value
     *
     * @param mixed $stringValue Value given
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
     * @param mixed $stringValue Value given
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
     * @param mixed $stringValue Value given
     * @return boolean
     */
    public function isNumber()
    {
        return $this->dataType == self::DATA_TYPE_NUMBER;
    }

    /**
     * Check if data type is a string
     *
     * @return boolean
     */
    public function isString()
    {
        return $this->dataType == self::DATA_TYPE_STRING;
    }

    /**
     * Check if data type is a boolean
     *
     * @return boolean
     */
    public function isBoolean()
    {
        return $this->dataType == self::DATA_TYPE_BOOLEAN;
    }

    /**
     * Check if data type is an array of number
     *
     * @return boolean
     */
    public function isArrayNumber()
    {
        return $this->dataType == self::DATA_TYPE_ARRAY_NUMBER;
    }

    /**
     * Check if data type is an array of string
     *
     * @return boolean
     */
    public function isArrayString()
    {
        return $this->dataType == self::DATA_TYPE_ARRAY_STRING;
    }

    /**
     * Check if data type is an array of boolean
     *
     * @return boolean
     */
    public function isArrayBoolean()
    {
        return $this->dataType == self::DATA_TYPE_ARRAY_BOOLEAN;
    }

    /**
     * Check if data type is full text
     *
     * @return boolean
     */
    public function isFulltext()
    {
        return $this->dataType == self::DATA_TYPE_FULLTEXT;
    }

    /**
     * Get column name
     *
     * @return string
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Get data type
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }
}