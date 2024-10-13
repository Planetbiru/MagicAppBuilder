<?php

namespace AppBuilder;

use MagicObject\Language\PicoLanguage;
use MagicObject\Util\PicoStringUtil;
use stdClass;

class AppLanguage extends PicoLanguage
{
    /**
     * A callable function used for custom processing of property retrievals.
     *
     * @var callable|null
     */
    private $callback;
    
    /**
     * Constructor for the AppLanguage class.
     *
     * Initializes the class with data and an optional callback function.
     *
     * @param stdClass|array|null $data An optional data structure (stdClass or array) to initialize the language properties.
     * @param callable|null $callback An optional callback function for custom handling when properties are accessed.
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
     * Retrieve the value of a property.
     *
     * This method attempts to fetch the value of a property specified by the property name.
     * If the property does not exist, it invokes the callback (if set) for custom processing.
     *
     * @param string $propertyName The name of the property to retrieve.
     * @return mixed|null The value of the property, or null if not found. If the property does not exist, 
     *                   the title-cased version of the property name is returned and passed to the callback if set.
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if (isset($this->$var)) {
            return $this->$var; // Return the value if the property exists.
        } else {
            $value = PicoStringUtil::camelToTitle($var); // Convert the property name to title case.
            if (isset($this->callback) && is_callable($this->callback)) {
                call_user_func($this->callback, $var, $value); // Call the callback with the property name and title case.
            }
            return $value; // Return the title-cased name if the property does not exist.
        }
    }
}