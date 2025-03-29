<?php

namespace MagicObject\Database;

use MagicObject\Util\PicoStringUtil;
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
     * Handles dynamic method calls for setting sorting criteria.
     *
     * This magic method allows dynamic sorting by intercepting method calls 
     * prefixed with "sortBy". It extracts the sorting field from the method name 
     * and applies the specified sorting type.
     *
     * ### Supported Dynamic Method:
     * - `sortBy<FieldName>(sortType)`: 
     *   - Example: `$obj->sortByName('asc')`
     *   - Sets the sorting field to `name`
     *   - Sets the sorting type to `'asc'`
     *
     * ### Behavior:
     * - The method name must start with "sortBy".
     * - The first parameter must be provided, defining the sorting type.
     * - If these conditions are met, the sorting field and type are set, and the 
     *   current instance is returned for method chaining.
     * - If the method does not match the expected format, `null` is returned.
     *
     * @param string $method The dynamically called method name, expected to start with "sortBy".
     * @param array $params Parameters passed to the method; the first value should be the sorting type.
     * @return self|null Returns the current instance for method chaining if successful, or null if the method call is invalid.
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, 'sortBy', 6) === 0 && isset($params[0])) {
            $field = PicoStringUtil::camelize(substr($method, 6));
            $value = $params[0];
            $this->setSortBy($field);
            $this->setSortType($value);
            return $this;
        }
        return null; // Return null for unrecognized method calls
    }

    /**
     * Get an instance of PicoSort.
     *
     * This method provides a convenient way to create a new instance of the PicoSort class.
     * If both the sorting field and sorting type are provided, they are used to initialize
     * the new instance. Otherwise, a default instance is created.
     *
     * @param string|null $sortBy The field to sort by.
     * @param string|null $sortType The type of sorting (asc or desc).
     * @return self A new instance of PicoSort.
     */
    public static function getInstance($sortBy = null, $sortType = null)
    {
        if(isset($sortBy) && isset($sortType)) {
            return new self($sortBy, $sortType);
        }
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
