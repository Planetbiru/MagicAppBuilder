<?php

namespace MagicAppTemplate;

use MagicApp\AppLanguage;
use MagicObject\SecretObject;

/**
 * Class AppLanguageImpl
 *
 * Provides a concrete implementation of the `MagicApp\AppLanguage` class, tailored for this
 * application's structure. This class is responsible for loading and managing language
 * resources (translations) from a predefined directory.
 *
 * The constructor automatically sets the base path for language files to `<project_root>/inc.lang`,
 * ensuring that the application can locate and use translation files (e.g., `en.ini`, `id.ini`)
 * for internationalization (i18n) purposes.
 *
 * @package MagicAppTemplate
 */
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
        parent::__construct($appConfig, $currentLanguage, dirname(dirname(dirname(__DIR__)))."/inc.lang", $callback);
    }
}