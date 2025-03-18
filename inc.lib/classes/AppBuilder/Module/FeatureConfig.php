<?php

namespace AppBuilder\Module;

class FeatureConfig {
    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $subquery;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $activateDeactivate;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $sortOrder;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $exportToExcel;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $exportToCsv;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $approvalRequired;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $approvalNote;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $trashRequired;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $approvalType;

    /**
     * Undocumented variable
     *
     * @var string
     */
    public $approvalPosition;

    /**
     * Undocumented variable
     *
     * @var bool
     */
    public $ajaxSupport;

    public function __construct($data) {
        foreach ($data as $key => $value) {
            if(in_array($key, ['approvalType', 'approvalPosition']))
            {
                // String
                $this->$key = $value;
            }
            else
            {
                $this->$key = ModuleDataUtil::isTrue($value);
            }
        }
    }
}
