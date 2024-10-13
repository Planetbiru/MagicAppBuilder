<?php

namespace AppBuilder;

use MagicObject\MagicObject;

class AppFeatures
{
    const BEFORE_DATA = 'before-data';
    const AFTER_DATA = 'after-data';
    
    /**
     * Activate/deactivate feature
     *
     * @var bool
     */
    private $activateDeactivate = false;

    /**
     * Sort order feature
     *
     * @var bool
     */
    private $sortOrder = false;

    /**
     * Approval required feature
     *
     * @var bool
     */
    private $approvalRequired = false;

    /**
     * Approval note feature
     *
     * @var bool
     */
    private $approvalNote = false;

    /**
     * Trash required feature
     *
     * @var bool
     */
    private $trashRequired = false;

    /**
     * Approval type (1 or 2)
     *
     * @var int
     */
    private $approvalType = 1;

    /**
     * Approval position (before or after data)
     *
     * @var string
     */
    private $approvalPosition = '';

    /**
     * Subquery feature
     *
     * @var bool
     */
    private $subquery = false;

    /**
     * Export to Excel feature
     *
     * @var bool
     */
    private $exportToExcel = false;

    /**
     * Export to CSV feature
     *
     * @var bool
     */
    private $exportToCsv = false;

    /**
     * Use temporary for export feature
     *
     * @var bool
     */
    private $exportUseTemporary = false;

    /**
     * Constructor
     *
     * @param MagicObject $features An object containing feature settings.
     */
    public function __construct($features)
    {
        if($features != null)
        {
            $this->activateDeactivate = $this->isTrue($features->get('activateDeactivate'));
            $this->sortOrder = $this->isTrue($features->get('sortOrder'));
            $this->exportToExcel = $this->isTrue($features->get('exportToExcel'));
            $this->exportToCsv = $this->isTrue($features->get('exportToCsv'));
            $this->approvalRequired = $this->isTrue($features->get('approvalRequired'));
            $this->approvalNote = $this->isTrue($features->get('approvalNote'));
            $this->trashRequired = $this->isTrue($features->get('trashRequired'));
            $this->subquery = $this->isTrue($features->get('subquery'));
            $this->approvalType = $features->get('approvalType') == 2 ? 2 : 1;
            $this->approvalPosition = $features->get('approvalPosition') == self::BEFORE_DATA ? self::BEFORE_DATA : self::AFTER_DATA;
            $this->exportUseTemporary = $this->isTrue($features->get('exportUseTemporary'));
        }
    }
    
    /**
     * Check if the provided value represents true.
     *
     * @param mixed $value The value to check.
     * @return bool Returns true if the value is equivalent to true.
     */
    private function isTrue($value)
    {
        return $value == '1' || strtolower($value) == 'true' || $value === 1 || $value === true;
    }

    /**
     * Get the status of the activate/deactivate feature.
     *
     * @return bool Returns true if the feature is activated.
     */
    public function isActivateDeactivate()
    {
        return $this->activateDeactivate == 1;
    }

    /**
     * Get the status of the sort order feature.
     *
     * @return bool Returns true if sort order is enabled.
     */
    public function isSortOrder()
    {
        return $this->sortOrder == 1;
    }

    /**
     * Get the status of the approval required feature.
     *
     * @return bool Returns true if approval is required.
     */
    public function isApprovalRequired()
    {
        return $this->approvalRequired == 1;
    }

    /**
     * Get the status of the approval note feature.
     *
     * @return bool Returns true if approval note is enabled.
     */ 
    public function isApprovalNote()
    {
        return $this->approvalNote == 1;
    }

    /**
     * Get the status of the trash required feature.
     *
     * @return bool Returns true if trash is required.
     */
    public function isTrashRequired()
    {
        return $this->trashRequired == 1;
    }

    /**
     * Get the approval position.
     *
     * @return string Returns the approval position.
     */
    public function getApprovalPosition()
    {
        return $this->approvalPosition;
    }

    /**
     * Get the status of the subquery feature.
     *
     * @return bool Returns true if subquery is enabled.
     */
    public function getSubquery()
    {
        return $this->subquery == 1;
    }

    /**
     * Get the approval type.
     *
     * @return int Returns the approval type (1 or 2).
     */
    public function getApprovalType()
    {
        return $this->approvalType;
    }

    /**
     * Set the approval type.
     *
     * @param int $approvalType The approval type (1 or 2).
     * @return self Returns the current instance for method chaining.
     */
    public function setApprovalType($approvalType)
    {
        $this->approvalType = $approvalType;

        return $this;
    }

    /**
     * Get the status of the export to Excel feature.
     *
     * @return bool Returns true if export to Excel is enabled.
     */
    public function isExportToExcel()
    {
        return $this->exportToExcel;
    }

    /**
     * Set the export to Excel feature.
     *
     * @param bool $exportToExcel Indicates whether to enable export to Excel.
     * @return self Returns the current instance for method chaining.
     */
    public function setExportToExcel($exportToExcel)
    {
        $this->exportToExcel = $exportToExcel;

        return $this;
    }

    /**
     * Get the status of the export to CSV feature.
     *
     * @return bool Returns true if export to CSV is enabled.
     */
    public function isExportToCsv()
    {
        return $this->exportToCsv;
    }

    /**
     * Set the export to CSV feature.
     *
     * @param bool $exportToCsv Indicates whether to enable export to CSV.
     * @return self Returns the current instance for method chaining.
     */
    public function setExportToCsv($exportToCsv)
    {
        $this->exportToCsv = $exportToCsv;

        return $this;
    }

    /**
     * Get the status of using temporary export.
     *
     * @return bool Returns true if temporary export is used.
     */ 
    public function getExportUseTemporary()
    {
        return $this->exportUseTemporary;
    }

    /**
     * Set the use of temporary export.
     *
     * @param bool $exportUseTemporary Indicates whether to use temporary export.
     * @return self Returns the current instance for method chaining.
     */
    public function setExportUseTemporary($exportUseTemporary)
    {
        $this->exportUseTemporary = $exportUseTemporary;

        return $this;
    }
}