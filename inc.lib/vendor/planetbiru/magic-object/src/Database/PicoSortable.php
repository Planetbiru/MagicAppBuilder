<?php

namespace MagicObject\Database;

use MagicObject\Request\PicoRequestBase;
use MagicObject\Util\PicoStringUtil;

/**
 * Sortable
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSortable
{
    /**
     * Sortable
     *
     * @var PicoSort[]
     */
    private $sortable = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $argc = func_num_args();
        if($argc > 0)
        {
            $params = array();
            if($argc > 1)
            {
                for($i = 0; $i < $argc; $i++)
                {
                    $params[] = func_get_arg($i);
                }
            }
            $this->initSortable($argc, $params);
        }
    }

    /**
     * Initialize sortable
     *
     * @param integer $argc Argument count
     * @param array $params Parameters
     * @return self
     */
    private function initSortable($argc, $params)
    {
        $this->sortable = array();

        $index = 0;
        $sortBy = null;
        foreach($params as $idx=>$value)
        {
            $sortType = null;
            if($idx % 2 == 0)
            {
                $index = (int) ($idx / 2);
                $sortBy = $value;
            }
            else
            {
                $sortType = $value;
                $this->sortable[$index] = $this->createSortable($sortBy, $sortType);
            }
        }
        if($argc % 2 == 1)
        {
            // set order type ASC
            $sortType = PicoSort::ORDER_TYPE_ASC;
            $lastOrder = $this->createSortable($sortBy, $sortType);
            $this->sortable[count($this->sortable) - 1] = $lastOrder;
        }

        return $this;
    }

    /**
     * Add sortable
     *
     * @param PicoSort|array $sort Sort
     * @return self
     */
    public function add($sort)
    {
        return $this->addSortable($sort);
    }

    /**
     * Add sortable
     *
     * @param PicoSort|array $sort Sort
     * @return self
     */
    public function addSortable($sort)
    {
        if($sort != null)
        {
            if($sort instanceof PicoSort)
            {
                $this->sortable[count($this->sortable)] = $sort;
            }
            else if(is_array($sort))
            {
                $sortable = $this->createSortable($sort[0], $sort[1]);
                $this->sortable[count($this->sortable)] = $sortable;
            }
        }
        return $this;
    }

    /**
     * Create sortable
     *
     * @param string $sortBy Sort by
     * @param string $sortType Sort type
     * @return PicoSort
     */
    public function createSortable($sortBy, $sortType)
    {
        return new PicoSort($sortBy, $sortType);
    }

    /**
     * Create sort by
     *
     * @param PicoTableInfo $tableInfo Table information
     * @return string
     */
    public function createOrderBy($tableInfo = null)
    {
        if($this->sortable == null || !is_array($this->sortable) || empty($this->sortable))
        {
            return null;
        }
        $ret = null;
        if($tableInfo == null)
        {
            $ret = $this->createWithoutMapping();
        }
        else
        {
            $ret = $this->createWithMapping($tableInfo);
        }
        return $ret;
    }

    /**
     * Create sort without mapping
     *
     * @return string
     */
    private function createWithoutMapping()
    {
        $ret = "";
        if(empty($this->sortable))
        {
            return "";
        }
        $sorts = array();
        foreach($this->sortable as $sortable)
        {
            $columnName = $sortable->getSortBy();
            $sortType = $sortable->getSortType();
            $sortBy = $columnName;
            $sorts[] = $sortBy . " " . $sortType;
        }
        if(!empty($sorts))
        {
            $ret = implode(", ", $sorts);
        }
        return $ret;
    }

    /**
     * Create sort with mapping
     *
     * @param PicoTableInfo $tableInfo Table information
     * @return string
     */
    private function createWithMapping($tableInfo)
    {
        $ret = null;
        $columns = $tableInfo->getColumns();
        $joinColumns = $tableInfo->getJoinColumns();
        $columnList = array_merge($columns, $joinColumns);
        $columnNames = array();
        foreach($columnList as $column)
        {
            $columnNames[] = $column['name'];
        }
        $sorts = array();
        foreach($this->sortable as $sortable)
        {
            $propertyName = $sortable->getSortBy();
            $sortType = $sortable->getSortType();
            if(isset($columnList[$propertyName]))
            {
                $sortBy = $columnList[$propertyName]['name'];
                $sorts[] = $sortBy . " " . $sortType;
            }
            else if(in_array($propertyName, $columnNames))
            {
                $sorts[] = $propertyName . " " . $sortType;
            }
        }
        if(!empty($sorts))
        {
            $ret = implode(", ", $sorts);
        }
        return $ret;
    }

    /**
     * Check id specification is empty or not
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->sortable);
    }

    /**
     * Get sortable
     *
     * @return PicoSort[]
     */
    public function getSortable()
    {
        return $this->sortable;
    }

    /**
     * Get instance of PicoSortable
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Magic method to debug object. This method is for debug purpose only.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->createWithoutMapping();
    }

    /**
     * Get sortable from user input
     *
     * @param PicoRequestBase $request Request
     * @param string[]|null $map Map
     * @param array|null $defaultSortable Default sortable
     * @return self
     */
    public static function fromUserInput($request, $map = null, $defaultSortable = null)
    {
        $sortable = new self;
        if(self::isArray($map))
        {
            foreach($map as $key=>$value)
            {
                if(PicoStringUtil::camelize($request->getOrderby()) == PicoStringUtil::camelize($key))
                {
                    $sortable->add(new PicoSort($value, PicoSort::fixSortType($request->getOrdertype())));
                }
            }
        }
        if($sortable->isEmpty() && self::isArray($defaultSortable))
        {
            // no filter from user input
            foreach($defaultSortable as $filter)
            {
                if(isset($filter['sortBy']) && isset($filter['sortType']))
                {
                    $sortable->add(new PicoSort($filter['sortBy'], $filter['sortType']));
                }
            }
        }
        return $sortable;
    }

    /**
     * Check if input is array
     *
     * @param mixed $array Array to be checked
     * @return boolean
     */
    public static function isArray($array)
    {
        return isset($array) && is_array($array);
    }
}