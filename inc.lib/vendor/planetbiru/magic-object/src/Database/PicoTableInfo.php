<?php

namespace MagicObject\Database;

use stdClass;

/**
 * Table info
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoTableInfo
{
    /**
     * Table name
     *
     * @var string
     */
    protected $tableName = null;

    /**
     * Columns
     *
     * @var array
     */
    protected $columns = array();

    /**
     * Join columns
     *
     * @var array
     */
    protected $joinColumns = array();

    /**
     * Primary keys
     *
     * @var array
     */
    protected $primaryKeys = array();

    /**
     * Auto increment keys
     *
     * @var array
     */
    protected $autoIncrementKeys = array();

    /**
     * Default value keys
     *
     * @var array
     */
    protected $defaultValue = array();

    /**
     * Not null columns
     *
     * @var array
     */
    protected $notNullColumns = array();

    /**
     * Column type
     *
     * @var string
     */
    protected $columnType;

    /**
     * Get instance
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self(null, array(), array(), array(), array(), array(), array());
    }

    /**
     * Constructor
     *
     * @param string $tableName Table name
     * @param array $columns Columns
     * @param array $joinColumns Join columns
     * @param array $primaryKeys Primary keys
     * @param array $autoIncrementKeys Auto increment keys
     * @param array $defaultValue Columns with default value
     * @param array $notNullColumns Columns with not null
     */
    public function __construct($tableName, $columns, $joinColumns, $primaryKeys, $autoIncrementKeys, $defaultValue, $notNullColumns)
    {
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->joinColumns = $joinColumns;
        $this->primaryKeys = $primaryKeys;
        $this->autoIncrementKeys = $autoIncrementKeys;
        $this->defaultValue = $defaultValue;
        $this->notNullColumns = $notNullColumns;
    }

    /**
     * Magic method to debug object
     *
     * @return string
     */
    public function __toString()
    {
        // create new object because all properties are private
        $stdClass = new stdClass;
        $stdClass->tableName = $this->tableName;
        $stdClass->columns = $this->columns;
        $stdClass->joinColumns = $this->joinColumns;
        $stdClass->primaryKeys = $this->primaryKeys;
        $stdClass->autoIncrementKeys = $this->autoIncrementKeys;
        $stdClass->defaultValue = $this->defaultValue;
        $stdClass->notNullColumns = $this->notNullColumns;
        return json_encode($stdClass);
    }

    /**
     * Get column map
     *
     * @return string[]
     */
    public function getColumnsMap()
    {
        $columns = $this->getColumns();
        $propertyColumns = array();
        foreach($columns as $prop=>$column)
        {
            $propertyColumns[$prop] = $column['name'];
        }
        return $propertyColumns;
    }

    /**
     * Get join column map
     *
     * @return string[]
     */
    public function getJoinColumnsMap()
    {
        $columns = $this->getJoinColumns();
        $propertyColumns = array();
        foreach($columns as $prop=>$column)
        {
            $propertyColumns[$prop] = $column['name'];
        }
        return $propertyColumns;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set table name
     *
     * @param string $tableName Table name
     *
     * @return self
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Get columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Set columns
     *
     * @param array $columns Columns
     *
     * @return self
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Get join columns
     *
     * @return array
     */
    public function getJoinColumns()
    {
        return $this->joinColumns;
    }

    /**
     * Set join columns
     *
     * @param array $joinColumns Join columns
     *
     * @return self
     */
    public function setJoinColumns($joinColumns)
    {
        $this->joinColumns = $joinColumns;

        return $this;
    }

    /**
     * Get primary keys
     *
     * @return array
     */
    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    /**
     * Set primary keys
     *
     * @param array $primaryKeys Primary keys
     *
     * @return self
     */
    public function setPrimaryKeys($primaryKeys)
    {
        $this->primaryKeys = $primaryKeys;

        return $this;
    }

    /**
     * Get auto increment keys
     *
     * @return array
     */
    public function getAutoIncrementKeys()
    {
        return $this->autoIncrementKeys;
    }

    /**
     * Set auto increment keys
     *
     * @param array $autoIncrementKeys Auto increment keys
     *
     * @return self
     */
    public function setAutoIncrementKeys($autoIncrementKeys)
    {
        $this->autoIncrementKeys = $autoIncrementKeys;

        return $this;
    }

    /**
     * Get default value keys
     *
     * @return array
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set default value keys
     *
     * @param array $defaultValue Default value keys
     *
     * @return self
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get not null columns
     *
     * @return array
     */
    public function getNotNullColumns()
    {
        return $this->notNullColumns;
    }

    /**
     * Set not null columns
     *
     * @param array $notNullColumns Not null columns
     *
     * @return self
     */
    public function setNotNullColumns($notNullColumns)
    {
        $this->notNullColumns = $notNullColumns;

        return $this;
    }
}