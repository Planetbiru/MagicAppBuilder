<?php

namespace MagicObject\Database;

/**
 * Predicate for building query conditions.
 *
 * This class allows you to define various query conditions
 * (e.g., equality, inequality, inclusion, pattern matching, etc.)
 * that can be used when constructing database queries.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoPredicate //NOSONAR
{
    private $field = "";
    private $value = "";
    private $comparation = null;
    private $filterLogic = null;

    /**
     * Constructor. Initializes the predicate with a field and value.
     *
     * @param string|null $field The name of the field.
     * @param mixed|null $value The value to compare against.
     */
    public function __construct($field = null, $value = null)
    {
        if ($field !== null) {
            is_array($value) ? $this->in($field, $value) : $this->equals($field, $value);
        }
    }

    /**
     * Check if a real join table is required.
     *
     * @return bool True if a join is required, false otherwise.
     */
    public function isRequireJoin()
    {
        return strpos($this->field, '.') !== false;
    }

    /**
     * Set an equality condition.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to compare against.
     * @return self
     */
    public function equals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = is_array($value) ? PicoDataComparation::in($value) : PicoDataComparation::equals($value);
        return $this;
    }

    /**
     * Set a not-equal condition.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to compare against.
     * @return self
     */
    public function notEquals($field, $value)
    {
        $this->field = $field;
        $this->value = $value;
        $this->comparation = is_array($value) ? PicoDataComparation::notIn($value) : PicoDataComparation::notEquals($value);
        return $this;
    }

    /**
     * Set a condition for NULL.
     *
     * @param string $field The name of the field.
     * @return self
     */
    public function isNull($field)
    {
        return $this->equals($field, null);
    }

    /**
     * Set a condition for NOT NULL.
     *
     * @param string $field The name of the field.
     * @return self
     */
    public function isNotNull($field)
    {
        return $this->notEquals($field, null);
    }

    /**
     * Set an IN condition.
     *
     * @param string $field The name of the field.
     * @param array $values The values to include.
     * @return self
     */
    public function in($field, array $values)
    {
        if (!empty($values)) {
            $this->field = $field;
            $this->value = $values;
            $this->comparation = PicoDataComparation::in((array) $values);
        }
        return $this;
    }

    /**
     * Set a NOT IN condition.
     *
     * @param string $field The name of the field.
     * @param array $values The values to exclude.
     * @return self
     */
    public function notIn($field, array $values)
    {
        if (!empty($values)) {
            $this->field = $field;
            $this->value = $values;
            $this->comparation = PicoDataComparation::notIn((array) $values);
        }
        return $this;
    }

    /**
     * Set a LIKE condition.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to compare against.
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
     * Set a NOT LIKE condition.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to compare against.
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
     * Set a LESS THAN condition.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to compare against.
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
     * Set a GREATER THAN condition.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to compare against.
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
     * Set a LESS THAN OR EQUALS condition.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to compare against.
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
     * Set a GREATER THAN OR EQUALS condition.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value to compare against.
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
     * Get the field name.
     *
     * @return string The name of the field.
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Get the value.
     *
     * @return mixed The value being compared against.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the comparation instance.
     *
     * @return PicoDataComparation|null The comparation instance or null.
     */
    public function getComparation()
    {
        return $this->comparation;
    }

    /**
     * Get the filter logic.
     *
     * @return string|null The filter logic or null.
     */
    public function getFilterLogic()
    {
        return $this->filterLogic;
    }

    /**
     * Set the filter logic.
     *
     * @param string $filterLogic The filter logic to set.
     * @return self
     */
    public function setFilterLogic($filterLogic)
    {
        $this->filterLogic = $filterLogic;
        return $this;
    }

    /**
     * Generate a LIKE clause that matches the start of a string.
     *
     * @param string $value The value to use for matching.
     * @return string The LIKE clause.
     */
    public static function generateLikeStarts($value)
    {
        return "$value%";
    }

    /**
     * Generate a LIKE clause that matches the end of a string.
     *
     * @param string $value The value to use for matching.
     * @return string The LIKE clause.
     */
    public static function generateLikeEnds($value)
    {
        return "%$value";
    }

    /**
     * Generate a LIKE clause that matches anywhere in a string.
     *
     * @param string $value The value to use for matching.
     * @return string The LIKE clause.
     */
    public static function generateLikeContains($value)
    {
        return "%$value%";
    }

    /**
     * Magic method to handle dynamic method calls for setting values.
     *
     * This method intercepts calls to methods that are not explicitly defined in the class.
     * It specifically looks for methods that start with "set" and performs an equality check
     * between the property corresponding to the method and the provided value.
     *
     * Supported dynamic method:
     *
     * - `set<PropertyName>(value)`: Checks if the property value equals the provided value.
     *   - For example, calling `$obj->setFoo($value)` checks if the property `foo`
     *     is equal to `$value` using the `equals` method.
     * 
     * If the method name does not start with "set" or if no value is provided,
     * the method returns null.
     *
     * @param string $method The method name being called, expected to start with "set".
     * @param array $params The parameters passed to the method, expected to contain the value.
     * @return mixed The result of the equality check (true or false) or null if the method call is not handled.
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, "set", 3) === 0 && isset($params[0])) {
            $field = lcfirst(substr($method, 3));
            $value = $params[0];
            return $this->equals($field, $value);
        }
        return null;
    }

    /**
     * Magic method to handle dynamic property assignment.
     *
     * @param string $name The property name.
     * @param mixed $value The value to set.
     */
    public function __set($name, $value)
    {
        $this->equals($name, $value);
    }

    /**
     * Get an instance of this class.
     *
     * @return self A new instance of the class.
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Generate a SQL LOWER function call.
     *
     * @param string $value The value to wrap in the LOWER function.
     * @return string The SQL function call.
     */
    public static function functionLower($value)
    {
        return "lower($value)";
    }

    /**
     * Generate a SQL UPPER function call.
     *
     * @param string $value The value to wrap in the UPPER function.
     * @return string The SQL function call.
     */
    public static function functionUpper($value)
    {
        return "upper($value)";
    }

    /**
     * Generate a SQL function call with a value.
     *
     * @param string $function The SQL function name.
     * @param string $value The value to pass to the function.
     * @return string The formatted SQL function call.
     */
    public static function functionAndValue($function, $value)
    {
        return sprintf("%s(%s)", $function, $value);
    }

    /**
     * Convert the object to a JSON string representation.
     *
     * @return string The JSON representation of the object.
     */
    public function __toString()
    {
        return json_encode([
            'field' => $this->field,
            'value' => $this->value,
            'comparation' => [$this->comparation ? $this->comparation->getComparison() : null],
            'filterLogic' => $this->filterLogic
        ]);
    }
}
