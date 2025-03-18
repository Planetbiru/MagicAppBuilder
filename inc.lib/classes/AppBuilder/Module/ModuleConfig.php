<?php

namespace AppBuilder\Module;

class ModuleConfig {

    /**
     * Undocumented variable
     *
     * @var EntityConfig
     */
    public $entity;

    /**
     * Undocumented variable
     *
     * @var FieldConfig[]
     */
    public $fields;

    /**
     * Undocumented variable
     *
     * @var SpecificationConfig[]
     */
    public $specifications;

    /**
     * Undocumented variable
     *
     * @var SortableConfig[]
     */
    public $sortables;

    /**
     * Undocumented variable
     *
     * @var FeatureConfig
     */
    public $feature;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $moduleCode;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $moduleName;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $moduleFile;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $moduleAsMenu;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $moduleMenu;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $target;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $updateEntity;

    public function __construct($jsonData) {
        $data = json_decode($jsonData, true);

        $this->entity = new EntityConfig($data['entity']);

        $this->fields = [];
        foreach ($data['fields'] as $fieldData) {
            $this->fields[] = new FieldConfig($fieldData);
        }

        $this->specifications = [];
        foreach ($data['specification'] as $specificationData) {
            $this->specifications[] = new SpecificationConfig($specificationData);
        }

        $this->sortables = [];
        foreach ($data['sortable'] as $sortableData) {
            $this->sortables[] = new SortableConfig($sortableData);
        }

        $this->feature = new FeatureConfig($data['features']);

        $this->moduleCode = $data['moduleCode'];
        $this->moduleName = $data['moduleName'];
        $this->moduleFile = $data['moduleFile'];
        $this->moduleAsMenu = ModuleDataUtil::isTrue($data['moduleAsMenu']);
        $this->moduleMenu = $data['moduleMenu'];
        $this->target = $data['target'];
        $this->updateEntity = ModuleDataUtil::isTrue($data['updateEntity']);
    }
}
