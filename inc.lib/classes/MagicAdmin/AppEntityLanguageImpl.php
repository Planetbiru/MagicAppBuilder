<?php

namespace MagicAdmin;

use MagicApp\AppEntityLanguage;

class AppEntityLanguageImpl extends AppEntityLanguage
{
    /**
     * AppEntityLanguageImpl constructor.
     *
     * Initializes the AppEntityLanguageImpl instance with the provided entity,
     * application configuration, and the current language. Also sets the root
     * directory path for language resource resolution.
     *
     * @param MagicObject  $entity           The entity for which language data should be loaded.
     * @param SecretObject $appConfig        The application's configuration object.
     * @param string       $currentLanguage  The current language code (e.g., 'en', 'id').
     */
    public function __construct($entity, $appConfig, $currentLanguage)
    {
        parent::__construct($entity, $appConfig, $currentLanguage, dirname(dirname(dirname(__DIR__)))."/magic-admin/inc.lang");
    }
}