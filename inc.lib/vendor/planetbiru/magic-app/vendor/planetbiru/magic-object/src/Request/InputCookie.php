<?php

namespace MagicObject\Request;

use MagicObject\Util\ClassUtil\PicoObjectParser;

/**
 * Class for handling input from cookies.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class InputCookie extends PicoRequestBase {
    
    /**
     * Indicates whether to recursively convert all objects.
     *
     * @var boolean
     */
    private $_recursive = false; // NOSONAR

    /**
     * Constructor for the InputCookie class.
     *
     * @param boolean $recursive Flag to indicate if all objects should be converted recursively.
     * @param boolean $parseNullAndBool Flag to indicate whether to parse NULL and BOOL values.
     * @param boolean $forceScalar Flag to indicate if only scalar values should be retrieved.
     */
    public function __construct($recursive = false, $parseNullAndBool = false, $forceScalar = false)
    {
        parent::__construct($forceScalar);
        $this->_recursive = $recursive;

        if ($parseNullAndBool) {
            $this->loadData($this->forceBoolAndNull($_COOKIE));
        } else {
            $this->loadData($_COOKIE);
        }
    }

    /**
     * Get the global variable $_COOKIE.
     *
     * @return array The cookie data.
     */
    public static function requestCookie()
    {
        return $_COOKIE;
    }

    /**
     * Override the loadData method to load cookie data.
     *
     * @param array $data Data to load into the object.
     * @param boolean $tolower Flag to indicate if the keys should be converted to lowercase (default is false).
     * @return self Returns the instance of the current object.
     */
    public function loadData($data, $tolower = false)
    {
        if ($this->_recursive) {
            $genericObject = PicoObjectParser::parseJsonRecursive($data);
            if ($genericObject !== null) {
                $values = $genericObject->valueArray();
                if ($values !== null && is_array($values)) {
                    $keys = array_keys($values);
                    foreach ($keys as $key) {
                        $this->{$key} = $genericObject->get($key);
                    }
                }
            }
        } else {
            parent::loadData($data);
        }
        return $this;
    }
}
