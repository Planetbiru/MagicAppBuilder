<?php

namespace MagicApp;

use Exception;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

/**
 * Class AppInclude
 *
 * Manages the inclusion of application components such as headers, footers, 
 * and error pages. It provides methods to dynamically retrieve the paths 
 * to these components based on the application's configuration.
 *
 * This class is responsible for generating the file paths for common application
 * components such as the header, footer, and error pages. It also allows dynamic
 * configuration based on the application's settings and the current module.
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
     * Initializes the AppInclude object with the application configuration
     * and the current module. If no application is set, a new SecretObject 
     * instance is created for the app.
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
     * Retrieves the file path for the main header. If a custom header path is defined in the configuration,
     * it is used, otherwise, a default path is returned.
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
     * Retrieves the file path for the main footer. If a custom footer path is defined in the configuration,
     * it is used, otherwise, a default path is returned.
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
     * Retrieves the file path for the forbidden access page (403). If a custom path is defined in the configuration,
     * it is used, otherwise, a default path is returned.
     *
     * @param string $dir Base directory for includes.
     * @return string Path to the forbidden page (403).
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
     * Retrieves the file path for the not found page (404). If a custom path is defined in the configuration,
     * it is used, otherwise, a default path is returned.
     *
     * @param string $dir Base directory for includes.
     * @return string Path to the not found page (404).
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
     * Returns the current application configuration object.
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
     * Allows setting a new application configuration object.
     *
     * @param SecretObject $appConfig The application configuration to set.
     * @return self The current instance, allowing method chaining.
     */
    public function setAppConfig($appConfig)
    {
        $this->appConfig = $appConfig;

        return $this;
    }

    /**
     * Get the current module.
     *
     * Returns the current module that is in use.
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
     * Allows setting a new module as the current one.
     *
     * @param PicoModule $currentModule The module to set as current.
     * @return self The current instance, allowing method chaining.
     */
    public function setCurrentModule($currentModule)
    {
        $this->currentModule = $currentModule;

        return $this;
    }

    /**
     * Print exception message.
     *
     * Prints the message of an exception. This method can be used for debugging
     * or logging purposes.
     *
     * @param Exception $e The exception to print.
     * @return string The exception message.
     */
    public function printException($e)
    {
        return $e->getMessage();
    }
}
