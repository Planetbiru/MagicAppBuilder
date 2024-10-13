<?php

namespace MagicObject\DataLabel;

use MagicObject\SetterGetter;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoStringUtil;
use stdClass;

/**
 * Class representing a data label with annotations for properties and tables.
 *
 * This class uses annotations to define properties and their metadata.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDataLabel extends SetterGetter
{
    const ANNOTATION_PROPERTIES = "Properties"; // Annotation key for properties
    const ANNOTATION_TABLE      = "Table"; // Annotation key for table name
    const KEY_NAME              = "name"; // Key for the name
    const ANNOTATION_VAR        = "var"; // Annotation key for variable

    /**
     * Parameters defined in the class annotations.
     *
     * @var array
     */
    private $classParams = array();
    
    /**
     * Name of the class.
     *
     * @var string
     */
    private $className = "";

    /**
     * Constructor for the PicoDataLabel class.
     *
     * Initializes the class and loads data if provided.
     *
     * @param self|array|object $data Data to initialize the object with.
     */
    public function __construct($data)
    {
        parent::__construct();
        $this->className = get_class($this);
        $jsonAnnot = new PicoAnnotationParser($this->className);
        $params = $jsonAnnot->getParameters();
        foreach ($params as $paramName => $paramValue) {
            $vals = $jsonAnnot->parseKeyValue($paramValue);
            $this->classParams[$paramName] = $vals;
        }
        if ($data != null) {
            $this->loadData($data);
        }
    }
    
    /**
     * Loads data into the object from a provided array, object, or self instance.
     *
     * @param mixed $data The data to load into the object.
     * @return self The current instance for method chaining.
     */
    public function loadData($data)
    {
        if ($data != null) {
            if ($data instanceof self) {
                $values = $data->value();
                foreach ($values as $key => $value) {
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->set($key2, $value);
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
     * Retrieves object information by parsing class and property annotations.
     *
     * @return stdClass An object containing the table name, columns, default values, and not-null columns.
     */
    public function getObjectInfo()
    {
        $reflexClass = new PicoAnnotationParser($this->className);
        $table = $reflexClass->getParameter(self::ANNOTATION_TABLE);
        $values = $reflexClass->parseKeyValue($table);
        $picoTableName = $values[self::KEY_NAME];
        $properties = array();
        $notNullColumns = array();
        $props = $reflexClass->getProperties();
        $defaultValue = array();

        // Iterate through each property of the class
        foreach ($props as $prop) {
            $reflexProp = new PicoAnnotationParser($this->className, $prop->name, PicoAnnotationParser::PROPERTY);
            $parameters = $reflexProp->getParameters();

            // Get column names for each parameter
            foreach ($parameters as $param => $val) {
                if (strcasecmp($param, self::ANNOTATION_PROPERTIES) == 0) {
                    $values = $reflexProp->parseKeyValue($val);
                    if (!empty($values)) {
                        $properties[$prop->name] = $values;
                    }
                }
            }
        }

        // Aggregate the information
        $info = new stdClass;
        $info->tableName = $picoTableName;
        $info->columns = $properties;
        $info->defaultValue = $defaultValue;
        $info->notNullColumns = $notNullColumns;
        
        return $info;
    }
}
