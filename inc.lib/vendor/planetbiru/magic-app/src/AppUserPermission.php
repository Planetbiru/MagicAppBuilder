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
 * This class provides functionality to check and load user permissions based on roles and modules.
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
     * @var bool
     */
    private $allowedList;
    
    /**
     * Allowed show detail
     *
     * @var bool
     */
    private $allowedDetail;
    
    /**
     * Allowed create
     *
     * @var bool
     */
    private $allowedCreate;
    
    /**
     * Allowed update
     *
     * @var bool
     */
    private $allowedUpdate;
    
    /**
     * Allowed delete
     *
     * @var bool
     */
    private $allowedDelete;
    
    /**
     * Allowed approve/reject
     *
     * @var bool
     */
    private $allowedApprove;
    
    /**
     * Allowed short order
     *
     * @var bool
     */
    private $allowedSortOrder;

    /**
     * Allowed export
     *
     * @var bool
     */
    private $allowedExport;

    /**
     * Allowed batch action
     *
     * @var bool
     */
    private $allowedBatchAction;
    
    /**
     * Indicates if permissions have been initialized.
     *
     * @var bool
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
     * Initializes the AppUserPermission object with application configuration, database, user role, 
     * current module, and current user.
     *
     * @param SecretObject $appConfig The application configuration object.
     * @param PicoDatabase $database The database connection object.
     * @param MagicObject $appUserRole The user role entity.
     * @param PicoModule $currentModule The current module being accessed.
     * @param AppUser $currentUser The current user object.
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
        if(isset($currentUser))
        {
            $this->userLevelId = $currentUser->getUserLevelId();
        }
    }
    
    /**
     * Load user permissions.
     *
     * This method loads the permissions for the current user based on their role and the module being accessed.
     * If the role bypasses permissions, all permissions are granted.
     *
     * @return void
     */
    public function loadPermission()
    {
        if($this->appConfig->issetRole() && $this->appConfig->getRole()->getBypassRole())
        {
            $this->allowedList =  true;
            $this->allowedDetail =  true;
            $this->allowedCreate =  true;
            $this->allowedUpdate =  true;
            $this->allowedDelete =  true;
            $this->allowedApprove =  true;
            $this->allowedSortOrder =  true;
            $this->allowedExport =  true;
        }
        else
        {
            try
            {
                if($this->entity != null)
                {
                    $this->entity->findOneByModuleNameAndUserLevelIdAndActive($this->currentModule->getModuleName(), $this->userLevelId, true);       
                    $this->allowedList = $this->entity->getAllowedList();
                    $this->allowedDetail = $this->entity->getAllowedDetail();
                    $this->allowedCreate = $this->entity->getAllowedCreate();
                    $this->allowedUpdate = $this->entity->getAllowedUpdate();
                    $this->allowedDelete = $this->entity->getAllowedDelete();
                    $this->allowedApprove = $this->entity->getAllowedApprove();
                    $this->allowedSortOrder = $this->entity->getAllowedSortOrder();
                    $this->allowedExport = $this->entity->getAllowedExport();
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
     * Check user permission for a given action.
     *
     * This method checks if the user has permission to perform a specific action,
     * based on input from GET or POST requests.
     *
     * @param PicoRequestBase $inputGet The GET request data.
     * @param PicoRequestBase $inputPost The POST request data.
     * @return boolean True if the user is allowed to perform the action, false otherwise.
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
     * Check user permission and trigger a callback if forbidden.
     *
     * This method checks if the user has permission to perform an action. If not,
     * it triggers the provided callback function to handle the forbidden access.
     *
     * @param PicoRequestBase $inputGet The GET request data.
     * @param PicoRequestBase $inputPost The POST request data.
     * @param callable $callbackForbidden The callback function to call when access is forbidden.
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
     * Check if user is allowed to perform the given action.
     *
     * This method checks if the user has permission to perform a specific action
     * based on their roles and permissions.
     *
     * @param string $userAction The action the user wants to perform.
     * @return boolean True if the user is allowed to perform the action, false otherwise.
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
            $this->allowedExport =  true;
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
                || ($userAction == UserAction::EXPORT && !$this->isAllowedExport())
                )
                ;  
            }
            return !$forbidden;
        }
    }

    /**
     * Check if user has permission to edit, activate, deactivate, and delete.
     *
     * @return boolean True if the user has permission for batch actions, false otherwise.
     */
    public function isAllowedBatchAction()
    {
        return $this->allowedBatchAction;
    }

    /**
     * Check if user has permission to approve.
     *
     * @return boolean True if the user has permission to approve, false otherwise.
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
     * Get allowed show list permission.
     *
     * @return boolean True if the user is allowed to see the list, false otherwise.
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
     * Get allowed show detail permission.
     *
     * @return boolean True if the user is allowed to see the detail, false otherwise.
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
     * Get allowed create permission.
     *
     * @return boolean True if the user is allowed to create, false otherwise.
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
     * Get allowed update permission.
     *
     * @return boolean True if the user is allowed to update, false otherwise.
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
     * Get allowed delete permission.
     *
     * @return boolean True if the user is allowed to delete, false otherwise.
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
     * Get allowed short order permission.
     *
     * @return boolean True if the user is allowed to perform sorting, false otherwise.
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
     * Get allowed export permission.
     *
     * @return boolean True if the user is allowed to perform sorting, false otherwise.
     */
    public function isAllowedExport()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
        return $this->allowedExport;
    }

    /**
     * Get user level.
     *
     * @return string The user level ID.
     */
    public function getUserLevelId()
    {
        return $this->userLevelId;
    }

    /**
     * Get current user.
     *
     * @return MagicObject The current user object.
     */
    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    /**
     * Get user action.
     *
     * @return string The action the user wants to perform.
     */
    public function getUserAction()
    {
        return $this->userAction;
    }

    /**
     * Set allowed sort order to false.
     *
     * @return self The current instance of the class.
     */
    public function setAllowedSortOrderFalse()
    {
        if(!$this->initialized)
        {
            $this->loadPermission();
        }
        $this->allowedSortOrder = false;

        return $this;
    }
}
