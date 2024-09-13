<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use stdClass;

/**
 * Entity label
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoEntityLabel
{
    const ANNOTATION_TABLE            = "Table";
    const ANNOTATION_LABEL            = "label";
    const ANNOTATION_COLUMN           = "Column";
    const ANNOTATION_JOIN_COLUMN      = "JoinColumn";
    const ANNOTATION_VAR              = "var";
    const ANNOTATION_ID               = "Id";
    const ANNOTATION_GENERATED_VALUE  = "GeneratedValue";
    const ANNOTATION_NOT_NULL         = "NotNull";
    const ANNOTATION_DEFAULT_COLUMN   = "DefaultColumn";
    const KEY_NAME                    = "name";
    const KEY_NULL                    = "null";
    const KEY_NOT_NULL                = "notnull";
    const KEY_NULLABLE                = "nullable";
    const KEY_INSERTABLE              = "insertable";
    const KEY_UPDATABLE               = "updatable";
    const KEY_STRATEGY                = "strategy";
    const KEY_GENERATOR               = "generator";
    const KEY_PROPERTY_TYPE           = "propertyType";
    const KEY_VALUE                   = "value";
    const KEY_ENTITY_OBJECT           = "entityObject";
    const VALUE_TRUE                  = "true";
    const VALUE_FALSE                 = "false";

    /**
     * Class name
     * @var string
     */
    private $className = "";

    /**
     * Languages
     *
     * @var string[]
     */
    private $langs = array();

    /**
     * Object
     *
     * @param MagicObject $object Entity
     * @param string[] $langs Languages
     */
    public function __construct($object, $langs)
    {
        $this->className = get_class($object);
        $this->langs = $langs;
    }

    /**
     * Get map
     *
     * @param string|null $lang Language
     * @return array|null
     */
    public function getMap($lang = null)
    {
        $info = $this->getObjectInfo();
        $labels = $info->labels;

        $map = array();

        // get from join columns
        if(isset($info->joinColumns) && is_array($info->joinColumns))
        {
            foreach($info->joinColumns as $propertyName=>$column)
            {
                if(isset($labels[$propertyName]))
                {
                    $map[$column[self::KEY_NAME]] = $labels[$propertyName];
                }
            }
        }

        // get from columns
        if(isset($info->columns) && is_array($info->columns))
        {
            foreach($info->columns as $propertyName=>$column)
            {
                if(isset($labels[$propertyName]))
                {
                    $map[$column[self::KEY_NAME]] = $labels[$propertyName];
                }
            }
        }

        // get from property
        $merged = array_merge($map, $labels);

        return $this->filter($merged, $lang);
    }

    /**
     * Filter
     *
     * @param array $merged Merged array
     * @param string $lang Language
     * @return array|null
     */
    private function filter($merged, $lang)
    {
        if($lang === null)
        {
            return $merged;
        }
        if(!in_array($lang, $this->langs))
        {
            return null;
        }
        else
        {
            $filtered = array();
            foreach($merged as $prop=>$val)
            {
                if(isset($val[$lang]))
                {
                    $filtered[$prop] = $val[$lang];
                }
                else
                {
                    $filtered[$prop] = null;
                }
            }
            return $filtered;
        }
    }

    /**
     * Parse key value string
     *
     * @param PicoAnnotationParser $reflexClass Reflection class
     * @param string $queryString Query string
     * @param string $parameter Parameter
     * @return array
     */
    private function parseKeyValue($reflexClass, $queryString, $parameter)
    {
        try
        {
            return $reflexClass->parseKeyValue($queryString);
        }
        catch(InvalidQueryInputException $e)
        {
            throw new InvalidAnnotationException("Invalid annotation @".$parameter);
        }
    }

    /**
     * Get object information
     *
     * @return stdClass
     */
    public function getObjectInfo() // NOSONAR
    {
        $reflexClass = new PicoAnnotationParser($this->className);
        $table = $reflexClass->getParameter(self::ANNOTATION_TABLE);
        $values = $this->parseKeyValue($reflexClass, $table, self::ANNOTATION_TABLE);
        $picoTableName = $values[self::KEY_NAME];
        $columns = array();
        $joinColumns = array();
        $primaryKeys = array();
        $autoIncrementKeys = array();
        $notNullColumns = array();
        $props = $reflexClass->getProperties();
        $defaultValue = array();
        $labels = array();

        // iterate each properties of the class
        foreach($props as $prop)
        {
            $reflexProp = new PicoAnnotationParser($this->className, $prop->name, PicoAnnotationParser::PROPERTY);
            $parameters = $reflexProp->getParameters();

            // get column name of each parameters
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_LABEL) == 0)
                {
                    $values = $this->parseKeyValue($reflexProp, $val, $param);
                    if(!empty($values))
                    {
                        foreach($values as $k1=>$v1)
                        {
                            if(!in_array($k1, $this->langs))
                            {
                                unset($values[$k1]);
                            }
                        }
                        $labels[$prop->name] = $values;
                    }
                }

                if(strcasecmp($param, self::ANNOTATION_COLUMN) == 0)
                {
                    $values = $this->parseKeyValue($reflexProp, $val, $param);
                    if(!empty($values))
                    {
                        $columns[$prop->name] = $values;
                    }
                }
            }

            // set column type
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($columns[$prop->name]))
                {
                    $type = explode(' ', trim($val, " \r\n\t "))[0];
                    $columns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
                }
            }

            // get join column name of each parameters
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_JOIN_COLUMN) == 0)
                {
                    $values = $this->parseKeyValue($reflexProp, $val, $param);
                    if(!empty($values))
                    {
                        $joinColumns[$prop->name] = $values;
                    }
                }
            }
            // set join column type
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($joinColumns[$prop->name]))
                {
                    $type = explode(' ', trim($val, " \r\n\t "))[0];
                    $joinColumns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
                    $joinColumns[$prop->name][self::KEY_ENTITY_OBJECT] = true;
                }
            }

            // list primary key
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_ID) == 0 && isset($columns[$prop->name]))
                {
                    $primaryKeys[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);
                }
            }

            // list autogenerated column
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_GENERATED_VALUE) == 0 && isset($columns[$prop->name]))
                {
                    $vals = $this->parseKeyValue($reflexProp, $val, $param);
                    $autoIncrementKeys[$prop->name] = array(
                        self::KEY_NAME=>isset($columns[$prop->name][self::KEY_NAME])?$columns[$prop->name][self::KEY_NAME]:null,
                        self::KEY_STRATEGY=>isset($vals[self::KEY_STRATEGY])?$vals[self::KEY_STRATEGY]:null,
                        self::KEY_GENERATOR=>isset($vals[self::KEY_GENERATOR])?$vals[self::KEY_GENERATOR]:null
                    );
                }
            }

            // define default column value
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_DEFAULT_COLUMN) == 0)
                {
                    $vals = $this->parseKeyValue($reflexProp, $val, $param);
                    if(isset($vals[self::KEY_VALUE]))
                    {
                        $defaultValue[$prop->name] = array(
                            self::KEY_NAME=>isset($columns[$prop->name][self::KEY_NAME])?$columns[$prop->name][self::KEY_NAME]:null,
                            self::KEY_VALUE=>$vals[self::KEY_VALUE],
                            self::KEY_PROPERTY_TYPE=>$columns[$prop->name][self::KEY_PROPERTY_TYPE]
                        );
                    }
                }
            }

            // list not null column
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_NOT_NULL) == 0 && isset($columns[$prop->name]))
                {
                    $notNullColumns[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);
                }
            }
        }
        // bring it together
        $info = new stdClass;
        $info->tableName = $picoTableName;
        $info->columns = $columns;
        $info->joinColumns = $joinColumns;
        $info->primaryKeys = $primaryKeys;
        $info->autoIncrementKeys = $autoIncrementKeys;
        $info->defaultValue = $defaultValue;
        $info->notNullColumns = $notNullColumns;
        $info->labels = $labels;
        return $info;
    }
}