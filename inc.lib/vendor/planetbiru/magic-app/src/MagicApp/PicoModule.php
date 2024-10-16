<?php

namespace MagicApp;

use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\Request\InputServer;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

class PicoModule
{
    const HEADER_LOCATION = "Location: ";

    /**
     * App configuration object.
     *
     * @var SecretObject
     */
    private $appConfig;

    /**
     * Target directory for module operations.
     *
     * @var string
     */
    private $targetDirectory = "";

    /**
     * Name of the module.
     *
     * @var string
     */
    private $moduleName = "";

    /**
     * Title of the module.
     *
     * @var string
     */
    private $moduleTitle = "";

    /**
     * Path of the PHP script that is currently executing.
     *
     * @var string
     */
    private $phpSelf = "";

    /**
     * User role object for the current session.
     *
     * @var MagicObject
     */
    private $userRole;

    /**
     * List of allowed modules for the current user role.
     *
     * @var string[]
     */
    private $allowedModules = [];

    /**
     * Database connection object.
     *
     * @var PicoDatabase
     */
    private $database;

    /**
     * Application module object.
     *
     * @var AppModule|mixed
     */
    private $appModule;

    /**
     * Constructor for the PicoModule class.
     *
     * @param SecretObject $appConfig Configuration for the application.
     * @param PicoDatabase $database Database connection.
     * @param AppModule|mixed $appModule Application module object.
     * @param string|null $targetDirectory Target directory for the module.
     * @param string|null $moduleName Name of the module.
     * @param string|null $moduleTitle Title of the module.
     */
    public function __construct($appConfig, $database, $appModule = null, $targetDirectory = null, $moduleName = null, $moduleTitle = null)
    {
        $this->appConfig = $appConfig;
        $this->database = $database;
        $this->targetDirectory = $targetDirectory;
        $this->moduleName = $moduleName;
        $this->moduleTitle = $moduleTitle;
        $this->appModule = $appModule;
        $inputServer = new InputServer();
        $this->phpSelf = $inputServer->getPhpSelf();
    }

    /**
     * Get the user role for the current session.
     *
     * @param MagicObject[] $appUserRoles List of user roles.
     * @return MagicObject The user role object.
     */
    public function getUserRole($appUserRoles)
    {
        if (!isset($this->userRole)) {
            $this->parseRole($appUserRoles);
        }
        return $this->userRole;
    }

    /**
     * Get the list of allowed modules for the current user role.
     *
     * @param MagicObject[] $appUserRoles List of user roles.
     * @return array Array of allowed module IDs.
     */
    public function getAllowedModules($appUserRoles)
    {
        if (empty($this->allowedModules)) {
            $this->parseRole($appUserRoles);
        }
        return $this->allowedModules;
    }

    /**
     * Parse user roles to determine allowed modules and user role.
     *
     * @param MagicObject[] $appUserRoles List of user roles.
     * @return self
     */
    public function parseRole($appUserRoles)
    {
        if (isset($appUserRoles) && is_array($appUserRoles)) {
            $this->allowedModules = [];
            foreach ($appUserRoles as $role) {
                if (isset($this->appModule) && $role->getModuleName() == $this->appModule->getModuleName()) {
                    $this->userRole = $role;
                }
                if ($this->getAccess($role) && $role->getModuleId() != null) {
                    $this->allowedModules[] = $role->getModuleId();
                }
            }
        }
        return $this;
    }

    /**
     * Check if the given role has access to the application.
     *
     * @param MagicObject $role The role to check.
     * @return boolean True if access is allowed, false otherwise.
     */
    public function getAccess($role)
    {
        return $role->getAllowedList()
            || $role->getAllowedDetail()
            || $role->getAllowedCreate()
            || $role->getAllowedUpdate()
            || $role->getAllowedDelete()
            || $role->getAllowedApprove()
            || $role->getAllowedSortOrder();
    }

    /**
     * Get the name of the current script.
     *
     * @return string The name of the current PHP script.
     */
    public function getSelf()
    {
        return basename($_SERVER['PHP_SELF']);
    }

    /**
     * Redirect the user to the current script.
     *
     * @return void
     */
    public function redirectToItself()
    {
        header(self::HEADER_LOCATION . $_SERVER['REQUEST_URI']);
        exit();
    }

    /**
     * Redirect the user to the current script with a specific parameter to show require approval only.
     *
     * @return void
     */
    public function redirectToItselfWithRequireApproval()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        if (stripos($uri, "?") !== false) {
            $arr = explode("?", $uri, 2);
            parse_str($arr[1], $params);
            $module = $arr[0];
        } else {
            $params = [];
            $module = $uri;
        }
        $params[] = "show_require_approval_only=true";
        $uri = $module . "?" . implode("&", $params);
        header(self::HEADER_LOCATION . $uri);
        exit();
    }

    /**
     * Redirect to a specified URL.
     *
     * @param string|null $userAction Current action to perform.
     * @param string|null $parameterName Name of the parameter to append.
     * @param string|null $parameterValue Value of the parameter to append.
     * @return void
     */
    public function redirectTo($userAction = null, $parameterName = null, $parameterValue = null)
    {
        $url = $this->getRedirectUrl($userAction, $parameterName, $parameterValue);
        header(self::HEADER_LOCATION . $url);
        exit();
    }

    /**
     * Generate a redirect URL based on the current context.
     *
     * @param string|null $userAction Action to perform.
     * @param string|null $parameterName Parameter name to include in the URL.
     * @param string|null $parameterValue Parameter value to include in the URL.
     * @param string[]|null $additionalParams Additional parameters to include in the URL.
     * @return string The generated redirect URL.
     */
    public function getRedirectUrl($userAction = null, $parameterName = null, $parameterValue = null, $additionalParams = null)
    {
        $urls = [];
        $params = [];
        $phpSelf = $this->phpSelf;

        if ($this->appConfig->getModule() != null && $this->appConfig->getModule()->getHideExtension() && PicoStringUtil::endsWith($phpSelf, ".php")) {
            $phpSelf = substr($phpSelf, 0, strlen($phpSelf) - 4);
        }

        $urls[] = $phpSelf;
        if ($userAction != null) {
            $params[] = UserAction::USER_ACTION . "=" . urlencode($userAction);
        }
        if ($parameterName != null) {
            $params[] = urlencode($parameterName) . "=" . urlencode($parameterValue);
        }
        if ($additionalParams != null && is_array($additionalParams)) {
            $additionalParamsKey = array_keys($additionalParams);
            foreach ($additionalParams as $paramName => $paramValue) {
                if ($parameterName == null || !in_array($parameterName, $additionalParamsKey)) {
                    $params[] = urlencode($paramName) . "=" . urlencode($paramValue);
                }
            }
        }
        if (!empty($params)) {
            $urls[] = implode("&", $params);
        }
        return implode("?", $urls);
    }

    /**
     * Get the name of the module.
     *
     * @return string The module name.
     */
    public function getModuleName()
    {
        return $this->moduleName;
    }

    /**
     * Get the title of the module.
     *
     * @return string The module title.
     */
    public function getModuleTitle()
    {
        return $this->moduleTitle;
    }

    /**
     * Get the database connection.
     *
     * @return PicoDatabase The database connection object.
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get the application module.
     *
     * @return AppModule|mixed The application module object.
     */
    public function getAppModule()
    {
        return $this->appModule;
    }

    /**
     * Get the target directory for the module.
     *
     * @return string The target directory.
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
