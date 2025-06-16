<?php

namespace MagicAppTemplate;

use MagicApp\AppEntityLanguage;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

/**
 * Class AppEntityLanguageImpl
 *
 * This class provides a concrete implementation of `MagicApp\AppEntityLanguage`.
 * It's designed to manage language-specific data and translations within an application.
 * This class handles the initialization of language services by configuring the associated entity,
 * application settings, the current active language, and the designated base directory for language resource files.
 * This ensures that various entities and application components can seamlessly retrieve and
 * display content in the appropriate locale, supporting robust internationalization (i18n).
 */
class AppEntityLanguageImpl extends AppEntityLanguage
{
    /**
     * Constructs a new AppEntityLanguageImpl instance.
     *
     * This constructor initializes the language handling capabilities for a given entity.
     * It sets up the necessary application configuration and the active language,
     * while also defining the base directory where language resource files are located.
     * This setup is essential for managing translations and localized data throughout the application.
     *
     * @param MagicObject  $entity          The entity object for which language-specific data or translations will be managed.
     * @param SecretObject $appConfig       The application's configuration object, used to access various global settings.
     * @param string       $currentLanguage The current language code (e.g., 'en', 'id', 'fr') to be used for localization.
     */
    public function __construct($entity, $appConfig, $currentLanguage)
    {
        parent::__construct($entity, $appConfig, $currentLanguage, dirname(dirname(dirname(__DIR__))) . "/inc.lang");
    }
}