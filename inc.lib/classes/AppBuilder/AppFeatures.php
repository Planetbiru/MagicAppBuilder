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
    
    private $subquery = false;
    
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
            $this->approvalRequired = $this->isTrue($features->get('approvalRequired'));
            $this->approvalNote = $this->isTrue($features->get('approvalNote'));
            $this->trashRequired = $this->isTrue($features->get('trashRequired'));
            $this->subquery = $this->isTrue($features->get('subquery'));
            $this->approvalType = $features->get('approvalType') == 2 ? 2 : 1;
            $this->approvalPosition = $features->get('approvalPosition') == self::BEFORE_DATA ? self::BEFORE_DATA : self::AFTER_DATA;
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
     * @param  integer  $approvalType  Approval type
     *
     * @return  self
     */ 
    public function setApprovalType($approvalType)
    {
        $this->approvalType = $approvalType;

        return $this;
    }
}