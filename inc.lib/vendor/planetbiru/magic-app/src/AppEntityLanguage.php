<?php

namespace MagicApp;

use MagicObject\Language\PicoEntityLanguage;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

/**
 * Class AppEntityLanguage
 *
 * This class extends PicoEntityLanguage to manage language-specific
 * configurations for application entities. It handles loading language
 * data from INI files based on the current language setting and the
 * application's configuration.
 */
class AppEntityLanguage extends PicoEntityLanguage
{
    /**
     * App config
     *
     * @var SecretObject
     */
    private $appConfig;

    /**
     * Current language
     *
     * @var string
     */
    private $currentLanguage;

    /**
     * Full class name of the entity
     *
     * @var string
     */
    private $fullClassName;
    
    /**
     * Base class name of the entity
     *
     * @var string
     */
    private $baseClassName;

    /**
     * Base language directory path
     *
     * @var string
     */
    private $baseLanguageDirectory;
    
    /**
     * Constructor
     *
     * Initializes the AppEntityLanguage with the entity, app configuration,
     * and the current language. It loads the corresponding language values.
     *
     * @param MagicObject $entity The entity whose language needs to be loaded
     * @param SecretObject $appConfig The application configuration
     * @param string $currentLanguage The current language code
     */
    public function __construct($entity, $appConfig, $currentLanguage)
    {
        parent::__construct($entity);
        $labels = $this->getDefaultLabel();
        $langs = $this->loadEntityLanguage($entity, $appConfig, $currentLanguage);
        $values = $langs->valueArray();
        $labels = array_merge($labels, $values);
        if (!empty($values)) {
            $this->addLanguage($currentLanguage, $labels, true);
        }
    }

    /**
     * Load entity language from an INI file based on the entity and config.
     *
     * @param MagicObject $entity The entity to load language for
     * @param SecretObject $appConfig The application configuration
     * @param string $currentLanguage The current language code
     * @return MagicObject The loaded language values as a MagicObject
     */
    public function loadEntityLanguage($entity, $appConfig, $currentLanguage)
    {
        $langs = new MagicObject();
        $this->baseClassName = $this->baseClassName(get_class($entity), $appConfig->getEntityBaseNamespace());
        $this->fullClassName = $this->baseClassName(get_class($entity), $appConfig->getEntityBaseNamespace(), 1);
        $this->appConfig = $appConfig;
        $this->currentLanguage = $currentLanguage;

        $app = $appConfig->getApplication();
        if(!isset($app))
        {
            $app = new SecretObject();
        }

        $this->baseLanguageDirectory = $app->getBaseLanguageDirectory();
        
        // Construct the language file path
        $languageFilePath = $this->baseLanguageDirectory . "/" . $currentLanguage . "/Entity/" . $this->fullClassName . ".ini";
        $languageFilePath = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $languageFilePath);
        
        // Load the language file if it exists
        if (file_exists($languageFilePath)) {
            $langs->loadIniFile($languageFilePath);
        }
        return $langs;
    }
    
    /**
     * Extracts the base class name from a full class name,
     * optionally using a prefix and accounting for parent classes.
     *
     * @param string $className The full class name
     * @param string|null $prefix An optional prefix to remove
     * @param int $parent Number of parent classes to consider
     * @return string The base class name
     */
    private function baseClassName($className, $prefix, $parent = 0) //NOSONAR
    {
        $result = null;
        if (!isset($prefix)) {
            if (strpos($className, "\\") === false) {
                $result = $className;
            } else {
                $arr = explode("\\", trim($className, "\\"));
                if ($parent > 0) {
                    $arr2 = array();
                    for ($i = count($arr) - 1, $j = 0; $i >= 0 && $j <= $parent; $i--, $j++) {
                        $arr2[] = $arr[$i];
                    }
                    $result = implode("\\", array_reverse($arr2));
                } else {
                    $result = end($arr);
                }
            }
        } else {
            $className = trim(str_replace("/", "\\", $className));
            $prefix = trim(str_replace("/", "\\", $prefix));
            if (strlen($className) > strlen($prefix) && strpos($className, $prefix) === 0) {
                $result = substr($className, strlen($prefix) + 1);
            } else {
                $result = $className;
            }
        }
        return $result;
    }

    /**
     * Get the application configuration.
     *
     * @return SecretObject The application configuration object
     */ 
    public function getAppConfig()
    {
        return $this->appConfig;
    }

    /**
     * Get the current language code.
     *
     * @return string The current language code
     */ 
    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
    }
}
