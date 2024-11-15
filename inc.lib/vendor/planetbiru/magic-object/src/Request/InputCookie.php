<?php

namespace MagicObject\Request;

use MagicObject\Util\ClassUtil\PicoObjectParser;

/**
 * Class for handling input from cookies.
 * This class provides functionality to retrieve and parse data from the global $_COOKIE array.
 * It supports options for recursively converting objects and parsing specific types like null and boolean values.
 * 
 * @author Kamshory
 * @package MagicObject\Request
 * @link https://github.com/Planetbiru/MagicObject
 */
class InputCookie extends PicoRequestBase {
    
    /**
     * Indicates whether to recursively convert all objects.
     *
     * @var bool
     */
    private $_recursive = false; // NOSONAR

    /**
     * Constructor for the InputCookie class.
     * Initializes the InputCookie instance, optionally setting flags for recursive object conversion, 
     * parsing null and boolean values, and forcing scalar value retrieval.
     *
     * @param bool $recursive Flag to indicate if all objects should be converted recursively (default is false).
     * @param bool $parseNullAndBool Flag to indicate whether to parse NULL and BOOL values from cookies (default is false).
     * @param bool $forceScalar Flag to indicate if only scalar values should be retrieved (default is false).
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
     * Get the global $_COOKIE variable.
     * 
     * This method is a static wrapper to return the raw cookie data from the $_COOKIE superglobal.
     *
     * @return array The cookie data from the $_COOKIE superglobal.
     */
    public static function requestCookie()
    {
        return $_COOKIE;
    }

    /**
     * Load cookie data into the object.
     * This method populates the object's properties with data from the provided cookie array.
     * It supports recursive object parsing if the _recursive flag is set.
     *
     * @param array $data Data to load into the object (usually from $_COOKIE).
     * @param bool $tolower Flag to indicate if the keys should be converted to lowercase (default is false).
     * @return self Returns the current instance for method chaining.
     */
    public function loadData($data, $tolower = false)
    {
        if ($this->_recursive) {
            // Parse the data recursively if the recursive flag is set.
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
            // Load data without recursion.
            parent::loadData($data);
        }
        return $this;
    }
}
