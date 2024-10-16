<?php

namespace MagicApp;

use MagicObject\MagicObject;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

/**
 * Class AppInclude
 *
 * Manages the inclusion of application components such as headers, footers, 
 * and error pages. It provides methods to dynamically retrieve the paths 
 * to these components based on the application's configuration.
 */
class AppInclude
{
    /**
     * Application configuration.
     *
     * @var SecretObject
     */
    private $appConfig;

    /**
     * Application instance.
     *
     * @var SecretObject
     */
    private $app;

    /**
     * Current module in use.
     *
     * @var PicoModule
     */
    private $currentModule;

    /**
     * AppInclude constructor.
     *
     * @param SecretObject $appConfig The application configuration object.
     * @param PicoModule $currentModule The current module being used.
     */
    public function __construct($appConfig, $currentModule)
    {
        $this->appConfig = $appConfig;
        $this->app = $this->appConfig->getApplication();
        if (!isset($this->app)) {
            $this->app = new SecretObject();
        }
        $this->currentModule = $currentModule;
    }

    /**
     * Get the path to the main header file.
     *
     * @param string $dir Base directory for includes.
     * @return string Path to the header file.
     */
    public function mainAppHeader($dir)
    {
        $path = $this->app->getBaseApplicationDirectory() . "/" .
                $this->app->getBaseIncludeDirectory() . "/" .
                $this->app->getIncludeHeaderFile();

        if (PicoStringUtil::endsWith($path, ".php") && file_exists($path)) {
            return $path;
        } else {
            return $dir . "/inc.app/header.php";
        }
    }

    /**
     * Get the path to the main footer file.
     *
     * @param string $dir Base directory for includes.
     * @return string Path to the footer file.
     */
    public function mainAppFooter($dir)
    {
        $path = $this->app->getBaseApplicationDirectory() . "/" .
                $this->app->getBaseIncludeDirectory() . "/" .
                $this->app->getIncludeFooterFile();

        if (PicoStringUtil::endsWith($path, ".php") && file_exists($path)) {
            return $path;
        } else {
            return $dir . "/inc.app/footer.php";
        }
    }

    /**
     * Get the path to the forbidden access page.
     *
     * @param string $dir Base directory for includes.
     * @return string Path to the forbidden page.
     */
    public function appForbiddenPage($dir)
    {
        $path = $this->app->getBaseApplicationDirectory() . "/" .
                $this->app->getBaseIncludeDirectory() . "/" .
                $this->app->getForbiddenPage() . "/403.php";

        if (PicoStringUtil::endsWith($path, ".php") && file_exists($path)) {
            return $path;
        } else {
            return $dir . "/inc.app/403.php";
        }
    }

    /**
     * Get the path to the not found page.
     *
     * @param string $dir Base directory for includes.
     * @return string Path to the not found page.
     */
    public function appNotFoundPage($dir)
    {
        $path = $this->app->getBaseApplicationDirectory() . "/" .
                $this->app->getBaseIncludeDirectory() . "/" .
                $this->app->getForbiddenPage() . "/404.php";

        if (PicoStringUtil::endsWith($path, ".php") && file_exists($path)) {
            return $path;
        } else {
            return $dir . "/inc.app/404.php";
        }
    }

    /**
     * Get the application configuration.
     *
     * @return SecretObject The application configuration.
     */
    public function getAppConfig()
    {
        return $this->appConfig;
    }

    /**
     * Set the application configuration.
     *
     * @param SecretObject $appConfig The application configuration to set.
     * @return self
     */
    public function setAppConfig($appConfig)
    {
        $this->appConfig = $appConfig;

        return $this;
    }

    /**
     * Get the current module.
     *
     * @return PicoModule The current module.
     */
    public function getCurrentModule()
    {
        return $this->currentModule;
    }

    /**
     * Set the current module.
     *
     * @param PicoModule $currentModule The module to set as current.
     * @return self
     */
    public function setCurrentModule($currentModule)
    {
        $this->currentModule = $currentModule;

        return $this;
    }

    /**
     * Print exception message.
     *
     * @param \Exception $e The exception to print.
     * @return string The exception message.
     */
    public function printException($e)
    {
        return $e->getMessage();
    }
}
