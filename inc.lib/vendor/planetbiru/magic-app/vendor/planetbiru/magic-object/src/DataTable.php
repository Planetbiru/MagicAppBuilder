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
 * 
 * Represents a data table for managing and rendering structured data. 
 * This class supports dynamic loading of data, multi-language support, and 
 * provides methods for manipulating the table's structure and appearance.
 *
 * The DataTable class can be used to create HTML tables dynamically based 
 * on the provided data, with support for property annotations to manage 
 * labels and attributes.
 *
 * @author Kamshory
 * @package MagicObject
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
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var string
     */
    private $_currentLanguage; // NOSONAR

    /**
     * Language instances.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var PicoLanguage[]
     */
    private $_lableLanguage = array(); // NOSONAR

    /**
     * Table identity.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var PicoGenericObject
     */
    private $_tableIdentity; // NOSONAR

    /**
     * Table information.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var PicoTableInfo
     */
    private $_tableInfo; // NOSONAR

    /**
     * Labels for the table.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var array
     */
    private $_labels = array(); // NOSONAR


    /**
     * Constructor
     *
     * Initializes the data table and loads data if provided.
     *
     * @param MagicObject|self|stdClass|array|null $data Data to be loaded
     */
    public function __construct($data = null)
    {
        if (isset($data)) {
            $this->loadData($data);
        }
        $this->init();
    }

    /**
     * Loads data into the DataTable object.
     *
     * This method processes the provided data and populates the object's 
     * properties accordingly. It supports MagicObject, arrays, and objects.
     *
     * @param mixed $data Data to load into the DataTable.
     * @return self Returns the current instance for method chaining.
     */
    public function loadData($data)
    {
        if ($data != null) {
            if ($data instanceof MagicObject) {
                $values = $data->value();
                try {
                    $this->_tableInfo = $data->tableInfo();
                } catch (Exception $e) {
                    $this->_tableInfo = null;
                }
                foreach ($values as $key => $value) {
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->set($key2, $value);
                    $this->_labels[$key2] = $data->label($key2);
                }
            } elseif (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->set($key2, $value);
                }
            }
        }
        return $this;
    }

    /**
     * Adds a language to the table for multi-language support.
     *
     * This method registers a language instance, which can be used to 
     * retrieve labels in the specified language.
     *
     * @param string $code Language code (e.g., 'en', 'fr').
     * @param object|stdClass|array $reference Reference for language data.
     * @param bool $use Indicates whether to set this language as the current one.
     * @return self Returns the current instance for method chaining.
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
     * Removes a specified language from the table.
     *
     * If the removed language was the current one, the first remaining 
     * language will be selected as the new current language.
     *
     * @param string $code Language code to remove.
     * @return self Returns the current instance for method chaining.
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
     * Sets the current language for label retrieval.
     *
     * This method updates the language code used for displaying labels.
     *
     * @param string $code Language code to set as current.
     * @return self Returns the current instance for method chaining.
     */
    public function selectLanguage($code)
    {
        $this->_currentLanguage = $code;
        return $this;
    }

    /**
     * Initializes the table's attributes and configurations based on annotations.
     *
     * This method parses the class annotations to set attributes, 
     * class lists, and preferred language settings.
     *
     * @return self Returns the current instance for method chaining.
     */
    private function init()
    {
        $className = get_class($this);
        $reflexClass = new PicoAnnotationParser($className);
        $this->_attributes = PicoTableUtil::parseElementAttributes($reflexClass->getFirstParameter(self::ANNOTATION_ATTRIBUTES));
        $classList = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::CLASS_LIST));
        $prefLanguage = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::ANNOTATION_LANGUAGE));
        $defaultColumnName = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::ANNOTATION_DEFAULT_COLUMN_LABEL));
        
        if ($defaultColumnName->issetContent()) {
            $this->_defaultColumnName = $defaultColumnName->getContent();
        }
        if ($classList->issetContent()) {
            $this->_classList = explode(" ", preg_replace('/\s+/', " ", $classList->getContent()));
            $this->_classList = array_unique($this->_classList);
        }
        if ($prefLanguage->issetContent()) {
            $this->_currentLanguage = $prefLanguage->getContent();
        }
        $this->_tableIdentity = $reflexClass->parseKeyValueAsObject($reflexClass->getFirstParameter(self::ANNOTATION_TABLE));
        return $this;
    }

    /**
     * Retrieves the list of properties for the table.
     *
     * Optionally filters the properties to include only those declared 
     * in the current class and returns them as an array or Reflection objects.
     *
     * @param bool $reflectSelf Whether to reflect on the current class.
     * @param bool $asArrayProps Whether to return properties as an array.
     * @return array Array of properties or Reflection objects.
     */
    protected function propertyList($reflectSelf = false, $asArrayProps = false)
    {
        $reflectionClass = $reflectSelf ? self::class : get_called_class();
        $class = new ReflectionClass($reflectionClass);

        // Filter only the calling class properties
        // Skip parent properties
        $properties = array_filter(
            $class->getProperties(),
            function ($property) use ($class) {
                return $property->getDeclaringClass()->getName() == $class->getName();
            }
        );
        
        if ($asArrayProps) {
            $result = array();
            $index = 0;
            foreach ($properties as $key) {
                $prop = $key->name;
                $result[$index] = $prop;
                $index++;
            }
            return $result;
        } else {
            return $properties;
        }
    }

    /**
     * Retrieves the content of a specified annotation.
     *
     * This method checks for the existence of an annotation and retrieves 
     * its attribute value if it exists.
     *
     * @param PicoAnnotationParser $reflexProp Class reflection for parsing annotations.
     * @param PicoGenericObject $parameters Parameters for the annotation.
     * @param string $annotation Annotation key to look up.
     * @param string $attribute Attribute key for the annotation value.
     * @return mixed|null The value of the annotation attribute or null if not found.
     */
    private function annotationContent($reflexProp, $parameters, $annotation, $attribute)
    {
        if ($parameters->get($annotation) != null) {
            $attrs = $reflexProp->parseKeyValueAsObject($parameters->get($annotation));
            if ($attrs->get($attribute) != null) {
                return $attrs->get($attribute);
            }
        }
        return null;
    }

    /**
     * Defines the label for a property based on its annotations.
     *
     * This method retrieves and selects the appropriate label for a given 
     * property, falling back to default behavior if necessary.
     *
     * @param PicoAnnotationParser $reflexProp Class reflection for property.
     * @param PicoGenericObject $parameters Parameters associated with the property.
     * @param string $key Property key for which to retrieve the label.
     * @param string $defaultLabel Default label to use if no annotation is found.
     * @return string The determined label for the property.
     */
    private function label($reflexProp, $parameters, $key, $defaultLabel)
    {
        $label = $defaultLabel;
        if (stripos($this->_defaultColumnName, "->")) {
            $cn = explode("->", $this->_defaultColumnName);
            $lbl = $this->annotationContent($reflexProp, $parameters, trim($cn[0]), trim($cn[1]));
            $label = PicoStringUtil::selectNotNull($lbl, $defaultLabel);
        } elseif ($this->_defaultColumnName == self::ANNOTATION_LANGUAGE) {
            if (isset($this->_lableLanguage) && isset($this->_lableLanguage[$this->_currentLanguage])) {
                $label = $this->_lableLanguage[$this->_currentLanguage]->isset($key) ? $this->_lableLanguage[$this->_currentLanguage]->get($key) : $defaultLabel;
            } else {
                $lbl = $this->annotationContent($reflexProp, $parameters, "Label", "content");
                $label = PicoStringUtil::selectNotNull($lbl, $defaultLabel);
            }
        }
        return $label;
    }

    /**
     * Appends table rows based on class properties.
     *
     * This method generates rows for the table based on the properties of the class 
     * and appends them to the provided DOM node.
     *
     * @param DOMDocument $doc The DOM document used to create elements.
     * @param DOMNode $tbody The DOM node representing the <tbody> of the table.
     * @param array $props Array of ReflectionProperty objects representing class properties.
     * @param string $className Name of the class for reflection.
     * @return void
     */
    private function appendByProp($doc, $tbody, $props, $className)
    {
        foreach ($props as $prop) {
            $key = $prop->name;
            $label = $key;
            $value = $this->get($key);
            if (is_scalar($value)) {
                $tr = $tbody->appendChild($doc->createElement(self::TAG_TR));

                $reflexProp = new PicoAnnotationParser($className, $key, PicoAnnotationParser::PROPERTY);

                if ($reflexProp != null) {
                    $parameters = $reflexProp->getParametersAsObject();
                    if ($parameters->issetLabel()) {
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
     * Appends table rows based on provided values.
     *
     * This method takes an array of values and creates rows in the table, 
     * appending them to the provided DOM node.
     *
     * @param DOMDocument $doc The DOM document used to create elements.
     * @param DOMNode $tbody The DOM node representing the <tbody> of the table.
     * @param stdClass $values Data to append as rows.
     * @return void
     */
    private function appendByValues($doc, $tbody, $values)
    {
        foreach ($values as $propertyName => $value) {
            if (is_scalar($value)) {
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
     * This method retrieves the label associated with a property, checking 
     * for language-specific labels before falling back to default labels.
     *
     * @param string $propertyName Name of the property for which to retrieve the label.
     * @return string The label for the specified property.
     */
    private function getLabel($propertyName)
    {
        $label = "";
        if (isset($this->_lableLanguage[$this->_currentLanguage])) {
            $language = $this->_lableLanguage[$this->_currentLanguage];
            $label = $language->get($propertyName);
        } else {
            if (isset($this->_labels[$propertyName])) {
                $label = $this->_labels[$propertyName];
            }
        }
        if (empty($label)) {
            $label = PicoStringUtil::camelToTitle($propertyName);
        }
        return $label;
    }

    /**
     * Adds a CSS class to the table.
     *
     * This method appends a class to the table's class list, ensuring 
     * that there are no duplicates.
     *
     * @param string $className Class name to add to the table.
     * @return self Returns the current instance for method chaining.
     */
    public function addClass($className)
    {
        if (PicoTableUtil::isValidClassName($className)) {
            $this->_classList[] = $className;
            // Fix duplicated class
            $this->_classList = array_unique($this->_classList);
        }
        return $this;
    }

    /**
     * Removes a CSS class from the table.
     *
     * This method filters out the specified class from the table's class list.
     *
     * @param string $className Class name to remove from the table.
     * @return self Returns the current instance for method chaining.
     */
    public function removeClass($className)
    {
        if (PicoTableUtil::isValidClassName($className)) {
            $this->_classList = array_filter($this->_classList, function ($cls) use ($className) {
                return $cls != $className;
            });
        }
        return $this;
    }

    /**
     * Replaces a class in the table with a new class name.
     *
     * @param string $search Class name to search for.
     * @param string $replace Class name to replace with.
     * @return self Returns the current instance for method chaining.
     */
    public function replaceClass($search, $replace)
    {
        $this->removeClass($search);
        $this->addClass($replace);
        return $this;
    }

    /**
     * Converts the DataTable object to an HTML string representation.
     *
     * This method generates the full HTML structure for the table, including 
     * headers and data rows, and returns it as a string.
     *
     * @return string HTML representation of the DataTable.
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
        if (!empty($props)) {
            $this->appendByProp($doc, $tbody, $props, $className);
        } else {
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
