<?php

namespace MagicObject\Database;

use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Exceptions\MandatoryTableNameException;
use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;

/**
 * Database stucture
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseStructure
{
    const ANNOTATION_TABLE = "Table";
    const ANNOTATION_COLUMN = "Column";
    const ANNOTATION_ID = "Id";
    const KEY_NAME = "name";
    const KEY_TYPE = "type";
    const KEY_NULL = "null";
    const KEY_NOT_NULL = "notnull";
    const KEY_NULLABLE = "nullable";
    const KEY_PRIMARY = "primary";
    const DATABASE_TYPE_MYSQL = "mysql";
    const DATABASE_TYPE_MARIADB = "mariadb";

    /**
     * Object
     *
     * @var MagicObject
     */
    private $object;

    /**
     * Class name
     *
     * @var string
     */
    private $className = "";

    /**
     * Constructor
     *
     * @param MagicObject $object
     */
    public function __construct($object)
    {
        $this->className = get_class($object);
        $this->object = $object;
    }


    /**
     * Show create table
     *
     * @param string $databaseType Database type. See PicoDatabaseType class
     * @param string $tableName Table name
     * @return string
     */
    public function showCreateTable($databaseType, $tableName = null)
    {
        $info = $this->getObjectInfo();
        if (!isset($tableName) || $info->getTableName() != null)
        {
            throw new MandatoryTableNameException("Table name is mandatory");
        }
        else
        {
            $tableName = $info->getTableName();
        }
        $createStrArr = array();
        $createStrArr[] = "CREATE TABLE IF NOT EXISTS $tableName(";
        $createStrArr[] = $this->showCreateTableByType($databaseType, $info);
        $createStrArr[] = ");";
        return implode("\r\n", $createStrArr);
    }

    /**
     * Show create table
     *
     * @param string $databaseType Database type. See PicoDatabaseType class
     * @param PicoTableInfo $info Table information
     * @return string
     */
    private function showCreateTableByType($databaseType, $info)
    {
        $createStrArr = array();
        $pk = array();
        if($databaseType == self::DATABASE_TYPE_MYSQL)
        {
            foreach($info->getColumns() as $column)
            {
                $createStrArr[] = $column[self::KEY_NAME]." ".$column[self::KEY_TYPE]." ".$this->nullable($column[self::KEY_NULLABLE]);
            }
            foreach($info->getColumns() as $column)
            {
                if(isset($column[self::KEY_PRIMARY]) && $column[self::KEY_PRIMARY] === true)
                {
                    $pk[] = $column[self::KEY_NAME];
                }
            }
            if(!empty($pk))
            {
                $createStrArr[] = "PRIMARY KEY (".implode(", ", $pk).")";
            }
        }

        return implode(",\r\n", $createStrArr);
    }

    /**
     * Create nullable
     *
     * @param mixed $nullable Nullable
     * @return string
     */
    private function nullable($nullable)
    {
        if($nullable === true || strtolower($nullable) == "true")
        {
            return "NULL";
        }
        else
        {
            return "NOT NULL";
        }
    }

    /**
     * Parse key value string
     *
     * @param PicoAnnotationParser $reflexClass Refection of class
     * @param string $queryString String to be parsed
     * @param string $parameter Parameter name
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
     * @return PicoTableInfo
     */
    public function getObjectInfo()
    {
        $reflexClass = new PicoAnnotationParser($this->className);
        $table = $reflexClass->getParameter(self::ANNOTATION_TABLE);
        $values = $this->parseKeyValue($reflexClass, $table, self::ANNOTATION_TABLE);

        $picoTableName = $values[self::KEY_NAME];
        $columns = array();
        $primaryKeys = array();
        $autoIncrementKeys = array();
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
                if(strcasecmp($param, self::ANNOTATION_COLUMN) == 0)
                {
                    $values = $this->parseKeyValue($reflexProp, $val, $param);
                    $columns[$prop->name] = $values;
                }
            }
            foreach($parameters as $param=>$val)
            {
                if(strcasecmp($param, self::ANNOTATION_ID) == 0 && isset($columns[$prop->name]))
                {
                    $columns[$prop->name][self::KEY_PRIMARY] = true;
                }
            }
        }
        return new PicoTableInfo($picoTableName, $columns, array(), $primaryKeys, $autoIncrementKeys, $defaultValue, $notNullColumns);
    }
}