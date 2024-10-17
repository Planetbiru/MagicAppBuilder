<?php

namespace MagicApp;

use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\Request\PicoRequestBase;
use MagicObject\SecretObject;

/**
 * Class AppUserPermission
 *
 * Manages user permissions for various actions within the application.
 */
class AppUserPermission
{
    /**
     * Application configuration
     *
     * @var SecretObject
     */
    private $appConfig;
    
    /**
     * Entity representing user roles.
     *
     * @var MagicObject
     */
    private $entity;
    
    /**
     * Current module context.
     *
     * @var PicoModule
     */
    private $currentModule;
    
    /**
     * Allowed show list
     *
     * @var boolean
     */
    private $allowedList;
    
    /**
     * Allowed show detail
     *
     * @var boolean
     */
    private $allowedDetail;
    
    /**
     * Allowed create
     *
     * @var boolean
     */
    private $allowedCreate;
    
    /**
     * Allowed update
     *
     * @var boolean
     */
    private $allowedUpdate;
    
    /**
     * Allowed delete
     *
     * @var boolean
     */
    private $allowedDelete;
    
    /**
     * Allowed approve/reject
     *
     * @var boolean
     */
    private $allowedApprove;
    
    /**
     * Allowed short order
     *
     * @var boolean
     */
    private $allowedSortOrder;

    /**
     * Allowed batch action
     *
     * @var boolean
     */
    private $allowedBatchAction;
    
    /**
     * Indicates if permissions have been initialized.
     *
     * @var boolean
     */
    private $initialized = false;
    
    /**
     * User level
     *
     * @var string
     */
    private $userLevelId;

    /**
     * Current user
     *
     * @var MagicObject
     */
    private $currentUser;

    /**
     * User action
     * 
     * @var string
     */
    private $userAction;
    
    /**
     * Constructor
     *
     * @param SecretObject $appConfig
     * @param PicoDatabase $database
     * @param MagicObject $appUserRole
     * @param PicoModule $currentModule
     * @param AppUser $currentUser
     */
    public function __construct($appConfig, $database, $appUserRole, $currentModule, $currentUser)
    {
        $this->appConfig = $appConfig;
        $this->entity = $appUserRole;
        if($this->entity != null && ($this->entity->currentDatabase() == null || !$this->entity->currentDatabase()->isConnected()))
        {
            $this->entity->currentDatabase($database);
        }
        $this->currentModule = $currentModule;
        $this->currentUser = $currentUser;
        $this->userLevelId = $currentUser->getUserLevelId();
    }
    
    /**
     * Load permission
     *
     * @return void
     */
    public function loadPermission()
    {
        if($this->appConfig->getRole()->getBypassRole())
        {
            $this->allowedList =  true;
            $this->allowedDetail =  true;
            $this->allowedCreate =  true;
            $this->allowedUpdate =  true;
            $this->allowedDelete =  true;
            $this->allowedApprove =  true;
            $this->allowedSortOrder =  true;
        }
        else
        {
            try
            {
                if($this->entity != null)
                {
                    $this->entity->findOneByModuleNameAndUserLevelIdAndActive($this->currentModule->getModuleName(), $this->userLevelId, true);       
                    $this->allowedList =  $this->entity->getAllowedList();
                    $this->allowedDetail =  $this->entity->getAllowedDetail();
                    $this->allowedCreate =  $this->entity->getAllowedCreate();
                    $this->allowedUpdate =  $this->entity->getAllowedUpdate();
                    $this->allowedDelete =  $this->entity->getAllowedDelete();
                    $this->allowedApprove =  $this->entity->getAllowedApprove();
                    $this->allowedSortOrder =  $this->entity->getAllowedSortOrder();
                }
                
                $this->initialized = true;
            }
            catch(Exception $e)
            {
                // do nothing
            }
        }

        $this->allowedBatchAction = $this->allowedUpdate || $this->allowedDelete;
        
        $this->initialized = true;
    }

    /**
     * Check user permission
     *
     * @param PicoRequestBase $inputGet
     * @param PicoRequestBase $inputPost
     * @param AppLanguage $appLanguage
     * @param callable $callbackForbidden
     * @return boolean
     */
    public function allowedAccess($inputGet, $inputPost)
    {
        $userAction = null;
        if(isset($inputPost) && $inputPost->getUserAction() != null)
        {
            $userAction = $inputPost->getUserAction();
        }
        if($userAction == null && isset($inputGet) && $inputGet->getUserAction() != null)
        {
            $userAction = $inputGet->getUserAction();
        }


        if(!$this->currentModule->getAppModule()->issetModuleId())
        {
            try
            {
                $this->currentModule->getAppModule()->findOneByModuleCode($this->currentModule->getModuleName());
            }
            catch(Exception $e)
            {
                // do nothing
            }
        }

        if(!isset($userAction) || empty($userAction))
        {
            return $this->isAllowedTo(UserAction::SHOW_ALL);
        }
        else
        {
            return $this->isAllowedTo($userAction);
        }
    }

