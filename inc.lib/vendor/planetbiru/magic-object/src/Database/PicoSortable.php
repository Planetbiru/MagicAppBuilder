<?php

namespace MagicObject\Database;

use MagicObject\MagicObject;
use MagicObject\Request\PicoRequestBase;
use MagicObject\Util\PicoStringUtil;

/**
 * Class representing sortable criteria for database queries.
 *
 * This class provides functionality to manage sorting criteria,
 * allowing the specification of fields to sort by and their sort types.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSortable
{
    /**
     * Array of sortable criteria.
     *
     * @var (PicoSort|string)[]
     */
    private $sortable = array();

    /**
     * Constructor to initialize sortable criteria based on provided arguments.
     * 
     * **Example:**
     * ```php
     * <?php
     * $sortable = new PicoSortable(); // Without initialization
     * // or
     * $sortable = new PicoSortable("rank", "ASC", "name", "ASC"); // With initialization
     * ```
     */
    public function __construct()
    {
        $argc = func_num_args();
        if ($argc > 0) {
            $params = array();
            if ($argc > 1) {
                for ($i = 0; $i < $argc; $i++) {
                    $params[] = func_get_arg($i);
                }
            }
            $this->initSortable($argc, $params);
        }
    }

    /**
     * Initialize sortable criteria.
     *
     * @param int $argc Number of arguments passed to the constructor.
     * @param array $params Parameters for sorting.
     * @return self Returns the current instance for method chaining.
     */
    private function initSortable($argc, $params)
    {
        $this->sortable = array();

        $index = 0;
        $sortBy = null;
        foreach ($params as $idx => $value) {
            $sortType = null;
            if ($idx % 2 == 0) {
                $index = (int) ($idx / 2);
                $sortBy = $value;
            } else {
                $sortType = $value;
                $this->sortable[$index] = $this->createSortable($sortBy, $sortType);
            }
        }
        if ($argc % 2 == 1) {
            // Set order type to ASC if an odd number of arguments is passed
            $sortType = PicoSort::ORDER_TYPE_ASC;
            $lastOrder = $this->createSortable($sortBy, $sortType);
            $this->sortable[count($this->sortable) - 1] = $lastOrder;
        }

        return $this;
    }

    /**
     * Add a sortable criterion.
     *
     * @param PicoSort|array|string $sort The sorting criterion to add.
     * @param bool $isRawSql Indicates whether the array-based sorting is raw SQL.
     * @return self Returns the current instance for method chaining.
     */
    public function add($sort, $isRawSql = false)
    {
        return $this->addSortable($sort, $isRawSql);
    }

    /**
     * Add a sortable criterion.
     *
     * @param PicoSort|array|string $sort The sorting criterion to add.
     * @param bool $isRawSql Indicates whether the array-based sorting is raw SQL.
     * @return self Returns the current instance for method chaining.
     */
    public function addSortable($sort, $isRawSql = false)
    {
        if (isset($sort)) {
            if ($sort instanceof PicoSort) {
                $this->sortable[count($this->sortable)] = $sort;
            } else if (is_array($sort)) {
                if($isRawSql)
                {
                    // Raw SQL
                    // No mapping
                    $this->sortable[count($this->sortable)] = $sort[0] . " " . $sort[1];
                }
                else
                {
                    // Convert array to PicoSort
                    $sortable = $this->createSortable($sort[0], $sort[1]);
                    $this->sortable[count($this->sortable)] = $sortable;
                }
                
            } else if (is_string($sort)) {
                // No mapping
                $this->sortable[count($this->sortable)] = $sort;
            }
        }
        return $this;
    }

    /**
     * Create a sortable criterion.
     *
     * @param string $sortBy The field to sort by.
     * @param string $sortType The type of sorting (ASC or DESC).
     * @return PicoSort A new instance of PicoSort.
     */
    public function createSortable($sortBy, $sortType)
    {
        return new PicoSort($sortBy, $sortType);
    }

    /**
     * Create an ORDER BY clause based on the sortable criteria.
     *
     * @param PicoTableInfo|null $tableInfo Information about the table for mapping.
     * @return string|null The ORDER BY clause, or null if there are no sortable criteria.
     */
    public function createOrderBy($tableInfo = null)
    {
        if ($this->sortable == null || !is_array($this->sortable) || empty($this->sortable)) {
            return null;
        }
        $ret = null;
        if ($tableInfo == null) {
            $ret = $this->createOrderByWithoutMapping();
        } else {
            $ret = $this->createOrderByWithMapping($tableInfo);
        }
        return $ret;
    }

    /**
     * Create an ORDER BY clause without mapping to table columns.
     *
     * @return string The ORDER BY clause.
     */
    private function createOrderByWithoutMapping()
    {
        $ret = "";
        if (empty($this->sortable)) {
            return "";
        }
        $sorts = array();
        foreach ($this->sortable as $sort) {
            if($sort instanceof PicoSort)
            {
                $columnName = $sort->getSortBy();
                $sortType = $sort->getSortType();
                $sorts[] = $columnName . " " . $sortType;
            }
            else if(is_string($sort))
            {
                $sorts[] = $sort;
            }
        }
        if (!empty($sorts)) {
            $ret = implode(", ", $sorts);
        }
        return $ret;
    }

    /**
     * Create an ORDER BY clause with mapping based on table information.
     *
     * @param PicoTableInfo $tableInfo Information about the table for mapping.
     * @return string The ORDER BY clause.
     */
    private function createOrderByWithMapping($tableInfo)
    {
        $ret = null;
        $columns = $tableInfo->getColumns();
        $joinColumns = $tableInfo->getJoinColumns();
        $columnList = array_merge($columns, $joinColumns);
        $columnNames = array();
        foreach ($columnList as $column) {
            $columnNames[] = $column[MagicObject::KEY_NAME];
        }
        $sorts = array();
        foreach ($this->sortable as $sort) {
            if($sort instanceof PicoSort)
            {
                $propertyName = $sort->getSortBy();
                $sortType = $sort->getSortType();
                if (isset($columnList[$propertyName])) {
                    $sortBy = $columnList[$propertyName]['name'];
                    $sorts[] = $sortBy . " " . $sortType;
                } else if (in_array($propertyName, $columnNames)) {
                    $sorts[] = $propertyName . " " . $sortType;
                }
            }
            else if(is_string($sort))
            {
                $sorts[] = $sort;
            }
        }
        if (!empty($sorts)) {
            $ret = implode(", ", $sorts);
        }
        return $ret;
    }

    /**
     * Check if there are no sortable criteria.
     *
     * @return bool true if there are no sortable criteria, false otherwise.
     */
    public function isEmpty()
    {
        return empty($this->sortable);
    }

    /**
     * Get the array of sortable criteria.
     *
     * @return PicoSort[] Array of sortable criteria.
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * Get an instance of PicoSortable.
     *
     * @return self A new instance of PicoSortable.
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Generates the `ORDER BY` clause without mapping.
     * This method is useful for debugging purposes.
     *
     * @return string The generated `ORDER BY` clause.
     */
    public function __toString()
    {
        return $this->createOrderByWithoutMapping();
    }

    /**
     * Create a PicoSortable instance from user input.
     *
     * @param PicoRequestBase $request The request containing sorting information.
     * @param string[]|null $map Mapping of request parameters to sorting fields.
     * @param array|null $defaultSortable Default sorting criteria if no user input is provided.
     * @return self A new instance of PicoSortable with the specified criteria.
     */
    public static function fromUserInput($request, $map = null, $defaultSortable = null)
    {
        $sortable = new self;
        if (self::isArray($map)) {
            foreach ($map as $key => $value) {
                if (PicoStringUtil::camelize($request->getOrderby()) == PicoStringUtil::camelize($key)) {
                    $sortable->add(new PicoSort($value, PicoSort::fixSortType($request->getOrdertype())));
                }
            }
        }
        if ($sortable->isEmpty() && self::isArray($defaultSortable)) {
            // No sorting criteria from user input; use defaults
            foreach ($defaultSortable as $filter) {
                if (isset($filter['sortBy']) && isset($filter['sortType'])) {
                    $sortable->add(new PicoSort($filter['sortBy'], $filter['sortType']));
                }
            }
        }
        return $sortable;
    }

    /**
     * Check if the given input is an array.
     *
     * @param mixed $array The input to check.
     * @return bool true if the input is an array, false otherwise.
     */
    public static function isArray($array)
    {
        return isset($array) && is_array($array);
    }
}
