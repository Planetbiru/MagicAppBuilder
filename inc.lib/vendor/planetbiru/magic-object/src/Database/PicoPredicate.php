<?php

namespace MagicObject\Database;

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
     * Constructor. If $field given, it will call equals method
     *
     * @param string $field
     * @param mixed $value
     * @return void
     */
    public function __construct($field = null, $value = null)
    {
        if($field != null)
        {
            $this->equals($field, $value);
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
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public function equals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = PicoDataComparation::equals($value);
        return $this;
    }
    
    /**
     * Is NULL
     * @param string $field
     * @return self
     */
    public function isNull($field)
    {
        return $this->equals($field, null);
    }

    /**
     * Not equals
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public function notEquals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = new PicoDataComparation($value, PicoDataComparation::NOT_EQUALS);
        return $this;
    }
    
    /**
     * Is Not NULL
     * @param string $field
     * @return self
     */
    public function isNotNull($field)
    {
        return $this->notEquals($field, null);
    }
    
    /**
     * In
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public function in($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = PicoDataComparation::in($value);
        return $this;
    }

    /**
     * Like
     * @param string $field
     * @param mixed $value
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
     * @param string $field
     * @param mixed $value
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
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public function lessThan($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = new PicoDataComparation($value, PicoDataComparation::LESS_THAN);
        return $this;
    }

    /**
     * Greater than
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public function greaterThan($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = new PicoDataComparation($value, PicoDataComparation::GREATER_THAN);
        return $this;
    }

    /**
     * Less than or equals
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public function lessThanOrEquals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = new PicoDataComparation($value, PicoDataComparation::LESS_THAN_OR_EQUALS);
        return $this;
    }

    /**
     * Greater than or equals
     * @param string $field
     * @param mixed $value
     * @return self
     */
    public function greaterThanOrEquals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = new PicoDataComparation($value, PicoDataComparation::GREATER_THAN_OR_EQUALS);
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
     * @param string $filterLogic  Filter logic
     *
     * @return self
     */ 
    public function setFilterLogic($filterLogic)
    {
        $this->filterLogic = $filterLogic;
        return $this;
    }
    
    /**
     * Generate LEFT LIKE wildcard
     *
     * @param string $value
     * @return string
     */
    public static function generateLeftLike($value)
    {
        return "%".$value;
    }
    
    /**
     * Generate CENTER LIKE wildcard
     *
     * @param string $value
     * @return string
     */
    public static function generateCenterLike($value)
    {
        return "%".$value."%";
    }
    
    /**
     * Generate RIGHT LIKE wildcard
     *
     * @param string $value
     * @return string
     */
    public static function generateRightLike($value)
    {
        return $value."%";
    }

    /**
     * Magic method
     *
     * @param string $method
     * @param array $params
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
     * Get instance of PicoPredicate
     *
     * @return PicoPredicate
     */
    public static function getInstance()
    {
        return new PicoPredicate();
    }
    
    /**
     * Function lower
     *
     * @param string $value
     * @return string
     */
    public static function functionLower($value)
    {
        return "lower($value)";
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