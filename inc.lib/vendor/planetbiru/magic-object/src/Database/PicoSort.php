<?php

namespace MagicObject\Database;

use stdClass;

/**
 * Class PicoSort
 *
 * A class for defining sorting criteria for database queries.
 * This class allows you to specify the field to sort by and the 
 * direction of sorting (ascending or descending).
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSort
{
    const ORDER_TYPE_ASC  = "asc";
    const ORDER_TYPE_DESC = "desc";
    const SORT_BY         = "sortBy";

    /**
     * The field to sort by.
     *
     * @var string
     */
    private $sortBy = "";

    /**
     * The type of sorting (ascending or descending).
     *
     * @var string
     */
    private $sortType = "";

    /**
     * Constructor to initialize sorting criteria.
     *
     * @param string|null $sortBy The field to sort by.
     * @param string|null $sortType The type of sorting (asc or desc).
     */
    public function __construct($sortBy = null, $sortType = null)
    {
        $this->setSortBy($sortBy);
        $this->setSortType($sortType);
    }

    /**
     * Get the field to sort by.
     *
     * @return string The field to sort by.
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set the field to sort by.
     *
     * @param string $sortBy The field to sort by.
     * @return self Returns the current instance for method chaining.
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
        return $this;
    }

    /**
     * Get the type of sorting.
     *
     * @return string The type of sorting (asc or desc).
     */
    public function getSortType()
    {
        return $this->sortType;
    }

    /**
     * Set the type of sorting.
     *
     * @param string $sortType The type of sorting (asc or desc).
     * @return self Returns the current instance for method chaining.
     */
    public function setSortType($sortType)
    {
        $this->sortType = $sortType;
        return $this;
    }

    /**
     * Magic method for dynamic method calls related to sorting criteria.
     *
     * This method enables the dynamic setting of sorting criteria by allowing
     * the invocation of methods prefixed with "sortBy". When such a method is called,
     * it extracts the sorting field from the method name and assigns a sorting type
     * based on the provided parameters.
     *
     * Supported dynamic method:
     *
     * - `sortBy<FieldName>(sortType)`: 
     *   Sets the field to sort by and the type of sorting.
     *   - For example, calling `$obj->sortByName('asc')` would:
     *     - Set the sorting field to `name`.
     *     - Set the sorting type to `asc`.
     *
     * If the method name does not start with "sortBy" or if no parameters are provided,
     * the method returns null.
     *
     * @param string $method The name of the method being called, expected to start with "sortBy".
     * @param array $params The parameters passed to the method; expected to contain the sorting type.
     * @return self|null Returns the current instance for method chaining or null if the method call is not handled.
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, 'sortBy', 6) === 0 && isset($params[0])) {
            $field = lcfirst(substr($method, 6));
            $value = $params[0];
            $this->setSortBy($field);
            $this->setSortType($value);
            return $this;
        }
        return null; // Added return for undefined methods
    }

    /**
     * Get an instance of PicoSort.
     *
     * @return self A new instance of PicoSort.
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Normalize the sort type to either ascending or descending.
     *
     * @param string $type The desired sort type (asc or desc).
     * @return string The normalized sort type (asc or desc).
     */
    public static function fixSortType($type)
    {
        return strcasecmp($type, self::ORDER_TYPE_DESC) == 0 ? self::ORDER_TYPE_DESC : self::ORDER_TYPE_ASC;
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
        $stdClass = new stdClass;
        $stdClass->sortBy = $this->sortBy;
        $stdClass->sortType = $this->sortType;
        return json_encode($stdClass, JSON_PRETTY_PRINT);
    }
}
