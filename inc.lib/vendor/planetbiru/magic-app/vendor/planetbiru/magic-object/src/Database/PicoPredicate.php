<?php

namespace MagicObject\Database;

/**
 * Predicate
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPredicate //NOSONAR
{
    /**
     * @var string
     */
    private $field = "";

    /**
     * Value
     *
     * @var mixed
     */
    private $value = "";

    /**
     * Data comparation
     *
     * @var PicoDataComparation
     */
    private $comparation = null;

    /**
     * Filter logic
     *
     * @var string
     */
    private $filterLogic = null;

    /**
     * Constructor. If $field given, it will call in method for array and equals for others
     *
     * @param string $field Field name
     * @param mixed $value Value
     * @return void
     */
    public function __construct($field = null, $value = null)
    {
        if($field != null)
        {
            if(is_array($value))
            {
                $this->in($field, $value);
            }
            else
            {
                $this->equals($field, $value);
            }
        }
    }

    /**
     * Return true if require real join table
     *
     * @return boolean
     */
    public function isRequireJoin()
    {
        return strpos($this->field, ".");
    }

    /**
     * Equals
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function equals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        if(is_array($value))
        {
            $this->comparation = PicoDataComparation::in($value);
        }
        else
        {
            $this->comparation = PicoDataComparation::equals($value);
        }
        return $this;
    }

    /**
     * Not equals
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function notEquals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        if(is_array($value))
        {
            $this->comparation = PicoDataComparation::notIn($value);
        }
        else
        {
            $this->comparation = PicoDataComparation::notEquals($value);
        }
        return $this;
    }

    /**
     * Is NULL
     * @param string $field Field name
     * @return self
     */
    public function isNull($field)
    {
        return $this->equals($field, null);
    }

    /**
     * Is Not NULL
     * @param string $field Field name
     * @return self
     */
    public function isNotNull($field)
    {
        return $this->notEquals($field, null);
    }

    /**
     * In
     * @param string $field Field name
     * @param mixed[] $values Value
     * @return self
     */
    public function in($field, $values)
    {
        if(!empty($values))
        {
            if(is_scalar($values))
            {
                $values = array($values);
            }
            $this->field = $field;
            $this->value = $values;
            $this->comparation = PicoDataComparation::in($values);
        }
        return $this;
    }

    /**
     * Not in
     * @param string $field Field name
     * @param mixed[] $values Value
     * @return self
     */
    public function notIn($field, $values)
    {
        if(!empty($values))
        {
            if(is_scalar($values))
            {
                $values = array($values);
            }
            $this->field = $field;
            $this->value = $values;
            $this->comparation = PicoDataComparation::notIn($values);
        }
        return $this;
    }

    /**
     * Like
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function like($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = PicoDataComparation::like($value);
        return $this;
    }

    /**
     * Not like
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function notLike($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = PicoDataComparation::notLike($value);
        return $this;
    }

    /**
     * Less than
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function lessThan($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = PicoDataComparation::lessThan($value);
        return $this;
    }

    /**
     * Greater than
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function greaterThan($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = PicoDataComparation::greaterThan($value);
        return $this;
    }

    /**
     * Less than or equals
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function lessThanOrEquals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = PicoDataComparation::lessThanOrEquals($value);
        return $this;
    }

    /**
     * Greater than or equals
     * @param string $field Field name
     * @param mixed $value Value
     * @return self
     */
    public function greaterThanOrEquals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = PicoDataComparation::greaterThanOrEquals($value);
        return $this;
    }

    /**
     * Get the value of field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get data comparation
     *
     * @return PicoDataComparation
     */
    public function getComparation()
    {
        return $this->comparation;
    }

    /**
     * Get filter logic
     *
     * @return string
     */
    public function getFilterLogic()
    {
        return $this->filterLogic;
    }

    /**
     * Set filter logic
     *
     * @param string $filterLogic Filter logic
     *
     * @return self
     */
    public function setFilterLogic($filterLogic)
    {
        $this->filterLogic = $filterLogic;
        return $this;
    }

    /**
     * Generate LIKE STARTS wildcard
     *
     * @param string $value Value
     * @return string
     */
    public static function generateLikeStarts($value)
    {
        return "$value%";
    }

    /**
     * Generate LIKE ENDS wildcard
     *
     * @param string $value Value
     * @return string
     */
    public static function generateLikeEnds($value)
    {
        return "%$value";
    }

    /**
     * Generate LIKE CONTAINS wildcard
     *
     * @param string $value Value
     * @return string
     */
    public static function generateLikeContains($value)
    {
        return "%$value%";
    }

    /**
     * Magic method to handle undefined method
     *
     * @param string $method Method name
     * @param array $params Parameters
     * @return self|mixed|null
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, "set", 3) === 0 && isset($params)) {
            $field = lcfirst(substr($method, 3));
            $value = $params[0];
            $this->equals($field, $value);
            return $this;
        }
    }

    /**
     * Magic object to set value
     *
     * @param string $name Column name
     * @param mixed|mixed[] $value Column value
     */
    public function __set($name, $value)
    {
        $this->equals($name, $value);
    }

    /**
     * Get instance of PicoPredicate
     *
     * @return PicoPredicate
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Function lower
     *
     * @param string $value Function value
     * @return string
     */
    public static function functionLower($value)
    {
        return "lower($value)";
    }

    /**
     * Function upper
     *
     * @param string $value Function value
     * @return string
     */
    public static function functionUpper($value)
    {
        return "upper($value)";
    }

    /**
     * Function lower
     *
     * @param string $function Function name
     * @param string $value Function value
     * @return string
     */
    public static function functionAndValue($function, $value)
    {
        return sprintf("%s(%s)", $function, $value);
    }

    /**
     * Magic method to debug object
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(array(
            'field' => $this->field,
            'value' => $this->value,
            'comparation' =>
                array(
                    $this->comparation->getComparison()
                ),
            'filterLogic' => $this->filterLogic
        ));
    }
}