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
            $currentDirectory = realpath($currentDirectory);

            // Check if the current directory is inside the root directory
            if ($currentDirectory !== false && strpos($currentDirectory, $rootDirectory) === 0) {
                // Get the relative path from the root directory to the current directory
                $relativePath = trim(str_replace($rootDirectory, '', $currentDirectory), DIRECTORY_SEPARATOR);

                if (!empty($relativePath)) {
                    // Count the depth (number of subdirectories)
                    $depth = substr_count($relativePath, DIRECTORY_SEPARATOR) + 1;

                    // Prepend "../" according to the depth
                    $prefix = str_repeat('../', $depth);

                    $appConfig->setAssets($prefix . $appConfig->getAssets());
                }
            }
        }

        parent::__construct($appConfig, $currentModule, $rootDirectory);
    }
}
