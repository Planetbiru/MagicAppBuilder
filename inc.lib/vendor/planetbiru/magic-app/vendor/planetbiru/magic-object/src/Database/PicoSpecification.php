<?php

namespace MagicObject\Database;

use MagicObject\Request\PicoRequestBase;
use MagicObject\Util\Database\PicoDatabaseUtil;

/**
 * Specification
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSpecification //NOSONAR
{
    const LOGIC_AND = "and";
    const LOGIC_OR  = "or";

    /**
     * Parent filter logic
     *
     * @var string
     */
    private $parentFilterLogic = null;

    /**
     * PicoPredicate[]
     *
     * @var array
     */
    private $specifications = array();

    /**
     * Check if require real join table
     *
     * @var boolean
     */
    private $requireJoin = false;

    /**
     * Default logic
     *
     * @var string
     */
    private $defaultLogic = self::LOGIC_AND;

    /**
     * Return true if require real join table
     *
     * @return boolean
     */
    public function isRequireJoin()
    {
        return strpos($this->__toString(), ".") !== false;
    }

    /**
     * Add AND specification
     *
     * @param PicoSpecification|PicoPredicate|array $predicate Filter
     * @return self
     */
    public function add($predicate)
    {
        $this->addAnd($predicate);
        return $this;
    }

    /**
     * Add AND specification
     *
     * @param PicoSpecification|PicoPredicate|array $predicate Filter
     * @return self
     */
    public function addAnd($predicate)
    {
        if($predicate instanceof PicoPredicate)
        {
            $this->addFilter($predicate, self::LOGIC_AND);
        }
        else if($predicate instanceof PicoSpecification)
        {
            $this->addSubfilter($predicate, self::LOGIC_AND);
        }
        else if(is_array($predicate) && count($predicate) > 1 && is_string($predicate[0]))
        {
            $this->addFilter(new PicoPredicate($predicate[0], $predicate[1]), self::LOGIC_AND);
        }
        return $this;
    }

    /**
     * Add OR specification
     *
     * @param PicoSpecification|PicoPredicate|array $predicate Filter
     * @return self
     */
    public function addOr($predicate)
    {
        if($predicate instanceof PicoPredicate)
        {
            $this->addFilter($predicate, self::LOGIC_OR);
        }
        else if($predicate instanceof PicoSpecification)
        {
            $this->addSubfilter($predicate, self::LOGIC_OR);
        }
        else if(is_array($predicate) && count($predicate) > 1 && is_string($predicate[0]))
        {
            $this->addFilter(new PicoPredicate($predicate[0], $predicate[1]), self::LOGIC_OR);
        }
        return $this;
    }

    /**
     * Add filter
     *
     * @param PicoSpecification|PicoPredicate|array $predicate Filter
     * @param string $logic Filter logic
     * @return self
     */
    private function addFilter($predicate, $logic)
    {
        if($predicate instanceof PicoPredicate)
        {
            $predicate->setFilterLogic($logic);
            $this->specifications[count($this->specifications)] = $predicate;
            if($predicate->isRequireJoin())
            {
                $this->requireJoin = true;
            }
        }
        else if($predicate instanceof PicoSpecification)
        {
            $specs = $predicate->getSpecifications();
            if(!empty($specs))
            {
                foreach($specs as $spec)
                {
                    $this->addFilter($spec, $spec->getParentFilterLogic());
                }
            }
        }
        else if(is_array($predicate))
        {
            $this->addFilterByArray($predicate, $logic);
        }
        return $this;
    }

    /**
     * Add filter by array
     *
     * @param array $predicate Filter
     * @param string $logic Filter logic
     * @return self
     */
    private function addFilterByArray($predicate, $logic)
    {
        foreach($predicate as $key=>$value)
        {
            $pred = new PicoPredicate($key, $value);
            $pred->setFilterLogic($logic);
            $this->specifications[count($this->specifications)] = $pred;
            if($pred->isRequireJoin())
            {
                $this->requireJoin = true;
            }
        }
        return $this;
    }

    /**
     * Add subfilter
     *
     * @param PicoSpecification|array $predicate Filter
     * @param string $logic Filter logic
     * @return self
     */
    private function addSubFilter($predicate, $logic)
    {
        if($predicate instanceof PicoSpecification)
        {
            $specification = new self;
            $specification->setParentFilterLogic($logic);
            $specifications = $predicate->getSpecifications();
            foreach($specifications as $pred)
            {
                if($pred instanceof PicoPredicate)
                {
                    $specification->addFilter($pred, $pred->getFilterLogic());
                    if($specification->isRequireJoin())
                    {
                        $this->requireJoin = true;
                    }
                }
                else if($pred instanceof PicoSpecification)
                {
                    $specification->addSubFilter($pred, $pred->getParentFilterLogic());
                }
            }
            $this->specifications[count($this->specifications)] = $specification;
        }
        return $this;
    }

    /**
     * Check id specification is empty or not
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->specifications);
    }

    /**
     * Check if value is empty or not
     *
     * @param mixed $value Value to be checked
     * @return boolean
     */
    public static function isValueEmpty($value)
    {
        if(!isset($value) && is_string($value))
        {
            return empty(trim($value));
        }
        return false;
    }

    /**
     * Get predicate
     *
     * @return array
     */
    public function getSpecifications()
    {
        return $this->specifications;
    }

    /**
     * Get parent filter logic
     *
     * @return string
     */
    public function getParentFilterLogic()
    {
        return $this->parentFilterLogic;
    }

    /**
     * Set parent filter logic
     *
     * @param string $parentFilterLogic Parent filter logic
     *
     * @return self
     */
    public function setParentFilterLogic($parentFilterLogic)
    {
        $this->parentFilterLogic = $parentFilterLogic;
        return $this;
    }

    /**
     * Create where
     *
     * @param PicoSpecification[] $specifications Specifications
     * @return string[]
     */
    private function getWhere($specifications)
    {
        $arr = array();
        foreach($specifications as $spec)
        {
            if(isset($spec) && $spec instanceof PicoPredicate)
            {
                $entityField = new PicoEntityField($spec->getField());
                $field = $entityField->getField();
                $parentField = $entityField->getParentField();
                $column = ($parentField == null) ? $field : $parentField.".".$field;
                if($spec->getComparation() != null)
                {
                    $where = $spec->getFilterLogic() . " " . $column . " " . $spec->getComparation()->getComparison() . " " . PicoDatabaseUtil::escapeValue($spec->getValue());
                    $arr[] = $where;
                }
            }
            else if($spec instanceof PicoSpecification)
            {
                // nested
                $arr[] = $spec->getParentFilterLogic() . " (" . $this->createWhereFromSpecification($spec) . ")";
            }
        }
        return $arr;
    }

    /**
     * Create WHERE from specification
     *
     * @param PicoSpecification $specification Filter
     * @return string
     */
    private function createWhereFromSpecification($specification)
    {
        $arr = array();
        $arr[] = "(1=1)";
        if($this->hasValue($specification))
        {
            $specifications = $specification->getSpecifications();
            foreach($specifications as $spec)
            {
                if($spec instanceof PicoPredicate)
                {
                    $entityField = new PicoEntityField($spec->getField());
                    $field = $entityField->getField();
                    $functionFormat = $entityField->getFunctionFormat();

                    $entityName = $entityField->getParentField();
                    $column = ($entityName == null) ? $field : $entityName.".".$field;
                    $columnFinal = $this->formatColumn($column, $functionFormat);

                    if($spec->getComparation() != null)
                    {
                        $arr[] = sprintf("%s %s %s %s", 
                            $spec->getFilterLogic(), 
                            $columnFinal, 
                            $spec->getComparation()->getComparison(), 
                            PicoDatabaseUtil::escapeValue($spec->getValue())
                        );
                    }
                }
                else
                {
                    $arr[] = $spec->getParentFilterLogic()." (".$this->createWhereFromSpecification($spec).")";
                }
            }
        }
        return PicoDatabaseUtil::trimWhere(implode(" ", $arr));
    }

    /**
     * Check if specification is not numm and not empty
     * @param mixed $specification Specification to be checked
     * @return boolean
     */
    private function hasValue($specification)
    {
        return $specification != null && !$specification->isEmpty();
    }

    /**
     * Format column
     *
     * @param string $column Column name
     * @param string $format Format
     * @return string
     */
    private function formatColumn($column, $format)
    {
        if($format == null || strpos($format, "%s") === false)
        {
            return $column;
        }
        return sprintf($format, $column);
    }

    /**
     * Get instance of PicoSpecification
     *
     * @return PicoSpecification
     */
    public static function getInstance()
    {
        return new self;
    }

    /**
     * Magic method to handle undefined method
     *
     * @param string $method Method called
     * @param array $params Parameters given
     * @return self|mixed|null
     */
    public function __call($method, $params)
    {
        if (strncasecmp($method, "set", 3) === 0 && isset($params)) {
            $field = lcfirst(substr($method, 3));
            $value = $params[0];
            $this->addPredicate($field, $value);
            return $this;
        }
    }

    /**
     * Magic object to set value
     *
     * @param string $field Field name
     * @param mixed|mixed[] $value Field value
     */
    public function __set($field, $value)
    {
        $this->addPredicate($field, $value);
    }

    /**
     * Add predicate
     *
     * @param string $field Field name
     * @param mixed|mixed[] $value Field value
     * @return self
     */
    private function addPredicate($field, $value)
    {
        if($this->defaultLogic == self::LOGIC_OR)
        {
            $this->addOr(new PicoPredicate($field, $value));
        }
        else
        {
            $this->addAnd(new PicoPredicate($field, $value));
        }
    }

    /**
     * Magic method to debug object. This method is for debug purpose only.
     *
     * @return string
     */
    public function __toString()
    {
        $specification = implode(" ", $this->getWhere($this->specifications));
        if(stripos($specification, "and ") === 0)
        {
            $specification = substr($specification, 4);
        }
        return $specification;
    }

    /**
     * Get specification from user input
     *
     * @param PicoRequestBase $request Request
     * @param PicoSpecificationFilter[]|null $map Filter map
     * @return PicoSpecification
     */
    public static function fromUserInput($request, $map = null)
    {
        $specification = new self;
        if(isset($map) && is_array($map))
        {
            foreach($map as $key=>$filter)
            {
                $filterValue = $request->get($key);
                if($filterValue != null && !self::isValueEmpty($filterValue) && $filter instanceof PicoSpecificationFilter)
                {
                    if($filter->isNumber() || $filter->isBoolean() || $filter->isArrayNumber() || $filter->isArrayBoolean() || $filter->isArrayString())
                    {
                        $specification->addAnd(PicoPredicate::getInstance()->equals($filter->getColumnName(), $filter->valueOf($filterValue)));
                    }
                    else if($filter->isFulltext())
                    {
                        $specification->addAnd(self::fullTextSearch($filter->getColumnName(), $filterValue));
                    }
                    else
                    {
                        $specification->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower($filter->getColumnName()), PicoPredicate::generateLikeContains(strtolower($filterValue))));
                    }
                }
            }
        }
        return $specification;
    }

    /**
     * Create full text search
     *
     * @param string $columnName Column name
     * @param string $keywords Keywords
     * @return self
     */
    public static function fullTextSearch($columnName, $keywords)
    {
        $specification = new self;
        $arr = explode(" ", $keywords);
        foreach($arr as $word)
        {
            if(!empty($word))
            {
                $specification->addAnd(
                    PicoPredicate::getInstance()
                        ->like(PicoPredicate::functionLower($columnName), PicoPredicate::generateLikeContains(strtolower($word)))
                );
            }
        }
        return $specification;
    }

    /**
     * Filter
     *
     * @param string $columnName Column name
     * @param string $dataType Data type
     * @return PicoSpecificationFilter
     */
    public static function filter($columnName, $dataType)
    {
        return new PicoSpecificationFilter($columnName, $dataType);
    }

    /**
     * Get default logic
     *
     * @return  string
     */
    public function getDefaultLogic()
    {
        return $this->defaultLogic;
    }

    /**
     * Set default logic
     *
     * @param  string  $defaultLogic  Default logic
     *
     * @return  self
     */
    public function setDefaultLogic($defaultLogic)
    {
        $this->defaultLogic = $defaultLogic;

        return $this;
    }

    /**
     * Set default logic AND
     *
     * @return  self
     */
    public function setDefaultLogicAnd()
    {
        $this->defaultLogic = self::LOGIC_AND;

        return $this;
    }

    /**
     * Set default logic OR
     *
     * @return  self
     */
    public function setDefaultLogicOr()
    {
        $this->defaultLogic = self::LOGIC_OR;

        return $this;
    }

    /**
     * Get check if require real join table
     *
     * @return boolean
     */ 
    public function getRequireJoin()
    {
        return $this->requireJoin;
    }
}