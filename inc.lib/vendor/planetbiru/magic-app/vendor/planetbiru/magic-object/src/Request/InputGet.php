<?php

namespace MagicObject\Request;

use MagicObject\Util\ClassUtil\PicoObjectParser;

class  InputGet extends PicoRequestBase {
    /**
     * Recursive
     *
     * @var boolean
     */
    private $_recursive = false; //NOSONAR

    /**
     * Constructor
     * @param boolean $recursive
     * @param boolean $parseNullAndBool
     * @param boolean $forceScalar
     */
    public function __construct($recursive = false, $parseNullAndBool = false, $forceScalar = false)
    {
        parent::__construct($forceScalar);
        $this->_recursive = $recursive; 
        if($parseNullAndBool)
        {
            $this->loadData($this->forceBoolAndNull($_GET));
        }
        else
        {
            $this->loadData($_GET);
        }
    }

    /**
     * Get global variable $_GET
     *
     * @return array
     */
    public static function requestGet()
    {
        return $_GET;
    }

    /**
     * Override loadData
     *
     * @param array $data
     * @return self
     */
    public function loadData($data, $tolower = false)
    {
        if($this->_recursive)
        {
            $genericObject = PicoObjectParser::parseJsonRecursive($data);
            if($genericObject != null)
            {
                $values = $genericObject->valueArray();
                if($values != null && is_array($values))
                {
                    $keys = array_keys($values);
                    foreach($keys as $key)
                    {
                        $this->{$key} = $genericObject->get($key);
                    }
                }
            }
        }
        else
        {
            parent::loadData($data);
        }
        return $this;
    } 
}