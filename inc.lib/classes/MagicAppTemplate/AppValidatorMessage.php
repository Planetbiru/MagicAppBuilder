<?php

namespace MagicAppTemplate;

use MagicObject\Util\PicoIniUtil;

/**
 * Class AppValidatorMessage
 *
 * Responsible for loading language-specific validator message templates
 * from an INI configuration file. Supports dynamic loading based on the language ID.
 *
 * Expected file structure:
 * <project_root>/inc.lang/{languageId}/validator.ini
 *
 * @package MagicAppTemplate
 */
class AppValidatorMessage
{
    /**
     * Loads the validator message template based on the provided language ID.
     *
     * This method searches for a `validator.ini` file located in the directory
     * corresponding to the given language ID (e.g., "en/validator.ini" or "id/validator.ini").
     *
     * @param string $languageId The language identifier (e.g., "en", "id").
     * @return array|null Returns an associative array of validator messages if found, or null if the file does not exist.
     */
    public static function loadTemplate($languageId)
    {
        $path = dirname(dirname(dirname(__DIR__))) . "/inc.lang/" . $languageId . "/validator.ini";
        if (file_exists($path)) {
            return PicoIniUtil::parseIniFile($path);
        }
        return null;
    }
}
