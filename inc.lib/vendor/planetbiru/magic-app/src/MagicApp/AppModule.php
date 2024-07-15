<?php

namespace MagicApp;

use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\Request\InputServer;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

class AppModule
{
    const HEADER_LOCATION = "Location: ";
    
    /**
     * App Config
     *
     * @var SecretObject
     */
    private $appConfig;
    
    /**
     * Module ID
     *
     * @var string
     */
    private $moduleId = "";
    
    /**
     * Module name
     *
     * @var string
     */
    private $moduleName = "";
    
    /**
     * Module title
     *
     * @var string
     */
    private $moduleTitle = "";
    
    /**
     * PHP self
     *
     * @var string
     */
    private $phpSelf = "";

    /**
     * User role
     *
     * @var MagicObject
     */
    private $userRole;

    /**
     * Get allowed modules
     * @var string[]
     */
    private $allowedModules = array();

    /**
     * Database
     * @var  PicoDatabase
     */
    private $database;
    
    /**
     * Constructor
     *
     * @param SecretObject $appConfig
     * @param string $moduleName
     */
    public function __construct($appConfig, $database, $moduleId, $moduleName, $moduleTitle = null)
    {
        $this->appConfig = $appConfig;
        $this->database = $database;
        $this->moduleId = $moduleId;
        $this->moduleName = $moduleName;
        $this->moduleTitle = $moduleTitle;
        $inputServer = new InputServer();
        $this->phpSelf = $inputServer->getPhpSelf();
    }
    

    /**
     * Get role
     *
     * @param MagicObject[] $appUserRoles
     * @return MagicObject
     */
    public function getUserRole($appUserRoles)
    {
        if(!isset($this->userRole))
        {
            $this->parseRole($appUserRoles);
        }
        return $this->userRole;
    }

    public function getAllowedModules($appUserRoles)
    {
        if(empty($this->allowedModules))
        {
            $this->parseRole($appUserRoles);
        }
        return $this->allowedModules;
    }

    public function parseRole($appUserRoles)
    {
        if(isset($appUserRoles) && is_array($appUserRoles))
        {

            $this->allowedModules = array();
            foreach($appUserRoles as $role)
            {
                //echo $role->getModuleId()." == " .$this->moduleId."<br>";
                //echo $role->getModuleId().' '.$this->moduleId."<br>";
                if($role->getModuleName() ==  $this->moduleId)
                {
                    $this->userRole = $role;
                }

                if(
                    $role->hasValueModule()
                    && $role->getModule()->getModuleId() 
                    &&
                    ($role->getAllowedList()
                ||  $role->getAllowedDetail()
                ||  $role->getAllowedCreate()
                ||  $role->getAllowedUpdate()
                ||  $role->getAllowedDelete()
                ||  $role->getAllowedApprove()
                ||  $role->getAllowedSortOrder())
                )
                {
                    $this->allowedModules[] = $role->getModule()->getModuleId() ;
                }
            }
        }
        return $this;
    }

    /**
     * Get self
     *
     * @return string
     */
    public function getSelf()
    {
        return basename($_SERVER['PHP_SELF']);
    }

    /**
     * Redirect to itself
     *
     * @return void
     */
    public function redirectToItself()
    {
        header(self::HEADER_LOCATION.$_SERVER['REQUEST_URI']);
        exit();
    }

    /**
     * Redirect to itself with show require approval only
     *
     * @return void
     */
    public function redirectToItselfWithRequireApproval()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        if(stripos($uri, "?") !== false)
        {
            $arr = explode("?", $uri, 2);
            parse_str($arr[1], $params);
            $module = $arr[0];
        }
        else
        {
            $params = array();
            $module = $uri;
        }
        $params[] = "show_require_approval_only=true";
        $uri = $module."?".implode("&", $params);
        header(self::HEADER_LOCATION.$uri);
        exit();
    }
    
    /**
     * Redirect to
     *
     * @param string $userAction Current action
     * @param string $parameterName
     * @param string $parameterValue
     * @return void
     */
    public function redirectTo($userAction = null, $parameterName = null, $parameterValue = null)
    {
        $url = $this->getRedirectUrl($userAction, $parameterName, $parameterValue);
        header(self::HEADER_LOCATION.$url);
        exit();
    }
    
    /**
     * Get redirect URL
     *
     * @param string $userAction
     * @param string $parameterName
     * @param string $parameterValue
     * @param string[] $additionalParams
     * @return string
     */
    public function getRedirectUrl($userAction = null, $parameterName = null, $parameterValue = null, $additionalParams = null)
    {
        $urls = array();
        $params = array();
        $phpSelf = $this->phpSelf;
        
        if($this->appConfig->getModule() != null && $this->appConfig->getModule()->getHideExtension() && PicoStringUtil::endsWith($phpSelf, ".php"))
        {
            $phpSelf = substr($phpSelf, 0, strlen($phpSelf) - 4);
        }
        
        $urls[] = $phpSelf;
        if($userAction != null)
        {
            $params[] = UserAction::USER_ACTION."=".urlencode($userAction);
        }
        if($parameterName != null)
        {
            $params[] = urlencode($parameterName)."=".urlencode($parameterValue);
        }
        if($additionalParams != null && is_array($additionalParams))
        {
            $additionalParamsKey = array_keys($additionalParams);
            foreach($additionalParams as $paramName=>$paramValue)
            {
                if($parameterName == null || !in_array($parameterName, $additionalParamsKey))
                {
                    $params[] = urlencode($paramName)."=".urlencode($paramValue);
                }
            }
        }
        if(!empty($params))
        {
            $urls[] = implode("&", $params);
        }
        return implode("?", $urls);
    }

    /**
     * Get module ID
     *
     * @return  string
     */ 
    public function getModuleId()
    {
        return $this->moduleId;
    }

    /**
     * Get module name
     *
     * @return  string
     */ 
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Get module title
     *
     * @return  string
     */ 
    public function getModuleTitle()
    {
        return $this->moduleTitle;
    }
}