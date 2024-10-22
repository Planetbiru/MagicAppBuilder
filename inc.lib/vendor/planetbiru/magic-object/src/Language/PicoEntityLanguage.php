<?php

namespace MagicObject\Language;

use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoGenericObject;
use MagicObject\Util\PicoStringUtil;
use ReflectionClass;
use stdClass;

/**
 * Entity Language Class
 *
 * This class manages entity language configurations, including loading 
 * labels and handling different language options.
 * 
 * @author Kamshory
 * @package MagicObject\Language
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoEntityLanguage
{
    const ANNOTATION_TABLE = "Table";
    const ANNOTATION_LANGUAGE = "Language";

    private $_defaultColumnName = "key"; //NOSONAR

    /**
     * Current language code.
     *
     * @var string
     */
    private $_currentLanguage; //NOSONAR

    /**
     * Array of PicoLanguage objects.
     *
     * @var PicoLanguage[]
     */
    private $_lableLanguage = array(); //NOSONAR

    /**
     * Table identity object.
     *
     * @var PicoGenericObject
     */
    private $_tableIdentity; //NOSONAR

    /**
     * Labels for the entity.
     *
     * @var array
     */
    private $_labels = array(); //NOSONAR

    /**
     * Entity class name.
     *
     * @var string
     */
    private $_entityClassName = ""; //NOSONAR

    /**
     * Entity language code.
     *
     * @var string
     */
    private $_entityLanguage = ""; //NOSONAR

    /**
     * Constructor
     *
     * @param MagicObject|null $entity The entity to load.
     */
    public function __construct($entity = null)
    {
        if (isset($entity)) {
            $this->loadEntityLabel($entity);
        }
    }

    /**
     * Load data into the object from the given entity.
     *
     * @param MagicObject $entity The entity to load.
     * @return self
     */
    public function loadEntityLabel($entity)
    {
        $this->_entityClassName = get_class($entity);
        $reflexClass = new PicoAnnotationParser($this->_entityClassName);
        $lang = $reflexClass->getFirstParameter(self::ANNOTATION_LANGUAGE);
        
        if ($lang !== null) {
            $prefLanguage = $reflexClass->parseKeyValueAsObject($lang);
            if ($prefLanguage->issetContent()) {
                $this->_entityLanguage = trim($prefLanguage->getContent());
                $this->_currentLanguage = $this->_entityLanguage;
            }
        } else {
            $this->_entityLanguage = "__default";
            $this->_currentLanguage = $this->_entityLanguage;
        }

        $this->_tableIdentity = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::ANNOTATION_TABLE));
        $propertyList = $this->propertyList(true);
        $defaultLanguage = array();
        
        foreach ($propertyList as $prop) {
            $reflexProp = new PicoAnnotationParser($this->_entityClassName, $prop, PicoAnnotationParser::PROPERTY);
            if ($reflexProp !== null) {
                $parameters = $reflexProp->getParametersAsObject();
                if ($parameters->issetLabel()) {
                    $property = PicoStringUtil::camelize($prop);
                    $label = $this->label($reflexProp, $parameters, PicoStringUtil::camelToTitle($property));
                    $defaultLanguage[$property] = $label;
                }
            }
        }

        $this->addLanguage($this->_entityLanguage, $defaultLanguage, true);
        return $this;
    }

    /**
     * Add a language to the entity.
     *
     * @param string $code Language code.
     * @param object|stdClass|array $reference Reference data for the language.
     * @param bool $use Flag to indicate whether to use this language immediately.
     * @return self
     */
    public function addLanguage($code, $reference, $use = false)
    {
        $this->_lableLanguage[$code] = new PicoLanguage($reference);
        if ($use) {
            $this->selectLanguage($code);
        }
        return $this;
    }

    /**
     * Remove a language from the entity.
     *
     * @param string $code Language code to remove.
     * @return self
     */
    public function removeLanguage($code)
    {
        if (isset($this->_lableLanguage[$code])) {
            unset($this->_lableLanguage[$code]);
        }
        if (!empty($this->_lableLanguage)) {
            $keys = array_keys($this->_lableLanguage);
            $this->selectLanguage($keys[0]);
        }
        return $this;
    }

    /**
     * Set the current language.
     *
     * @param string $code Language code to set as current.
     * @return self
     */
    public function selectLanguage($code)
    {
        $this->_currentLanguage = $code;
        return $this;
    }

    /**
     * Get the list of properties of the entity.
     *
     * @param bool $asArrayProps Flag to determine if result should be returned as an array.
     * @return array List of properties.
     */
    public function propertyList($asArrayProps = false)
    {
        $class = new ReflectionClass($this->_entityClassName);

        // Filter only the properties declared in the calling class
        $properties = array_filter(
            $class->getProperties(),
            function ($property) use ($class) {
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );

        if ($asArrayProps) {
            return array_map(function ($prop) {
                return $prop->name;
            }, $properties);
        } else {
            return $properties;
        }
    }

    /**
     * Get the content of a specific annotation.
     *
     * @param PicoAnnotationParser $reflexProp Property reflection object.
     * @param PicoGenericObject $parameters Parameters associated with the property.
     * @param string $annotation Annotation name to search for.
     * @param string $attribute Attribute name to retrieve.
     * @return mixed|null
     */
    private function annotationContent($reflexProp, $parameters, $annotation, $attribute)
    {
        if ($parameters->get($annotation) !== null) {
            $attrs = $reflexProp->parseKeyValueAsObject($parameters->get($annotation));
            if ($attrs->get($attribute) !== null) {
                return $attrs->get($attribute);
            }
        }
        return null;
    }

    /**
     * Define the label for a property.
     *
     * @param PicoAnnotationParser $reflexProp Property reflection object.
     * @param PicoGenericObject $parameters Parameters associated with the property.
     * @param string $defaultLabel Default label to use if no annotation is found.
     * @return string The defined label.
     */
    private function label($reflexProp, $parameters, $defaultLabel)
    {
        $lbl = $this->annotationContent($reflexProp, $parameters, "Label", "content");
        return PicoStringUtil::selectNotNull($lbl, $defaultLabel);
    }

    /**
     * Set a property value.
     *
     * @param string $propertyName Name of the property to set.
     * @param mixed|null $propertyValue Value to set for the property.
     * @return self
     */
    public function set($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        $this->$var = $propertyValue;
        return $this;
    }

    /**
     * Get a property value.
     *
     * @param string $propertyName Name of the property to get.
     * @return mixed|null The value of the property or null if not set.
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if (isset($this->_lableLanguage[$this->_currentLanguage]) && $this->_lableLanguage[$this->_currentLanguage]->get($var) !== null) {
            return $this->_lableLanguage[$this->_currentLanguage]->get($var);
        } else {
            return PicoStringUtil::camelToTitle($var);
        }
    }

    /**
     * Magic method to get property values.
     *
     * Example: echo $instance->foo;
     *
     * @param string $name Name of the property to get.
     * @return mixed Value of the property if set, otherwise null.
     */
    public function __get($name)
    {
        if ($this->__isset($name)) {
            return $this->get($name);
        }
    }

    /**
     * Check if a property is set or not.
     *
     * @param string $name Name of the property to check.
     * @return bool True if the property is set, false otherwise.
     */
    public function __isset($name)
    {
        return isset($this->$name);
    }

    /**
     * Magic method to handle undefined methods.
     *
     * This method allows for the dynamic handling of method calls that are not explicitly defined
     * in the class. Specifically, it processes calls to getter methods that start with the prefix "get".
     * 
     * When a method starting with "get" is invoked, this method extracts the property name
     * from the method name and calls the `get` method to retrieve the corresponding value.
     * 
     * Supported dynamic getter methods:
     * - `get<PropertyName>`: 
     *   This will call the `get` method with the property name derived from the method call.
     *   For example, calling `$obj->getAge()` would result in a call to `$this->get('age')`.
     * 
     * If the method name does not start with "get" or does not correspond to a valid property,
     * this method will return `null`.
     *
     * @param string $method Name of the method being called, expected to start with "get".
     * @param array $args Arguments passed to the method; typically unused in this context.
     * @return mixed|null The value of the requested property if it exists; otherwise, null.
     */
    public function __call($method, $args) //NOSONAR
    {
        if (stripos($method, "get") === 0 && strlen($method) > 3) {
            $prop = lcfirst(substr($method, 3));
            return $this->get($prop);
        }
    }

    /**
     * Get the table identity.
     *
     * @return PicoGenericObject The table identity object.
     */
    public function getTableIdentity()
    {
        return $this->_tableIdentity;
    }

    /**
     * Get the entity language code.
     *
     * @return string The entity language code.
     */
    public function getEntityLanguage()
    {
        return $this->_entityLanguage;
    }

    /**
     * Get the entity class name.
     *
     * @return string The entity class name.
     */
    public function getEntityClassName()
    {
        return $this->_entityClassName;
    }

    /**
     * Converts the object to its string representation.
     *
     * This method checks if the label language and current language
     * are set. If they are, it returns the JSON-encoded string of
     * the label corresponding to the current language. If not, it
     * returns an empty JSON object.
     *
     * @return string A JSON-encoded string of the label for the current language,
     *                or an empty JSON object if the current language is not set.
     */
    public function __toString()
    {
        if (isset($this->_lableLanguage) && isset($this->_currentLanguage) && isset($this->_lableLanguage[$this->_currentLanguage])) {
            return json_encode($this->_lableLanguage[$this->_currentLanguage]);
        } else {
            return "{}";
        }
    }
}
