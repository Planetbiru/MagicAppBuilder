<?php

namespace MagicObject\Database;

use MagicObject\MagicObject;
use stdClass;

/**
 * Class representing information about a database table.
 *
 * This class contains details such as the table name, columns, 
 * primary keys, and other related metadata necessary for managing 
 * database interactions.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoTableInfo // NOSONAR
{
    /**
     * The name of the table.
     *
     * @var string
     */
    protected $tableName = null;

    /**
     * The columns of the table.
     *
     * @var array
     */
    protected $columns = array();

    /**
     * The columns used for joining other tables.
     *
     * @var array
     */
    protected $joinColumns = array();

    /**
     * The primary keys of the table.
     *
     * @var array
     */
    protected $primaryKeys = array();

    /**
     * The columns that auto-increment.
     *
     * @var array
     */
    protected $autoIncrementKeys = array();

    /**
     * The columns that have default values.
     *
     * @var array
     */
    protected $defaultValue = array();

    /**
     * The columns that cannot be null.
     *
     * @var array
     */
    protected $notNullColumns = array();

    /**
     * The type of the columns.
     *
     * @var string
     */
    protected $columnType;
    
    /**
     * Flag to disable cache when any entities join with this entity
     *
     * @var boolean
     */
    protected $noCache = false;

    /**
     * The package name or namespace.
     *
     * @var string
     */
    protected $package;

    /**
     * List of sorted column names.
     *
     * This property stores an array of column names in their sorted order.
     *
     * @var array List of sorted column names.
     */
    private $sortedColumnName = array();

    /**
     * Gets an instance of PicoTableInfo.
     *
     * @return self A new instance of PicoPredicate.
     */
    public static function getInstance()
    {
        return new self(null, array(), array(), array(), array(), array(), array());
    }

    /**
     * Constructor for PicoTableInfo.
     *
     * Initializes the table information with the provided parameters.
     *
     * @param string|null $tableName The name of the table.
     * @param array $columns The columns of the table.
     * @param array $joinColumns The columns used for joins.
     * @param array $primaryKeys The primary keys of the table.
     * @param array $autoIncrementKeys The auto-increment keys of the table.
     * @param array $defaultValue The columns with default values.
     * @param array $notNullColumns The columns that cannot be null.
     * @param bool $noCache Flag to disable cache when any entities join with this entity
     * @param string $package The package name or namespace of the class
     */
    public function __construct($tableName, $columns, $joinColumns, $primaryKeys, $autoIncrementKeys, $defaultValue, $notNullColumns, $noCache = false, $package = null) // NOSONAR
    {
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->joinColumns = $joinColumns;
        $this->primaryKeys = $primaryKeys;
        $this->autoIncrementKeys = $autoIncrementKeys;
        $this->defaultValue = $defaultValue;
        $this->notNullColumns = $notNullColumns;
        $this->noCache = $noCache;
        $this->package = $package;

        // Store sorted column list
        $res = array();
        $res = array_merge($res, array_keys($columns));
        $res = array_unique($res);

        $this->sortedColumnName = $res;
    }

    /**
     * Magic method to return a JSON representation of the object.
     *
     * @return string JSON encoded string of the object's properties.
     */
    public function __toString()
    {
        // Create a new stdClass object to expose the properties
        $stdClass = new stdClass;
        $stdClass->tableName = $this->tableName;
        $stdClass->columns = $this->columns;
        $stdClass->joinColumns = $this->joinColumns;
        $stdClass->primaryKeys = $this->primaryKeys;
        $stdClass->autoIncrementKeys = $this->autoIncrementKeys;
        $stdClass->defaultValue = $this->defaultValue;
        $stdClass->notNullColumns = $this->notNullColumns;
        $stdClass->noCache = $this->noCache;
        $stdClass->package = $this->package;
        return json_encode($stdClass, JSON_PRETTY_PRINT);
    }

    /**
     * Gets a map of column properties.
     *
     * @return string[] An associative array mapping property names to column names.
     */
    public function getColumnsMap()
    {
        $columns = $this->getColumns();
        $propertyColumns = array();
        foreach ($columns as $prop => $column) {
            $propertyColumns[$prop] = $column[MagicObject::KEY_NAME];
        }
        return $propertyColumns;
    }

    /**
     * Gets a map of join column properties.
     *
     * @return string[] An associative array mapping property names to join column names.
     */
    public function getJoinColumnsMap()
    {
        $columns = $this->getJoinColumns();
        $propertyColumns = array();
        foreach ($columns as $prop => $column) {
            $propertyColumns[$prop] = $column[MagicObject::KEY_NAME];
        }
        return $propertyColumns;
    }

    /**
     * Gets the name of the table.
     *
     * @return string|null The name of the table.
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Sets the name of the table.
     *
     * @param string $tableName The name of the table.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Gets the columns of the table.
     *
     * @return array The columns of the table.
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Sets the columns of the table.
     *
     * @param array $columns The columns to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setColumns($columns)
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * Gets the join columns of the table.
     *
     * @return array The join columns.
     */
    public function getJoinColumns()
    {
        return $this->joinColumns;
    }

    /**
     * Sets the join columns of the table.
     *
     * @param array $joinColumns The join columns to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setJoinColumns($joinColumns)
    {
        $this->joinColumns = $joinColumns;
        return $this;
    }

    /**
     * Gets the primary keys of the table.
     *
     * @return array The primary keys.
     */
    public function getPrimaryKeys()
    {
        return $this->primaryKeys;
    }

    /**
     * Sets the primary keys of the table.
     *
     * @param array $primaryKeys The primary keys to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setPrimaryKeys($primaryKeys)
    {
        $this->primaryKeys = $primaryKeys;
        return $this;
    }

    /**
     * Gets the auto-increment keys of the table.
     *
     * @return array The auto-increment keys.
     */
    public function getAutoIncrementKeys()
    {
        return $this->autoIncrementKeys;
    }

    /**
     * Sets the auto-increment keys of the table.
     *
     * @param array $autoIncrementKeys The auto-increment keys to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setAutoIncrementKeys($autoIncrementKeys)
    {
        $this->autoIncrementKeys = $autoIncrementKeys;
        return $this;
    }

    /**
     * Gets the default value keys of the table.
     *
     * @return array The default value keys.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Sets the default value keys of the table.
     *
     * @param array $defaultValue The default value keys to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * Gets the not-null columns of the table.
     *
     * @return array The not-null columns.
     */
    public function getNotNullColumns()
    {
        return $this->notNullColumns;
    }

    /**
     * Sets the not-null columns of the table.
     *
     * @param array $notNullColumns The not-null columns to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setNotNullColumns($notNullColumns)
    {
        $this->notNullColumns = $notNullColumns;
        return $this;
    }

    /**
     * Get flag to disable cache when any entities join with this entity
     *
     * @return boolean Flag to disable cache when any entities join with this entity
     */ 
    public function getNoCache()
    {
        return $this->noCache;
    }

    /**
     * Set flag to disable cache when any entities join with this entity
     *
     * @param boolean  $noCache  Flag to disable cache when any entities join with this entity
     *
     * @return self Returns the current instance for method chaining.
     */ 
    public function setNoCache($noCache)
    {
        $this->noCache = $noCache;

        return $this;
    }

    /**
     * Get the package name or namespace.
     *
     * @return string The package name or namespace.
     */ 
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Set the package name or namespace.
     *
     * @param string  $package  The package name or namespace.
     *
     * @return self Returns the current instance for method chaining.
     */ 
    public function setPackage($package)
    {
        $this->package = $package;

        return $this;
    }

    /**
     * Get the sorted column names.
     *
     * This method retrieves the list of sorted column names.
     *
     * @return array List of sorted column names.
     */
    public function getSortedColumnName()
    {
        return $this->sortedColumnName;
    }

    /**
     * Set the sorted column names.
     *
     * This method sets the list of sorted column names.
     *
     * @param array $sortedColumnName List of sorted column names.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setSortedColumnName($sortedColumnName)
    {
        $this->sortedColumnName = $sortedColumnName;

        return $this;
    }
}
