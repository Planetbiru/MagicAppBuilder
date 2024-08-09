<?php

namespace AppBuilder;

use MagicObject\MagicObject;

class AppFeatures
{
    const BEFORE_DATA = 'before-data';
    const AFTER_DATA = 'after-data';
    
    /**
     * Activate/deactivate
     *
     * @var boolean
     */
    private $activateDeactivate = false;
    
    /**
     * Sort order
     *
     * @var boolean
     */
    private $sortOrder = false;
    
    /**
     * Approval required
     *
     * @var boolean
     */
    private $approvalRequired = false;
    
    /**
     * Approval note
     *
     * @var boolean
     */
    private $approvalNote = false;
    
    /**
     * Trash required
     *
     * @var boolean
     */
    private $trashRequired = false;
    
    /**
     * Approval type
     *
     * @var integer
     */
    private $approvalType = 1;

    /**
     * Approval position
     *
     * @var string
     */
    private $approvalPosition = '';
    
    /**
     * Subquery
     * @var boolean
     */
    private $subquery = false;

    /**
     * Export to Excel
     * @var boolean
     */
    private $exportToExcel = false;

    /**
     * Export to CSV
     * @var boolean
     */
    private $exportToCsv = false;
    
    /**
     * Export use temporary
     *
     * @var boolean
     */
    private $exportUseTemporary = false;
    
    /**
     * Constructor
     *
     * @param MagicObject $features
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
     * Check if value is true
     *
     * @param mixed $value
     * 
     * @return boolean
     */
    private function isTrue($value)
    {
        return $value == '1' || strtolower($value) == 'true' || $value === 1 || $value === true;
    }

    /**
     * Get the value of activateDeactivate
     * 
     * @return boolean
     */ 
    public function isActivateDeactivate()
    {
        return $this->activateDeactivate == 1;
    }

    /**
     * Get the value of sortOrder
     * 
     * @return boolean
     */ 
    public function isSortOrder()
    {
        return $this->sortOrder == 1;
    }

    /**
     * Get the value of approvalRequired
     * 
     * @return boolean
     */ 
    public function isApprovalRequired()
    {
        return $this->approvalRequired == 1;
    }

    /**
     * Get the value of approvalNote
     * 
     * @return boolean
     */ 
    public function isApprovalNote()
    {
        return $this->approvalNote == 1;
    }

    /**
     * Get the value of trashRequired
     * 
     * @return boolean
     */ 
    public function isTrashRequired()
    {
        return $this->trashRequired == 1;
    }

    /**
     * Get approval position
     *
     * @return  string
     */ 
    public function getApprovalPosition()
    {
        return $this->approvalPosition;
    }

    /**
     * Get the value of subquery
     */ 
    public function getSubquery()
    {
        return $this->subquery == 1;
    }

    /**
     * Get approval type
     *
     * @return  integer
     */ 
    public function getApprovalType()
    {
        return $this->approvalType;
    }

    /**
     * Set approval type
     *
     * @param  integer $approvalType Approval type
     *
     * @return  self
     */ 
    public function setApprovalType($approvalType)
    {
        $this->approvalType = $approvalType;

        return $this;
    }

    /**
     * Get export to Excel
     *
     * @return  boolean
     */ 
    public function isExportToExcel()
    {
        return $this->exportToExcel;
    }

    /**
     * Set export to Excel
     *
     * @param  boolean $exportToExcel Export to Excel
     *
     * @return  self
     */ 
    public function setExportToExcel($exportToExcel)
    {
        $this->exportToExcel = $exportToExcel;

        return $this;
    }

    /**
     * Get export to CSV
     *
     * @return  boolean
     */ 
    public function isExportToCsv()
    {
        return $this->exportToCsv;
    }

    /**
     * Set export to CSV
     *
     * @param  boolean $exportToCsv Export to CSV
     *
     * @return  self
     */ 
    public function setExportToCsv($exportToCsv)
    {
        $this->exportToCsv = $exportToCsv;

        return $this;
    }

    /**
     * Get export use temporary
     *
     * @return  boolean
     */ 
    public function getExportUseTemporary()
    {
        return $this->exportUseTemporary;
    }

    /**
     * Set export use temporary
     *
     * @param  boolean $exportUseTemporary Export use temporary
     *
     * @return  self
     */ 
    public function setExportUseTemporary($exportUseTemporary)
    {
        $this->exportUseTemporary = $exportUseTemporary;

        return $this;
    }
}