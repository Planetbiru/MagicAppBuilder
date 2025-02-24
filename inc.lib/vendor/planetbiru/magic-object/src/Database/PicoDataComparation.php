<?php

namespace MagicObject\Database;

/**
 * Class PicoDataComparation
 *
 * This class provides various comparison operations for use in database queries.
 * It allows the creation of comparison objects that can be utilized to 
 * compare values against specified criteria, facilitating flexible and 
 * expressive database querying.
 *
 * The class supports a variety of comparison operators such as equality, 
 * inequality, inclusion, and range comparisons. Each operator can be 
 * applied to values of various types, including strings, booleans, and numbers.
 *
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDataComparation // NOSONAR
{
    const EQUALS                 = "=";
    const NOT_EQUALS             = "!=";
    const IN                     = "in";
    const BETWEEN                = "between";
    const NOT_IN                 = "not in";
    const IS                     = "is";
    const IS_NOT                 = "is not";
    const LIKE                   = "like";
    const NOT_LIKE               = "not like";
    const LESS_THAN              = "<";
    const GREATER_THAN           = ">";
    const LESS_THAN_OR_EQUALS    = "<=";
    const GREATER_THAN_OR_EQUALS = ">=";
    const TYPE_STRING            = "string";
    const TYPE_BOOLEAN           = "boolean";
    const TYPE_NUMERIC           = "numeric";
    const TYPE_NULL              = "null";

    /**
     * The comparison operator.
     *
     * @var string
     */
    private $comparison = "=";

    /**
     * The value to compare against.
     *
     * @var mixed
     */
    private $value = null;

    /**
     * The type of the value.
     *
     * @var string
     */
    private $type = self::TYPE_NULL;

    /**
     * Creates a comparison for equality.
     *
     * @param mixed $value The value to compare.
     * @return self Returns the current instance for method chaining.
     */
    public static function equals($value)
    {
        return new self($value, self::EQUALS);
    }

    /**
     * Creates a comparison for inequality.
     *
     * @param mixed $value The value to compare.
     * @return self Returns the current instance for method chaining.
     */
    public static function notEquals($value)
    {
        return new self($value, self::NOT_EQUALS);
    }

    /**
     * Creates a comparison for inclusion in a set.
     *
     * @param mixed[] $values The values to compare against.
     * @return self Returns the current instance for method chaining.
     */
    public static function in($values)
    {
        return new self($values, self::IN);
    }

    /**
     * Creates a comparison for inclusion within a range.
     *
     * @param mixed[] $values The range boundaries to compare against.
     * @return self Returns the current instance for method chaining.
     */
    public static function inRange($values)
    {
        return new self($values, self::BETWEEN);
    }

    /**
     * Creates a comparison for values within a specified range.
     *
     * @param mixed $min The lower boundary of the range.
     * @param mixed $max The upper boundary of the range.
     * @return self Returns the current instance for method chaining.
     */
    public static function between($min, $max)
    {
        return new self(array($min, $max), self::BETWEEN);
    }

    /**
     * Creates a comparison for exclusion from a set.
     *
     * @param mixed[] $values The values to compare against.
     * @return self Returns the current instance for method chaining.
     */
    public static function notIn($values)
    {
        return new self($values, self::NOT_IN);
    }

    /**
     * Creates a comparison using the LIKE operator.
     *
     * @param mixed $value The value to compare.
     * @return self Returns the current instance for method chaining.
     */
    public static function like($value)
    {
        return new self($value, self::LIKE);
    }

    /**
     * Creates a comparison using the NOT LIKE operator.
     *
     * @param mixed $value The value to compare.
     * @return self Returns the current instance for method chaining.
     */
    public static function notLike($value)
    {
        return new self($value, self::NOT_LIKE);
    }

    /**
     * Creates a comparison for less than.
     *
     * @param mixed $value The value to compare.
     * @return self Returns the current instance for method chaining.
     */
    public static function lessThan($value)
    {
        return new self($value, self::LESS_THAN);
    }

    /**
     * Creates a comparison for greater than.
     *
     * @param mixed $value The value to compare.
     * @return self Returns the current instance for method chaining.
     */
    public static function greaterThan($value)
    {
        return new self($value, self::GREATER_THAN);
    }

    /**
     * Creates a comparison for less than or equal to.
     *
     * @param mixed $value The value to compare.
     * @return self Returns the current instance for method chaining.
     */
    public static function lessThanOrEquals($value)
    {
        return new self($value, self::LESS_THAN_OR_EQUALS);
    }

    /**
     * Creates a comparison for greater than or equal to.
     *
     * @param mixed $value The value to compare.
     * @return self Returns the current instance for method chaining.
     */
    public static function greaterThanOrEquals($value)
    {
        return new self($value, self::GREATER_THAN_OR_EQUALS);
    }

    /**
     * Constructor for PicoDataComparation.
     *
     * @param mixed $value The value to compare.
     * @param string $comparison The comparison operator.
     */
    public function __construct($value, $comparison = self::EQUALS)
    {
        $this->comparison = $comparison;
        $this->value = $value;
        if (is_string($value)) {
            $this->type = self::TYPE_STRING;
        } elseif (is_bool($value)) {
            $this->type = self::TYPE_BOOLEAN;
        } elseif (is_numeric($value)) {
            $this->type = self::TYPE_NUMERIC;
        }
    }

    /**
     * Returns the appropriate equals operator based on the value's state.
     *
     * If the value is null or of type null, returns the IS operator; 
     * otherwise, returns the standard equals operator.
     *
     * @return string The equals operator.
     */
    private function _equals()
    {
        return ($this->value === null || $this->type == self::TYPE_NULL) ? self::IS : self::EQUALS;
    }

    /**
     * Returns the appropriate not equals operator based on the value's state.
     *
     * If the value is null or of type null, returns the IS NOT operator; 
     * otherwise, returns the standard not equals operator.
     *
     * @return string The not equals operator.
     */
    private function _notEquals()
    {
        return ($this->value === null || $this->type == self::TYPE_NULL) ? self::IS_NOT : self::NOT_EQUALS;
    }

    /**
     * Returns the less than operator.
     *
     * @return string The less than operator.
     */
    private function _lessThan()
    {
        return self::LESS_THAN;
    }

    /**
     * Returns the greater than operator.
     *
     * @return string The greater than operator.
     */
    private function _greaterThan()
    {
        return self::GREATER_THAN;
    }

    /**
     * Returns the less than or equals operator.
     *
     * @return string The less than or equals operator.
     */
    private function _lessThanOrEquals()
    {
        return self::LESS_THAN_OR_EQUALS;
    }

    /**
     * Returns the greater than or equals operator.
     *
     * @return string The greater than or equals operator.
     */
    private function _greaterThanOrEquals()
    {
        return self::GREATER_THAN_OR_EQUALS;
    }

    /**
     * Determines the comparison operator based on the current value and its type.
     *
     * @return string The comparison operator corresponding to the current state.
     */
    public function getComparison() // NOSONAR
    {
        switch ($this->comparison) {
            case self::NOT_EQUALS:
                return $this->_notEquals();
            case self::LESS_THAN:
                return $this->_lessThan();
            case self::GREATER_THAN:
                return $this->_greaterThan();
            case self::LESS_THAN_OR_EQUALS:
                return $this->_lessThanOrEquals();
            case self::GREATER_THAN_OR_EQUALS:
                return $this->_greaterThanOrEquals();
            case self::LIKE:
            case self::NOT_LIKE:
            case self::IN:
            case self::NOT_IN:
            case self::BETWEEN:
                return $this->comparison;
            default:
                return $this->_equals();
        }
    }

    /**
     * Gets the value being compared.
     *
     * @return mixed The value that is currently being compared.
     */
    public function getValue()
    {
        return $this->value;
    }
}
