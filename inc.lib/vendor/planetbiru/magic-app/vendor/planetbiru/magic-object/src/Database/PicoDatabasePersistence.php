<?php
namespace MagicObject\Database;

use DateTime;
use Exception;
use PDO;
use PDOException;
use PDOStatement;
use MagicObject\Exceptions\EmptyResultException;
use MagicObject\Exceptions\EntityException;
use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidFilterException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Exceptions\NoInsertableColumnException;
use MagicObject\Exceptions\NoColumnMatchException;
use MagicObject\Exceptions\NoDatabaseConnectionException;
use MagicObject\Exceptions\NoUpdatableColumnException;
use MagicObject\Exceptions\NoPrimaryKeyDefinedException;
use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\ExtendedReflectionClass;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoEmptyParameter;
use MagicObject\Util\Database\PicoDatabaseUtil;
use ReflectionProperty;

/**
 * Database persistence
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabasePersistence // NOSONAR
{
    const ANNOTATION_TABLE = "Table";
    const ANNOTATION_CACHE = "Cache";
    const ANNOTATION_COLUMN = "Column";
    const ANNOTATION_JOIN_COLUMN = "JoinColumn";
    const ANNOTATION_VAR = "var";
    const ANNOTATION_ID = "Id";
    const ANNOTATION_GENERATED_VALUE = "GeneratedValue";
    const ANNOTATION_NOT_NULL = "NotNull";
    const ANNOTATION_DEFAULT_COLUMN = "DefaultColumn";
    const ANNOTATION_JSON_FORMAT = "JsonFormat";
    const SQL_DATE_TIME_FORMAT = "SqlDateTimeFormat";
    
    const KEY_NAME = "name";
    const KEY_REFERENCE_COLUMN_NAME = "referenceColumnName";
    const KEY_NULL = "null";
    const KEY_NOT_NULL = "notnull";
    const KEY_NULLABLE = "nullable";
    const KEY_INSERTABLE = "insertable";
    const KEY_UPDATABLE = "updatable";
    const KEY_STRATEGY = "strategy";
    const KEY_GENERATOR = "generator";
    const KEY_PROPERTY_TYPE = "propertyType";
    const KEY_VALUE = "value";
    const KEY_ENABLE = "enable";
    const KEY_ENTITY_OBJECT = "entityObject";
    
    const VALUE_TRUE = "true";
    const VALUE_FALSE = "false";

    const ORDER_ASC = "asc";
    const ORDER_DESC = "desc";

    const MESSAGE_NO_PRIMARY_KEY_DEFINED = "No primaru key defined";
    const MESSAGE_NO_RECORD_FOUND = "No record found";
    const MESSAGE_INVALID_FILTER = "Invalid filter";
    const SQL_DATETIME_FORMAT = "Y-m-d H:i:s";
    const DATE_TIME_FORMAT = "datetimeformat";
    
    const NAMESPACE_SEPARATOR = "\\";
    const JOIN_TABLE_SUBFIX = "__jn__";
    const MAX_LINE_LENGTH = 80;

    const COMMA = ", ";
    const COMMA_RETURN = ", \r\n";
    
    /**
     * Database connection
     *
     * @var PicoDatabase
     */
    protected $database;

    /**
     * Object
     *
     * @var MagicObject
     */
    protected $object;

    /**
     * Class name
     * @var string
     */
    protected $className = "";

    /**
     * Skip null
     *
     * @var boolean
     */
    private $flagIncludeNull = false;

    /**
     * Imported class list
     * @var array
     */
    private $importedClassList = array();

    /**
     * Flag that class list has been processed or not
     * @var boolean
     */
    private $processClassList = false;

    /**
     * Get namespace of class
     * @var string
     */
    private $namespaceName = "";
    
    /**
     * Flag that generated value has been added
     *
     * @var boolean
     */
    private $generatedValue = false;

    /**
     * Flag that entity require database autoincrement
     *
     * @var boolean
     */
    private $requireDbAutoincrement = false;

    /**
     * Flag that database autoincrement has been completed
     *
     * @var boolean
     */
    private $dbAutoinrementCompleted = false;
    
    /**
     * Table Info
     *
     * @var PicoTableInfo
     */
    private $tableInfoProp = null;
    
    /**
     * Entity table cache
     *
     * @var array
     */
    private $entityTable = array();
    
    /**
     * Join map
     *
     * @var PicoJoinMap[]
     */
    protected $joinColumMaps = array();

    /**
     * Flag that WHERE is defined first
     *
     * @var boolean
     */
    protected $whereIsDefinedFirst = false;

    /**
     * WHERE saved on previous
     *
     * @var string
     */
    protected $whereStr = null;

    /**
     * Specification
     *
     * @var PicoSpecification
     */
    protected $specification;

    /**
     * Pageable
     *
     * @var PicoPageable
     */
    protected $pageable;

    /**
     * Sortable
     *
     * @var PicoSortable
     */
    protected $sortable;

    /**
     * Join cache
     *
     * @var array
     */
    private $joinCache = array();

    /**
     * Database connection
     *
     * @param PicoDatabase|null $database Database connection
     * @param MagicObject|mixed $object Entity object
     */
    public function __construct($database, $object)
    {
        $this->database = $database;
        $this->className = get_class($object);
        $this->object = $object;
    }
    
    /**
     * Check if string is null or empty
     *
     * @param string $string String to be checked
     * @return string
     */
    public static function nulOrEmpty($string)
    {
        return $string == null || empty($string);
    }
    
    /**
     * Check if string is not null and not empty
     *
     * @param string $string String to be checked
     * @return string
     */
    public static function notNullAndNotEmpty($string)
    {
        return $string != null && !empty($string);
    }
    
    /**
     * Apply subquery result
     *
     * @param array $data Master data
     * @param array $row Reference data
     * @param array $subqueryMap Subquery map
     * @return array
     */
    public static function applySubqueryResult($data, $row, $subqueryMap)
    {
        if(isset($subqueryMap) && is_array($subqueryMap))
        {      
            foreach($subqueryMap as $info)
            {
                $objectName = $info['objectName'];
                $objectNameSub = $info['objectName'];
                if(isset($row[$objectNameSub]))
                {
                    $data[$objectName] = (new MagicObject())
                        ->set($info['primaryKey'], $row[$info['columnName']])
                        ->set($info['propertyName'], $row[$objectNameSub])
                    ;
                }
                else
                {
                    $data[$objectName] = new MagicObject();
                }
            }
        }
        return $data;
    }
    
    /**
     * Set flag to skip null column
     *
     * @param bool $skip Skip null
     * @return self
     */
    public function includeNull($skip)
    {
        $this->flagIncludeNull = $skip;
        return $this;
    }

    /**
     * Parse key value string
     *
     * @param PicoAnnotationParser $reflexClass Class parser
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
     * Add column name
     *
     * @param array $columns Columns
     * @param PicoAnnotationParser $reflexProp Property parser
     * @param ReflectionProperty $prop Property reflection
     * @param array $parameters Parameters
     * @return array
     */
    private function addColumnName($columns, $reflexProp, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_COLUMN) == 0)
            {
                $values = $this->parseKeyValue($reflexProp, $val, $param);
                if(!empty($values))
                {
                    $columns[$prop->name] = $values;
                }
            }
        }
        return $columns;
    }
    
    /**
     * Add column type
     *
     * @param array $columns Columns
     * @param PicoAnnotationParser $reflexProp Property parser
     * @param ReflectionProperty $prop Property reflection
     * @param array $parameters Parameters
     * @return array
     */
    private function addColumnType($columns, $reflexProp, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($columns[$prop->name]))
            {
                $type = explode(' ', trim($val, " \r\n\t "))[0];
                $columns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
            }
            if(strcasecmp($param, self::SQL_DATE_TIME_FORMAT) == 0)
            {
                $values = $this->parseKeyValue($reflexProp, $val, $param);
                if(isset($values['pattern']))
                {
                    $columns[$prop->name][self::DATE_TIME_FORMAT] = $values['pattern'];
                }
            }
        }
        return $columns;
    }
    
    /**
     * Add column column name
     *
     * @param array $joinColumns Join columns
     * @param PicoAnnotationParser $reflexProp Property parser
     * @param ReflectionProperty $prop Property reflection
     * @param array $parameters Parameters
     * @return array
     */
    private function addJoinColumnName($joinColumns, $reflexProp, $prop, $parameters)
    {
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
        return $joinColumns;
    }
    
    /**
     * Add column column type
     *
     * @param array $joinColumns Join columns
     * @param ReflectionProperty $prop Property reflection
     * @param array $parameters Parameters
     * @return array
     */
    private function addJoinColumnType($joinColumns, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($joinColumns[$prop->name]))
            {
                $type = explode(' ', trim($val, " \r\n\t "))[0];
                $joinColumns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
                $joinColumns[$prop->name][self::KEY_ENTITY_OBJECT] = true;
            }
        }  
        return $joinColumns;
    }
    
    /**
     * Add primary key
     *
     * @param array $joinColumns Join columns
     * @param array $columns Columns
     * @param ReflectionProperty $prop Property reflection
     * @param array $parameters Parameters
     * @return array
     */
    private function addPrimaryKey($primaryKeys, $columns, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_ID) == 0 && isset($columns[$prop->name]))
            {
                $primaryKeys[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);
            }
        }
        return $primaryKeys;
    }
    
    /**
     * Add primary key
     *
     * @param array $autoIncrementKeys Autoincrement keys
     * @param array $columns Columns
     * @param PicoAnnotationParser $reflexClass Class parser
     * @param ReflectionProperty $prop Property reflection
     * @param array $parameters Parameters
     * @return array
     */
    private function addAutogenerated($autoIncrementKeys, $columns, $reflexClass, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_GENERATED_VALUE) == 0 && isset($columns[$prop->name]))
            {
                $vals = $this->parseKeyValue($reflexClass, $val, $param);
                $autoIncrementKeys[$prop->name] = array(
                    self::KEY_NAME=>isset($columns[$prop->name][self::KEY_NAME])?$columns[$prop->name][self::KEY_NAME]:null,
                    self::KEY_STRATEGY=>isset($vals[self::KEY_STRATEGY])?$vals[self::KEY_STRATEGY]:null,
                    self::KEY_GENERATOR=>isset($vals[self::KEY_GENERATOR])?$vals[self::KEY_GENERATOR]:null
                );
            }
        }
        return $autoIncrementKeys;
    }
    
    /**
     * Add default value
     *
     * @param array $defaultValue Default value
     * @param array $columns Columns
     * @param PicoAnnotationParser $reflexClass Class parser
     * @param ReflectionProperty $prop Property reflection
     * @param array $parameters Parameters
     * @return array
     */
    private function addDefaultValue($defaultValue, $columns, $reflexClass, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_DEFAULT_COLUMN) == 0)
            {
                $vals = $this->parseKeyValue($reflexClass, $val, $param);
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
        return $defaultValue;
    }
    
    /**
     * Add not null column
     *
     * @param array $notNullColumns Not null column
     * @param array $columns Columns
     * @param ReflectionProperty $prop Property reflection
     * @param array $parameters Parameters
     * @return array
     */
    private function addNotNull($notNullColumns, $columns, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_NOT_NULL) == 0 && isset($columns[$prop->name]))
            {
                $notNullColumns[$prop->name] = array(self::KEY_NAME=>$columns[$prop->name][self::KEY_NAME]);
            }
        }
        return $notNullColumns;
    }

    /**
     * Get table information by parsing class and property annotation
     *
     * @return PicoTableInfo
     * @throws EntityException
     */
    public function getTableInfo()
    {
        if(!isset($this->tableInfoProp))
        {
            $noCache = false;
            $reflexClass = new PicoAnnotationParser($this->className);
            $table = $reflexClass->getParameter(self::ANNOTATION_TABLE);
            $cache = $reflexClass->getParameter(self::ANNOTATION_CACHE);
            if(!isset($table))
            {
                throw new EntityException($this->className . " is not valid entity");
            }
            
            if(isset($cache))
            {
                $noCache = self::VALUE_FALSE == strtolower($cache[self::KEY_ENABLE]);
            }

            $values = $this->parseKeyValue($reflexClass, $table, self::ANNOTATION_TABLE);
    
            $picoTableName = isset($values[self::KEY_NAME]) ? $values[self::KEY_NAME] : "";
            $columns = array();
            $joinColumns = array();
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
                $columns = $this->addColumnName($columns, $reflexProp, $prop, $parameters);
                
                
                // set column type
                $columns = $this->addColumnType($columns, $reflexProp, $prop, $parameters);
                
                
                // get join column name of each parameters
                $joinColumns = $this->addJoinColumnName($joinColumns, $reflexProp, $prop, $parameters);
                
                
                // set join column type
                $joinColumns = $this->addJoinColumnType($joinColumns, $prop, $parameters);
                        

                // list primary key
                $primaryKeys = $this->addPrimaryKey($primaryKeys, $columns, $prop, $parameters);
                

                // list autogenerated column
                $autoIncrementKeys = $this->addAutogenerated($autoIncrementKeys, $columns, $reflexClass, $prop, $parameters);
                
                
                // define default column value
                $defaultValue = $this->addDefaultValue($defaultValue, $columns, $reflexClass, $prop, $parameters);
                

                // list not null column
                $notNullColumns = $this->addNotNull($notNullColumns, $columns, $prop, $parameters);
                
            }
            // bring it together
            $this->tableInfoProp = new PicoTableInfo($picoTableName, $columns, $joinColumns, $primaryKeys, $autoIncrementKeys, $defaultValue, $notNullColumns, $noCache);
        }
        return $this->tableInfoProp;
    }

    /**
     * Get match row
     *
     * @param PDOStatement $stmt PDO statement
     * @return bool
     */
    public function matchRow($stmt)
    {
        if($stmt == null)
        {
            return false;
        }
        $rowCount = $stmt->rowCount();
        return $rowCount != null && $rowCount > 0;
    }
    
    /**
     * Save data to database
     *
     * @param bool $includeNull Flag include NULL
     * @return PDOStatement|EntityException
     */
    public function save($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $stmt = null;
        try
        {
            $where = $this->getWhere($info, $queryBuilder);
            if(!$this->isValidFilter($where))
            {
                throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
            }
            $data2saved = clone $this->object->value();
            $data = $this->_select($info, $queryBuilder, $where);
            if($data != null)
            {
                // save current data
                foreach($data2saved as $prop=>$value)
                {
                    if($value != null)
                    {
                        $this->object->set($prop, $value);
                    }
                }
                $stmt = $this->_update($info, $queryBuilder, $where);
            }
            else
            {
                $stmt = $this->_insert($info, $queryBuilder);
            }
        }
        catch(Exception $e)
        {
            $stmt = $this->_insert($info, $queryBuilder);
        }     
        return $stmt;
    }

    /**
     * Query of save data
     *
     * @param bool $includeNull Flag include NULL
     * @return PicoDatabaseQueryBuilder
     * @throws EntityException
     */
    public function saveQuery($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $query = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        try
        {
            $where = $this->getWhere($info, $queryBuilder);
            if(!$this->isValidFilter($where))
            {
                throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
            }
            $data2saved = clone $this->object->value();
            $data = $this->_select($info, $queryBuilder, $where);
            if($data != null)
            {
                // save current data
                foreach($data2saved as $prop=>$value)
                {
                    if($value != null)
                    {
                        $this->object->set($prop, $value);
                    }
                }
                $query = $this->_updateQuery($info, $queryBuilder, $where);
            }
            else
            {
                $query = $this->_insertQuery($info, $queryBuilder);
            }
        }
        catch(Exception $e)
        {
            $query = $this->_insertQuery($info, $queryBuilder);
        }     
        return $query;
    }

    /**
     * Get object values
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @return array
     */
    private function getValues($info, $queryBuilder)
    {
        $values = array();
        foreach($info->getColumns() as $property=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            $value = $this->object->get($property);
            $value = $this->fixInput($value, $column);
            if($this->flagIncludeNull || $value !== null)
            {
                $value = $queryBuilder->escapeValue($value);
                $values[$columnName] = $value;
            }
        }
        return $values;
    }

    /**
     * Get null column set manualy
     * @param PicoTableInfo $info Table information
     * @return array
     */
    private function getNullCols($info)
    {
        $nullCols = array();
        $nullList = $this->object->nullPropertyList();
        if($this->isArray($nullList))
        {
            foreach($nullList as $key=>$val)
            {
                if($val === true && isset($info->getColumns()[$key]))
                {
                    $columnName = $info->getColumns()[$key][self::KEY_NAME];
                    $nullCols[] = $columnName;
                }
            }
        }
        return $nullCols;
    }
    
    /**
     * Get noninsertable column
     * @param PicoTableInfo $info Table information
     * @return array
     */
    private function getNonInsertableCols($info)
    {
        $nonInsertableCols = array();
        foreach($info->getColumns() as $params)
        {
            if(isset($params) 
                && isset($params[self::KEY_INSERTABLE])
                && strcasecmp($params[self::KEY_INSERTABLE], self::VALUE_FALSE) == 0              
                )
            {
                $columnName = $params[self::KEY_NAME];
                $nonInsertableCols[] = $columnName;
            }
        }
        return $nonInsertableCols;
    }
    
    /**
     * Get nonupdatable column
     * @param PicoTableInfo $info Table information
     * @return array
     */
    private function getNonUpdatableCols($info)
    {
        $nonUpdatableCols = array();
        foreach($info->getColumns() as $params)
        {
            if(isset($params) 
                && isset($params[self::KEY_UPDATABLE])
                && strcasecmp($params[self::KEY_UPDATABLE], self::VALUE_FALSE) == 0              
                )
            {
                $columnName = $params[self::KEY_NAME];
                $nonUpdatableCols[] = $columnName;
            }
        }
        return $nonUpdatableCols;
    }

    /**
     * Get SET statement
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @return string
     */
    private function getSet($info, $queryBuilder)
    {
        $sets = array();
        $primaryKeys = $this->getPrimaryKeys($info);
        $nullCols = $this->getNullCols($info);
        $nonUpdatableCols = $this->getNonUpdatableCols($info);
        foreach($info->getColumns() as $property=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            if(!$this->isPrimaryKeys($columnName, $primaryKeys))
            {
                $value = $this->object->get($property);
                $value = $this->fixInput($value, $column);
                if(($this->flagIncludeNull || $value !== null) 
                    && !in_array($columnName, $nullCols) 
                    && !in_array($columnName, $nonUpdatableCols)
                    )
                {
                    $value = $queryBuilder->escapeValue($value);
                    $sets[] = $columnName . " = " . $value;
                }
            }
        }
        foreach($nullCols as $columnName)
        {
            $sets[] = "$columnName = null";
        }
        if(empty($sets))
        {
            throw new NoUpdatableColumnException("No updatable column");
        }
        return $this->joinStringArray($sets, self::MAX_LINE_LENGTH, self::COMMA, self::COMMA_RETURN);
    }

    /**
     * Get WHERE statement
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @return string
     */
    private function getWhere($info, $queryBuilder)
    {
        if($this->whereIsDefinedFirst && !empty($this->whereStr))
        {
            return $this->whereStr;
        }
        $wheres = array();
        foreach($info->getPrimaryKeys() as $property=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            $value = $this->object->get($property);
            $value = $queryBuilder->escapeValue($value);
            if(strcasecmp($value, self::KEY_NULL) == 0)
            {
                $wheres[] = $columnName . " is null";
            }
            else
            {
                $wheres[] = $columnName . " = " . $value;
            }
        }
        if(empty($wheres))
        {
            throw new NoPrimaryKeyDefinedException("No primary key defined");
        }
        return implode(" and ", $wheres);
    }

    /**
     * Get primary keys
     *
     * @param PicoTableInfo $info Table information
     * @return array
     * @throws EntityException
     */
    public function getPrimaryKeys($info = null)
    {
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        $primaryKeys = array();
        foreach($info->getPrimaryKeys() as $column)
        {
            $primaryKeys[] = $column[self::KEY_NAME];
        }
        return $primaryKeys;
    }

    /**
     * Get columns
     *
     * @param PicoTableInfo $info Table information
     * @return array
     * @throws EntityException
     */
    public function getColumns($info = null)
    {
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        $columns = array();
        foreach($info->getColumns() as $column)
        {
            $columns[] = $column[self::KEY_NAME];
        }
        return $columns;
    }

    /**
     * Get join columns
     *
     * @param PicoTableInfo $info Table information
     * @return array
     * @throws EntityException
     */
    public function getJoinSources($info = null)
    {
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        $joinColumns = array();
        foreach($info->getJoinColumns() as $joinColumn)
        {
            $joinColumns[] = $joinColumn[self::KEY_NAME];
        }
        return $joinColumns;
    }

    /**
     * Check if column is primary key or not
     *
     * @param string $columnName Column name
     * @param array $primaryKeys Primary keys
     * @return bool
     */
    public function isPrimaryKeys($columnName, $primaryKeys)
    {
        return in_array($columnName, $primaryKeys);
    }

    /**
     * Get primary key with autoincrement value
     *
     * @param PicoTableInfo $info Table information
     * @return array
     */
    public function getPrimaryKeyAutoIncrement($info)
    {
        $aiKeys = array();
        if($info->getAutoIncrementKeys() != null)
        {
            $primaryKeys = array_keys($info->getPrimaryKeys());
            foreach($info->getAutoIncrementKeys() as $name=>$value)
            {
                if(in_array($name, $primaryKeys))
                {
                    $aiKeys[$name] = $value;
                }
            }
        }
        return $aiKeys;
    }
    
    /**
     * Add generated value
     *
     * @param PicoTableInfo $info Table information
     * @param bool $firstCall First call
     * @return void
     */
    private function addGeneratedValue($info, $firstCall)
    {
        if(!$this->generatedValue)
        {
            $keys = $info->getAutoIncrementKeys();
            if($this->isArray($keys))
            {
                foreach($keys as $prop=>$col)
                {
                    $autoVal = $this->object->get($prop);
                    if(self::nulOrEmpty($autoVal) && isset($col[self::KEY_STRATEGY]))
                    {
                        $this->setGeneratedValue($prop, $col[self::KEY_STRATEGY], $firstCall);
                    }
                }
            }
        }
    }
    
    /**
     * Set generated value
     *
     * @param string $prop Property name
     * @param string $strategy Generation strategy
     * @return void
     */
    private function setGeneratedValue($prop, $strategy, $firstCall)
    {
        if(strcasecmp($strategy, "GenerationType.UUID") == 0)
        {
            if($firstCall && ($this->object->get($prop) == null || $this->object->get($prop) == "") && !$this->generatedValue)
            {
                $generatedValue = $this->database->generateNewId();
                $this->object->set($prop, $generatedValue);
                $this->generatedValue = true;
            }
        }
        else if(strcasecmp($strategy, "GenerationType.IDENTITY") == 0)
        {
            if($firstCall)
            {
                $this->requireDbAutoincrement = true;
            }
            else if($this->requireDbAutoincrement && !$this->dbAutoinrementCompleted)
            {
                $generatedValue = $this->database->getDatabaseConnection()->lastInsertId();
                $this->object->set($prop, $generatedValue);
                $this->dbAutoinrementCompleted = true;
            }         
        }
    }

    /**
     * Insert data
     *
     * @param bool $includeNull Flag include NULL
     * @return PDOStatement|EntityException
     */
    public function insert($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $info = $this->getTableInfo();
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        return $this->_insert($info, $queryBuilder);
    }

    /**
     * Query of insert data
     *
     * @param bool $includeNull Flag include NULL
     * @return PicoDatabaseQueryBuilder|EntityException
     */
    public function insertQuery($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $info = $this->getTableInfo();
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        return $this->_insertQuery($info, $queryBuilder);
    }

    /**
     * Insert data
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @return PDOStatement
     */
    private function _insert($info = null, $queryBuilder = null)
    {
        $sqlQuery = $this->_insertQuery($info, $queryBuilder);
        $stmt = $this->database->executeInsert($sqlQuery);
        if(!$this->generatedValue)
        {
            $this->addGeneratedValue($info, false);
        }
        return $stmt;
    }

    /**
     * Insert data
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @return PicoDatabaseQueryBuilder
     * @throws EntityException
     */
    private function _insertQuery($info = null, $queryBuilder = null)
    {
        $this->generatedValue = false;
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        $this->addGeneratedValue($info, true);
        $values = $this->getValues($info, $queryBuilder);
        $fixValues = $this->fixInsertableValues($values, $info);       
        
        return $queryBuilder
            ->newQuery()
            ->insert()
            ->into($info->getTableName())
            ->fields($this->createStatementFields($fixValues))
            ->values($this->createStatementValues($fixValues));
    }
    
    /**
     * Fix insertable values
     *
     * @param array $values Values
     * @param PicoTableInfo $info Table information
     * @return array
     */
    private function fixInsertableValues($values, $info = null)
    {
        $fixedValues = array();
        if($info != null)
        {
            $insertableCols = array();
            $nonInsertableCols = $this->getNonInsertableCols($info);
            foreach($values as $key=>$value)
            {
                if(!in_array($key, $nonInsertableCols))
                {
                    $insertableCols[$key] = $value;
                }
            }
            $fixedValues = $insertableCols;        
        }
        else
        {
            $fixedValues = $values;
        }       
        
        /**
         * 1. TABLE - Indicates that the persistence provider must assign primary keys for the entity using an underlying database table to ensure uniqueness.
         * 2. SEQUENCE - Indicates that the persistence provider must assign primary keys for the entity using a database sequence.
         * 3. IDENTITY - Indicates that the persistence provider must assign primary keys for the entity using a database identity column.
         * 4. AUTO - Indicates that the persistence provider should pick an appropriate strategy for the particular database. The AUTO generation strategy may expect a database resource to exist, or it may attempt to create one. A vendor may provide documentation on how to create such resources in the event that it does not support schema generation or cannot create the schema resource at runtime.
         * 5. UUID - Indicates that the persistence provider must assign primary keys for the entity with a UUID value.
         */ 
        
        if($info->getAutoIncrementKeys() != null)
        {
            foreach($info->getAutoIncrementKeys() as $propertyName=>$col)
            {
                if($this->isRequireGenerateValue($col[self::KEY_STRATEGY], $propertyName))
                {
                    $value = $this->database->generateNewId();
                    $values[$col[self::KEY_NAME]] = $value;
                    $this->object->set($propertyName, $value);
                    $this->generatedValue = true;
                }
            }
        }        
        if(empty($fixedValues))
        {
            throw new NoInsertableColumnException("No insertable column");
        }
        return $fixedValues;
    }
    
    /**
     * Check if data require a generated value
     *
     * @param string $strategy
     * @param string $propertyName
     * @return bool
     */
    private function isRequireGenerateValue($strategy, $propertyName)
    {
        return strcasecmp($strategy, "GenerationType.UUID") == 0 
                && ($this->object->get($propertyName) == null || $this->object->get($propertyName) == "") 
                && !$this->generatedValue;
    }

    /**
     * Implode array keys to field list
     *
     * @param array $values Values
     * @param PicoTableInfo $info Table information
     * @return string
     */
    public function createStatementFields($values)
    {
        return "(".implode(self::COMMA, array_keys($values)).")";
    }

    /**
     * Implode array values to value list
     *
     * @param array $values Values
     * @return string
     */
    public function createStatementValues($values)
    {      
        return "(".implode(self::COMMA, array_values($values)).")";
    }

    /**
     * Get table column name from an object property
     *
     * @param string $propertyName Property names
     * @param array $columns Columns
     * @return string
     */
    private function getColumnNames($propertyNames, $columns)
    {
        $source = str_replace("And", "#And#", $propertyNames."#");
        $source = str_replace("Or", "#Or#", $source);

        $source = str_replace("#Or#Or", "Or#Or", $source);
        $source = str_replace("#And#And", "And#And", $source);

        $sourcex = str_replace("#And#", "@", $source);
        $sourcex = str_replace("#Or#", "@", $sourcex);
        
        $sourcex = trim($sourcex, "#");
        
        $arrSource = explode("@", $sourcex);
        foreach($arrSource as $idx=>$val)
        {
            $arrSource[$idx] = lcfirst($val);
        }

        $result = $propertyNames;

        foreach($arrSource as $pro)
        {
            if (isset($columns[$pro]))
            {
                $col = $columns[$pro];
                $columnName = $col[self::KEY_NAME];
                $result = str_ireplace($pro, " ".$columnName." ? ", $result);
            }
        }
        if($result != $propertyNames)
        {
            return $result;
        }
        throw new NoColumnMatchException("No column match");
    }

    /**
     * Ger column map
     *
     * @param PicoTableInfo $info Table information
     * @return array
     */
    private function getColumnMap($info)
    {
        $maps = array();
        if($info->getJoinColumns() != null)
        {
            foreach($info->getJoinColumns() as $key=>$value)
            {
                $maps[$key] = $value[self::KEY_NAME];
            }
        }
        if($info->getColumns() != null)
        {
            foreach($info->getColumns() as $key=>$value)
            {
                $maps[$key] = $value[self::KEY_NAME];
            }
        }
        return $maps;
    }

    /**
     * Fix comparison
     *
     * @param string $column Column
     * @return string
     */
    private function fixComparison($column)
    {
        if(stripos($column, ' ') !== false)
        {
            $arr = explode(' ', $column);
            foreach($arr as $idx=>$val)
            {
                if($val == 'And')
                {
                    $arr[$idx] = 'and';
                }
                if($val == 'Or')
                {
                    $arr[$idx] = 'or';
                }
            }
            $column = implode(' ', $arr);
        }
        return $column;
    }

    /**
     * Create WHERE by argument given
     *
     * @param PicoTableInfo $info Table information
     * @param string $propertyName Property name
     * @param array $propertyValues Property values
     * @return string
     */
    private function createWhereFromArgs($info, $propertyName, $propertyValues)
    {
        $columnNames = $this->getColumnNames($propertyName, $info->getColumns());
        $arr = explode("?", $columnNames);
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $wheres = array();
        for($i = 0; $i < count($arr) - 1 && $i < count($propertyValues); $i++)
        {
            $column = ltrim($this->fixComparison($arr[$i]), ' ');
            if($propertyValues[$i] instanceof PicoDataComparation)
            {
                $wheres[] = $column . $propertyValues[$i]->getComparison() . " ". $queryBuilder->escapeValue($propertyValues[$i]->getValue());
            }
            else
            {
                $value = $queryBuilder->escapeValue($propertyValues[$i]);
                if(strcasecmp($value, 'null') == 0)
                {
                    $wheres[] = $column . "is " . $value;
                }
                else if(is_array($propertyValues[$i]))
                {
                    $wheres[] = $column . "in (" . $value.")";
                }
                else
                {
                    $wheres[] = $column . "= " . $value;
                }
            }
        }
        return $this->joinStringArray($wheres, self::MAX_LINE_LENGTH);
    }
    
    /**
     * Get table name
     *
     * @param string|null $entityName Entity name
     * @return string|null
     */
    private function getTableOf($entityName)
    {
        if($entityName == null || empty($entityName))
        {
            return null;
        }
        if(isset($this->entityTable[$entityName]))
        {
            return $this->entityTable[$entityName];
        }
        $tableName = $entityName;
        try
        {
            $className = $this->getRealClassName($entityName);          
            $annotationParser = new PicoAnnotationParser($className);
            $parameters = $annotationParser->getParametersAsObject();
            if($parameters->getTable() != null)
            {
                $attribute = $annotationParser->parseKeyValueAsObject($parameters->getTable());
                if($attribute->getName() != null)
                {
                    $tableName = $attribute->getName();
                    $this->entityTable[$entityName] = $tableName;
                }
            }
        }
        catch(Exception $e)
        {
            // do nothing
            $tableName = null;
        }
        return $tableName;
    }
    
    /**
     * Get entity primary key
     *
     * @param string $entityName Entity name
     * @return string[]
     */
    private function getPrimaryKeyOf($entityName)
    {
        $columns = array();
        try
        {
            $className = $this->getRealClassName($entityName);
            $annotationParser = new PicoAnnotationParser($className);
            $props = $annotationParser->getProperties();
            foreach($props as $prop)
            {
                $reflexProp = new PicoAnnotationParser($className, $prop->name, PicoAnnotationParser::PROPERTY);
                $parameters = $reflexProp->getParametersAsObject();
                if($parameters->getId() != null && $parameters->getId() instanceof PicoEmptyParameter)
                {
                    $properties = $reflexProp->parseKeyValueAsObject($parameters->getColumn());
                    $columns[$prop->name] = $properties->getName();
                }
            }
        }
        catch(Exception $e)
        {
            // do nothing
        }
        return $columns;
    }
    
    /**
     * Get column maps of the entity
     *
     * @param string $entityName Entity name
     * @return array
     */
    private function getColumnMapOf($entityName)
    {
        $columns = array();
        try
        {
            $className = $this->getRealClassName($entityName);
            $annotationParser = new PicoAnnotationParser($className);
            $props = $annotationParser->getProperties();
            foreach($props as $prop)
            {
                $reflexProp = new PicoAnnotationParser($className, $prop->name, PicoAnnotationParser::PROPERTY);
                $parameters = $reflexProp->getParametersAsObject();
                $properties = $reflexProp->parseKeyValueAsObject($parameters->getColumn());
                $columns[$prop->name] = $properties->getName();
            }
        }
        catch(Exception $e)
        {
            // do nothing
        }
        return $columns;
    }
    
    /**
     * Get join source
     *
     * @param string|null $parentName Parent name
     * @param string $masterTable Master table
     * @param string|null $entityTable Entity table
     * @param string $field Field
     * @return string
     */
    private function getJoinSource($parentName, $masterTable, $entityTable, $field, $master = false)
    {
        $result = $masterTable.".".$field;
        if($entityTable != null && $parentName != null)
        {
            if($master)
            {
                $result = $entityTable.".".$field;
            }
            else if(isset($this->joinColumMaps[$parentName]))
            {
                $joinMap = $this->joinColumMaps[$parentName];
                $aliasTable = $joinMap->getJoinTableAlias();
                $result = $aliasTable.".".$field;
            }
        }
        return $result;
    } 

    /**
     * Create WHERE from specification
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder
     * @param PicoSpecification $specification Specification
     * @param PicoTableInfo $info Table information
     * @return string
     */
    protected function createWhereFromSpecification($sqlQuery, $specification, $info)
    {
        $masterColumnMaps = $this->getColumnMap($info);   
        $arr = array();
        $arr[] = "(1=1)";
        if($specification != null && !$specification->isEmpty())
        {
            $specifications = $specification->getSpecifications();
            foreach($specifications as $spec)
            {           
                $arr = $this->addWhere($arr, $masterColumnMaps, $sqlQuery, $spec, $info);
            }
        }
        $ret = $this->joinStringArray($arr, self::MAX_LINE_LENGTH);
        return $this->trimWhere($ret);
    }

    /**
     * Join array string with maximum length. If max is reached, it will create new line
     *
     * @param string[] $arr Array string to be joined
     * @param int $max Threshold to split line
     * @param string $normalSplit Normal splitter
     * @param string $maxSplit Overflow splitter
     * @return string
     */
    private function joinStringArray($arr, $max = 0, $normalSplit = " ", $maxSplit = " \r\n")
    {
        if($arr == null)
        {
            return "";
        }
        if($max == 0)
        {
            return implode($normalSplit, $arr);
        }
        $arr2 = $this->splitChunk($arr, $max, $normalSplit);
        $arr3 = array();
        foreach($arr2 as $value)
        {
            $value2 = implode($normalSplit, $value);
            if(!empty(trim($value2)))
            {
                $arr3[] = $value2;
            }
        }
        return implode($maxSplit, $arr3);
    }
    
    /**
     * Split chunk query
     *
     * @param string[] $arr Array string to be joined
     * @param int $max Threshold to split line
     * @param string $normalSplit Normal splitter
     * @return array
     */
    private function splitChunk($arr, $max, $normalSplit)
    {
        $arr2 = array();
        $idx = 0;
        foreach($arr as $value)
        {
            if(!isset($arr2[$idx]))
            {
                $arr2[$idx] = array();
            }
            if(strlen(implode($normalSplit, $arr2[$idx])) + strlen($value) < $max)
            {
                if(!empty(trim($value)))
                {
                    $arr2[$idx][] = $value;
                }
            }
            else
            {
                $idx++;
                if(!empty(trim($value)))
                {
                    $arr2[$idx][] = $value;
                }
            }         
        }
        return $arr2;
    }
    
    /**
     * Add where statemenet
     *
     * @param array $arr Array values
     * @param array $masterColumnMaps Master column  map
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder
     * @param PicoSpecification $spec Specification
     * @param PicoTableInfo $info Table information
     * @return array
     */
    private function addWhere($arr, $masterColumnMaps, $sqlQuery, $spec, $info)
    {
        if($spec instanceof PicoPredicate)
        {
            $masterTable = $info->getTableName();
            $entityField = new PicoEntityField($spec->getField(), $info);
            $field = $entityField->getField();
            $entityName = $entityField->getEntity();
            $parentName = $entityField->getParentField();
            $functionFormat = $entityField->getFunctionFormat();
            
            if($entityName != null)
            {
                $entityTable = $this->getTableOf($entityName);
                
                if($entityTable != null)
                {
                    $joinColumnmaps = $this->getColumnMapOf($entityName);                           
                    $maps = $joinColumnmaps;
                }
                else
                {
                    $maps = $masterColumnMaps;
                }
            }
            else
            {
                $entityTable = null;
                $maps = $masterColumnMaps;
            }
            
            $columnNames = array_values($maps);          
            // flat
            if(isset($maps[$field]))
            {
                // get from map
                $column = $this->getJoinSource($parentName, $masterTable, $entityTable, $maps[$field], $entityTable == $masterTable);
                $columnFinal = $this->formatColumn($column, $functionFormat);
                $arr[] = $spec->getFilterLogic() . " " . $columnFinal . " " . $spec->getComparation()->getComparison() . " " . $this->contructComparisonValue($spec, $sqlQuery);
            }
            else if(in_array($field, $columnNames))
            {
                // get colum name
                $column = $this->getJoinSource($parentName, $masterTable, $entityTable, $field, $entityTable == $masterTable);
                $columnFinal = $this->formatColumn($column, $functionFormat);
                $arr[] = $spec->getFilterLogic() . " " . $columnFinal . " " . $spec->getComparation()->getComparison() . " " . $this->contructComparisonValue($spec, $sqlQuery);
            }
        }
        else if($spec instanceof PicoSpecification)
        {
            // nested
            $arr[] = $spec->getParentFilterLogic() . " (" . $this->createWhereFromSpecification($sqlQuery, $spec, $info) . ")";
        }
        return $arr;
    }
    
    /**
     * Construct comarison value
     *
     * @param PicoPredicate $predicate Predicate
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder
     * @return string
     */
    private function contructComparisonValue($predicate, $sqlQuery)
    {
        if(is_array($predicate->getValue()))
        {
            $list = array();
            foreach($predicate->getValue() as $value)
            {
                $list[] = $sqlQuery->escapeValue($value);
            }
            return "(".implode(", ", $list).")";
        }
        else
        {
            return $sqlQuery->escapeValue($predicate->getValue());
        } 
    }

    /**
     * Format column
     *
     * @param string $column Column name
     * @param string $format Format
     * @return string
     */
    private function formatColumn($column, $format)
    {
        if($format == null || strpos($format, "%s") === false)
        {
            return $column;
        }
        return sprintf($format, $column);
    }
    
    /**
     * Trim WHERE
     *
     * @param string $where Where statement
     * @return string
     */
    private function trimWhere($where)
    {
        return PicoDatabaseUtil::trimWhere($where);
    }

    /**
     * Create ORDER BY
     *
     * @param PicoTableInfo $info Table information
     * @param PicoSortable|string $order Sortable
     * @return string|null
     */
    private function createOrderBy($info, $order)
    {
        if($order instanceof PicoSortable)
        {
            return $this->createOrderByQuery($order, $info);
        }
        else if(is_string($order))
        {
            $orderBys = array();
            $pKeys = array_values($info->getPrimaryKeys());
            if(!empty($pKeys))
            {
                foreach($pKeys as $pKey)
                {
                    $pKeyCol = $pKey[self::KEY_NAME];
                    $orderBys[] = $pKeyCol." ".strtolower($order);
                }
            }
            return $this->joinStringArray($orderBys, self::MAX_LINE_LENGTH, self::COMMA, self::COMMA_RETURN);
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Create sort by
     *
     * @param PicoSortable $order Sortable
     * @param PicoTableInfo $info Table information
     * @return string
     */
    public function createOrderByQuery($order, $info = null)
    {
        if($order->getSortable() == null || !is_array($order->getSortable()) || empty($order->getSortable()))
        {
            return null;
        }
        $ret = null;
        if($info == null)
        {
            $ret = $this->createWithoutMapping($order, $info);
        }
        else
        {
            $ret = $this->createWithMapping($order, $info);
        }
        return $ret;
    }
    
    /**
     * Create sort without mapping
     *
     * @param PicoSortable $order Sortable
     * @param PicoTableInfo|null $info
     * @return string
     */
    private function createWithoutMapping($order, $info)
    {
        $ret = null;
        $sorts = array();
        foreach($order->getSortable() as $sortable)
        {
            $columnName = $sortable->getSortBy();
            $sortType = $sortable->getSortType();             
            $sortBy = $columnName;
            $entityField = new PicoEntityField($sortBy, $info);
            if($entityField->getEntity() != null)
            {
                $tableName = $this->getTableOf($entityField->getEntity());
                $sortBy = $tableName.".".$sortBy;
            }
            $sorts[] = $sortBy . " " . $sortType;           
        }
        if(!empty($sorts))
        {
            $ret = $this->joinStringArray($sorts, self::MAX_LINE_LENGTH, self::COMMA, self::COMMA_RETURN);
        }
        return $ret;
    }
    
    /**
     * Create sort with mapping
     *
     * @param PicoSortable $order Sortable
     * @param PicoTableInfo $info Table information
     * @return string
     */
    private function createWithMapping($order, $info)
    {
        $masterColumnMaps = $this->getColumnMap($info);
        $masterTable = $info->getTableName();
        
        $arr = array();
    
        foreach($order->getSortable() as $sortOrder)
        {           
            $entityField = new PicoEntityField($sortOrder->getSortBy(), $info);
            $field = $entityField->getField();
            $entityName = $entityField->getEntity();
            $parentName = $entityField->getParentField();
            
            if($entityName != null)
            {
                $entityTable = $this->getTableOf($entityName);
                if($entityTable != null)
                {
                    $joinColumnmaps = $this->getColumnMapOf($entityName);                           
                    $maps = $joinColumnmaps;
                }
                else
                {
                    $maps = $masterColumnMaps;
                }
                $columnNames = array_values($maps);
            }
            else
            {
                $entityTable = null;
                $maps = $masterColumnMaps;
                $columnNames = array_values($maps);
            }
            
            if(isset($maps[$field]))
            {
                // get from map
                $column = $this->getJoinSource($parentName, $masterTable, $entityTable, $maps[$field], $masterTable == $entityTable);
                
                $arr[] = $column . " " . $sortOrder->getSortType();
            }
            else if(in_array($field, $columnNames))
            {
                // get colum name
                $column = $this->getJoinSource($parentName, $masterTable, $entityTable, $field, $masterTable == $entityTable);
                $arr[] = $column . " " . $sortOrder->getSortType();
            }
        }
        return $this->joinStringArray($arr, self::MAX_LINE_LENGTH, self::COMMA, self::COMMA_RETURN);
    }
    
    /**
     * Check if primary key has valid value or not
     *
     * @param string[] $primaryKeys Primary keys
     * @param array $propertyValues Property values
     * @return bool
     */
    private function isValidPrimaryKeyValues($primaryKeys, $propertyValues)
    {
        return isset($primaryKeys) && !empty($primaryKeys) && count($primaryKeys) <= count($propertyValues);
    }

    /**
     * Convert scalar to array
     *
     * @param mixed $propertyValue Property values
     * @return array
     */
    private function toArray($propertyValues)
    {
        if(!is_array($propertyValues))
        {
            return array($propertyValues);
        }
        return $propertyValues;
    }
    
    /**
     * Get all table columns on entity 
     *
     * @param PicoTableInfo $info Table information
     * @return string
     */
    private function getAllColumns($info)
    {
        $columns = $info->getColumns();
        $result = array();
        foreach($columns as $column)
        {
            $result[] = $info->getTableName().".".$column[self::KEY_NAME];
        }
        return $this->joinStringArray($result, self::MAX_LINE_LENGTH, self::COMMA, self::COMMA_RETURN);
    }
    
    /**
     * Find one record by primary key value
     *
     * @param mixed $propertyValue Property values
     * @return object
     * @throws EntityException|InvalidFilterException|EmptyResultException
     */
    public function find($propertyValues)
    {
        $propertyValues = $this->toArray($propertyValues);
        $data = null;
        $info = $this->getTableInfo();
        
        $primaryKeys = $info->getPrimaryKeys();
        
        if($this->isValidPrimaryKeyValues($primaryKeys, $propertyValues))
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
            $wheres = array();
            $index = 0;
            foreach($primaryKeys as $primatyKey)
            {
                $columnName = $primatyKey[self::KEY_NAME];
                $columnValue = $propertyValues[$index];
                if($columnValue === null)
                {
                    $wheres[] = $columnName . " is null";
                }
                else
                {
                    $wheres[] = $columnName . " = " . $queryBuilder->escapeValue($propertyValues[$index]);
                }
            }
            $where = implode(" and ", $wheres);
            if(!$this->isValidFilter($where))
            {
                throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
            }
            $sqlQuery = $queryBuilder
                ->newQuery()
                ->select($this->getAllColumns($info))
                ->from($info->getTableName())
                ->where($where)
                ->limit(1)
                ->offset(0);
            try
            {
                $stmt = $this->database->executeQuery($sqlQuery);
                if($this->matchRow($stmt))
                {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $data = $this->fixDataType($row, $info); 
                    $data = $this->join($data, $row, $info);
                    return $data;
                }
                else
                {
                    throw new EmptyResultException(self::MESSAGE_NO_RECORD_FOUND);
                }
            }
            catch(Exception $e)
            {
                throw new EmptyResultException($e->getMessage());
            }
        }
        else
        {
            throw new EmptyResultException("No primary key set");
        }
    }

    /**
     * Add specification to query builder
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder
     * @param PicoSpecification|array $specification Specification
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder
     */
    private function setSpecification($sqlQuery, $specification, $info)
    {
        if($specification != null && $specification instanceof PicoSpecification && !$specification->isEmpty())
        {
            $where = $this->createWhereFromSpecification($sqlQuery, $specification, $info);
            if(self::notNullAndNotEmpty($where))
            {
                $sqlQuery->where($where);
            }
        }
        return $sqlQuery;
    }

    /**
     * Add pageable to query builder
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder
     * @param PicoPageable $pageable Pageable
     * @return PicoDatabaseQueryBuilder
     */
    private function setPageable($sqlQuery, $pageable)
    {
        if($pageable instanceof PicoPageable)
        {
            $offsetLimit = $pageable->getOffsetLimit();
            if($offsetLimit != null)
            {
                $limit = $offsetLimit->getLimit();
                $offset = $offsetLimit->getOffset();
                $sqlQuery->limit($limit);
                $sqlQuery->offset($offset);
            }
        }
        return $sqlQuery;
    }
        
    /**
     * Add sortable to query builder
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder
     * @param PicoPageable|null $pageable Pageable
     * @param PicoSortable|string|null $sortable Sortable
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder
     */
    private function setSortable($sqlQuery, $pageable, $sortable, $info)
    {
        if($sortable != null)
        {
            if($sortable instanceof PicoSortable)
            {
                $sortOrder = $this->createOrderByQuery($sortable, $info);
                $sqlQuery = $this->setOrdeBy($sqlQuery, $sortOrder);
            }
            else if(is_string($sortable))
            {
                $sortOrder = $this->createOrderBy($info, $sortable);
                $sqlQuery = $this->setOrdeBy($sqlQuery, $sortOrder);
            }
        } 
        else if($pageable != null && $pageable instanceof PicoPageable)
        {
            $sortOrder = $pageable->createOrderBy($info);
            if($this->notNullAndNotEmpty($sortOrder))
            {
                $sqlQuery->orderBy($sortOrder);
            }
            else if(is_string($sortable))
            {
                $sortOrder = $this->createOrderBy($info, $sortable);
                $sqlQuery = $this->setOrdeBy($sqlQuery, $sortOrder);
            }
            $offsetLimit = $pageable->getOffsetLimit();
            if($offsetLimit != null)
            {
                $limit = $offsetLimit->getLimit();
                $offset = $offsetLimit->getOffset();
                $sqlQuery->limit($limit);
                $sqlQuery->offset($offset);
            }
        }
        else if(is_string($pageable))
        {
            $sortOrder = $this->createOrderBy($info, $pageable);
            $sqlQuery = $this->setOrdeBy($sqlQuery, $sortOrder);
        }
        return $sqlQuery;
    }

    /**
     * Set ORDER BY
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder
     * @param string $sortOrder Sort order
     * @return PicoDatabaseQueryBuilder
     */
    private function setOrdeBy($sqlQuery, $sortOrder)
    {
        if($this->notNullAndNotEmpty($sortOrder))
        {
            $sqlQuery->orderBy($sortOrder);
        }
        return $sqlQuery;
    }
    
    /**
     * Add JOIN query
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder
     */
    protected function addJoinQuery($sqlQuery, $info)
    {
        $joinColumns = $info->getJoinColumns();
        
        $masterTable = $info->getTableName();
        $tableAlias = array();
        
        foreach($joinColumns as $propertyName=>$joinColumn)
        {
            $entity = $joinColumn[self::KEY_PROPERTY_TYPE];
            $columnName = $joinColumn[self::KEY_NAME];
            $joinTable = $this->getTableOf($entity);
            if(!isset($tableAlias[$joinTable]))
            {
                $tableAlias[$joinTable] = 0;
            }
            $tableAlias[$joinTable]++;
            
            $joinTableAlias = $joinTable.self::JOIN_TABLE_SUBFIX.$tableAlias[$joinTable];
            
            $this->joinColumMaps[$propertyName] = new PicoJoinMap($propertyName, $columnName, $entity, $joinTable, $joinTableAlias);
            
            $joinColumn = $this->getPrimaryKeyOf($entity);

            $joinPrimaryKeys = array_values($this->getPrimaryKeyOf($entity));

            if(isset($joinColumn[self::KEY_REFERENCE_COLUMN_NAME]))
            {
                $referenceColumName = $joinColumn[self::KEY_REFERENCE_COLUMN_NAME];
            }
            else if(!empty($joinPrimaryKeys))
            {
                $referenceColumName = $joinPrimaryKeys[0];
            }
            else
            {
                $referenceColumName = $joinColumn[self::KEY_NAME];
            }
            
            $sqlQuery->leftJoin($joinTable." ".$joinTableAlias)->on($joinTableAlias.".".$referenceColumName." = ".$masterTable.".".$columnName);
        }
        return $sqlQuery;
    }
    
    /**
     * Check if need join query
     *
     * @param PicoSpecification $specification Specification
     * @param PicoPageable|null $pageable Pageable
     * @param PicoSortable|string|null $sortable Sortable
     * @param PicoTableInfo $info Table information
     * @return bool
     */
    protected function isRequireJoin($specification, $pageable, $sortable, $info)
    {
        if($this->isRequireJoinFromSpecification($specification))
        {
            return true;
        }
        if($this->isRequireJoinFromPageableAndSortable($pageable, $sortable, $info))
        {
            return true;
        }
        return false;
    }

    /**
     * Require join from sortable
     *
     * @param PicoPageable|null $pageable Pageable
     * @param PicoSortable|string|null $sortable Sortable
     * @param PicoTableInfo $info Table information
     * @return bool
     */
    private function isRequireJoinFromPageableAndSortable($pageable, $sortable, $info)
    {
        $result = false;
        if($sortable != null)
        {
            if($sortable instanceof PicoSortable)
            {
                $result = strpos($sortable->__toString(), ".") !== false;
            }
            else if(is_string($sortable))
            {
                $result = strpos($sortable, ".") !== false;
            }
        } 
        else if($pageable != null && $pageable instanceof PicoPageable)
        {
            $result = strpos($pageable->createOrderBy($info), ".") !== false;
        }
        else if(is_string($pageable))
        {
            $result = strpos($this->createOrderBy($info, $pageable), ".") !== false;
        }
        return $result;
    }

    /**
     * Require join from specification
     *
     * @param PicoSpecification $specification Specification
     * @return bool
     */
    private function isRequireJoinFromSpecification($specification)
    {
        return isset($specification) && $specification instanceof PicoSpecification && $specification->isRequireJoin();
    }

    /**
     * Create PDO statement
     *
     * @param PicoSpecification $specification Specification
     * @param PicoPageable $pageable Pagable
     * @param PicoSortable $sortable Sortable
     * @param array $subqueryMap Subquery map
     * @param string $selected Selected
     * @return PDOStatement
     * @throws PDOException
     */
    public function createPDOStatement($specification, $pageable, $sortable, $subqueryMap = null, $selected = null)
    {
        $info = $this->getTableInfo();
        if($selected == null || empty($selected))
        {
            $selected = $this->getAllColumns($info);
        }
        if($subqueryMap != null)
        {
            $selected = $this->joinString($selected, $this->subquery($info, $subqueryMap), self::COMMA_RETURN);
        }
        $sql = $this->findSpecificQuery($selected, $specification, $pageable, $sortable, $info);
        return $this->database->query($sql);
    }

    /**
     * Get findAll query
     *
     * @param PicoSpecification|null $specification Specification
     * @param PicoPageable|null $pageable Pageable
     * @param PicoSortable|string|null $sortable Sortable
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder
     */
    public function findAllQuery($specification, $pageable = null, $sortable = null, $info = null)
    {
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        return $this->findSpecificQuery($this->getAllColumns($info), $specification, $pageable, $sortable, $info);
    }
    
    /**
     * Get findSpecific query
     *
     * @param string $selected
     * @param PicoSpecification|null $specification Specification
     * @param PicoPageable|null $pageable Pageable
     * @param PicoSortable|string|null $sortable Sortable
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder
     */
    public function findSpecificQuery($selected, $specification, $pageable = null, $sortable = null, $info = null)
    {
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        $sqlQuery = new PicoDatabaseQueryBuilder($this->database);
        
        $sqlQuery
            ->newQuery()
            ->select($selected)
            ->from($info->getTableName());
        
        if($this->isRequireJoin($specification, $pageable, $sortable, $info))
        {
            $sqlQuery = $this->addJoinQuery($sqlQuery, $info);
        }
            
        if($specification != null)
        {
            $sqlQuery = $this->setSpecification($sqlQuery, $specification, $info);
        }
        
        if($pageable != null)
        {
            $sqlQuery = $this->setPageable($sqlQuery, $pageable);      
        }
        
        if($pageable != null || $sortable != null)
        {
            $sqlQuery = $this->setSortable($sqlQuery, $pageable, $sortable, $info);        
        }
        return $sqlQuery;
    }

    /**
     * Get all record from database wihout filter
     *
     * @param PicoSpecification|null $specification Specification
     * @param PicoSortable|string|null $sortable Sortable
     * @param array|null $subqueryMap Subquery map
     * @throws EntityException|EmptyResultException
     */
    public function findOne($specification, $sortable = null, $subqueryMap = null)
    {
        $info = $this->getTableInfo(); 
        $pageable = new PicoPageable(array(1, 1));
        if($subqueryMap == null)    
        {
            return $this->findSpecific($this->getAllColumns($info), $specification, $pageable, $sortable);
        }
        else
        {
            return $this->findSpecificWithSubquery($this->getAllColumns($info), $specification, $pageable, $sortable, $subqueryMap);
        }
    }

    /**
     * Get all record from database wihout filter
     *
     * @param PicoSpecification|null $specification Specification
     * @param PicoPageable|null $pageable Pageable
     * @param PicoSortable|string|null $sortable Sortable
     * @param array|null $subqueryMap Subquery map
     * @throws EntityException|EmptyResultException
     */
    public function findAll($specification, $pageable = null, $sortable = null, $subqueryMap = null)
    {
        $info = $this->getTableInfo(); 
        if($subqueryMap == null)    
        {
            return $this->findSpecific($this->getAllColumns($info), $specification, $pageable, $sortable);
        }
        else
        {
            return $this->findSpecificWithSubquery($this->getAllColumns($info), $specification, $pageable, $sortable, $subqueryMap);
        }
    }
    
    /**
     * Find one with primary key value
     *
     * @param mixed $primaryKeyVal Primary key value
     * @param array $subqueryMap Subquery map
     * @return array
     */
    public function findOneWithPrimaryKeyValue($primaryKeyVal, $subqueryMap)
    {
        $info = $this->getTableInfo();
        $tableName = $info->getTableName();
        $selected = $this->getAllColumns($info);
        $data = null;
        $info = $this->getTableInfo();
        $selected = $this->joinString($selected, $this->subquery($info, $subqueryMap), self::COMMA_RETURN);
        $primaryKey = null;
        try
        {
            $primaryKeys = array_values($info->getPrimaryKeys());
            if(is_array($primaryKeys) && isset($primaryKeys[0][self::KEY_NAME]))
            {
                // it will be faster than asterisk
                $primaryKey = $primaryKeys[0][self::KEY_NAME];
            }
            if($primaryKey == null)
            {
                throw new NoPrimaryKeyDefinedException(self::MESSAGE_NO_PRIMARY_KEY_DEFINED);
            }
            
            $sqlQuery = new PicoDatabaseQueryBuilder($this->database);
            $sqlQuery
                ->select($selected)
                ->from($tableName)
                ->where("$primaryKey = ? ", $primaryKeyVal)
                ->limit(1)
                ->offset(0);
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $data = $this->fixDataType($row, $info); 
                $data = self::applySubqueryResult($data, $row, $subqueryMap);
            }
            else
            {
                throw new EmptyResultException(self::MESSAGE_NO_RECORD_FOUND);
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;
    }
    
    /**
     * Get all record from database wihout filter with subquery
     *
     * @param string $selected Selected
     * @param PicoSpecification $specification Specification
     * @param PicoPageable|null $pageable Pageable
     * @param PicoSortable|string|null $sortable Sortable
     * @param array $subqueryMap Subquery map
     * @throws EntityException|EmptyResultException
     */
    public function findSpecificWithSubquery($selected, $specification, $pageable = null, $sortable = null, $subqueryMap = null)
    {
        $data = null;
        $result = array();
        $info = $this->getTableInfo();
        if($subqueryMap != null)
        {
            $selected = $this->joinString($selected, $this->subquery($info, $subqueryMap), self::COMMA_RETURN);
        }
        $sqlQuery = $this->findSpecificQuery($selected, $specification, $pageable, $sortable, $info);
    
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))                
                {
                    $data = $this->fixDataType($row, $info); 
                    if($subqueryMap == null)
                    {
                        $data = $this->join($data, $row, $info);
                    }
                    else
                    {
                        $data = self::applySubqueryResult($data, $row, $subqueryMap);
                    }
                    $result[] = $data;
                }
            }
            else
            {
                throw new EmptyResultException(self::MESSAGE_NO_RECORD_FOUND);
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $result;
    }
    
    /**
     * Create subquery
     *
     * @param PicoTableInfo $info Table information
     * @param array $subqueryMap Subquery map
     * @return string
     */
    public function subquery($info, $subqueryMap)
    {
        $subquery = array();
        $tableName = $info->getTableName();
        if(isset($subqueryMap) && is_array($subqueryMap))
        {
            $idx = 1;
            foreach($subqueryMap as $info)
            {
                $joinTableName = $info['tableName'];
                $columnName = $info['columnName'];                
                $primaryKey = $info['primaryKey'];
                $objectNameSub = $info['objectName'];
                $propertyName = $info['propertyName'];
                $joinName = $info['tableName']."_".$idx;
                $selection = $info['tableName']."_".$idx.".".$propertyName; 
                $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
                $queryBuilder
                    ->select($selection)
                    ->from("$joinTableName $joinName")
                    ->where("$joinName.$primaryKey = $tableName.$columnName")
                    ->limit(1)
                    ->offset(0);
                $subquery[] = "(".$queryBuilder.") as $objectNameSub";
                $idx++;
            }
        }
        return implode(self::COMMA_RETURN, $subquery);
    }
    
    /**
     * Join string with separator
     *
     * @param string $string1 First string
     * @param string $string2 Second string
     * @param string $separator Separator
     * @return string
     */
    public function joinString($string1, $string2, $separator)
    {
        if(!empty($string1) && !empty($string2))
        {
            return $string1.$separator.$string2;
        }
        else
        {
            return $string1;
        }
    }
    
    

    /**
     * Get all record from database wihout filter
     *
     * @param string $selected Selected
     * @param PicoSpecification $specification Specification
     * @param PicoPageable|null $pageable Pageable
     * @param PicoSortable|string|null $sortable Sortable
     * @return array|null
     * @throws EntityException|EmptyResultException
     */
    public function findSpecific($selected, $specification, $pageable = null, $sortable = null)
    {
        $data = null;
        $result = array();
        $info = $this->getTableInfo();
        $sqlQuery = $this->findSpecificQuery($selected, $specification, $pageable, $sortable, $info);
    
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))                
                {
                    $data = $this->fixDataType($row, $info); 
                    $data = $this->join($data, $row, $info);
                    $result[] = $data;
                }
            }
            else
            {
                throw new EmptyResultException(self::MESSAGE_NO_RECORD_FOUND);
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $result;
    }

    /**
     * Get query for all mathced record from database
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @param PicoPageable $pageable Pageable
     * @param PicoSortable|string $sortable Sortable
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder
     * @throws PDOException|NoDatabaseConnectionException|EntityException
     */
    public function findByQuery($propertyName, $propertyValue, $pageable = null, $sortable = null, $info = null, $subqueryMap = null)
    {
        $selected = $this->getAllColumns($info);
        if($subqueryMap != null)
        {
            $selected = $this->joinString($selected, $this->subquery($info, $subqueryMap), self::COMMA_RETURN);
        }
        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($selected)
            ->from($info->getTableName())
            ->where($where);
        if($pageable != null)
        {
            $sqlQuery = $this->setPageable($sqlQuery, $pageable);        
        }
        if($pageable != null || $sortable != null)
        {
            $sqlQuery = $this->setSortable($sqlQuery, $pageable, $sortable, $info);        
        }

        return $sqlQuery;
    }

    /**
     * Get all mathced record from database
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @param PicoPageable $pageable Pageable
     * @param PicoSortable|string $sortable Sortable
     * @param array $subqueryMap Subquery map
     * @return array|null
     * @throws PDOException|NoDatabaseConnectionException|EntityException
     */
    public function findBy($propertyName, $propertyValue, $pageable = null, $sortable = null, $subqueryMap = null)
    {
        $info = $this->getTableInfo();
        $data = null;
        $result = array();
        $sqlQuery = $this->findByQuery($propertyName, $propertyValue, $pageable, $sortable, $info, $subqueryMap);
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))               
                {
                    $data = $this->fixDataType($row, $info); 
                    $data = $this->join($data, $row, $info);
                    $result[] = $data;
                }
            }
        }
        catch(PDOException $e)
        {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
        catch(NoDatabaseConnectionException $e)
        {
            throw new NoDatabaseConnectionException($e->getMessage());
        }
        catch(Exception $e)
        {
            // do nothing
        }
        return $result;
    }
    
    /**
     * Check if record is exists or not
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return bool
     */
    public function existsBy($propertyName, $propertyValue)
    {
        return $this->countBy($propertyName, $propertyValue) > 0;
    }

    /**
     * Get all record from database wihout filter
     *
     * @param PicoSpecification|null $specification Specification
     * @param PicoPageable $pageable Pagable
     * @param PicoSortable $sortable Sortable
     * @return int
     * @throws EntityException|EmptyResultException
     */
    public function countAll($specification = null, $pageable = null, $sortable = null)
    {
        $info = $this->getTableInfo();
        $primaryKeys = array_values($info->getPrimaryKeys());
        $agg = "*";
        if(is_array($primaryKeys) && isset($primaryKeys[0][self::KEY_NAME]))
        {
            // it will be faster than asterisk
            $agg = $primaryKeys[0][self::KEY_NAME];
        }
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($this->getAllColumns($info))
            ->from($info->getTableName())
        ;
        if($specification != null && $specification instanceof PicoSpecification)
        {
            if($this->isRequireJoin($specification, $pageable, $sortable, $info))
            {
                $sqlQuery = $this->addJoinQuery($sqlQuery, $info);
            }
            $sqlQuery = $this->setSpecification($sqlQuery, $specification, $info);
        }
        else
        {
            $sqlQuery = $queryBuilder
                ->newQuery()
                ->select($agg)
                ->from($info->getTableName())
            ;
        }
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            return $stmt->rowCount();
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
    }
    
    /**
     * Count data
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return int
     * @throws EntityException|InvalidFilterException|PDOException|EmptyResultException
     */
    public function countBy($propertyName, $propertyValue)
    {
        $info = $this->getTableInfo();
        $primaryKeys = array_values($info->getPrimaryKeys());
        $agg = "*";
        if(is_array($primaryKeys) && isset($primaryKeys[0][self::KEY_NAME]))
        {
            // it will be faster than asterisk
            $agg = $primaryKeys[0][self::KEY_NAME];
        }
        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($agg)
            ->from($info->getTableName())
            ->where($where)
        ;
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($stmt != null)
            {
                return $stmt->rowCount();
            }
            else
            {
                throw new PDOException("Unknown error");
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
    }
    
    /**
     * Delete record from database without read it first
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return int
     * @throws EntityException|InvalidFilterException|PDOException|EmptyResultException
     */
    public function deleteBy($propertyName, $propertyValue)
    {
        $info = $this->getTableInfo();
        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->delete()
            ->from($info->getTableName())
            ->where($where)
        ;
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($stmt != null)
            {
                return $stmt->rowCount();
            }
            else
            {
                throw new PDOException("Unknown error");
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
    }
    
    /**
     * Get one mathced record from database
     *
     * @param string $propertyName Property name
     * @param array $propertyValues Property values
     * @param PicoSortable|string|null $sortable Sortable
     * @return array|null
     * @throws EntityException|InvalidFilterException|EmptyResultException
     */
    public function findOneBy($propertyName, $propertyValue, $sortable = null)
    {
        $info = $this->getTableInfo();
        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($this->getAllColumns($info))
            ->from($info->getTableName())
            ->where($where);
        $sqlQuery = $this->setSortable($sqlQuery, null, $sortable, $info);  
        $sqlQuery->limit(1)->offset(0);
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $data = $this->fixDataType($row, $info);               
                $data = $this->join($data, $row, $info);
            }
            else
            {
                $data = null;
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;   
    }

    /**
     * Get real class name
     *
     * @param string $classNameJoin Join class name
     * @return string
     */
    private function getRealClassName($classNameJoin)
    {
        $result = $classNameJoin;
        if(stripos($classNameJoin, self::NAMESPACE_SEPARATOR) === false)
        {
            if(!$this->processClassList)
            {
                // processed once
                $reflect = new ExtendedReflectionClass($this->className);
                $useStatements = $reflect->getUseStatements(); 
                $this->namespaceName = $reflect->getNamespaceName();
                if($this->isArray($useStatements))
                {
                    foreach($useStatements as $val)
                    {
                        $as = $val['as'];
                        $cls = $val['class'];
                        $this->importedClassList[$as] = $cls;
                    }
                }
            }
            if(isset($this->importedClassList[$classNameJoin]))
            {
                // get from map
                $result = $this->importedClassList[$classNameJoin];
            }
            else if(stripos($classNameJoin, self::NAMESPACE_SEPARATOR) === false)
            {
                // assumpt has same namespace
                $result = rtrim($this->namespaceName, self::NAMESPACE_SEPARATOR).self::NAMESPACE_SEPARATOR. $classNameJoin;
            }
        }
        return $result;
    }
    
    /**
     * Get reference column name
     *
     * @param array $join Join columns
     * @return string
     */
    private function getReferenceColumnName($join)
    {
        if(isset($join[self::KEY_REFERENCE_COLUMN_NAME]))
        {
            return $join[self::KEY_REFERENCE_COLUMN_NAME];
        }
        else
        {
            return $join[self::KEY_NAME];
        }
    }

    /**
     * Get property name
     * @param string $classNameJoin Class name join
     * @param string $referenceColumName Reference column name
     * @return string|null
     */
    private function getJoinKeyName($classNameJoin, $referenceColumName)
    {
        $className = $this->getRealClassName($classNameJoin);
        $persist = new self(null, new $className());
        $info = $persist->getTableInfo();
        foreach($info->getColumns() as $prop => $col)
        {
            if($col[self::KEY_NAME] == $referenceColumName)
            {
                return $prop;
            }
        }
        return $referenceColumName;
    }

    /**
     * Prepare join cache
     *
     * @param string $classNameJoin Join class name
     * @return void
     */
    private function prepareJoinCache($classNameJoin)
    {
        if(!isset($this->joinCache[$classNameJoin]))
        {
            $this->joinCache[$classNameJoin] = array();
        }
    }

    /**
     * Retrieves joined data based on the specified class name and key value.
     *
     * This method checks the join cache for previously retrieved data. If the data is not found in the cache,
     * it creates a new instance of the specified class, sets the appropriate database connection,
     * and retrieves the data using the specified join key.
     *
     * @param string $classNameJoin The name of the class to join with.
     * @param string $referenceColumnName The name of the column used as the join key.
     * @param mixed $joinKeyValue The value of the join key to search for.
     * @return MagicObject|null Returns the retrieved MagicObject if found, or null if not found.
     */
    private function getJoinData($classNameJoin, $referenceColumName, $joinKeyValue)
    {
        $persist = new self(null, new $classNameJoin());
        $info = $persist->getTableInfo();
        $noCache = isset($info) ? $info->getNoCache() : false;
        
        // Check if caching is disabled or if the data is not already cached
        if($noCache || !isset($this->joinCache[$classNameJoin]) || !isset($this->joinCache[$classNameJoin][$joinKeyValue]))
        {      
            $className = $this->getRealClassName($classNameJoin);
            $obj = new $className(null);      
            
            $dbEnt = $this->object->databaseEntity();
            // Determine the database connection to use
            if($dbEnt != null)
            {
                // Using multiple database connection
                $obj->databaseEntity($dbEnt);
                $obj->currentDatabase($dbEnt->getDatabase($obj));
            }
            else
            {
                // Using master database connection
                $obj->currentDatabase($this->object->currentDatabase());
            }
            
            // Dynamically call the method to find the object by the join key
            $method = 'findOneBy' . ucfirst($referenceColumName);
            $obj->{$method}($joinKeyValue);   
            
            // Cache the result for future retrievals if caching is enabled
            if(!$noCache)        
            {
                $this->joinCache[$classNameJoin][$joinKeyValue] = $obj;
            }
            return $obj;
        }
        else if(isset($this->joinCache[$classNameJoin]) && isset($this->joinCache[$classNameJoin][$joinKeyValue]))
        {
            return $this->joinCache[$classNameJoin][$joinKeyValue];
        }
        else
        {
            return null; // Return null if no data is found
        }
    }
    
    /**
     * Join data by annotation @JoinColumn
     * 
     * @param mixed $data Object
     * @param array $row Row
     * @param PicoTableInfo $info Table information
     * @return object
     */
    public function join($data, $row, $info)
    {
        if(!empty($info->getJoinColumns()))
        {
            foreach($info->getJoinColumns() as $propName=>$join)
            {
                $referenceColumName = $this->getReferenceColumnName($join);
                $classNameJoin = $join[self::KEY_PROPERTY_TYPE];
                $columnName = $join[self::KEY_NAME];
                $joinKeyName = $this->getJoinKeyName($classNameJoin, $referenceColumName);
                try
                {
                    if(isset($row[$columnName]))
                    {
                        $this->prepareJoinCache($classNameJoin);
                        $obj = $this->getJoinData($classNameJoin, $joinKeyName, $row[$columnName]);
                        if($obj != null)
                        {
                            $data = $this->addProperty($data, $propName, $obj);
                        }
                    }
                }
                catch(Exception $e)
                {
                    // set null
                    $data = $this->addProperty($data, $propName, null);
                }
            }
        }
        return $data;
    }

    /**
     * Add property
     *
     * @param array|object $data Original data
     * @param string $propName Property name
     * @param mixed $value Property value
     * @return array|object
     */
    private function addProperty($data, $propName, $value)
    {
        if(is_array($data))
        {                       
            $data[$propName] = $value;
        }
        else
        {
            $data->{$propName} = $value;
        }
        return $data;
    }
    
    /**
     * Check if filter is valid or not
     *
     * @param string $filter Filter
     * @return bool
     */
    private function isValidFilter($filter)
    {
        return $this->notNullAndNotEmptyAndNotSpace($filter);
    }

    /**
     * Check if data is not null and not empty and not a space
     *
     * @param string $value Value to be checked
     * @return bool
     */
    private function notNullAndNotEmptyAndNotSpace($value)
    {
        return $value != null && !empty(trim($value));
    }

    /**
     * Fix data type
     *
     * @param array $data Input data
     * @param PicoTableInfo $info Table information
     * @return array
     */
    public function fixDataType($data, $info)
    {
        $result = array();
        $typeMap = $this->createTypeMap($info);
        foreach($info->getColumns() as $prop=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            $value = $data[$columnName];
            if(isset($typeMap[$columnName]))
            {
                $result[$prop] = $this->fixData($value, $typeMap[$columnName]);
            }
        }
        return $result;
    }

    /**
     * Fix value
     *
     * @param mixed $value Input value
     * @param string $type Data type
     * @return mixed
     */
    public function fixData($value, $type)
    {
        $typeLower = strtolower($type);
        /*
        Map of data type (MySQL)
        "double"=>"double",
        "float"=>"double",
        "bigint"=>"integer",
        "smallint"=>"integer",
        "tinyint(1)"=>"boolean",
        "tinyint"=>"integer",
        "int"=>"integer",
        "varchar"=>"string",
        "char"=>"string",
        "tinytext"=>"string",
        "mediumtext"=>"string",
        "longtext"=>"string",
        "text"=>"string",   
        "enum"=>"string",   
        "bool"=>"boolean",
        "boolean"=>"boolean",
        "timestamp"=>"string",
        "datetime"=>"string",
        "date"=>"string",
        "time"=>"string"
        */     
        
        if($type == 'DateTime')
        {
            $v = strlen($value) > 19 ? substr($value, 0, 19) : $value;
            $ret = DateTime::createFromFormat(self::SQL_DATETIME_FORMAT, $v);
        }
        else if($typeLower == 'bool' || $typeLower == 'boolean')
        {
            $ret = $this->boolval($value);
        }
        else if($typeLower == 'integer')
        {
            $ret = $this->intval($value);
        }
        else if($typeLower == 'double')
        {
            $ret = $this->doubleval($value);
        }
        else if($this->isDateTimeNull($value))
        {
            $ret = null;
        }
        else
        {
            $ret = $value;
        }
        return $ret;
    }
    
    /**
     * Boolean value
     *
     * @param mixed $value Input value
     * @return bool
     */
    private function boolval($value)
    {
        return $value == 1 || $value == '1';
    }
    
    /**
     * Integer value
     *
     * @param mixed $value Input value
     * @return mixed
     */
    private function intval($value)
    {
        if($value === null)
        {
            $ret = null;
        }
        else
        {
            $ret = intval($value);
        }
        return $ret;
    }
    
    /**
     * Double value
     *
     * @param mixed $value Input value
     * @return mixed
     */
    private function doubleval($value)
    {
        if($value === null)
        {
            $ret = null;
        }
        else
        {
            $ret = doubleval($value);
        }
        return $ret;
    }

    /**
     * Fixing input
     *
     * @param mixed $value Input value
     * @param array $column Column
     * @return mixed
     */
    private function fixInput($value, $column)
    {
        if($value instanceof DateTime)
        {
            if(isset($column[self::DATE_TIME_FORMAT]))
            {
                return (string) $value->format($column[self::DATE_TIME_FORMAT]);
            }
            else
            {
                return (string) $value->format(self::SQL_DATETIME_FORMAT);
            }
        }
        return $value;
    }
    
    /**
     * Check if date time is NULL
     * @param string $value Value to be checked
     * @return bool
     */
    private function isDateTimeNull($value)
    {
        if(!isset($value) || !is_string($value))
        {
            return false;
        }
        $value = str_replace("T", " ", $value);
        if(strlen($value) > 26)
        {
            $value = substr($value, 0, 26);
        }
        return $value == '0000-00-00 00:00:00.000000' 
            || $value == '0000-00-00 00:00:00.000' 
            || $value == '0000-00-00 00:00:00'
            || $value == '0000-00-00'
            ;
    }

    /**
     * Create type map
     *
     * @param PicoTableInfo $info Table information
     * @return array
     */
    private function createTypeMap($info)
    {
        $map = array();
        if(isset($info) && $info->getColumns() != null)
        {
            foreach($info->getColumns() as $cols)
            {
                $map[$cols[self::KEY_NAME]] = $cols[self::KEY_PROPERTY_TYPE];
            }
        }
        return $map;
    }

    /**
     * Select record from database
     *
     * @return mixed
     * @throws EntityException
     */
    public function select()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_select($info, $queryBuilder, $where, $this->specification, $this->pageable, $this->sortable);
    }

    /**
     * Select all records from database
     *
     * @return mixed
     * @throws EntityException
     */
    public function selectAll()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_selectAll($info, $queryBuilder, $where, $this->specification, $this->pageable, $this->sortable);
    }

    /**
     * Query of select data
     *
     * @return PicoDatabaseQueryBuilder
     * @throws EntityException
     */
    public function selectQuery()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_selectQuery($info, $queryBuilder, $where);
    }

    /**
     * Select record from database with primary keys given
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @param string $where Where statement
     * @return mixed
     * @throws EntityException|InvalidFilterException|EmptyResultException
     */
    private function _select($info = null, $queryBuilder = null, $where = null, $specification = null, $pageable = null, $sortable = null)
    {
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        if($where == null)
        {
            $where = $this->getWhere($info, $queryBuilder);
        }
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($this->getAllColumns($info))
            ->from($info->getTableName());

        if($this->isRequireJoin($specification, $pageable, $sortable, $info))
        {
            $sqlQuery = $this->addJoinQuery($sqlQuery, $info);
        }
        $sqlQuery->where($where);
        $sqlQuery->limit(1)->offset(0);
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $data = $this->fixDataType($row, $info);
                $data = $this->join($data, $row, $info);
            }
            else
            {
                $data = null;
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $data;
    }

    /**
     * Select record from database with primary keys given
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @param string $where Where statement
     * @return mixed
     * @throws EntityException|InvalidFilterException|EmptyResultException
     */
    private function _selectAll($info = null, $queryBuilder = null, $where = null, $specification = null, $pageable = null, $sortable = null)
    {
        $result = array();
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        if($where == null)
        {
            $where = $this->getWhere($info, $queryBuilder);
        }
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }
        $data = null;
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($this->getAllColumns($info))
            ->from($info->getTableName());

        if($this->isRequireJoin($specification, $pageable, $sortable, $info))
        {
            $sqlQuery = $this->addJoinQuery($sqlQuery, $info);
        }
        $sqlQuery->where($where);
        try
        {
            $stmt = $this->database->executeQuery($sqlQuery);
            if($this->matchRow($stmt))
            {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT))
                {
                    $data = $this->fixDataType($row, $info);
                    $data = $this->join($data, $row, $info);
                    $result[] = $data;
                }
            }
            else
            {
                $result = array();
            }
        }
        catch(Exception $e)
        {
            throw new EmptyResultException($e->getMessage());
        }
        return $result;
    }

    /**
     * Select record from database with primary keys given
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @param string $where Where statement
     * @return PicoDatabaseQueryBuilder
     * @throws EntityException|InvalidFilterException
     */
    private function _selectQuery($info = null, $queryBuilder = null, $where = null)
    {
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        if($where == null)
        {
            $where = $this->getWhere($info, $queryBuilder);
        }
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }
        return $queryBuilder
            ->newQuery()
            ->select($this->getAllColumns($info))
            ->from($info->getTableName())
            ->where($where)
        ;
    }

    /**
     * Update data
     *
     * @param bool $includeNull Flag include NULL
     * @return PDOStatement
     * @throws EntityException
     */
    public function update($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_update($info, $queryBuilder, $where);
    }

    /**
     * Query of update data
     *
     * @param bool $includeNull Flag include NULL
     * @return PicoDatabaseQueryBuilder
     * @throws EntityException
     */
    public function updateQuery($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_updateQuery($info, $queryBuilder, $where);
    }

    /**
     * Update record on database with primary keys given
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @param string $where Where statement
     * @return PDOStatement
     * @throws InvalidFilterException
     */
    private function _update($info = null, $queryBuilder = null, $where = null)
    {
        $sqlQuery = $this->_updateQuery($info, $queryBuilder, $where);
        return $this->database->executeUpdate($sqlQuery);
    }

    /**
     * Update record on database with primary keys given
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @param string $where Where statement
     * @return PicoDatabaseQueryBuilder
     * @throws InvalidFilterException|EntityException
     */
    private function _updateQuery($info = null, $queryBuilder = null, $where = null)
    {
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        if($where == null)
        {
            $where = $this->getWhere($info, $queryBuilder);
        }
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }
        $set = $this->getSet($info, $queryBuilder);
        return $queryBuilder
            ->newQuery()
            ->update($info->getTableName())
            ->set($set)
            ->where($where)
        ;
    }

    /**
     * Delete record from database
     *
     * @return PDOStatement
     * @throws EntityException
     */
    public function delete()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_delete($info, $queryBuilder, $where);
    }

    /**
     * Query of delete record
     *
     * @return PicoDatabaseQueryBuilder
     * @throws EntityException
     */
    public function deleteQuery()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_deleteQuery($info, $queryBuilder, $where);
    }

    /**
     * Delete record from database with primary keys given
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @param string $where Where statement
     * @return PDOStatement
     */
    private function _delete($info = null, $queryBuilder = null, $where = null)
    {
        $sqlQuery = $this->_deleteQuery($info, $queryBuilder, $where);
        return $this->database->executeDelete($sqlQuery);
    }

    /**
     * Delete record from database with primary keys given
     *
     * @param PicoTableInfo $info Table information
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder
     * @param string $where Where statement
     * @return PicoDatabaseQueryBuilder
     * @throws InvalidFilterException|EntityException
     */
    private function _deleteQuery($info = null, $queryBuilder = null, $where = null)
    {
        if($queryBuilder == null)
        {
            $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        }
        if($info == null)
        {
            $info = $this->getTableInfo();
        }
        if($where == null)
        {
            $where = $this->getWhere($info, $queryBuilder);
        }
        if(!$this->isValidFilter($where))
        {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }              
        return $queryBuilder
            ->newQuery()
            ->delete()
            ->from($info->getTableName())
            ->where($where)
        ;
    }

    /**
     * Get MagicObject with WHERE specification
     *
     * @param PicoSpecification $specification Specification
     * @return PicoDatabasePersistenceExtended
     */
    public function whereWithSpecification($specification)
    {
        $persist = new PicoDatabasePersistenceExtended($this->database, $this->object);
        $persist->specification = $specification;

        $sqlQuery = new PicoDatabaseQueryBuilder($this->database);
        $info = $persist->getTableInfo();  
        try
        {
            if($persist->isRequireJoin($specification, null, null, $info))
            {
                $persist->addJoinQuery($sqlQuery, $info);
            }
            $persist->whereStr = $persist->createWhereFromSpecification($sqlQuery, $specification, $info);
            $persist->whereIsDefinedFirst = true;
        }
        catch(Exception $e)
        {
            // Do nothing
        }
        return $persist;
    }

    /**
     * Check if parameter is array
     *
     * @param mixed $value Value to be checked
     * @return bool
     */
    public function isArray($value)
    {
        return isset($value) && is_array($value);
    }
}
