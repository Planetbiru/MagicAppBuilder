<?php

namespace MagicObject\Database;

/**
 * Sort
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSort
{
    const ORDER_TYPE_ASC  = "asc";
    const ORDER_TYPE_DESC = "desc";
    const SORT_BY         = "sortBy";

    /**
     * Sort by
     *
     * @var string
     */
    private $sortBy = "";

    /**
     * Sort type
     *
     * @var string
     */
    private $sortType = "";

    /**
     * Constructor
     * @param string $sortBy Sort by
     * @param string $sortType Sort type
     */
    public function __construct($sortBy = null, $sortType = null)
    {
        $this->setSortBy($sortBy);
        $this->setSortType($sortType);
    }

    /**
     * Get sort by
     *
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set sort by
     *
     * @param string $sortBy Sort by
     *
     * @return self
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    /**
     * Get sort type
     *
     * @return string
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * Set sort type
     *
     * @param string $sortType Sort type
     *
     * @return self
     */
    public function setSortType($sortType)
    {
        $this->sortType = $sortType;

        return $this;
    }

    /**
     * Magic method
     *
     * @param string $method Method name
     * @param array $params Parameters
     * @return self|mixed|null
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, self::SORT_BY, 6) === 0 && isset($params) && isset($params[0])){
            $field = lcfirst(substr($method, 6));
            $value = $params[0];
            $this->setSortBy($field);
            $this->setSortType($value);
            return $this;
        }
    }

    /**
     * Get instance of PicoSort
     *
     * @return self
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Fix sort type
     *
     * @param string $type Sort type
     * @return string
     */
    public static function fixSortType($type)
    {
        return strcasecmp($type, self::ORDER_TYPE_DESC) == 0 ? self::ORDER_TYPE_DESC : self::ORDER_TYPE_ASC;
    }

    /**
     * Magic method to debug object.This method is for debug purpose only.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(array(
            'sortBy' => $this->sortBy,
            'sortType' => $this->sortType
        ));
    }
}