    /**
     * Check user permission
     *
     * @param PicoRequestBase $inputGet
     * @param PicoRequestBase $inputPost
     * @param callable $callbackForbidden
     * @return void
     */
    public function checkPermission($inputGet, $inputPost, $callbackForbidden)
    {
        if(isset($callbackForbidden) && is_callable($callbackForbidden))
        {
            $userAction = null;
            if(isset($inputPost) && $inputPost->getUserAction() != null)
            {
                $userAction = $inputPost->getUserAction();
            }
            if($userAction == null && isset($inputGet) && $inputGet->getUserAction() != null)
            {
                $userAction = $inputGet->getUserAction();
            }
            if(isset($userAction) && !$this->isAllowedTo($userAction))
            {
               
                $this->userAction = $userAction;
                call_user_func($callbackForbidden, $this->appConfig);
            }
        }
    }

    /**
     * Check if access is allowed or not
     *
     * @param string $userAction
     * @return boolean
     */
    public function isAllowedTo($userAction) // NOSONAR
    {
        if($this->currentModule->getAppModule()->getSpecialAccess() && $this->getCurrentUser()->getUserLevel()->getSpecialAccess())
        {
            $this->allowedList =  true;
            $this->allowedDetail =  true;
            $this->allowedCreate =  true;
            $this->allowedUpdate =  true;
            $this->allowedDelete =  true;
            $this->allowedApprove =  true;
            $this->allowedSortOrder =  true;
            return true;
        }
        else
        {
            if($userAction != null)
            {
                $forbidden = 
                (
                ($userAction == UserAction::SHOW_ALL && !$this->isAllowedList())
                || ($userAction == UserAction::CREATE && !$this->isAllowedCreate())
                || ($userAction == UserAction::UPDATE && !$this->isAllowedUpdate())
                || ($userAction == UserAction::ACTIVATE && !$this->isAllowedUpdate())
                || ($userAction == UserAction::DELETE && !$this->isAllowedDelete())
                || ($userAction == UserAction::DETAIL && !$this->isAllowedDetail())
                || ($userAction == UserAction::DELETE && !$this->isAllowedDelete())
                || ($userAction == UserAction::SORT_ORDER && !$this->isAllowedSortOrder())
                || ($userAction == UserAction::APPROVE && !$this->isAllowedApprove())
                || ($userAction == UserAction::REJECT && !$this->isAllowedApprove())
                )
                ;  
            }
            return !$forbidden;
        }
    }

    /**
     * Check if user has permission to edit, activate, deactivate, and delete
     *
     * @return boolean
     */
    public function isAllowedBatchAction()
    {
        return $this->allowedBatchAction;
    }
    
    /**
     * Check if user has permission to approve
     *
     * @return boolean
     */
    public function isAllowedApprove()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
    
        return $this->allowedApprove;
    }

    /**
     * Get allowed show list
     *
     * @return boolean
     */ 
    public function isAllowedList()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
        
        return $this->allowedList;
    }

    /**
     * Get allowed show detail
     *
     * @return boolean
     */ 
    public function isAllowedDetail()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
        
        return $this->allowedDetail;
    }

    /**
     * Get allowed create
     *
     * @return boolean
     */ 
    public function isAllowedCreate()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
        
        return $this->allowedCreate;
    }

    /**
     * Get allowed update
     *
     * @return boolean
     */ 
    public function isAllowedUpdate()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
        
        return $this->allowedUpdate;
    }

    /**
     * Get allowed delete
     *
     * @return boolean
     */ 
    public function isAllowedDelete()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
        
        return $this->allowedDelete;
    }

    /**
     * Get allowed short order
     *
     * @return boolean
     */ 
    public function isAllowedSortOrder()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
        
        return $this->allowedSortOrder;
    }

    /**
     * Get user level
     *
     * @return string
     */ 
    public function getUserLevelId()
    {
        return $this->userLevelId;
    }


    /**
     * Get current user
     *
     * @return MagicObject
     */ 
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Get user action
     *
     * @return string
     */ 
    public function getUserAction()
    {
        return $this->userAction;
    }

    /**
     * Set allowed short order
     *
     * @return self
     */ 
    public function setAllowedSortOrderFalse()
    {
        $this->allowedSortOrder = false;

        return $this;
    }
}