<?php

namespace MagicAppTemplate;

use MagicApp\PicoModule;
use MagicApp\AppInclude;
use MagicObject\SecretObject;

/**
 * Class AppIncludeImpl
 *
 * This class provides a concrete implementation of `MagicApp\AppInclude`. It is responsible for managing
 * file inclusion paths and dynamically adjusting asset paths within the application.
 *
 * The constructor intelligently calculates the relative path from the current script's directory to the
 * project root and prepends the necessary directory traversal characters (`../`) to the asset paths defined
 * in the application configuration. This ensures that asset URLs are resolved correctly regardless of the
 * nesting level of the currently executing page or module.
 */
class AppIncludeImpl extends AppInclude
{
    /**
     * AppIncludeImpl constructor.
     *
     * Initializes the AppIncludeImpl instance with the given application configuration
     * and the current module. Also sets the root path for includes resolution.
     *
     * @param SecretObject $appConfig       The application configuration object.
     * @param PicoModule   $currentModule   The current module instance in use.
     * @param string       $currentDirectory The current directory path.
     */
    public function __construct($appConfig, $currentModule, $currentDirectory = null)
    {
        $rootDirectory = dirname(dirname(dirname(__DIR__)));

        // Normalize paths
        $rootDirectory = realpath($rootDirectory);
        if(isset($currentDirectory) && !empty($currentDirectory))
        {
            self::updateAssetsPath($appConfig, $rootDirectory, $currentDirectory);
        }

        parent::__construct($appConfig, $currentModule, $rootDirectory);
    }

    /**
     * Updates the asset path in the application configuration by prepending relative directory traversal strings (`../`).
     *
     * This static method calculates the directory depth of the current script relative to the project root
     * and adjusts the `assets` path in the `$appConfig` object accordingly. This ensures that asset URLs
     * are resolved correctly from any nested directory.
     *
     * @param SecretObject $appConfig The application configuration object, which will be modified.
     * @param string $currentDirectory The absolute path of the directory where the script is executing.
     * @param string|null $rootDirectory The absolute path of the project's root directory. If null, it will be determined automatically.
     * @return void
     */
    public static function updateAssetsPath($appConfig, $currentDirectory, $rootDirectory = null)
    {
        $currentDirectory = realpath($currentDirectory);
        $depth = self::getAccessorDepth($currentDirectory, $rootDirectory);
        if($depth > 0)
        {
            // Prepend "../" according to the depth
            $prefix = str_repeat('../', $depth);
            $appConfig->setAssets($prefix . $appConfig->getAssets());
        }
    }

    /**
     * Calculates the directory depth of a given path relative to the project's root directory.
     *
     * This method determines how many levels deep the `$currentDirectory` is from the `$rootDirectory`.
     * The result is used to construct relative paths (e.g., for assets). For example, if the root is `/app`
     * and the current directory is `/app/modules/admin`, the depth is 2.
     *
     * @param string|null $currentDirectory The absolute path of the directory to check.
     * @param string|null $rootDirectory The absolute path of the project's root directory. If null, it will be determined automatically.
     * @return int The calculated depth as an integer. Returns 0 if the current directory is the root or not within it.
     */
    public static function getAccessorDepth($currentDirectory = null, $rootDirectory = null)
    {
        $depth = 0;
        if(!isset($rootDirectory) || empty($rootDirectory))
        {
            $rootDirectory = dirname(dirname(dirname(__DIR__)));
        }

        // Normalize paths
        $rootDirectory = realpath($rootDirectory);
        if(isset($currentDirectory) && !empty($currentDirectory))
        {
            $currentDirectory = realpath($currentDirectory);

            // Check if the current directory is inside the root directory
            if ($currentDirectory !== false && strpos($currentDirectory, $rootDirectory) === 0) {
                // Get the relative path from the root directory to the current directory
                $relativePath = trim(str_replace($rootDirectory, '', $currentDirectory), DIRECTORY_SEPARATOR);

                if (!empty($relativePath)) {
                    // Count the depth (number of subdirectories)
                    $depth = substr_count($relativePath, DIRECTORY_SEPARATOR) + 1;
                }
            }
        }
        return $depth;
    }
}
