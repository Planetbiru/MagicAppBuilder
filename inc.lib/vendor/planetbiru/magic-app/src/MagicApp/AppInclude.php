<?php

namespace MagicApp;

use MagicObject\MagicObject;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

class AppInclude
{
    /**
     * App config
     *
     * @var SecretObject
     */
    private $appConfig;

    /**
     * App
     *
     * @var SecretObject
     */
    private $app;
    
    /**
     * Current module
     *
     * @var PicoModule
     */
    private $currentModule;
    
    public function __construct($appConfig, $currentModule)
    {
        $this->appConfig = $appConfig;
        $this->app = $this->appConfig->getApplication();
        if(!isset($this->app))
        {
            $this->app = new SecretObject();
        }
        $this->currentModule = $currentModule;
    }
    /**
     * Main header
     *
     * @param string $dir
     * @param MagicObject|SecretObject $config
     * @return string
     */
    public function mainAppHeader($dir)
    {
        $path = $this->app->getBaseApplicationDirectory()."/".$this->app->getBaseIncludeDirectory()."/".$this->app->getIncludeHeaderFile();
        if(PicoStringUtil::endsWith($path, ".php") && file_exists($path))
        {
            return $path;
        }
        else
        {
            return $dir . "/inc.app/header.php";
        }
    }
    
    /**
     * Main footer
     *
     * @param string $dir
     * @param MagicObject|SecretObject $config
     * @return string
     */
    public function mainAppFooter($dir)
    {
        $path = $this->app->getBaseApplicationDirectory()."/".$this->app->getBaseIncludeDirectory()."/".$this->app->getIncludeFooterFile();
        if(PicoStringUtil::endsWith($path, ".php") && file_exists($path))
        {
            return $path;
        }
        else
        {
            return $dir . "/inc.app/footer.php";
        }
    }

    /**
     * Forbidden
     *
     * @param string $dir
     * @param MagicObject|SecretObject $config
     * @return string
     */
    public function appForbiddenPage($dir)
    {
        $path = $this->app->getBaseApplicationDirectory()."/".$this->app->getBaseIncludeDirectory()."/".$this->app->getForbiddenPage()."/403.php";
        if(PicoStringUtil::endsWith($path, ".php") && file_exists($path))
        {
            return $path;
        }
        else
        {
            return $dir . "/inc.app/403.php";
        }
    }

    /**
     * Page not found
     *
     * @param string $dir
     * @param MagicObject|SecretObject $config
     * @return string
     */
    public function appNotFoundPage($dir)
    {
        $path = $this->app->getBaseApplicationDirectory()."/".$this->app->getBaseIncludeDirectory()."/".$this->app->getForbiddenPage()."/404.php";
        if(PicoStringUtil::endsWith($path, ".php") && file_exists($path))
        {
            return $path;
        }
        else
        {
            return $dir . "/inc.app/404.php";
        }
    }

    /**
     * Get app config
     *
     * @return SecretObject
     */ 
    public function getAppConfig()
    {
        return $this->appConfig;
    }

    /**
     * Set app config
     *
     * @param SecretObject  $appConfig  App config
     *
     * @return self
     */ 
    public function setAppConfig($appConfig)
    {
        $this->appConfig = $appConfig;

        return $this;
    }

    /**
     * Get current module
     *
     * @return PicoModule
     */ 
    public function getCurrentModule()
    {
        return $this->currentModule;
    }

    /**
     * Set current module
     *
     * @param PicoModule  $currentModule  Current module
     *
     * @return self
     */ 
    public function setCurrentModule($currentModule)
    {
        $this->currentModule = $currentModule;

        return $this;
    }

    public function printException($e)
    {
        return $e->getMessage();
    }
}