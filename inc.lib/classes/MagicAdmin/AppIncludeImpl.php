<?php

namespace MagicAdmin;

use MagicApp\AppInclude;

class AppIncludeImpl extends AppInclude
{
    /**
     * AppIncludeImpl constructor.
     *
     * Initializes the AppIncludeImpl instance with the given application configuration
     * and the current module. Also sets the root path for includes resolution.
     *
     * @param SecretObject $appConfig     The application configuration object.
     * @param PicoModule   $currentModule The current module instance in use.
     */
    public function __construct($appConfig, $currentModule)
    {
        parent::__construct($appConfig, $currentModule, dirname(dirname(dirname(__DIR__))));
    }
}