<?php

namespace AppBuilder\Module;

class FieldConfig
{

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $fieldName;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $fieldLabel;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $includeInsert;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $includeEdit;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $includeDetail;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $includeList;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $includeExport;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $isKey;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $isInputRequired;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $elementType;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $filterElementType;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $dataType;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $inputFilter;
    public $referenceData;
    public $referenceFilter;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $multipleData;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $multipleFilter;

    public function __construct($data)
    {
        foreach ($data as $key => $value) {
            if(in_array($key, [
                'includeInsert', 
                'includeEdit', 
                'includeDetail', 
                'includeList',
                'includeExport',
                'isKey',
                'isInputRequired',
                'multipleData',
                'multipleFilter'
                ]))
            {
                $this->$key = ModuleDataUtil::isTrue($value);
            }
            else
            {
                $this->$key = $value;
            }
            
        }
        $this->updateReferenceData();
        
        $this->updateFilterData();
    }

    private function updateReferenceData()
    {
        if (isset($this->referenceData)) {
            $this->referenceData = (object) $this->referenceData;
            if (isset($this->referenceData->entity)) {
                $this->referenceData->entity = (object) $this->referenceData->entity;
                if (isset($this->referenceData->entity->specification)) {
                    $this->referenceData->entity->specification = array_map(function ($spec) {
                        return (object) $spec;
                    }, $this->referenceData->entity->specification);
                }
                if (isset($this->referenceData->entity->sortable)) {
                    $this->referenceData->entity->sortable = array_map(function ($sort) {
                        return (object) $sort;
                    }, $this->referenceData->entity->sortable);
                }
            }
            if (isset($this->referenceData->map)) {
                $this->referenceData->map = array_map(function ($map) {
                    return (object) $map;
                }, $this->referenceData->map);
            }
        }
    }

    private function updateFilterData()
    {
        if (isset($this->referenceFilter)) {
            $this->referenceFilter = (object) $this->referenceFilter;
            if (isset($this->referenceFilter->entity)) {
                $this->referenceFilter->entity = (object) $this->referenceFilter->entity;
                if (isset($this->referenceFilter->entity->specification)) {
                    $this->referenceFilter->entity->specification = array_map(function ($spec) {
                        return (object) $spec;
                    }, $this->referenceFilter->entity->specification);
                }
                if (isset($this->referenceFilter->entity->sortable)) {
                    $this->referenceFilter->entity->sortable = array_map(function ($sort) {
                        return (object) $sort;
                    }, $this->referenceFilter->entity->sortable);
                }
            }
            if (isset($this->referenceFilter->map)) {
                $this->referenceFilter->map = array_map(function ($map) {
                    return (object) $map;
                }, $this->referenceFilter->map);
            }
        }
    }
}
