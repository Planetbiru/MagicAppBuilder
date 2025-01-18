<?php

namespace MagicApp;

use MagicObject\Language\PicoLanguage;
use MagicObject\SecretObject;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

/**
 * Class AppLanguage
 *
 * Extends PicoLanguage to manage application-specific language data.
 * Loads language strings from an INI file based on the current language setting.
 */
class AppLanguage extends PicoLanguage
{
    /**
     * Application configuration.
     *
     * @var SecretObject
     */
    private $appConfig;

    /**
     * Current language being used.
     *
     * @var string
     */
    private $currentLanguage;

    /**
     * Callback function for handling missing properties.
     *
     * @var callable|null
     */
    private $callback;

    /**
     * Constructor for AppLanguage.
     *
     * @param SecretObject|null $appConfig The application configuration object.
     * @param string|null $currentLanguage The current language to load.
     * @param callable|null $callback A callback function for missing properties.
     */
    public function __construct($appConfig = null, $currentLanguage = null, $callback = null)
    {
        $this->appConfig = $appConfig;
        $this->currentLanguage = $currentLanguage;
        $this->loadData($this->loadLanguageData());

        if (isset($callback) && is_callable($callback)) {
            $this->callback = $callback;
        }
    }

    /**
     * Load language data from the INI file.
     *
     * @return array The parsed language data.
     */
    private function loadLanguageData()
    {
        $langFile = $this->appConfig->getBaseLanguageDirectory() . "/" . $this->currentLanguage . "/app.ini";
        if(file_exists($langFile))
        {
            $data = PicoIniUtil::parseIniFile($langFile);   
            return isset($data) && is_array($data) ? $data : array();
        }
        return array();
    }

    /**
     * Get the value of a specified property.
     *
     * If the property does not exist, the callback (if set) is called.
     *
     * @param string $propertyName The name of the property to retrieve.
     * @return mixed|null The property value or null if not found.
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);

        if (isset($this->{$var})) {
            return $this->{$var};
        } else {
            $value = PicoStringUtil::camelToTitle($var);
            if (isset($this->callback) && is_callable($this->callback)) {
                call_user_func($this->callback, $var, $value);
            }
            return $value;
        }
    }
}
