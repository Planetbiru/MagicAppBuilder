<?php

namespace AppBuilder\Module;

/**
 * Class ModuleConfig
 *
 * Represents the full configuration of a module, including its entity structure,
 * fields, filters, sorting options, features, and metadata such as module name
 * and menu integration.
 */
class ModuleConfig {

    /**
     * Configuration for the module's main, approval, and trash entities.
     *
     * @var EntityConfig
     */
    public $entity;

    /**
     * List of field configurations used in the module.
     *
     * @var FieldConfig[]
     */
    public $fields;

    /**
     * List of specifications (filters or conditions) for the module.
     *
     * @var SpecificationConfig[]
     */
    public $specifications;

    /**
     * List of sort configurations used to order data in lists.
     *
     * @var SortableConfig[]
     */
    public $sortables;

    /**
     * Feature-level flags controlling module capabilities.
     *
     * @var FeatureConfig
     */
    public $feature;

    /**
     * Unique code identifying the module.
     *
     * @var string
     */
    public $moduleCode;

    /**
     * Human-readable name of the module.
     *
     * @var string
     */
    public $moduleName;

    /**
     * File name or identifier of the module definition.
     *
     * @var string
     */
    public $moduleFile;

    /**
     * Whether the module should appear as a menu item.
     *
     * @var bool
     */
    public $moduleAsMenu;

    /**
     * Menu label or identifier under which the module appears.
     *
     * @var string
     */
    public $moduleMenu;

    /**
     * The target location or context for the module (e.g., backend, frontend).
     *
     * @var string
     */
    public $target;

    /**
     * Whether this module is responsible for updating entity data.
     *
     * @var bool
     */
    public $updateEntity;

    /**
     * ModuleConfig constructor.
     *
     * Initializes the module configuration from a JSON string.
     *
     * @param string $jsonData JSON string representing the module configuration.
     */
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
