<?php

namespace MagicApp;

use MagicObject\Language\PicoEntityLanguage;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

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
     * Full class name
     *
     * @var string
     */
    private $fullClassName;
    
    /**
     * Base class name
     *
     * @var string
     */
    private $baseClassName;

    /**
     * Base language directory
     *
     * @var string
     */
    private $baseLanguageDirectory;
    
    /**
     * Constructor
     *
     * @param MagicObject $entity
     * @param SecretObject $appConfig
     * @param string $currentLanguage
     */
    public function __construct($entity, $appConfig, $currentLanguage)
    {
        parent::__construct($entity);

        $langs = $this->loadEntityLanguage($entity, $appConfig, $currentLanguage);

        if(!$langs->empty())
        {
            $this->addLanguage($currentLanguage, $langs->value(), true);
        }
    }

    /**
     * Load entity language
     *
     * @param MagicObject $entity
     * @param SecretObject $appConfig
     * @param string $currentLanguage
     * @return MagicObject
     */
    public function loadEntityLanguage($entity, $appConfig, $currentLanguage)
    {
        $langs = new MagicObject();
        $this->baseClassName = $this->baseClassName(get_class($entity), $appConfig->getEntityBaseNamespace());
        $this->fullClassName = $this->baseClassName(get_class($entity), $appConfig->getEntityBaseNamespace(), 1);
        $this->appConfig = $appConfig;
        $this->currentLanguage = $currentLanguage;
        $this->baseLanguageDirectory = $appConfig->getApplication()->getBaseLanguageDirectory();
        
        // add language
        $languageFilePath = $this->baseLanguageDirectory."/".$currentLanguage."/Entity/".$this->fullClassName.".ini";
        if(file_exists($languageFilePath))
        {
            $langs->loadIniFile($languageFilePath);
        }
        return $langs;
    }
    
    /**
     * Get base class name
     *
     * @param string $className
     * @return string
     */
    private function baseClassName($className, $prefix, $parent = 0)
    {
        $result = null;
        if(!isset($prefix))
        {
            if(strpos($className, "\\") === false)
            {
                $result = $className;
            }
            else
            {
                $arr = explode("\\", trim($className, "\\"));
                if($parent > 0)
                {
                    $arr2 = array();
                    for($i = count($arr) - 1, $j = 0; $i >= 0 && $j <= $parent; $i--, $j++)
                    {
                        $arr2[] = $arr[$i];
                    }
                    $result = implode("\\", array_reverse($arr2));
                }
                else
                {
                    $result = end($arr);
                }
            }
        }
        else
        {
            $className = trim(str_replace("/", "\\", $className));
            $prefix = trim(str_replace("/", "\\", $prefix));
            if(strlen($className) > strlen($prefix) && strpos($className, $prefix) === 0)
            {
                $result = substr($className, strlen($prefix) + 1);
            }
            else
            {
                $result = $className;
            }
        }
        return $result;
    }

    /**
     * Get app config
     *
     * @return SecretObject
     */ 
    public function getAppConfig()
    {
        return $this->appConfig;
    }

    /**
     * Get current language
     *
     * @return string
     */ 
    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
    }
}