<?php

namespace MagicObject\Database;

/**
 * Class for handling data comparisons.
 * Provides various comparison operations for use in database queries.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDataComparation
{
    const EQUALS                 = "=";
    const NOT_EQUALS             = "!=";
    const IN                     = "in";
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
     * @return self
     */
    public static function equals($value)
    {
        return new self($value, self::EQUALS);
    }

    /**
     * Creates a comparison for inequality.
     *
     * @param mixed $value The value to compare.
     * @return self
     */
    public static function notEquals($value)
    {
        return new self($value, self::NOT_EQUALS);
    }

    /**
     * Creates a comparison for inclusion in a set.
     *
     * @param mixed[] $values The values to compare against.
     * @return self
     */
    public static function in($values)
    {
        return new self($values, self::IN);
    }

    /**
     * Creates a comparison for exclusion from a set.
     *
     * @param mixed[] $values The values to compare against.
     * @return self
     */
    public static function notIn($values)
    {
        return new self($values, self::NOT_IN);
    }

    /**
     * Creates a comparison using the LIKE operator.
     *
     * @param mixed $value The value to compare.
     * @return self
     */
    public static function like($value)
    {
        return new self($value, self::LIKE);
    }

    /**
     * Creates a comparison using the NOT LIKE operator.
     *
     * @param mixed $value The value to compare.
     * @return self
     */
    public static function notLike($value)
    {
        return new self($value, self::NOT_LIKE);
    }

    /**
     * Creates a comparison for less than.
     *
     * @param mixed $value The value to compare.
     * @return self
     */
    public static function lessThan($value)
    {
        return new self($value, self::LESS_THAN);
    }

    /**
     * Creates a comparison for greater than.
     *
     * @param mixed $value The value to compare.
     * @return self
     */
    public static function greaterThan($value)
    {
        return new self($value, self::GREATER_THAN);
    }

    /**
     * Creates a comparison for less than or equal to.
     *
     * @param mixed $value The value to compare.
     * @return self
     */
    public static function lessThanOrEquals($value)
    {
        return new self($value, self::LESS_THAN_OR_EQUALS);
    }

    /**
     * Creates a comparison for greater than or equal to.
     *
     * @param mixed $value The value to compare.
     * @return self
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
     * Gets the appropriate equals operator based on value.
     *
     * @return string
     */
    private function _equals()
    {
        return ($this->value === null || $this->type == self::TYPE_NULL) ? self::IS : self::EQUALS;
    }

    /**
     * Gets the appropriate not equals operator based on value.
     *
     * @return string
     */
    private function _notEquals()
    {
        return ($this->value === null || $this->type == self::TYPE_NULL) ? self::IS_NOT : self::NOT_EQUALS;
    }

    /**
     * Gets the less than operator.
     *
     * @return string
     */
    private function _lessThan()
    {
        return self::LESS_THAN;
    }

    /**
     * Gets the greater than operator.
     *
     * @return string
     */
    private function _greaterThan()
    {
        return self::GREATER_THAN;
    }

    /**
     * Gets the less than or equals operator.
     *
     * @return string
     */
    private function _lessThanOrEquals()
    {
        return self::LESS_THAN_OR_EQUALS;
    }

    /**
     * Gets the greater than or equals operator.
     *
     * @return string
     */
    private function _greaterThanOrEquals()
    {
        return self::GREATER_THAN_OR_EQUALS;
    }

    /**
     * Gets the comparison operator based on the value and type.
     *
     * @return string
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
                return $this->comparison;
            default:
                return $this->_equals();
        }
    }

    /**
     * Gets the value being compared.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
