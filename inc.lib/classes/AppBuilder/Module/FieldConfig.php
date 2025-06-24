<?php

namespace AppBuilder\Module;

/**
 * Class FieldConfig
 *
 * Represents the configuration of a single field in a module. This includes
 * visibility in various views (insert, edit, detail, list, export), data type,
 * UI element type, filter behavior, and references to external or related data.
 */
class FieldConfig
{

    /**
     * The field's internal name (usually matches the database column).
     *
     * @var string
     */
    public $fieldName;

    /**
     * The label to be displayed in the UI for this field.
     *
     * @var string
     */
    public $fieldLabel;

    /**
     * Indicates if the field is included in the insert view/form.
     *
     * @var bool
     */
    public $includeInsert;

    /**
     * Indicates if the field is included in the edit view/form.
     *
     * @var bool
     */
    public $includeEdit;

    /**
     * Indicates if the field is shown in the detail view.
     *
     * @var bool
     */
    public $includeDetail;

    /**
     * Indicates if the field is shown in the list/grid view.
     *
     * @var bool
     */
    public $includeList;

    /**
     * Indicates if the field should be included in exported data.
     *
     * @var bool
     */
    public $includeExport;

    /**
     * Indicates whether the field is a key (typically primary key).
     *
     * @var bool
     */
    public $isKey;

    /**
     * Indicates if the field is required during data input.
     *
     * @var bool
     */
    public $isInputRequired;

    /**
     * The UI element type used to render this field (e.g., text, select, checkbox).
     *
     * @var string
     */
    public $elementType;

    /**
     * The UI element type used to filter this field in the list view.
     *
     * @var string
     */
    public $filterElementType;

    /**
     * The data type of the field (e.g., string, int, date).
     *
     * @var string
     */
    public $dataType;

    /**
     * The input filter used for this field (e.g., trim, sanitize).
     *
     * @var string
     */
    public $inputFilter;

    /**
     * Reference data configuration for the input (e.g., for dropdown values).
     *
     * @var object|array
     */
    public $referenceData;

    /**
     * Reference data configuration used in filters.
     *
     * @var object|array
     */
    public $referenceFilter;

    /**
     * Indicates if the field supports multiple input values (e.g., multi-select).
     *
     * @var bool
     */
    public $multipleData;

    /**
     * Indicates if the field supports multiple values for filtering.
     *
     * @var bool
     */
    public $multipleFilter;

    /**
     * FieldConfig constructor.
     *
     * Initializes field configuration from an associative array.
     * Automatically converts booleans and normalizes reference data structure.
     *
     * @param array $data Associative array of field configuration.
     */
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

    /**
     * Converts referenceData fields into object structures if applicable.
     *
     * Handles nested entities and maps for consistency in usage.
     */
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

    /**
     * Converts referenceFilter fields into object structures if applicable.
     *
     * Handles nested entities and maps similar to referenceData.
     */
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
