<?php

namespace MagicObject\Database;

use MagicObject\Request\PicoRequestBase;
use MagicObject\Util\Database\PicoDatabaseUtil;

class PicoSpecification
{
    const LOGIC_AND = "and";
    const LOGIC_OR = "or";

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
     * Return true if require real join table
     *
     * @return boolean
     */
    public function isRequireJoin()
    {
        return $this->requireJoin;
    }

    /**
     * Add AND specification
     *
     * @param PicoSpecification|PicoPredicate|array $predicate
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
     * @param PicoSpecification|PicoPredicate|array $predicate
     * @return self
     */
    public function addAnd($predicate)
    {
        if($predicate instanceof PicoPredicate)
        {
            $this->addFilter($predicate, self::LOGIC_AND);
        }
        if($predicate instanceof PicoSpecification)
        {
            $this->addSubfilter($predicate, self::LOGIC_AND);      
        } 
        return $this;
    }

    /**
     * Add OR specification
     *
     * @param PicoSpecification|PicoPredicate|array $predicate
     * @return self
     */
    public function addOr($predicate)
    {
        if($predicate instanceof PicoPredicate)
        {
            $this->addFilter($predicate, self::LOGIC_OR);      
        }  
        if($predicate instanceof PicoSpecification)
        {
            $this->addSubfilter($predicate, self::LOGIC_OR);      
        }  
        return $this;
    }

    /**
     * Add filter
     *
     * @param PicoSpecification|PicoPredicate|array $predicate
     * @param string $logic
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
        else if(is_array($predicate))
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
        }
        return $this;
    }

    /**
     * Add subfilter
     *
     * @param PicoSpecification|array $predicate
     * @param string $logic
     * @return self
     */
    private function addSubFilter($predicate, $logic)
    {
        if($predicate instanceof PicoSpecification)
        {
            $specification = new self();
            $specification->setParentFilterLogic($logic);
            $specifications = $predicate->getSpecifications();
            foreach($specifications as $pred)
            {
                $specification->addFilter($pred, $pred->getFilterLogic());
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
     * @param string $parentFilterLogic  Parent filter logic
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
     * @param PicoSpecification[] $specifications
     * @return string[]
     */
    private function getWhere($specifications)
    {
        $arr = array();
        foreach($specifications as $spec)
        {
            if($spec instanceof PicoPredicate)
            {
                $entityField = new PicoEntityField($spec->getField());
                $field = $entityField->getField();
                $entityName = $entityField->getEntity();
                $column = ($entityName == null) ? $field : $entityName.".".$field;
                $arr[] = $spec->getFilterLogic() . " " . $column . " " . $spec->getComparation()->getComparison() . " " . PicoDatabaseUtil::escapeValue($spec->getValue());               
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
     * @param PicoSpecification $specification
     * @return string
     */
    private function createWhereFromSpecification($specification)
    {
        
        $arr = array();
        $arr[] = "(1=1)";
        if($specification != null && !$specification->isEmpty())
        {
            $specifications = $specification->getSpecifications();
            foreach($specifications as $spec)
            {           
                $entityField = new PicoEntityField($spec->getField());
                $field = $entityField->getField();
                $functionFormat = $entityField->getFunctionFormat();

                $entityName = $entityField->getEntity();
                $column = ($entityName == null) ? $field : $entityName.".".$field;
                $columnFinal = $this->formatColumn($column, $functionFormat);
                
                $arr[] = $spec->getFilterLogic() . " " . $columnFinal . " " . $spec->getComparation()->getComparison() . " " . PicoDatabaseUtil::escapeValue($spec->getValue());      
            }
        }
        return PicoDatabaseUtil::trimWhere(implode(" ", $arr));
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
        return new PicoSpecification();
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
     * @param PicoRequestBase $request
     * @param PicoSpecificationFilter[]|null $map
     * @return PicoSpecification
     */
    public static function fromUserInput($request, $map = null)
    {
        $specification = new PicoSpecification();
        if(isset($map) && is_array($map))
        {
            foreach($map as $key=>$filter)
            {
                $filterValue = $request->get($key);
                if($filterValue != null && trim($filterValue) != "" && $filter instanceof PicoSpecificationFilter)
                {
                    if($filter->isNumber() || $filter->isBoolean())
                    {
                        $specification->addAnd(PicoPredicate::getInstance()->equals($filter->getColumnName(), $filter->valueOf($filterValue)));
                    }
                    else if($filter->isFulltext())
                    {
                        $specification->addAnd(self::fullTextSearch($filter->getColumnName(), $filterValue));
                    }
                    else
                    {
                        $specification->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower($filter->getColumnName()), PicoPredicate::generateCenterLike(strtolower($filterValue))));
                    }
                }
            }
        }
        return $specification;
    }

    /**
     * Create full text search
     *
     * @param string $columnName
     * @param string $keywords
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
                $specification->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower($columnName), PicoPredicate::generateCenterLike(strtolower($word))));
            }
        }
        return $specification;
    }

    /**
     * Filter
     *
     * @param string $columnName
     * @param string $dataType
     * @return PicoSpecificationFilter
     */
    public static function filter($columnName, $dataType)
    {
        return new PicoSpecificationFilter($columnName, $dataType);
    }
}