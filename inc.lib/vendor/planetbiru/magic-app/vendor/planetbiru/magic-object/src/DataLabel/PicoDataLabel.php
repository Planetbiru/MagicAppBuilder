<?php

namespace MagicObject\DataLabel;

use MagicObject\SetterGetter;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\PicoStringUtil;
use stdClass;

/**
 * Data label
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDataLabel extends SetterGetter
{
    const ANNOTATION_PROPERTIES = "Properties";
    const ANNOTATION_TABLE      = "Table";
    const KEY_NAME              = "name";
    const ANNOTATION_VAR        = "var";
    
    /**
     * Class params
     *
     * @var array
     */
    private $classParams = array();
    
    /**
     * Class name
     * @var string
     */
    private $className = "";

    /**
     * Constructor
     *
     * @param self|array|object $data Data
     */
    public function __construct($data)
    {
        parent::__construct();
        $this->className = get_class($this);
        $jsonAnnot = new PicoAnnotationParser($this->className);
        $params = $jsonAnnot->getParameters();
        foreach($params as $paramName=>$paramValue)
        {
            $vals = $jsonAnnot->parseKeyValue($paramValue);
            $this->classParams[$paramName] = $vals;
        }
        if($data != null)
        {
            $this->loadData($data);
        }
    }
    
    /**
     * Load data to object
     * @param mixed $data Data to be load
     * @return self
     */
    public function loadData($data)
    {
        if($data != null)
        {
            if($data instanceof self)
            {
                $values = $data->value();
                foreach ($values as $key => $value) {
                    $key2 = PicoStringUtil::camelize($key);
                    $this->set($key2, $value);
                }
            }
            else if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = PicoStringUtil::camelize($key);
                    $this->set($key2, $value);
                }
            }
        }
        return $this;
    }
    
    /**
     * Get object information by parsing class and property annotation
     *
     * @return stdClass
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

        // iterate each properties of the class
        foreach($props as $prop)
        {
            $reflexProp = new PicoAnnotationParser($this->className, $prop->name, PicoAnnotationParser::PROPERTY);
            $parameters = $reflexProp->getParameters();

            // get column name of each parameters
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_PROPERTIES) == 0)
                {
                    $values = $reflexProp->parseKeyValue($val);
                    if(!empty($values))
                    {
                        $properties[$prop->name] = $values;
                    }
                }
            }
        }
        // bring it together
        $info = new stdClass;
        $info->tableName = $picoTableName;
        $info->columns = $properties;
        $info->defaultValue = $defaultValue;
        $info->notNullColumns = $notNullColumns;
        return $info;
    }
    
}