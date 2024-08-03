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
     * Class name
     *
     * @var string
     */
    private $className;
    
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
        $this->className = $this->baseClassName(get_class($entity), $appConfig->getEntityBaseNamespace());
        $this->appConfig = $appConfig;
        $this->currentLanguage = $currentLanguage;
        
        // add language
        $baseEntityDirectory = $appConfig->getEntityBaseDirectory()."/".$currentLanguage;
        $languageFilePath = $baseEntityDirectory."/".$this->className.".ini";
        if(file_exists($languageFilePath))
        {
            $langs = new MagicObject();
            $langs->loadIniFile($languageFilePath);
            $this->addLanguage($currentLanguage, $langs->value(), true);
        }
    }
    
    /**
     * Get base class name
     *
     * @param string $className
     * @return string
     */
    private function baseClassName($className, $prefix)
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
                $result = end($arr);
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