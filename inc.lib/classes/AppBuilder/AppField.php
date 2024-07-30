<?php

namespace AppBuilder;

use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoObjectParser;

class AppField
{

    /**
     * Field name
     *
     * @var string
     */
    private $fieldName;

    /**
     * Field label
     *
     * @var string
     */
    private $fieldLabel;

    /**
     * Include insert
     *
     * @var boolean
     */
    private $includeInsert;

    /**
     * Include edit
     *
     * @var boolean
     */
    private $includeEdit;

    /**
     * Include detail
     *
     * @var boolean
     */
    private $includeDetail;

    /**
     * Include list
     *
     * @var boolean
     */
    private $includeList;

    /**
     * Include export
     *
     * @var boolean
     */
    private $includeExport;

    /**
     * Key
     *
     * @var boolean
     */
    private $key;

    /**
     * Required
     *
     * @var boolean
     */
    private $required;

    /**
     * Element type
     *
     * @var string
     */
    private $elementType;

    /**
     * Filter element type
     *
     * @var string
     */
    private $filterElementType;

    /**
     * Data type
     *
     * @var string
     */
    private $dataType;

    /**
     * Input filter
     *
     * @var string
     */
    private $inputFilter;
    
    /**
     * Reference data
     *
     * @var MagicObject
     */
    private $referenceData = array();


    /**
     * Reference filter
     *
     * @var MagicObject
     */
    private $referenceFilter = array();

    /**
     * Additional output
     *
     * @var array
     */
    private $additionalOutput = array();

    public function __construct($value)
    {
        $this->fieldName = $value->getFieldName();
        $this->fieldLabel = $value->getFieldLabel();
        $this->includeInsert = $this->isTrue($value->getIncludeInsert());
        $this->includeEdit = $this->isTrue($value->getIncludeEdit());
        $this->includeDetail = $this->isTrue($value->getIncludeDetail());
        $this->includeList = $this->isTrue($value->getIncludeList());
        $this->includeExport = $this->isTrue($value->getIncludeExport());
        $this->key = $this->isTrue($value->getIsKey());
        $this->required = $this->isTrue($value->getIsInputRequired());
        $this->elementType = $value->getelementType();
        $this->dataType = $value->getdataType();
        $this->filterElementType = $value->getFilterElementType();
        $this->inputFilter = $value->getInputFilter();
        $this->referenceData = PicoObjectParser::parseJsonRecursive($value->getreferenceData());
        $this->referenceFilter = PicoObjectParser::parseJsonRecursive($value->getreferenceFilter());
        $this->additionalOutput = $value->getAdditionalOutput();
    }

    /**
     * Get nonupdateable columns
     *
     * @param EntityInfo $entityInfo
     * @return string[]
     */
    public static function getNonupdatetableColumns($entityInfo)
    {
        $columns = array();
        $columns[] = $entityInfo->getTimeCreate();
        $columns[] = $entityInfo->getAdminCreate();
        $columns[] = $entityInfo->getIpCreate();
        return $columns;
    }

    /**
     * Check if value is true
     *
     * @param mixed $value
     * @return boolean
     */
    private function isTrue($value)
    {
        if(is_string($value))
        {
            $val = $value == '1' || strtolower($value) == 'true';
        }
        else
        {
            $val = $value === 1 || $value === true;
        }
        return $val;
    }

    /**
     * Get field name
     *
     * @return  string
     */ 
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Get field label
     *
     * @return  string
     */ 
    public function getFieldLabel()
    {
        return $this->fieldLabel;
    }

    /**
     * Get include insert
     *
     * @return  boolean
     */ 
    public function getIncludeInsert()
    {
        return $this->includeInsert;
    }

    /**
     * Get include edit
     *
     * @return  boolean
     */ 
    public function getIncludeEdit()
    {
        return $this->includeEdit;
    }

    /**
     * Get include detail
     *
     * @return  boolean
     */ 
    public function getIncludeDetail()
    {
        return $this->includeDetail;
    }

    /**
     * Get include list
     *
     * @return  boolean
     */ 
    public function getIncludeList()
    {
        return $this->includeList;
    }

    /**
     * Get key
     *
     * @return  boolean
     */ 
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get required
     *
     * @return  boolean
     */ 
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Get element type
     *
     * @return  string
     */ 
    public function getElementType()
    {
        return $this->elementType;
    }

    /**
     * Get filter element type
     *
     * @return  string
     */ 
    public function getFilterElementType()
    {
        return $this->filterElementType;
    }

    /**
     * Get data type
     *
     * @return  string
     */ 
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * Get input filter
     *
     * @return  string
     */ 
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

    /**
     * Get reference data
     *
     * @return MagicObject
     */ 
    public function getReferenceData()
    {
        return $this->referenceData;
    }

    /**
     * Get reference filter
     *
     * @return MagicObject
     */ 
    public function getReferenceFilter()
    {
        return $this->referenceFilter;
    }

    /**
     * Get additional output
     *
     * @return  array
     */ 
    public function getAdditionalOutput()
    {
        return $this->additionalOutput;
    }

    /**
     * Get include export
     *
     * @return  boolean
     */ 
    public function getIncludeExport()
    {
        return $this->includeExport;
    }

    /**
     * Set include export
     *
     * @param  boolean  $includeExport  Include export
     *
     * @return  self
     */ 
    public function setIncludeExport($includeExport)
    {
        $this->includeExport = $includeExport;

        return $this;
    }
}