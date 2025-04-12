<?php

namespace MagicObject\Database;

/**
 * Class representing a specification filter.
 *
 * This class defines filters for columns, specifying the data type 
 * and providing methods to convert values based on the defined type.
 * 
 * @author Kamshory
 * @package MagicObject\Database
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
     * The name of the column this filter applies to.
     *
     * @var string
     */
    private $columnName;

    /**
     * The data type of the column (e.g., number, string, boolean).
     *
     * @var string
     */
    private $dataType;

    /**
     * Constructor for PicoSpecificationFilter.
     *
     * Initializes the column name and data type.
     *
     * @param string $columnName The name of the column.
     * @param string $dataType The data type of the column.
     */
    public function __construct($columnName, $dataType)
    {
        $this->columnName = $columnName;
        $this->dataType = $dataType;
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
            'columnName' => $this->columnName,
            'dataType' => $this->dataType,
        ), JSON_PRETTY_PRINT);
    }

    /**
     * Converts a given value to the appropriate type based on the filter's data type.
     *
     * @param mixed $stringValue The value to convert.
     * @return mixed The converted value, typecasted as needed.
     */
    public function valueOf($stringValue)
    {
        $result = null;
        if ($this->isArrayNumber()) {
            $result = $this->getArrayNumber($stringValue);
        } elseif ($this->isArrayBoolean()) {
            $result = $this->getArrayBoolean($stringValue);
        } elseif ($this->isNumber()) {
            $result = $this->getNumber($stringValue);
        } elseif ($this->isBoolean()) {
            $result = $this->getBoolean($stringValue);
        } else {
            $result = $stringValue; // Default to string
        }
        return $result;
    }

    /**
     * Checks if the data type represents an array.
     *
     * This method determines whether the data type contains 
     * an array-like structure based on the presence of square brackets (`[]`).
     *
     * @return bool true if the data type represents an array, false otherwise.
     */
    public function isArray()
    {
        return strpos($this->dataType, "[") !== false;
    }

    /**
     * Converts a value to an array of numbers.
     *
     * @param mixed $stringValue The value to convert.
     * @return float[]|int[] An array of numeric values.
     */
    private function getArrayNumber($stringValue)
    {
        if (is_array($stringValue)) {
            $values = array();
            foreach ($stringValue as $value) {
                $values[] = $this->getNumber($value);
            }
            return $values;
        } else {
            return $stringValue; // Return as is if not an array
        }
    }

    /**
     * Converts a value to an array of booleans.
     *
     * @param mixed $stringValue The value to convert.
     * @return bool[] An array of boolean values.
     */
    private function getArrayBoolean($stringValue)
    {
        if (is_array($stringValue)) {
            $values = array();
            foreach ($stringValue as $value) {
                $values[] = $this->getBoolean($value);
            }
            return $values;
        } else {
            return $stringValue; // Return as is if not an array
        }
    }

    /**
     * Converts a value to a number.
     *
     * @param mixed $stringValue The value to convert.
     * @return float|int The converted numeric value.
     */
    private function getNumber($stringValue)
    {
        if (strpos($stringValue, ".") !== false) {
            return floatval($stringValue);
        } else {
            return intval($stringValue);
        }
    }

    /**
     * Converts a value to a boolean.
     *
     * @param mixed $stringValue The value to convert.
     * @return bool The converted boolean value.
     */
    private function getBoolean($stringValue)
    {
        return strcasecmp($stringValue, "yes") === 0
            || strcasecmp($stringValue, "true") === 0
            || $stringValue === "1"
            || $stringValue === 1;
    }

    /**
     * Checks if the data type is a number.
     *
     * @return bool true if the data type is a number, false otherwise.
     */
    public function isNumber()
    {
        return $this->dataType === self::DATA_TYPE_NUMBER;
    }

    /**
     * Checks if the data type is a string.
     *
     * @return bool true if the data type is a string, false otherwise.
     */
    public function isString()
    {
        return $this->dataType === self::DATA_TYPE_STRING;
    }

    /**
     * Checks if the data type is a boolean.
     *
     * @return bool true if the data type is a boolean, false otherwise.
     */
    public function isBoolean()
    {
        return $this->dataType === self::DATA_TYPE_BOOLEAN;
    }

    /**
     * Checks if the data type is an array of numbers.
     *
     * @return bool true if the data type is an array of numbers, false otherwise.
     */
    public function isArrayNumber()
    {
        return $this->dataType === self::DATA_TYPE_ARRAY_NUMBER;
    }

    /**
     * Checks if the data type is an array of strings.
     *
     * @return bool true if the data type is an array of strings, false otherwise.
     */
    public function isArrayString()
    {
        return $this->dataType === self::DATA_TYPE_ARRAY_STRING;
    }

    /**
     * Checks if the data type is an array of booleans.
     *
     * @return bool true if the data type is an array of booleans, false otherwise.
     */
    public function isArrayBoolean()
    {
        return $this->dataType === self::DATA_TYPE_ARRAY_BOOLEAN;
    }

    /**
     * Checks if the data type is full text.
     *
     * @return bool true if the data type is full text, false otherwise.
     */
    public function isFulltext()
    {
        return $this->dataType === self::DATA_TYPE_FULLTEXT;
    }

    /**
     * Gets the column name of this filter.
     *
     * @return string The name of the column.
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Gets the data type of this filter.
     *
     * @return string The data type of the column.
     */
    public function getDataType()
    {
        return $this->dataType;
    }
}
