<?php

namespace MagicObject\Database;

use MagicObject\Request\PicoRequestBase;
use MagicObject\Util\Database\PicoDatabaseUtil;

/**
 * Class PicoSpecification
 *
 * This class is responsible for building complex database query specifications,
 * allowing for the combination of predicates using logical operators (AND, OR).
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSpecification // NOSONAR
{
    const LOGIC_AND = "AND";
    const LOGIC_OR  = "OR";

    /**
     * Parent filter logic (AND/OR) for nested specifications.
     *
     * @var string
     */
    private $parentFilterLogic = null;

    /**
     * Array of PicoPredicate objects representing individual conditions.
     *
     * @var (PicoPredicate|string)[]
     */
    private $specifications = array();

    /**
     * Indicates whether a real join table is required in the database query.
     *
     * @var bool
     */
    private $requireJoin = false;

    /**
     * Default logic for combining predicates (AND/OR).
     *
     * @var string
     */
    private $defaultLogic = self::LOGIC_AND;
    
    /**
     * Gets an instance of PicoSpecification.
     *
     * @return PicoSpecification A new instance of PicoSpecification.
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Creates and returns an instance of the class with an optional PicoPredicate condition.
     *
     * This static method creates a new instance of the class and, if the provided parameters 
     * are set, adds a PicoPredicate condition using the given field and value.
     *
     * @param string|null $field The name of the field to be used in the predicate. 
     *                           If null, no predicate is added.
     * @param mixed|null  $value The value to compare against the field in the predicate. 
     * 
     * @return self A new instance of the class with the optionally added predicate.
     */
    public static function getInstanceOf($field = null, $value = null)
    {
        $instance = new self;
        if(isset($field))
        {
            $instance->addAnd(new PicoPredicate($field, $value));
        }
        return $instance;
    }

    /**
     * Checks if a real join table is required based on the specifications.
     *
     * @return bool True if a join is required, false otherwise.
     */
    public function isRequireJoin()
    {
        return strpos($this->__toString(), ".") !== false;
    }

    /**
     * Adds a specification with default AND logic.
     *
     * This method allows adding a filtering condition using logical AND.
     * The condition can be a `PicoPredicate`, `PicoSpecification`, an array representing a predicate, or a raw SQL condition as a string.
     *
     * @param PicoSpecification|PicoPredicate|array|string $predicate The filter condition to be added.
     *   - `PicoPredicate`: Represents a structured condition.
     *   - `PicoSpecification`: A collection of conditions.
     *   - `array`: Must contain at least two elements where the first is a column name and the second is a value.
     *   - `string`: A raw SQL fragment.
     * @return self Returns the current instance for method chaining.
     */
    public function add($predicate)
    {
        return $this->addAnd($predicate);
    }

    /**
     * Adds an AND condition to the specifications.
     *
     * This method allows adding a filtering condition using logical AND.
     * The condition can be a `PicoPredicate`, `PicoSpecification`, an array representing a predicate, or a raw SQL condition as a string.
     *
     * @param PicoSpecification|PicoPredicate|array|string $predicate The filter condition to be added.
     *   - `PicoPredicate`: Represents a structured condition.
     *   - `PicoSpecification`: A collection of conditions.
     *   - `array`: Must contain at least two elements where the first is a column name and the second is a value.
     *   - `string`: A raw SQL fragment.
     * @return self Returns the current instance for method chaining.
     */
    public function addAnd($predicate)
    {
        if(isset($predicate))
        {
            if ($predicate instanceof PicoPredicate) {
                $this->addFilter($predicate, self::LOGIC_AND);
            } elseif ($predicate instanceof PicoSpecification) {
                $this->addSubfilter($predicate, self::LOGIC_AND);
            } elseif (is_array($predicate) && count($predicate) > 1 && is_string($predicate[0])) {
                $this->addFilter(new PicoPredicate($predicate[0], $predicate[1]), self::LOGIC_AND);
            } elseif (is_string($predicate)) {
                $this->addFilter($predicate, self::LOGIC_AND);
            }
        }
        return $this;
    }

    /**
     * Adds an OR condition to the specifications.
     *
     * Similar to `addAnd()`, but applies logical OR instead of AND.
     *
     * @param PicoSpecification|PicoPredicate|array|string $predicate The filter condition to be added.
     *   - `PicoPredicate`: Represents a structured condition.
     *   - `PicoSpecification`: A collection of conditions.
     *   - `array`: Must contain at least two elements where the first is a column name and the second is a value.
     *   - `string`: A raw SQL fragment.
     * @return self Returns the current instance for method chaining.
     */
    public function addOr($predicate)
    {
        if(isset($predicate))
        {
            if ($predicate instanceof PicoPredicate) {
                $this->addFilter($predicate, self::LOGIC_OR);
            } elseif ($predicate instanceof PicoSpecification) {
                $this->addSubfilter($predicate, self::LOGIC_OR);
            } elseif (is_array($predicate) && count($predicate) > 1 && is_string($predicate[0])) {
                $this->addFilter(new PicoPredicate($predicate[0], $predicate[1]), self::LOGIC_OR);
            } elseif (is_string($predicate)) {
                $this->addFilter($predicate, self::LOGIC_OR);
            }
        }
        return $this;
    }

    /**
     * Adds a filtering condition to the specifications.
     *
     * This method processes the given predicate and applies the specified logical operator (AND/OR).
     * If the predicate is an instance of `PicoPredicate`, it will be modified to include the logical operator.
     * If a `PicoSpecification` is provided, its specifications will be extracted and added recursively.
     * If the predicate is an array, it will be processed accordingly.
     * If a raw SQL string is provided, it will be added with the logical operator.
     *
     * @param PicoSpecification|PicoPredicate|array|string $predicate The filter condition to be added.
     *   - `PicoPredicate`: Represents a structured condition.
     *   - `PicoSpecification`: A collection of conditions.
     *   - `array`: Must contain at least two elements where the first is a column name and the second is a value.
     *   - `string`: A raw SQL fragment.
     * @param string $logic The logical operator (`AND` or `OR`) to be applied.
     * @return self Returns the current instance for method chaining.
     */
    private function addFilter($predicate, $logic) // NOSONAR
    {
        if(isset($predicate))
        {
            if ($predicate instanceof PicoPredicate) {
                $predicate->setFilterLogic($logic);
                $this->specifications[count($this->specifications)] = $predicate;
                if ($predicate->isRequireJoin()) {
                    $this->requireJoin = true;
                }
            } elseif ($predicate instanceof PicoSpecification) {
                $specs = $predicate->getSpecifications();
                if (!empty($specs)) {
                    foreach ($specs as $spec) {
                        $this->addFilter($spec, $spec->getParentFilterLogic());
                    }
                }
            } elseif (is_array($predicate)) {
                $this->addFilterByArray($predicate, $logic);
            } elseif (is_string($predicate)) {
                $this->specifications[count($this->specifications)] = $logic. " " . $predicate;
            }
        }
        return $this;
    }

    /**
     * Adds a filter specification from an array.
     *
     * @param array $predicate The filter data represented as an associative array.
     * @param string $logic The logical operator (AND/OR) to use with these filters.
     * @return self Returns the current instance for method chaining.
     */
    private function addFilterByArray($predicate, $logic)
    {
        if(self::isArray($predicate))
        {
            foreach ($predicate as $key => $value) {
                $pred = new PicoPredicate($key, $value);
                $pred->setFilterLogic($logic);
                $this->specifications[count($this->specifications)] = $pred;
                if ($pred->isRequireJoin()) {
                    $this->requireJoin = true;
                }
            }
        }
        return $this;
    }

    /**
     * Adds a subfilter specification.
     *
     * @param PicoSpecification|array $predicate The subfilter to be added.
     * @param string $logic The logical operator (AND/OR) to use with this subfilter.
     * @return self Returns the current instance for method chaining.
     */
    private function addSubFilter($predicate, $logic)
    {
        if (isset($predicate) && $predicate instanceof PicoSpecification) {
            $specification = new self;
            $specification->setParentFilterLogic($logic);
            $specifications = $predicate->getSpecifications();
            foreach ($specifications as $pred) {
                if ($pred instanceof PicoPredicate) {
                    $specification->addFilter($pred, $pred->getFilterLogic());
                    if ($specification->isRequireJoin()) {
                        $this->requireJoin = true;
                    }
                } elseif ($pred instanceof PicoSpecification) {
                    $specification->addSubFilter($pred, $pred->getParentFilterLogic());
                }
            }
            $this->specifications[count($this->specifications)] = $specification;
        }
        return $this;
    }

    /**
     * Checks if the specifications collection is empty.
     *
     * @return bool True if there are no specifications, false otherwise.
     */
    public function isEmpty()
    {
        return empty($this->specifications);
    }
    
    /**
     * Check if the given input is an array.
     *
     * @param mixed $array The input to check.
     * @return bool True if the input is an array, false otherwise.
     */
    public static function isArray($array)
    {
        return isset($array) && is_array($array);
    }

    /**
     * Checks if the given value is considered empty.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is empty, false otherwise.
     */
    public static function isValueEmpty($value)
    {
        return !isset($value) || (is_string($value) && empty(trim($value)));
    }

    /**
     * Retrieves the array of specifications.
     *
     * @return PicoPredicate[] The array of PicoPredicate objects.
     */
    public function getSpecifications()
    {
        return $this->specifications;
    }

    /**
     * Gets the parent filter logic for this specification.
     *
     * @return string|null The parent filter logic, or null if not set.
     */
    public function getParentFilterLogic()
    {
        return $this->parentFilterLogic;
    }

    /**
     * Sets the parent filter logic for this specification.
     *
     * @param string $parentFilterLogic The logical operator (AND/OR) for this specification.
     * @return self Returns the current instance for method chaining.
     */
    public function setParentFilterLogic($parentFilterLogic)
    {
        $this->parentFilterLogic = $parentFilterLogic;
        return $this;
    }

    /**
     * Creates a WHERE clause based on the current specifications.
     *
     * @param PicoSpecification[] $specifications The specifications to create the WHERE clause from.
     * @return string[] An array of strings representing the WHERE clause conditions.
     */
    private function getWhere($specifications)
    {
        $arr = array();
        if(self::isArray($specifications))
        {
            foreach ($specifications as $spec) {
                if (isset($spec) && $spec instanceof PicoPredicate) {
                    $entityField = new PicoEntityField($spec->getField());
                    $field = $entityField->getField();
                    $parentField = $entityField->getParentField();
                    $column = $this->getColumnName($field, $parentField);
                    if ($spec->getComparation() !== null) {
                        $where = $spec->getFilterLogic() . " " . $column . " " . $spec->getComparation()->getComparison() . " " . PicoDatabaseUtil::escapeValue($spec->getValue());
                        $arr[] = $where;
                    }
                } elseif ($spec instanceof PicoSpecification) {
                    // Nested specification
                    $arr[] = $spec->getParentFilterLogic() . " (" . $this->createWhereFromSpecification($spec) . ")";
                }
            }
        }
        return $arr;
    }
    
    /**
     * Retrieves the full column name, including any parent field.
     * 
     * This method returns the column name formatted as "parentField.field" if the parent field is provided; otherwise, it returns just the field name.
     * 
     * @param string $field The field name of the entity.
     * @param string|null $parentField The parent field name, if applicable.
     * 
     * @return string The full column name, either just the field name or the parent field concatenated with the field.
     */
    private function getColumnName($field, $parentField)
    {
       return ($parentField === null) ? $field : $parentField . "." . $field;
    }

    /**
     * Creates a WHERE clause from the given specification.
     *
     * @param PicoSpecification $specification The filter specification to create the WHERE clause from.
     * @return string The constructed WHERE clause as a string.
     */
    private function createWhereFromSpecification($specification)
    {
        $arr = array();
        $arr[] = "(1=1)";
        if ($this->hasValue($specification)) {
            foreach ($specification->getSpecifications() as $spec) {
                if ($spec instanceof PicoPredicate) {
                    $entityField = new PicoEntityField($spec->getField());
                    $field = $entityField->getField();
                    $parentField = $entityField->getParentField();
                    $column = ($parentField === null) ? $field : $parentField . "." . $field;
                    if ($spec->getComparation() !== null) {
                        $arr[] = $spec->getFilterLogic() . " " . $column . " " . $spec->getComparation()->getComparison() . " " . PicoDatabaseUtil::escapeValue($spec->getValue());
                    }
                } elseif ($spec instanceof PicoSpecification) {
                    $arr[] = $spec->getParentFilterLogic() . " (" . $this->createWhereFromSpecification($spec) . ")";
                }
            }
        }
        return PicoDatabaseUtil::trimWhere(implode(" ", $arr));
    }

    /**
     * Checks if the specification is not null and not empty.
     *
     * @param mixed $specification The specification to check.
     * @return bool True if the specification is valid, false otherwise.
     */
    private function hasValue($specification)
    {
        return $specification !== null && !$specification->isEmpty();
    }

    /**
     * Magic method to handle undefined method calls dynamically.
     *
     * This method allows for dynamic handling of method calls that are not explicitly defined in the class.
     * Specifically, it enables the setting of properties through methods prefixed with "set".
     * When such a method is called, the method extracts the property name from the method name,
     * and then it calls the `addPredicate` method to set the corresponding value.
     *
     * Supported dynamic method:
     *
     * - `set<FieldName>(value)`: 
     *   Sets a predicate for the specified field.
     *   For example, calling `$obj->setAge(30)` would:
     *   - Extract the field name `age` from the method name.
     *   - Call `addPredicate('age', 30)` to set the value.
     *
     * If the method name does not start with "set" or if the parameters are not provided,
     * the method returns null.
     *
     * @param string $method The name of the method being called, expected to start with "set".
     * @param array $params The parameters passed to the method; expected to contain the value to set.
     * @return self|null Returns the current instance for method chaining if the method is valid, or null otherwise.
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, "set", 3) === 0 && isset($params)) {
            $field = lcfirst(substr($method, 3));
            $value = $params[0];
            $this->addPredicate($field, $value);
            return $this;
        }
        return null;
    }

    /**
     * Magic method to set values dynamically using property assignment.
     *
     * @param string $field The field name to set.
     * @param mixed|mixed[] $value The value(s) to set for the field.
     */
    public function __set($field, $value)
    {
        $this->addPredicate($field, $value);
    }

    /**
     * Adds a predicate to the specifications based on the field and value.
     *
     * @param string $field The field name to which the value is assigned.
     * @param mixed|mixed[] $value The value(s) to set for the field.
     * @return self Returns the current instance for method chaining.
     */
    private function addPredicate($field, $value)
    {
        if(isset($field))
        {
            if ($this->defaultLogic === self::LOGIC_OR) {
                $this->addOr(new PicoPredicate($field, $value));
            } else {
                $this->addAnd(new PicoPredicate($field, $value));
            }
        }
        return $this;
    }

    /**
     * Creates a part of the `WHERE` clause from specifications for debugging purposes only.
     * Removes leading logical operators (`AND`, `OR`) if present.
     *
     * @return string The generated `WHERE` clause.
     */
    public function __toString()
    {
        $specification = trim(implode(" ", $this->getWhere($this->specifications)));
        if (stripos($specification, "and ") === 0) {
            $specification = substr($specification, 4);
        }
        if (stripos($specification, "or ") === 0) {
            $specification = substr($specification, 3);
        }
        return $specification;
    }

    /**
     * Gets a specification based on user input.
     *
     * @param PicoRequestBase $request The request object containing user input.
     * @param PicoSpecificationFilter[]|null $map The filter map defining expected filters.
     * @return PicoSpecification The constructed specification based on user input.
     */
    public static function fromUserInput($request, $map = null)
    {
        $specification = new self;
        if (self::isArray($map)) {
            foreach ($map as $key => $filter) {
                $filterValue = $request->get($key);
                $filterValue = self::fixInput($filterValue, $filter);
                if ($filterValue !== null && !self::isValueEmpty($filterValue) && $filter instanceof PicoSpecificationFilter) {
                    if ($filter->isNumber() || $filter->isBoolean() || $filter->isArrayNumber() || $filter->isArrayBoolean() || $filter->isArrayString()) {
                        $specification->addAnd(PicoPredicate::getInstance()->equals($filter->getColumnName(), $filter->valueOf($filterValue)));
                    } elseif ($filter->isFulltext()) {
                        $specification->addAnd(self::fullTextSearch($filter->getColumnName(), $filterValue));
                    } else if(is_array($filterValue)) {
                        $specification->addAnd(self::fullTextSearchArray($filter->getColumnName(), $filterValue));
                    } else {
                        $specification->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower($filter->getColumnName()), PicoPredicate::generateLikeContains(strtolower($filterValue))));
                    }
                }
            }
        }
        return $specification;
    }

    /**
     * Converts all string values in an array to lowercase.
     *
     * @param array $input The input array containing string values.
     * @return array The modified array with all values converted to lowercase.
     */
    public static function toLowerCase($input)
    {
        foreach ($input as $key => $val) {
            $input[$key] = strtolower($val);
        }
        return $input;
    }

    /**
     * Adjusts the filter value based on the filter's configuration.
     *
     * This method ensures that the input value aligns with the filter type.
     * If the filter does not expect an array but the input is an array, 
     * the first value in the array is selected. If no adjustment is needed, 
     * the input value is returned as-is.
     *
     * @param mixed $filterValue The raw user input value.
     * @param PicoSpecificationFilter $filter The filter object specifying expected data type.
     * @return mixed The adjusted value, based on the filter's configuration.
     */
    private static function fixInput($filterValue, $filter)
    {
        if(!$filter->isArray() && is_array($filterValue) && !empty($filterValue)) {
            $filterValue = array_values($filterValue)[0];
        }
        return $filterValue;
    }

    /**
     * Creates a full text search specification based on keywords.
     *
     * @param string $columnName The column name to search within.
     * @param string $keywords The keywords to search for.
     * @return self A new specification containing the full text search predicates.
     */
    public static function fullTextSearch($columnName, $keywords)
    {
        $specification = new self;
        $arr = explode(" ", $keywords);
        foreach ($arr as $word) {
            if (!empty($word)) {
                $specification->addAnd(
                    PicoPredicate::getInstance()
                        ->like(PicoPredicate::functionLower($columnName), PicoPredicate::generateLikeContains(strtolower($word)))
                );
            }
        }
        return $specification;
    }

    /**
     * Creates a full-text search specification for an array of keyword sets.
     * Each set of keywords is processed separately, allowing for multiple search conditions.
     * Uses an OR condition between different keyword sets and an AND condition within each set.
     *
     * @param string $columnName The database column name to search within.
     * @param array $keywordArray An array of keyword sets, where each element contains a string of keywords.
     * @return self A new specification containing the combined full-text search predicates.
     */
    public static function fullTextSearchArray($columnName, $keywordArray)
    {
        $specs = new self;
        foreach($keywordArray as $keywords)
        {
            $specification = new self;
            $arr = explode(" ", $keywords);
            foreach ($arr as $word) {
                if (!empty($word)) {
                    $specification->addAnd(
                        PicoPredicate::getInstance()
                            ->like(PicoPredicate::functionLower($columnName), PicoPredicate::generateLikeContains(strtolower($word)))
                    );
                }
            }
            $specs->addOr($specification);
        }
        return $specs;
    }

    /**
     * Creates a filter object based on column name and data type.
     *
     * @param string $columnName The column name to filter by.
     * @param string $dataType The data type of the column (e.g., string, integer).
     * @return PicoSpecificationFilter A new instance of PicoSpecificationFilter.
     */
    public static function filter($columnName, $dataType)
    {
        return new PicoSpecificationFilter($columnName, $dataType);
    }

    /**
     * Gets the default logic used for combining predicates.
     *
     * @return string The default logic (AND/OR).
     */
    public function getDefaultLogic()
    {
        return $this->defaultLogic;
    }

    /**
     * Sets the default logic used for combining predicates.
     *
     * @param string $defaultLogic The default logic (AND/OR) to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setDefaultLogic($defaultLogic)
    {
        $this->defaultLogic = $defaultLogic;
        return $this;
    }

    /**
     * Sets the default logic to AND.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setDefaultLogicAnd()
    {
        $this->defaultLogic = self::LOGIC_AND;
        return $this;
    }

    /**
     * Sets the default logic to OR.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setDefaultLogicOr()
    {
        $this->defaultLogic = self::LOGIC_OR;
        return $this;
    }

    /**
     * Checks if a real join table is required based on the specifications.
     *
     * @return bool True if a join is required, false otherwise.
     */ 
    public function getRequireJoin()
    {
        return $this->requireJoin;
    }
}
