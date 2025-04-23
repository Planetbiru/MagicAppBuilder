<?php

namespace MagicAdmin;

use MagicApp\AppLanguage;

class AppLanguageImpl extends AppLanguage
{
    /**
     * AppLanguageImpl constructor.
     *
     * Initializes the AppLanguageImpl instance with the application configuration,
     * current language, and optional callback. Also sets the root directory path
     * for language file resolution.
     *
     * @param SecretObject|null $appConfig        Optional application configuration object.
     * @param string|null       $currentLanguage  Optional current language code (e.g., 'en', 'id').
     * @param callable|null     $callback         Optional callback for additional customization.
     */
    public function __construct($appConfig = null, $currentLanguage = null, $callback = null)
    {
        parent::__construct($appConfig, $currentLanguage, dirname(dirname(dirname(__DIR__))), $callback);
    }
}