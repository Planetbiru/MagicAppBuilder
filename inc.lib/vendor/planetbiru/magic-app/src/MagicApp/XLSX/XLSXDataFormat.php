<?php

namespace MagicApp\XLSX;

use Exception;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

/**
 * Class XLSXDataFormat
 *
 * This class is responsible for defining the data format for columns in an 
 * XLSX file based on a given entity. It maps the entity's properties to 
 * Excel-compatible data types, allowing for precision settings for numeric values. 
 * It dynamically generates getter methods for accessing formatted data types 
 * based on the entity's schema.
 */
class XLSXDataFormat
{
    /**
     * Columns
     *
     * @var array
     */
    private $columns = array();

    /**
     * Precision
     * @var integer
     */
    private $precision;
    
    /**
     * Constructor
     *
     * @param MagicObject $entity The entity to map
     * @param integer|null $precision The precision for numeric values
     */
    public function __construct($entity, $precision = null)
    {
        if (isset($precision)) {
            $this->precision = $precision;
        }
        $this->columns = array();
        try {
            $tableInfo = $entity->tableInfo();
            if ($tableInfo != null && $tableInfo->getColumns() != null) {
                $columns = $tableInfo->getColumns(); 
                foreach ($columns as $propertyName => $column) {
                    $newPropertyName = PicoStringUtil::camelize($propertyName);
                    $this->columns[$newPropertyName] = $column;
                }
            }
        } catch (Exception $e) {
            // Handle exception silently
        }
    }
    
    /**
     * Magic method for dynamic getter methods
     *
     * @param string $name Method name
     * @param array $arguments Method arguments
     * @return mixed|null|void
     */
    public function __call($name, $arguments) // NOSONAR
    {
        if (substr($name, 0, 3) === 'get') {
            $newPropertyName = PicoStringUtil::camelize(substr($name, 3));
            if (isset($this->columns[$newPropertyName])) {
                $column = $this->columns[$newPropertyName];
                $type = isset($column['type']) ? $column['type'] : XLSXDataType::TYPE_STRING;
                return $this->toExcelType($type);
            }
            return $this->toExcelType(XLSXDataType::TYPE_STRING);
        } else if (substr($name, 0, 2) === 'as') {
            return strtolower(substr($name, 2));
        }
    }
    
    /**
     * Convert to Excel type
     *
     * @param string $type The type to convert
     * @return XLSXDataType
     */
    public function toExcelType($type)
    {
        return new XLSXDataType($type, $this->precision);
    }
}
