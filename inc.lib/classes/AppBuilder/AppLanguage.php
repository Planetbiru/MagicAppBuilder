<?php

namespace AppBuilder;

use MagicObject\Language\PicoLanguage;
use MagicObject\Util\PicoStringUtil;
use stdClass;

class AppLanguage extends PicoLanguage
{
    /**
     * Callback
     *
     * @var callable
     */
    private $callback;
    
    /**
     * Constructor
     *
     * @param stdClass|array $data
     */
    public function __construct($data = null, $callback = null)
    {
        parent::__construct($data);
        if(isset($callback) && is_callable($callback))
        {
            $this->callback = $callback;
        }
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