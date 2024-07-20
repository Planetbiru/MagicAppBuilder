<?php

namespace MagicApp;

use MagicObject\Language\PicoLanguage;
use MagicObject\SecretObject;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

class AppLanguage extends PicoLanguage
{
    /**
     * App Config
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
     * Callback
     *
     * @var callable
     */
    private $callback;
    
    /**
     * Constructor
     *
     * @param SecretObject $appConfig
     * @param string $currentLanguage
     * @param callable $callback
     */
    public function __construct($appConfig = null, $currentLanguage = null, $callback = null)
    {
        $this->appConfig = $appConfig;
        $this->currentLanguage = $currentLanguage;
        $this->loadData($this->loadLaguageData());
        if(isset($callback) && is_callable($callback))
        {
            $this->callback = $callback;
        }
    }

    /**
     * Load data
     *
     * @return array
     */
    private function loadLaguageData()
    {
        $langFile = $this->appConfig->getBaseDirectoryLanguage()."/".$this->currentLanguage."/app.ini";

        if(!file_exists(dirname($langFile)))
        {
            mkdir(dirname($langFile), 0755, true);
        }
        if(!file_exists($langFile))
        {
            file_put_contents($langFile, "");
        }
        $data = PicoIniUtil::parseIniFile($langFile);
        if(!isset($data) || !is_array($data))
        {
            $data = array();
        }
        return $data;
    }
    
    /**
     * Get property value
     *
     * @param string $propertyName
     * @return mixed|null
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->$var))
        {
            return $this->$var;
        }
        else
        {
            $value = PicoStringUtil::camelToTitle($var);
            if(isset($this->callback) && is_callable($this->callback))
            {
                call_user_func($this->callback, $var, $value);
            }
            return $value;
        }
    }
}