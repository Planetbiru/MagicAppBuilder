<?php

namespace AppBuilder\Module;

/**
 * Class EntityConfig
 *
 * Represents the configuration for entities used in a module, including
 * main entity, approval entity, and trash entity, along with flags that
 * indicate whether approval and trash functionalities are required.
 */
class EntityConfig {
    /**
     * Main entity configuration.
     *
     * @var EntityItem
     */
    public $mainEntity;

    /**
     * Approval entity configuration.
     *
     * @var EntityItem
     */
    public $approvalEntity;

    /**
     * Trash entity configuration.
     *
     * @var EntityItem
     */
    public $trashEntity;

    /**
     * Indicates if approval is required.
     *
     * @var bool
     */
    public $approvalRequired;

    /**
     * Indicates if trash functionality is required.
     *
     * @var bool
     */
    public $trashRequired;

    /**
     * EntityConfig constructor.
     *
     * @param array $data Configuration data.
     */
    public function __construct($data) {
        $this->mainEntity = new EntityItem($data['mainEntity']);
        $this->approvalEntity = new EntityItem($data['approvalEntity']);
        $this->trashEntity = new EntityItem($data['trashEntity']);
        $this->approvalRequired = ModuleDataUtil::isTrue($data['approvalRequired']);
        $this->trashRequired = ModuleDataUtil::isTrue($data['trashRequired']);
    }
}