<?php

namespace MagicObject;

use DOMDocument;
use DOMNode;
use Exception;
use MagicObject\Database\PicoTableInfo;
use MagicObject\Language\PicoLanguage;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoGenericObject;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoTableUtil;
use ReflectionClass;
use stdClass;

/**
 * Class DataTable
 * Represents a data table for managing and rendering structured data.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class DataTable extends SetterGetter
{
    const ANNOTATION_TABLE = "Table";
    const ANNOTATION_ATTRIBUTES = "Attributes";
    const CLASS_LIST = "ClassList";
    const ANNOTATION_ID = "Id";
    const ANNOTATION_COLUMN = "Column";
    const ANNOTATION_VAR = "var";
    const ANNOTATION_GENERATED_VALUE = "GeneratedValue";
    const ANNOTATION_NOT_NULL = "NotNull";
    const ANNOTATION_DEFAULT_COLUMN = "DefaultColumn";
    const ANNOTATION_DEFAULT_COLUMN_LABEL = "DefaultColumnLabel";
    const ANNOTATION_LANGUAGE = "Language";
    const KEY_PROPERTY_TYPE = "property_type";
    const KEY_PROPERTY_NAME = "property_name";

    const KEY_NAME = "name";
    const KEY_CLASS = "class";
    const KEY_VALUE = "value";
    const SQL_DATE_TIME_FORMAT = "Y-m-d H:i:s";
    const DATE_TIME_FORMAT = "datetimeformat";

    const TAG_TABLE = "table";
    const TAG_THEAD = "thead";
    const TAG_TBODY = "tbody";
    const TAG_TR = "tr";
    const TAG_TH = "th";
    const TAG_TD = "td";

    const TD_LABEL = "td-label";
    const TD_VALUE = "td-value";

    /**
     * Current language code.
     *
     * @var string
     */
    private $_currentLanguage; //NOSONAR

    /**
     * Language instances.
     *
     * @var PicoLanguage[]
     */
    private $_lableLanguage = array(); //NOSONAR

    /**
     * Table identity.
     *
     * @var PicoGenericObject
     */
    private $_tableIdentity; //NOSONAR

    /**
     * Table information.
     *
     * @var PicoTableInfo
     */
    private $_tableInfo; //NOSONAR

    /**
     * Labels for the table.
     *
     * @var array
     */
    private $_labels = array(); //NOSONAR

    /**
     * Constructor
     *
     * Initializes the data table and loads data if provided.
     *
     * @param MagicObject|self|stdClass|array|null $data Data to be loaded
     */
    public function __construct($data = null)
    {
        if(isset($data))
        {
            $this->loadData($data);
        }
        $this->init();
    }

    /**
     * Loads data into the object.
     *
     * @param mixed $data Reference to the data to load.
     * @return self
     */
    public function loadData($data)
    {
        if($data != null)
        {
            if($data instanceof MagicObject)
            {
                $values = $data->value();
                try
                {
                    $this->_tableInfo = $data->tableInfo();
                }
                catch(Exception $e)
                {
                    $this->_tableInfo = null;
                }
                foreach ($values as $key => $value) {
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->set($key2, $value);
                    $this->_labels[$key2] = $data->label($key2);
                }
            }
            else if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->set($key2, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Adds a language to the table.
     *
     * @param string $code Language code.
     * @param object|stdClass|array $reference Reference for language.
     * @param boolean $use Flag to indicate whether to use this language.
     * @return self
     */
    public function addLanguage($code, $reference, $use = false)
    {
        $this->_lableLanguage[$code] = new PicoLanguage($reference);
        if($use)
        {
            $this->selectLanguage($code);
        }
        return $this;
    }

    /**
     * Removes a language from the table.
     *
     * @param string $code Language code.
     * @return self
     */
    public function removeLanguage($code)
    {
        if(isset($this->_lableLanguage[$code]))
        {
            unset($this->_lableLanguage[$code]);
        }
        if(!empty($this->_lableLanguage))
        {
            $keys = array_keys($this->_lableLanguage);
            $this->selectLanguage($keys[0]);
        }
        return $this;
    }

    /**
     * Sets the current language.
     *
     * @param string $code Language code.
     * @return self
     */
    public function selectLanguage($code)
    {
        $this->_currentLanguage = $code;
        return $this;
    }

    /**
     * Initializes the table's attributes and configurations.
     *
     * @return self
     */
    private function init()
    {
        $className = get_class($this);
        $reflexClass = new PicoAnnotationParser($className);
        $this->_attributes = PicoTableUtil::parseElementAttributes($reflexClass->getFirstParameter(self::ANNOTATION_ATTRIBUTES));
        $classList = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::CLASS_LIST));
        $prefLanguage = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::ANNOTATION_LANGUAGE));
        $defaultColumnName = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::ANNOTATION_DEFAULT_COLUMN_LABEL));
        if($defaultColumnName->issetContent())
        {
            $this->_defaultColumnName = $defaultColumnName->getContent();
        }
        if($classList->issetContent())
        {
            $this->_classList = explode(" ", preg_replace('/\s+/', " ", $classList->getContent()));
            $this->_classList = array_unique($this->_classList);
        }
        if($prefLanguage->issetContent())
        {
            $this->_currentLanguage = $prefLanguage->getContent();
        }
        $this->_tableIdentity = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::ANNOTATION_TABLE));
        return $this;
    }

    /**
     * Retrieves the list of properties for the table.
     *
     * @param boolean $reflectSelf Whether to reflect on the current class.
     * @param boolean $asArrayProps Whether to return properties as an array.
     * @return array
     */
    protected function propertyList($reflectSelf = false, $asArrayProps = false)
    {
        $reflectionClass = $reflectSelf ? self::class : get_called_class();
        $class = new ReflectionClass($reflectionClass);

        // filter only the calling class properties
        // skip parent properties
        $properties = array_filter(
            $class->getProperties(),
            function($property) use($class) {
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );
        if($asArrayProps)
        {
            $result = array();
            $index = 0;
            foreach ($properties as $key) {
                $prop = $key->name;
                $result[$index] = $prop;

                $index++;
            }
            return $result;
        }
        else
        {
            return $properties;
        }
    }

    /**
     * Retrieves the content of an annotation.
     *
     * @param PicoAnnotationParser $reflexProp Class reflection.
     * @param PicoGenericObject $parameters Parameters.
     * @param string $annotation Annotation key.
     * @param string $attribute Attribute key.
     * @return mixed|null
     */
    private function annotationContent($reflexProp, $parameters, $annotation, $attribute)
    {
        if($parameters->get($annotation) != null)
        {
            $attrs = $reflexProp->parseKeyValueAsObject($parameters->get($annotation));
            if($attrs->get($attribute) != null)
            {
                return $attrs->get($attribute);
            }
        }
        return null;
    }

    /**
     * Defines the label for a property based on annotations.
     *
     * @param PicoAnnotationParser $reflexProp Class reflection.
     * @param PicoGenericObject $parameters Parameters.
     * @param string $key Property key.
     * @param string $defaultLabel Default label.
     * @return string
     */
    private function label($reflexProp, $parameters, $key, $defaultLabel)
    {
        $label = $defaultLabel;
        if(stripos($this->_defaultColumnName, "->"))
        {
            $cn = explode("->", $this->_defaultColumnName);
            $lbl = $this->annotationContent($reflexProp, $parameters, trim($cn[0]), trim($cn[1]));
            $label = PicoStringUtil::selectNotNull($lbl, $defaultLabel);

        }
        else if($this->_defaultColumnName == self::ANNOTATION_LANGUAGE)
        {
            if(isset($this->_lableLanguage) && isset($this->_lableLanguage[$this->_currentLanguage]))
            {
                $label = $this->_lableLanguage[$this->_currentLanguage]->isset($key) ? $this->_lableLanguage[$this->_currentLanguage]->get($key) : $defaultLabel;
            }
            else
            {
                $lbl = $this->annotationContent($reflexProp, $parameters, "Label", "content");
                $label = PicoStringUtil::selectNotNull($lbl, $defaultLabel);
            }

        }
        return $label;
    }

    /**
     * Appends table rows based on properties.
     *
     * @param DOMDocument $doc DOM Document.
     * @param DOMNode $tbody DOM Node for the tbody.
     * @param array $props Properties to append.
     * @param string $className Class name for reflection.
     * @return void
     */
    private function appendByProp($doc, $tbody, $props, $className)
    {
        foreach($props as $prop)
        {
            $key = $prop->name;
            $label = $key;
            $value = $this->get($key);
            if(is_scalar($value))
            {
                $tr = $tbody->appendChild($doc->createElement(self::TAG_TR));

                $reflexProp = new PicoAnnotationParser($className, $key, PicoAnnotationParser::PROPERTY);

                if($reflexProp != null)
                {
                    $parameters = $reflexProp->getParametersAsObject();
                    if($parameters->issetLabel())
                    {
                        $label = $this->label($reflexProp, $parameters, $key, $label);
                    }
                }

                $td1 = $tr->appendChild($doc->createElement(self::TAG_TD));
                $td1->setAttribute(self::KEY_CLASS, self::TD_LABEL);
                $td1->textContent = $label;

                $td2 = $tr->appendChild($doc->createElement(self::TAG_TD));
                $td2->setAttribute(self::KEY_CLASS, self::TD_VALUE);
                $td2->textContent = isset($value) ? $value : "";
            }
        }
    }

    /**
     * Appends table rows based on values.
     *
     * @param DOMDocument $doc DOM Document.
     * @param DOMNode $tbody DOM Node for the tbody.
     * @param stdClass $values Data to append as rows.
     * @return void
     */
    private function appendByValues($doc, $tbody, $values)
    {
        foreach($values as $propertyName=>$value)
        {
            if(is_scalar($value))
            {
                $tr = $tbody->appendChild($doc->createElement(self::TAG_TR));
                $label = $this->getLabel($propertyName);

                $td1 = $tr->appendChild($doc->createElement(self::TAG_TD));
                $td1->setAttribute(self::KEY_CLASS, self::TD_LABEL);
                $td1->textContent = $label;

                $td2 = $tr->appendChild($doc->createElement(self::TAG_TD));
                $td2->setAttribute(self::KEY_CLASS, self::TD_VALUE);
                $td2->textContent = isset($value) ? $value : "";
            }
        }
    }

    /**
     * Gets the label for a specified property.
     *
     * @param string $propertyName Property name.
     * @return string
     */
    private function getLabel($propertyName)
    {
        $label = "";
        if(isset($this->_lableLanguage[$this->_currentLanguage]))
        {
            $language = $this->_lableLanguage[$this->_currentLanguage];

            $label = $language->get($propertyName);

        }
        else
        {
            if(isset($this->_labels[$propertyName]))
            {
                $label = $this->_labels[$propertyName];
            }
        }
        if(empty($label))
        {
            $label = PicoStringUtil::camelToTitle($propertyName);
        }
        return $label;
    }

    /**
     * Adds a class to the table.
     *
     * @param string $className Class name to add.
     * @return self
     */
    public function addClass($className)
    {
        if(PicoTableUtil::isValidClassName($className))
        {
            $this->_classList[] = $className;
            // fix duplicated class
            $this->_classList = array_unique($this->_classList);
        }
        return $this;
    }

    /**
     * Removes a class from the table.
     *
     * @param string $className Class name to remove.
     * @return self
     */
    public function removeClass($className)
    {
        if(PicoTableUtil::isValidClassName($className))
        {
            $tmp = array();
            foreach($this->_classList as $cls)
            {
                if($cls != $className)
                {
                    $tmp[] = $cls;
                }
            }
            $this->_classList = $tmp;
        }
        return $this;
    }

    /**
     * Replaces a class in the table with a new class name.
     *
     * @param string $search Class name to search for.
     * @param string $replace Class name to replace with.
     * @return self
     */
    public function replaceClass($search, $replace)
    {
        $this->removeClass($search);
        $this->addClass($replace);
        return $this;
    }

    /**
     * Converts the object to a string representation (HTML).
     *
     * @return string HTML representation of the data table.
     */
    public function __toString()
    {
        $className = get_class($this);
        $doc = new DOMDocument();
        $table = $doc->appendChild($doc->createElement(self::TAG_TABLE));

        PicoTableUtil::setAttributes($table, $this->_attributes);
        PicoTableUtil::setClassList($table, $this->_classList);
        PicoTableUtil::setIdentity($table, $this->_tableIdentity);

        $tbody = $table->appendChild($doc->createElement(self::TAG_TBODY));
        $doc->formatOutput = true;

        $props = $this->propertyList();
        if(!empty($props))
        {
            $this->appendByProp($doc, $tbody, $props, $className);
        }
        else
        {
            $values = $this->value();
            $this->appendByValues($doc, $tbody, $values);
        }
         return $doc->saveHTML();
    }

    /**
     * Gets table information.
     *
     * @return PicoTableInfo
     */
    public function getTableInfo()
    {
        return $this->_tableInfo;
    }
}