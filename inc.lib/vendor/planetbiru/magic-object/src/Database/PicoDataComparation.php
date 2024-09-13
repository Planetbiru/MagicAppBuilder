<?php

namespace MagicObject\Database;

/**
 * Data comparation
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
     * Value comparator
     *
     * @var string
     */
    private $comparison = "=";

    /**
     * Value
     *
     * @var mixed
     */
    private $value = null;

    /**
     * Stype
     *
     * @var string
     */
    private $type = "null";

    /**
     * Equals
     * @param mixed $value Value
     */
    public static function equals($value)
    {
        return new self($value, self::EQUALS);
    }

    /**
     * Not equals
     * @param mixed $value Value
     */
    public static function notEquals($value)
    {
        return new self($value, self::NOT_EQUALS);
    }

    /**
     * In
     * @param mixed[] $values Values
     */
    public static function in($values)
    {
        return new self($values, self::IN);
    }

    /**
     * Not in
     * @param mixed[] $values Values
     */
    public static function notIn($values)
    {
        return new self($values, self::NOT_IN);
    }

    /**
     * Like
     * @param mixed $value Value
     */
    public static function like($value)
    {
        return new self($value, self::LIKE);
    }

    /**
     * Not like
     * @param mixed $value Value
     */
    public static function notLike($value)
    {
        return new self($value, self::NOT_LIKE);
    }

    /**
     * Less than
     * @param mixed $value Value
     */
    public static function lessThan($value)
    {
        return new self($value, self::LESS_THAN);
    }

    /**
     * Greater than
     * @param mixed $value Value
     */
    public static function greaterThan($value)
    {
        return new self($value, self::GREATER_THAN);
    }

    /**
     * Less than or equals
     * @param mixed $value Value
     */
    public static function lessThanOrEquals($value)
    {
        return new self($value, self::LESS_THAN_OR_EQUALS);
    }

    /**
     * Greater than or equals
     * @param mixed $value Value
     */
    public static function greaterThanOrEquals($value)
    {
        return new self($value, self::GREATER_THAN_OR_EQUALS);
    }

    /**
     * Constructor
     *
     * @param mixed $value Value
     * @param string $comparison Comparison
     */
    public function __construct($value, $comparison=self::EQUALS)
    {
        $this->comparison = $comparison;
        $this->value = $value;
        if(is_string($value))
		{
			$this->type = self::TYPE_STRING;
		}
		else if(is_bool($value))
		{
			$this->type = self::TYPE_BOOLEAN;
		}
		else if(is_numeric($value))
		{
            $this->type = self::TYPE_NUMERIC;
        }
    }

    /**
     * Get equals operator
     *
     * @return string
     */
    private function _equals()
    {
        return ($this->value === null || $this->type == self::TYPE_NULL) ? self::IS : self::EQUALS;
    }

    /**
     * Get not equals operator
     *
     * @return string
     */
    private function _notEquals()
    {
        return ($this->value === null || $this->type == self::TYPE_NULL) ? self::IS_NOT : self::NOT_EQUALS;
    }

    /**
     * Get less than operator
     *
     * @return string
     */
    private function _lessThan()
    {
        return self::LESS_THAN;
    }

    /**
     * Get greater than operator
     *
     * @return string
     */
    private function _greaterThan()
    {
        return self::GREATER_THAN;
    }

    /**
     * Get less than or equals operator
     *
     * @return string
     */
    private function _lessThanOrEquals()
    {
        return self::LESS_THAN_OR_EQUALS;
    }

    /**
     * Get greater than or equals operator
     *
     * @return string
     */
    private function _greaterThanOrEquals()
    {
        return self::GREATER_THAN_OR_EQUALS;
    }

    /**
     * Get comparison operator
     *
     * @return string
     */
    public function getComparison()
    {
        $ret = $this->_equals();
        if($this->comparison === self::NOT_EQUALS)
        {
            $ret = $this->_notEquals();
        }
        else if($this->comparison === self::LESS_THAN)
        {
            $ret = $this->_lessThan();
        }
        else if($this->comparison === self::GREATER_THAN)
        {
            $ret = $this->_greaterThan();
        }
        else if($this->comparison === self::LESS_THAN_OR_EQUALS)
        {
            $ret = $this->_lessThanOrEquals();
        }
        else if($this->comparison === self::GREATER_THAN_OR_EQUALS)
        {
            $ret = $this->_greaterThanOrEquals();
        }
        else if($this->comparison == self::LIKE || $this->comparison == self::NOT_LIKE)
        {
            $ret = $this->comparison;
        }
        else if($this->comparison == self::IN || $this->comparison == self::NOT_IN)
        {
            $ret = $this->comparison;
        }
        return $ret;
    }

    /**
     * Get the value of property value
     */
    public function getValue()
    {
        return $this->value;
    }
}