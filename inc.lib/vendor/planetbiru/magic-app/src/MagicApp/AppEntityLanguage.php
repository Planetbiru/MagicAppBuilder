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
     * Constructor
     *
     * @param MagicObject $entity
     * @param SecretObject $appConfig
     * @param string $currentLanguage
     */
    public function __construct($entity, $appConfig, $currentLanguage)
    {
        parent::__construct($entity);
        $this->appConfig = $appConfig;
        $this->currentLanguage = $currentLanguage;
    }

    /**
     * Get app config
     *
     * @return  SecretObject
     */ 
    public function getAppConfig()
    {
        return $this->appConfig;
    }

    /**
     * Get current language
     *
     * @return  string
     */ 
    public function getCurrentLanguage()
    {
        return $this->currentLanguage;
    }
}