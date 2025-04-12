<?php
namespace MagicObject\Database;

use DateTime;
use Exception;
use MagicObject\Exceptions\ClassNotFoundException;
use MagicObject\Exceptions\EmptyResultException;
use MagicObject\Exceptions\EntityException;
use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidFilterException;
use MagicObject\Exceptions\NoInsertableColumnException;
use MagicObject\Exceptions\NoColumnMatchException;
use MagicObject\Exceptions\NoDatabaseConnectionException;
use MagicObject\Exceptions\NoUpdatableColumnException;
use MagicObject\Exceptions\NoPrimaryKeyDefinedException;
use MagicObject\Exceptions\UnknownErrorException;
use MagicObject\MagicObject;
use MagicObject\Util\ClassUtil\ExtendedReflectionClass;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoEmptyParameter;
use MagicObject\Util\Database\PicoDatabaseUtil;
use MagicObject\Util\Database\PicoTimeZoneChanger;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionProperty;

/**
 * Database persistence
 * 
 * @author Kamshory
 * @package MagicObject\Database
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
    const ANNOTATION_PACKAGE = "package";
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
    const KEY_TYPE = "type";
    const KEY_ENABLE = "enable";
    const KEY_ENTITY_OBJECT = "entityObject";
    
    const VALUE_TRUE = "true";
    const VALUE_FALSE = "false";

    const ORDER_ASC = "asc";
    const ORDER_DESC = "desc";

    const MESSAGE_NO_PRIMARY_KEY_DEFINED = "No primary key is defined.";
    const MESSAGE_NO_RECORD_FOUND = "No records found.";
    const MESSAGE_INVALID_FILTER = "Invalid filter";
    const SQL_DATETIME_FORMAT = "Y-m-d H:i:s";
    const DATE_TIME_FORMAT = "datetimeformat";
    
    const NAMESPACE_SEPARATOR = "\\";
    const JOIN_TABLE_SUBFIX = "__jn__";
    const MAX_LINE_LENGTH = 80;

    const COMMA = ", ";
    const COMMA_RETURN = ", \r\n";
    const INLINE_TRIM = " \r\n\t ";
    const ALWAYS_TRUE = "(1=1)";
    
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
     * @var bool
     */
    private $flagIncludeNull = false;

    /**
     * Imported class list
     * 
     * @var array
     */
    private $importedClassList = array();

    /**
     * Flag that class list has been processed or not
     * 
     * @var bool
     */
    private $processClassList = false;

    /**
     * Get namespace of class
     * 
     * @var string
     */
    private $namespaceName = "";
    
    /**
     * Flag that generated value has been added
     *
     * @var bool
     */
    private $generatedValue = false;

    /**
     * Flag that entity require database autoincrement
     *
     * @var bool
     */
    private $requireDbAutoincrement = false;

    /**
     * Flag that database autoincrement has been completed
     *
     * @var bool
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
     * @var bool
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
     * The timezone offset for the current session, in the format '+hh:mm' or '-hh:mm'.
     * This offset represents the time difference between the current session's timezone and UTC.
     *
     * @var string
     */
    private $timeZoneOffset;

    /**
     * The system's timezone offset, representing the offset used by the system's configuration, 
     * in the format '+hh:mm' or '-hh:mm'.
     * This offset is typically used when interacting with the system's time settings.
     *
     * @var string
     */
    private $timeZoneOffsetSystem;

    /**
     * The timezone identifier for the current session, e.g., 'Asia/Jakarta'.
     * This timezone is used for time-related operations within the session.
     *
     * @var string
     */
    private $timeZone;

    /**
     * The system's timezone identifier, representing the timezone used by the system, 
     * e.g., 'Europe/London'.
     * This timezone is typically used for system-level time settings.
     *
     * @var string
     */
    private $timeZoneSystem;

    /**
     * Class constructor to initialize database connection and entity object.
     *
     * @param PicoDatabase|null $database Database connection or null
     * @param MagicObject|mixed $object Entity object to be handled
     */
    public function __construct($database, $object)
    {
        $this->database = $database;
        $this->className = get_class($object);
        $this->object = $object;
        $timeZoneSystem = null;
        $currentTimeZone = date_default_timezone_get();
        if(isset($database))
        {
            $databaseConfig = $this->database->getDatabaseCredentials();
            $timeZoneSystem = $databaseConfig->getTimeZoneSystem();
        }
        if(!isset($timeZoneSystem))
        {
            $timeZoneSystem = $currentTimeZone;
        }
        $this->timeZone = $currentTimeZone;
        $this->timeZoneSystem = $timeZoneSystem;
        $this->timeZoneOffset = PicoDatabase::getTimeZoneOffsetFromString($currentTimeZone);
        $this->timeZoneOffsetSystem = PicoDatabase::getTimeZoneOffsetFromString($timeZoneSystem);
    }
    
    /**
     * Check if a given string is null or empty.
     *
     * @param string $string The string to check
     * @return bool true if the string is null or empty, false otherwise
     */
    public static function nullOrEmpty($string)
    {
        return $string == null || empty($string);
    }
    
    /**
     * Check if a given string is not null and not empty.
     *
     * @param string $string The string to check
     * @return bool true if the string is not null and not empty, false otherwise
     */
    public static function notNullAndNotEmpty($string)
    {
        return $string != null && !empty($string);
    }
    
    /**
     * Apply results from a subquery to master data.
     *
     * @param array $data Master data to which subquery results will be applied
     * @param array $row Reference data containing subquery results
     * @param array $subqueryMap Mapping information for subqueries
     * @return array Updated master data with applied subquery results
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
     * Set a flag to include or skip null columns in the operation.
     *
     * @param bool $skip Flag indicating whether to skip null columns
     * @return self Returns the current instance for method chaining.
     */
    public function includeNull($skip)
    {
        $this->flagIncludeNull = $skip;
        return $this;
    }

    /**
     * Parse a key-value string using a specified parser.
     *
     * @param PicoAnnotationParser $reflexClass The class used for parsing
     * @param string $queryString The key-value string to parse
     * @param string $parameter The name of the parameter being parsed
     * @return array Parsed key-value pairs
     * @throws InvalidAnnotationException If the annotations are invalid or cannot be parsed.
     */
    private function parseKeyValue($reflexClass, $queryString, $parameter)
    {
        try
        {
            return $reflexClass->parseKeyValue($queryString);
        }
        catch(InvalidAnnotationException $e)
        {
            throw new InvalidAnnotationException("Invalid annotation @".$parameter);
        } 
    }
    
    /**
     * Add column name to the columns array based on provided parameters.
     *
     * @param array $columns The current columns array
     * @param PicoAnnotationParser $reflexProp The property parser
     * @param ReflectionProperty $prop The property reflection instance
     * @param array $parameters Parameters containing column name annotations
     * @return array Updated columns array with new column names
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
                    if(isset($values['default_value']))
                    {
                        // Add defaultValue
                        $values[MagicObject::KEY_DEFAULT_VALUE] = $values['default_value'];
                    }
                    $columns[$prop->name] = $values;
                }
            }
        }
        return $columns;
    }
    
    /**
     * Add column type information to the columns array.
     *
     * @param array $columns The current columns array
     * @param PicoAnnotationParser $reflexProp The property parser
     * @param ReflectionProperty $prop The property reflection instance
     * @param array $parameters Parameters containing column type annotations
     * @return array Updated columns array with new column types
     */
    private function addColumnType($columns, $reflexProp, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($columns[$prop->name]))
            {
                $type = explode(' ', trim($val, self::INLINE_TRIM))[0];
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
     * Add a join column name to the join columns array.
     *
     * @param array $joinColumns The current join columns array
     * @param PicoAnnotationParser $reflexProp The property parser for the current property
     * @param ReflectionProperty $prop The reflection property instance
     * @param array $parameters Parameters containing join column annotations
     * @return array Updated join columns array with the new column name
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
     * Add a join column type to the join columns array.
     *
     * @param array $joinColumns The current join columns array
     * @param ReflectionProperty $prop The reflection property instance
     * @param array $parameters Parameters containing join column type annotations
     * @return array Updated join columns array with the new column type
     */
    private function addJoinColumnType($joinColumns, $prop, $parameters)
    {
        foreach($parameters as $param=>$val)
        {
            if(strcasecmp($param, self::ANNOTATION_VAR) == 0 && isset($joinColumns[$prop->name]))
            {
                $type = explode(' ', trim($val, self::INLINE_TRIM))[0];
                $joinColumns[$prop->name][self::KEY_PROPERTY_TYPE] = $type;
                $joinColumns[$prop->name][self::KEY_ENTITY_OBJECT] = true;
            }
        }  
        return $joinColumns;
    }
    
    /**
     * Add primary key information to the primary keys array.
     *
     * @param array $primaryKeys The current primary keys array
     * @param array $columns The columns array
     * @param ReflectionProperty $prop The reflection property instance
     * @param array $parameters Parameters containing primary key annotations
     * @return array Updated primary keys array with the new primary key
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
     * Add autogenerated key information to the auto-increment keys array.
     *
     * @param array $autoIncrementKeys The current auto-increment keys array
     * @param array $columns The columns array
     * @param PicoAnnotationParser $reflexClass The property parser
     * @param ReflectionProperty $prop The reflection property instance
     * @param array $parameters Parameters containing auto-generated value annotations
     * @return array Updated auto-increment keys array with new autogenerated key
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
     * Add default value information to the default values array.
     *
     * @param array $defaultValue The current default values array
     * @param array $columns The columns array
     * @param PicoAnnotationParser $reflexClass The property parser
     * @param ReflectionProperty $prop The reflection property instance
     * @param array $parameters Parameters containing default value annotations
     * @return array Updated default values array with new default value
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
     * Add not-null column information to the not-null columns array.
     *
     * @param array $notNullColumns The current not-null columns array
     * @param array $columns The columns array
     * @param ReflectionProperty $prop The reflection property instance
     * @param array $parameters Parameters containing not-null annotations
     * @return array Updated not-null columns array with new not-null column
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
     * Get table information by parsing class and property annotations.
     *
     * @return PicoTableInfo Table information based on parsed annotations
     * @throws EntityException If the entity is invalid
     */
    public function getTableInfo()
    {
        if(!isset($this->tableInfoProp))
        {
            $noCache = false;
            $reflexClass = new PicoAnnotationParser($this->className);
            $table = $reflexClass->getParameter(self::ANNOTATION_TABLE);
            $cache = $reflexClass->getParameter(self::ANNOTATION_CACHE);
            $package = $reflexClass->getParameter(self::ANNOTATION_PACKAGE);
            if(!isset($table))
            {
                throw new EntityException($this->className . " is not valid entity");
            }
            
            if(isset($cache))
            {
                $noCache = isset($cache[self::KEY_ENABLE]) && self::VALUE_FALSE == strtolower($cache[self::KEY_ENABLE]);
            }
            if(empty($package))
            {
                $package = null;
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
            $this->tableInfoProp = new PicoTableInfo($picoTableName, $columns, $joinColumns, $primaryKeys, $autoIncrementKeys, $defaultValue, $notNullColumns, $noCache, $package);
        }
        return $this->tableInfoProp;
    }

    /**
     * Check if the given PDO statement matches any rows.
     *
     * @param PDOStatement $stmt PDO statement to check.
     * @param string|null $databaseType Optional database type, for specific behavior (e.g., SQLite).
     * @return bool true if rows match, false otherwise.
     */
    public function matchRow($stmt, $databaseType = null)
    {
        if(isset($databaseType) && $databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            return true;
        }
        if($stmt == null)
        {
            return false;
        }
        $rowCount = $stmt->rowCount();
        return $rowCount != null && $rowCount > 0;
    }
    
    /**
     * Save the current object to the database.
     *
     * @param bool $includeNull Whether to include NULL values in the save operation.
     * @return PDOStatement|EntityException Returns the executed statement on success or throws an exception on failure.
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
     * Construct a query for saving the current object data.
     *
     * @param bool $includeNull Whether to include NULL values in the query.
     * @return PicoDatabaseQueryBuilder Returns the constructed query builder for the save operation.
     * @throws EntityException If an error occurs while constructing the query.
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
     * Retrieve the values of the object for database operations.
     *
     * @param PicoTableInfo $info Table information containing column definitions.
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder for escaping values.
     * @return array Associative array of column names and their corresponding values.
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
     * Get a list of columns that should be set to NULL.
     *
     * @param PicoTableInfo $info Table information containing column definitions.
     * @return array List of column names that should be set to NULL.
     */
    private function getNullCols($info)
    {
        $nullCols = array();
        $nullList = $this->object->nullPropertyList();
        if(self::isArray($nullList))
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
     * Retrieve a list of columns that are not insertable.
     *
     * @param PicoTableInfo $info Table information containing column definitions.
     * @return array List of non-insertable column names.
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
     * Retrieve a list of columns that are not updatable.
     *
     * @param PicoTableInfo $info Table information containing column definitions.
     * @return array List of non-updatable column names.
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
     * Construct the SET statement for an SQL update operation.
     *
     * @param PicoTableInfo $info Table information containing column definitions.
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder for escaping values.
     * @return string The constructed SET clause for the update statement.
     * @throws NoUpdatableColumnException If no updatable columns are found.
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
     * Construct the WHERE statement for SQL operations.
     *
     * @param PicoTableInfo $info Table information containing primary key definitions.
     * @param PicoDatabaseQueryBuilder $queryBuilder Query builder for escaping values.
     * @return string The constructed WHERE clause.
     * @throws NoPrimaryKeyDefinedException If no primary keys are defined.
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
     * Retrieve the primary keys from the table information.
     *
     * @param PicoTableInfo|null $info Optional table information; if null, it retrieves the current table info.
     * @return array List of primary key column names.
     * @throws EntityException If an error occurs while retrieving primary keys.
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
     * Retrieve all column names from the table information.
     *
     * @param PicoTableInfo|null $info Optional table information; if null, it retrieves the current table info.
     * @return array List of column names.
     * @throws EntityException If an error occurs while retrieving columns.
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
     * Retrieve all join column names from the table information.
     *
     * @param PicoTableInfo|null $info Optional table information; if null, it retrieves the current table info.
     * @return array List of join column names.
     * @throws EntityException If an error occurs while retrieving join columns.
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
     * Check if the specified column name is a primary key.
     *
     * @param string $columnName The name of the column to check.
     * @param array $primaryKeys An array of primary key column names.
     * @return bool true if the column is a primary key, false otherwise.
     */
    public function isPrimaryKeys($columnName, $primaryKeys)
    {
        return in_array($columnName, $primaryKeys);
    }

    /**
     * Retrieve primary keys that have auto-increment values.
     *
     * @param PicoTableInfo $info Information about the table, including key definitions.
     * @return array An associative array of auto-increment primary keys and their values.
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
     * Add generated values for auto-increment or UUID fields.
     *
     * @param PicoTableInfo $info Table information.
     * @param bool $firstCall Indicates whether this is the first call to the method.
     * @return self Returns the current instance for method chaining.
     */
    private function addGeneratedValue($info, $firstCall)
    {
        if(!$this->generatedValue)
        {
            $keys = $info->getAutoIncrementKeys();
            if(self::isArray($keys))
            {
                foreach($keys as $prop=>$col)
                {
                    $autoVal = $this->object->get($prop);
                    if(self::nullOrEmpty($autoVal) && isset($col[self::KEY_STRATEGY]))
                    {
                        $this->setGeneratedValue($prop, $col[self::KEY_STRATEGY], $firstCall);
                    }
                }
            }
        }
        return $this;
    }
    
    /**
     * Set a generated value for a specified property based on its generation strategy.
     *
     * @param string $prop The property name to set the generated value for.
     * @param string $strategy The generation strategy to use (e.g., UUID, IDENTITY).
     * @param bool $firstCall Indicates whether this is the first call to the method.
     * @return self Returns the current instance for method chaining.
     */
    private function setGeneratedValue($prop, $strategy, $firstCall)
    {
        if(stripos($strategy, "UUID") !== false)
        {
            if($firstCall && ($this->object->get($prop) == null || $this->object->get($prop) == "") && !$this->generatedValue)
            {
                $generatedValue = $this->database->generateNewId();
                $this->object->set($prop, $generatedValue);
                $this->generatedValue = true;
            }
        }
        else if(stripos($strategy, "IDENTITY") !== false)
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
        return $this;
    }

    /**
     * Insert the current object's data into the database.
     *
     * @param bool $includeNull Whether to include NULL values in the insert operation.
     * @return PDOStatement|EntityException Returns the executed statement on success or throws an exception on failure.
     */
    public function insert($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $info = $this->getTableInfo();
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        return $this->_insert($info, $queryBuilder);
    }

    /**
     * Construct the query for inserting the current object's data.
     *
     * @param bool $includeNull Whether to include NULL values in the insert query.
     * @return PicoDatabaseQueryBuilder|EntityException Returns the constructed query builder or throws an exception on failure.
     */
    public function insertQuery($includeNull = false)
    {
        $this->flagIncludeNull = $includeNull;
        $info = $this->getTableInfo();
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        return $this->_insertQuery($info, $queryBuilder);
    }

    /**
     * Execute the insert operation using the given table information and query builder.
     *
     * @param PicoTableInfo|null $info Table information.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder Query builder for the insert operation.
     * @return PDOStatement The executed statement.
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
     * Construct the SQL insert query using the provided table information and query builder.
     *
     * @param PicoTableInfo|null $info Table information.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder Query builder for the insert operation.
     * @return PicoDatabaseQueryBuilder The constructed query builder.
     * @throws EntityException If an error occurs while constructing the query.
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
     * Filter the values to only include those that are insertable based on table info.
     *
     * @param array $values Values to be filtered.
     * @param PicoTableInfo|null $info Table information.
     * @return array The filtered array of insertable values.
     * @throws NoInsertableColumnException If no values are found that can be inserted.
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
     * Check if a generated value is required based on the strategy and property name.
     *
     * @param string $strategy The generation strategy for the property.
     * @param string $propertyName The name of the property to check.
     * @return bool true if a generated value is required, false otherwise.
     */
    private function isRequireGenerateValue($strategy, $propertyName)
    {
        return stripos($strategy, "UUID") !== false 
                && ($this->object->get($propertyName) == null || $this->object->get($propertyName) == "") 
                && !$this->generatedValue;
    }

    /**
     * Create a comma-separated string of field names for an SQL insert statement.
     *
     * @param array $values An associative array of values where keys are field names.
     * @return string A string representation of the field names.
     */
    public function createStatementFields($values)
    {
        return "(".implode(self::COMMA, array_keys($values)).")";
    }

    /**
     * Create a comma-separated string of values for an SQL insert statement.
     *
     * @param array $values An associative array of values.
     * @return string A string representation of the values.
     */
    public function createStatementValues($values)
    {      
        return "(".implode(self::COMMA, array_values($values)).")";
    }

    /**
     * Convert a property name to its corresponding database column name.
     *
     * @param string $propertyNames A string containing property names.
     * @param array $columns An array of column definitions.
     * @return string The resulting string with column names.
     * @throws NoColumnMatchException If no column matches the provided property names.
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
     * Get a mapping of columns from the provided table information.
     *
     * @param PicoTableInfo $info Table information.
     * @return array An associative array mapping property names to column names.
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
     * Adjust the comparison string for SQL queries.
     *
     * @param string $column The column comparison string.
     * @return string The adjusted comparison string.
     */
    private function fixComparison($column)
    {
        if(strpos($column, ' ') !== false)
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
     * Create a SQL WHERE clause based on provided arguments.
     *
     * @param PicoTableInfo $info Table information.
     * @param string $propertyName The name of the property to include in the WHERE clause.
     * @param array $propertyValues The values to compare against.
     * @return string The constructed WHERE clause.
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
     * Get table name of the entity
     *
     * @param string|null $entityName Entity name
     * @param PicoTableInfo $info Table information
     * @return string|null The corresponding table name or null if not found
     * @throws Exception If unable to retrieve the class name or parse annotations
     */
    private function getTableOf($entityName, $info)
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
            $className = $this->getRealClassName($entityName, $info);          
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
     * Get entity primary key of the entity
     *
     * @param string $entityName Entity name
     * @param PicoTableInfo $info Table information
     * @return string[] Array of primary key column names
     * @throws ClassNotFoundException If unable to retrieve the class name or parse annotations
     */
    private function getPrimaryKeyOf($entityName, $info)
    {
        $columns = array();
        try
        {
            $className = $this->getRealClassName($entityName, $info);
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
        catch(ClassNotFoundException $e)
        {
            // do nothing
        }
        return $columns;
    }
    
    /**
     * Get column maps of the entity
     *
     * @param string $entityName Entity name
     * @param PicoTableInfo $info Table information
     * @return array Associative array mapping property names to column names
     * @throws ClassNotFoundException If unable to retrieve the class name or parse annotations
     */
    private function getColumnMapOf($entityName, $info)
    {
        $columns = array();
        try
        {
            $className = $this->getRealClassName($entityName, $info);
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
        catch(ClassNotFoundException $e)
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
     * @param string $field Field name
     * @param bool $master Indicates if the master table is being used
     * @return string Fully qualified column name for the join
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
     * Create WHERE clause from specification
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder instance
     * @param PicoSpecification $specification Specification containing filter criteria
     * @param PicoTableInfo $info Table information
     * @return string The constructed WHERE clause
     */
    protected function createWhereFromSpecification($sqlQuery, $specification, $info)
    {
        $masterColumnMaps = $this->getColumnMap($info);   
        $arr = array();
        $arr[] = self::ALWAYS_TRUE;
        if($specification != null && !$specification->isEmpty())
        {
            $specifications = $specification->getSpecifications();
            foreach($specifications as $spec)
            {           
                $arr = $this->addWhere($arr, $masterColumnMaps, $sqlQuery, $spec, $info);
            }
        }
        $ret = $this->joinStringArray($arr, self::MAX_LINE_LENGTH);
        return PicoDatabaseUtil::trimWhere($ret);
    }

    /**
     * Join array of strings with a maximum length for each line
     *
     * @param string[] $arr Array of strings to join
     * @param int $max Maximum length of each line
     * @param string $normalSplit Normal splitter for joining
     * @param string $maxSplit Splitter for overflow lines
     * @return string Joined string with line breaks as necessary
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
     * Split array into chunks based on maximum length
     *
     * @param string[] $arr Array of strings to split
     * @param int $max Maximum length for each chunk
     * @param string $normalSplit Normal splitter for joining
     * @return array Array of string chunks
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
     * Add WHERE statement to the query
     *
     * @param array $arr Array of existing WHERE clauses
     * @param array $masterColumnMaps Master column mappings
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder instance
     * @param PicoSpecification|PicoPredicate|array|string $spec Specification to process
     * @param PicoTableInfo $info Table information
     * @return array Updated array of WHERE clauses
     */
    private function addWhere($arr, $masterColumnMaps, $sqlQuery, $spec, $info)
    {
        if(isset($specs) && is_array($spec) && count($spec) == 2)
        {
            // Convert array to specification
            $spec = PicoPredicate::getInstance()->equals($spec[0], $spec[1]);
        }
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
                $entityTable = $this->getTableOf($entityName, $info);
                
                if($entityTable != null)
                {
                    $joinColumnmaps = $this->getColumnMapOf($entityName, $info);                           
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
                // Get from map.
                $column = $this->getJoinSource($parentName, $masterTable, $entityTable, $maps[$field], $entityTable == $masterTable);
                $columnFinal = $this->formatColumn($column, $functionFormat);
                $arr[] = $this->constructSpecificationQuery($sqlQuery, $spec, $columnFinal);
            }
            else if(in_array($field, $columnNames))
            {
                // Get colum name.
                $column = $this->getJoinSource($parentName, $masterTable, $entityTable, $field, $entityTable == $masterTable);
                $columnFinal = $this->formatColumn($column, $functionFormat);
                $arr[] = $this->constructSpecificationQuery($sqlQuery, $spec, $columnFinal);
            }
        }
        else if($spec instanceof PicoSpecification)
        {
            // Neested
            $arr[] = $spec->getParentFilterLogic() . " (" . $this->createWhereFromSpecification($sqlQuery, $spec, $info) . ")";
        }
        else if(is_string($spec))
        {
            // Raw SQL query.
            $arr[] = $this->constructSpecificationQuery($sqlQuery, $spec, null);
        }
        return $arr;
    }

    /**
     * Constructs an SQL query fragment based on the given predicate.
     * 
     * This method generates an SQL condition using a `PicoPredicate` instance or a raw SQL string.
     * If the predicate specifies a "BETWEEN" comparison with an array of values, it constructs 
     * a range-based condition. Otherwise, it applies a standard comparison operation.
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery The query builder instance used for SQL operations.
     * @param PicoPredicate|string $spec The predicate defining the condition, or a raw SQL string.
     * @param string $columnFinal The database column to be compared.
     * @return string The constructed SQL query fragment. Returns an empty string if no valid predicate is provided.
     */
    private function constructSpecificationQuery($sqlQuery, $spec, $columnFinal)
    {
        if(!isset($spec))
        {
            return "";
        }
        if($spec instanceof PicoPredicate)
        {
            if($spec->getComparation()->getComparison() == PicoDataComparation::BETWEEN && is_array($spec->getValue()) && count($spec->getValue()) > 1)
            {
                $values = $spec->getValue();
                $min = $values[0];
                $max = end($values);
                $str = $spec->getFilterLogic() . " ( " 
                . $columnFinal . " >= " . $sqlQuery->escapeValue($min)
                . " AND "
                . $columnFinal . " <= " . $sqlQuery->escapeValue($max)
                . " )"
                ;
            }
            else
            {
                $str = $spec->getFilterLogic() . " " . $columnFinal . " " . $spec->getComparation()->getComparison() . " " . $this->contructComparisonValue($sqlQuery, $spec);
            }
            return $str;
        }
        else if(is_string($spec))
        {
            return $spec;
        }
    }
    
    /**
     * Construct comparison value for predicates
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder instance
     * @param PicoPredicate $predicate Predicate with comparison values
     * @return string Formatted comparison value for SQL query
     */
    private function contructComparisonValue($sqlQuery, $predicate)
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
     * Format column name with optional formatting
     *
     * @param string $column Column name to format
     * @param string|null $format Formatting string
     * @return string Formatted column name
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
     * Create ORDER BY clause
     *
     * @param PicoTableInfo $info Table information
     * @param PicoSortable|string $order Sorting criteria
     * @return string|null The constructed ORDER BY clause or null
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
     * Create sorting SQL query
     *
     * @param PicoSortable $order Sorting criteria
     * @param PicoTableInfo|null $info Table information
     * @return string|null The constructed ORDER BY clause or null
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
            $ret = $this->createSortWithoutMapping($order, $info);     
        }
        else
        {
            $ret = $this->createSortWithMapping($order, $info);
        }
        
        return $ret;
    }
    
    /**
     * Create sorting without mapping
     *
     * @param PicoSortable $order Sorting criteria
     * @param PicoTableInfo|null $info Table information
     * @return string|null The constructed ORDER BY clause or null
     */
    private function createSortWithoutMapping($order, $info)
    {
        $ret = null;
        $sorts = array();
        foreach($order->getSortable() as $sort)
        {
            if($sort instanceof PicoSort)
            {
                $columnName = $sort->getSortBy();
                $sortType = $sort->getSortType();             
                $sortBy = $columnName;
                $entityField = new PicoEntityField($sortBy, $info);
                if($entityField->getEntity() != null)
                {
                    $tableName = $this->getTableOf($entityField->getEntity(), $info);
                    $sortBy = $tableName.".".$sortBy;
                }
                $sorts[] = $sortBy . " " . $sortType;     
            }    
            else if(is_string($sort))
            {
                $sorts[] = $sort;
            }
        }
        if(!empty($sorts))
        {
            $ret = $this->joinStringArray($sorts, self::MAX_LINE_LENGTH, self::COMMA, self::COMMA_RETURN);
        }
        return $ret;
    }
    
    /**
     * Create sorting with mapping
     *
     * @param PicoSortable $order Sorting criteria
     * @param PicoTableInfo $info Table information
     * @return string The constructed ORDER BY clause
     */
    private function createSortWithMapping($order, $info) // NOSONAR
    {
        $masterColumnMaps = $this->getColumnMap($info);
        $masterTable = $info->getTableName();
        
        $arr = array();
    
        foreach($order->getSortable() as $sort)
        {
            if($sort instanceof PicoSort)
            {           
                $entityField = new PicoEntityField($sort->getSortBy(), $info);
                $field = $entityField->getField();
                $entityName = $entityField->getEntity();
                $parentName = $entityField->getParentField();
                
                if($entityName != null)
                {
                    $entityTable = $this->getTableOf($entityName, $info);
                    if($entityTable != null)
                    {
                        $joinColumnmaps = $this->getColumnMapOf($entityName, $info);                           
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
                    
                    $arr[] = $column . " " . $sort->getSortType();
                }
                else if(in_array($field, $columnNames))
                {
                    // get colum name
                    $column = $this->getJoinSource($parentName, $masterTable, $entityTable, $field, $masterTable == $entityTable);
                    $arr[] = $column . " " . $sort->getSortType();
                }
            }
            else if(is_string($sort))
            {
                $arr[] = $sort;
            }
        }
        return $this->joinStringArray($arr, self::MAX_LINE_LENGTH, self::COMMA, self::COMMA_RETURN);
    }
    
    /**
     * Check if primary keys have valid values
     *
     * @param string[] $primaryKeys Array of primary key names
     * @param array $propertyValues Property values to check
     * @return bool true if primary keys are valid, false otherwise
     */
    private function isValidPrimaryKeyValues($primaryKeys, $propertyValues)
    {
        return isset($primaryKeys) && !empty($primaryKeys) && count($primaryKeys) <= count($propertyValues);
    }

    /**
     * Convert a scalar value to an array
     *
     * @param mixed $propertyValues Property values to convert
     * @return array Converted array of property values
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
     * Get all columns of the entity
     *
     * @param PicoTableInfo $info Table information
     * @return string Comma-separated string of all column names
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
     * Finds a single record by its primary key value(s).
     *
     * This method retrieves a single record from the database that matches the specified primary key value(s).
     * It returns the found record as an object. If no record is found or if the filter is invalid, appropriate 
     * exceptions will be thrown.
     *
     * @param mixed $propertyValues The primary key value(s) used to find the record.
     * @return object The found record, or null if not found.
     * @throws EntityException If there is an issue with the entity.
     * @throws InvalidFilterException If the provided filter criteria are invalid.
     * @throws EmptyResultException If no record is found or no primary key is set.
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
            $where = $this->createWhereByPrimaryKeys($queryBuilder, $primaryKeys, $propertyValues);
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
                if($this->matchRow($stmt, $this->database->getDatabaseType()))
                {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if($row === false)
                    {
                        // SQLite database
                        $data = null;
                    }
                    else
                    {
                        $data = $this->fixDataType($row, $info); 
                        $data = $this->join($data, $row, $info);
                    }
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
     * Creates the WHERE clause for the query based on the primary keys and their values.
     *
     * This method constructs a WHERE clause for the SQL query using the provided primary key names
     * and values. It checks for null values and properly escapes the values for security.
     *
     * @param PicoDatabaseQueryBuilder $queryBuilder The query builder instance used to create the SQL query.
     * @param array $primaryKeys The primary keys of the table.
     * @param mixed $propertyValues The values for the primary keys.
     * @return string The constructed WHERE clause.
     * @throws InvalidFilterException If the constructed filter is invalid.
     */
    private function createWhereByPrimaryKeys($queryBuilder, $primaryKeys, $propertyValues)
    {
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
        return $where;
    }

    /**
     * Add specification to query builder
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder instance
     * @param PicoSpecification|array $specification Specification or specifications array
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder Modified query builder with specification applied
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
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder instance
     * @param PicoPageable $pageable Pageable object
     * @return PicoDatabaseQueryBuilder Modified query builder with pageable applied
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
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder instance
     * @param PicoPageable|null $pageable Pageable object (optional)
     * @param PicoSortable|string|null $sortable Sortable object or field name (optional)
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder Modified query builder with sortable applied
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
     * Set ORDER BY clause in the query
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder instance
     * @param string $sortOrder Sort order string
     * @return PicoDatabaseQueryBuilder Modified query builder with ORDER BY clause
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
     * Add JOIN query to the query builder
     *
     * @param PicoDatabaseQueryBuilder $sqlQuery Query builder instance
     * @param PicoTableInfo $info Table information
     * @return PicoDatabaseQueryBuilder Modified query builder with JOIN clauses
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
            $joinTable = $this->getTableOf($entity, $info);
            if(!isset($tableAlias[$joinTable]))
            {
                $tableAlias[$joinTable] = 0;
            }
            $tableAlias[$joinTable]++;
            
            $joinTableAlias = $joinTable.self::JOIN_TABLE_SUBFIX.$tableAlias[$joinTable];
            
            $this->joinColumMaps[$propertyName] = new PicoJoinMap($propertyName, $columnName, $entity, $joinTable, $joinTableAlias);
            
            $joinColumn = $this->getPrimaryKeyOf($entity, $info);

            $joinPrimaryKeys = array_values($this->getPrimaryKeyOf($entity, $info));

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
     * Check if JOIN query is required based on specification, pageable, and sortable
     *
     * @param PicoSpecification $specification Specification object
     * @param PicoPageable|null $pageable Pageable object (optional)
     * @param PicoSortable|string|null $sortable Sortable object or field name (optional)
     * @param PicoTableInfo $info Table information
     * @return bool true if JOIN is required, otherwise false
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
     * Determine if JOIN is required based on pageable and sortable
     *
     * @param PicoPageable|null $pageable Pageable object (optional)
     * @param PicoSortable|string|null $sortable Sortable object or field name (optional)
     * @param PicoTableInfo $info Table information
     * @return bool true if JOIN is required, otherwise false
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
     * Determine if JOIN is required based on specification
     *
     * @param PicoSpecification $specification Specification object
     * @return bool true if JOIN is required, otherwise false
     */
    private function isRequireJoinFromSpecification($specification)
    {
        return isset($specification) && $specification instanceof PicoSpecification && $specification->isRequireJoin();
    }

    /**
     * Create a PDO statement based on specification, pageable, sortable, and selected fields
     *
     * @param PicoSpecification $specification Specification object
     * @param PicoPageable $pageable Pageable object
     * @param PicoSortable $sortable Sortable object
     * @param array|null $subqueryMap Subquery map (optional)
     * @param string|null $selected Selected fields (optional)
     * @return PDOStatement Prepared PDO statement
     * @throws PDOException If there is an error in PDO operations
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
     * Get query to find all records from the database without any filter
     *
     * @param PicoSpecification|null $specification Specification object (optional)
     * @param PicoPageable|null $pageable Pageable object (optional)
     * @param PicoSortable|string|null $sortable Sortable object or field name (optional)
     * @param PicoTableInfo|null $info Table information (optional)
     * @return PicoDatabaseQueryBuilder Query builder for finding all records
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
     * Get findSpecific query builder
     *
     * @param string $selected           The columns to select
     * @param PicoSpecification|null $specification  Specification to filter results
     * @param PicoPageable|null $pageable Pageable information for pagination
     * @param PicoSortable|string|null $sortable Sort order for the results
     * @param PicoTableInfo|null $info   Table information (optional, defaults to current table info)
     * @return PicoDatabaseQueryBuilder    The configured query builder
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
     * Retrieve a single record from the database without filters
     *
     * @param PicoSpecification|null $specification  Specification to filter results
     * @param PicoSortable|string|null $sortable       Sort order for the results
     * @param array|null $subqueryMap                Optional subquery mappings
     * @return array|null                              The retrieved record or null if not found
     * @throws EntityException|EmptyResultException    If no results are found
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
     * Retrieve all records from the database without filters
     *
     * @param PicoSpecification|null $specification  Specification to filter results
     * @param PicoPageable|null $pageable           Pageable information for pagination
     * @param PicoSortable|string|null $sortable     Sort order for the results
     * @param array|null $subqueryMap               Optional subquery mappings
     * @return array|null                             The list of records or null if not found
     * @throws EntityException|EmptyResultException   If no results are found
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
     * Find a record by its primary key value
     *
     * @param mixed $primaryKeyVal    The value of the primary key
     * @param array $subqueryMap      Optional subquery mappings
     * @return array|null             The retrieved record or null if not found
     * @throws EmptyResultException    If no record is found
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
            if($this->matchRow($stmt, $this->database->getDatabaseType()))
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if($row === false)
                {
                    // SQLite database
                    $data = null;
                }
                else
                {
                    $data = $this->fixDataType($row, $info); 
                    $data = self::applySubqueryResult($data, $row, $subqueryMap);
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
        return $data;
    }
    
    /**
     * Retrieve records from the database with optional subqueries
     *
     * @param string $selected        The columns to select
     * @param PicoSpecification $specification  Specification to filter results
     * @param PicoPageable|null $pageable         Pageable information for pagination
     * @param PicoSortable|string|null $sortable   Sort order for the results
     * @param array|null $subqueryMap               Optional subquery mappings
     * @return array|null             The list of records or null if not found
     * @throws EntityException|EmptyResultException If no results are found
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
            if($this->matchRow($stmt, $this->database->getDatabaseType()))
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
     * Create a subquery based on the provided mapping
     *
     * @param PicoTableInfo $info   Table information
     * @param array $subqueryMap    Mapping for subqueries
     * @return string               The generated subquery string
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
     * Concatenate two strings with a separator
     *
     * @param string $string1       The first string
     * @param string $string2       The second string
     * @param string $separator      The separator to use
     * @return string               The concatenated string
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
     * Retrieve specific records from the database
     *
     * @param string $selected        The columns to select
     * @param PicoSpecification $specification  Specification to filter results
     * @param PicoPageable|null $pageable         Pageable information for pagination
     * @param PicoSortable|string|null $sortable   Sort order for the results
     * @return array|null             The list of records or null if not found
     * @throws EntityException|EmptyResultException If no results are found
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
            if($this->matchRow($stmt, $this->database->getDatabaseType()))
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
     * Build a query for matched records based on specified criteria
     *
     * @param string $propertyName   The property name to filter by
     * @param mixed $propertyValue   The value of the property to filter by
     * @param PicoPageable|null $pageable         Pageable information for pagination
     * @param PicoSortable|string|null $sortable   Sort order for the results
     * @param PicoTableInfo $info    Table information
     * @param array|null $subqueryMap Optional subquery mappings
     * @return PicoDatabaseQueryBuilder The configured query builder
     * @throws PDOException|NoDatabaseConnectionException|EntityException If an error occurs
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
     * Retrieve matched records from the database
     *
     * @param string $propertyName   The property name to filter by
     * @param mixed $propertyValue   The value of the property to filter by
     * @param PicoPageable|null $pageable         Pageable information for pagination
     * @param PicoSortable|string|null $sortable   Sort order for the results
     * @param array|null $subqueryMap Optional subquery mappings
     * @return array|null             The list of matched records or null if not found
     * @throws PDOException|NoDatabaseConnectionException|EntityException If an error occurs
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
            if($this->matchRow($stmt, $this->database->getDatabaseType()))
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
            throw new UnknownErrorException($e->getMessage());
        }
        return $result;
    }
    
    /**
     * Check if a record exists based on property criteria
     *
     * @param string $propertyName   The property name to check
     * @param mixed $propertyValue   The value of the property to check
     * @return bool                 True if the record exists, false otherwise
     */
    public function existsBy($propertyName, $propertyValue)
    {
        return $this->countBy($propertyName, $propertyValue) > 0;
    }

    /**
     * Count the total number of records without filters
     *
     * @param PicoSpecification|null $specification  Specification to filter results
     * @param PicoPageable|null $pageable            Pageable information for pagination
     * @param PicoSortable|null $sortable             Sort order for the results
     * @return int                                   The count of records
     * @throws EntityException|EmptyResultException   If an error occurs
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
     * Count records based on specified criteria.
     *
     * @param string $propertyName   The property name to filter by.
     * @param mixed $propertyValue   The value of the property to filter by.
     * @return int                   The count of matched records.
     * @throws EntityException|InvalidFilterException|PDOException|EmptyResultException If an error occurs.
     */
    public function countBy($propertyName, $propertyValue)
    {
        $info = $this->getTableInfo();
        $primaryKeys = array_values($info->getPrimaryKeys());
        $agg = !empty($primaryKeys) ? $primaryKeys[0][self::KEY_NAME] : "*"; // Use primary key if available

        $where = $this->createWhereFromArgs($info, $propertyName, $propertyValue);
        
        if (!$this->isValidFilter($where)) {
            throw new InvalidFilterException(self::MESSAGE_INVALID_FILTER);
        }

        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $sqlQuery = $queryBuilder
            ->newQuery()
            ->select($this->database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE ? $agg : "count(*)")
            ->from($info->getTableName())
            ->where($where);
        $count = 0;
        try {
            $stmt = $this->database->executeQuery($sqlQuery);
            if ($stmt) {
                if($this->database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
                {
                    $data = $stmt->fetchColumn();
                    if($data === false)
                    {
                        // SQLite database
                        $count = 0;
                    }
                    else
                    {
                        $count = 1;
                    }
                } 
                else
                {
                    $count = $stmt->rowCount();
                }
            }
            return $count;
        } catch (Exception $e) {
            throw new EmptyResultException($e->getMessage());
        }
    }

    
    /**
     * Delete records based on specified criteria without reading them first
     *
     * @param string $propertyName   The property name to filter by
     * @param mixed $propertyValue   The value of the property to filter by
     * @return int                   The number of deleted records
     * @throws EntityException|InvalidFilterException|PDOException|EmptyResultException If an error occurs
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
     * Retrieves a single matched record from the database based on the specified property name and value.
     *
     * This method constructs a SQL query to find a single record that matches the provided property name and value.
     * It can also sort the results based on the optional sortable parameter. If no record is found, it returns null.
     *
     * @param string $propertyName The name of the property to filter the records by.
     * @param mixed $propertyValue The value of the property to match against.
     * @param PicoSortable|string|null $sortable Optional. Defines sorting for the result set.
     * @return array|null Returns the matching record as an associative array, or null if no record is found.
     * @throws EntityException If there is an issue with the entity operations.
     * @throws InvalidFilterException If the constructed filter is invalid.
     * @throws EmptyResultException If the query results in an empty set.
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
            if($this->matchRow($stmt, $this->database->getDatabaseType()))
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if($row === false)
                {
                    // SQLite database
                    $data = null;
                }
                else
                {
                    $data = $this->fixDataType($row, $info);               
                    $data = $this->join($data, $row, $info);
                }
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
     * Retrieves the fully qualified class name based on the given class name and table information.
     *
     * If the class name does not include a namespace, it constructs the full class name using
     * the package information from the provided PicoTableInfo. If the package is not defined, 
     * it attempts to resolve the class name from the current namespace or imported classes.
     *
     * @param string $classNameJoin The class name to join.
     * @param PicoTableInfo $info Table information containing package details.
     * @return string The fully qualified class name, which may include the package or namespace.
     */
    private function getRealClassName($classNameJoin, $info)
    {
        $result = $classNameJoin;
        if(stripos($classNameJoin, self::NAMESPACE_SEPARATOR) === false)
        {
            // Class name does not include a namespace.
            $package = $info->getPackage();
            if(self::isNotEmpty($package))
            {
                // Use the package annotation to construct the full class name.
                $package = trim($package);
                $result = $package . self::NAMESPACE_SEPARATOR . $classNameJoin;
            }
            else
            {
                $result = $this->getRealClassNameWithoutPackage($classNameJoin);
            }
        }
        return $result;
    }
    
    /**
     * Resolves the fully qualified class name when no package is defined.
     *
     * This method checks if the class name is present in the imported class list or assumes
     * it belongs to the current namespace if not found. It processes the class list only once
     * to improve efficiency.
     *
     * @param string $classNameJoin The class name to join.
     * @return string The fully qualified class name from the imported list or the same namespace.
     */
    private function getRealClassNameWithoutPackage($classNameJoin)
    {
        if(!$this->processClassList)
        {
            // Process the class list only once.
            $reflect = new ExtendedReflectionClass($this->className);
            $useStatements = $reflect->getUseStatements(); 
            $this->namespaceName = $reflect->getNamespaceName();
            if(self::isArray($useStatements))
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
            // Retrieve the class name from the imported list.
            $result = $this->importedClassList[$classNameJoin];
        }
        else if(stripos($classNameJoin, self::NAMESPACE_SEPARATOR) === false)
        {
            // Assume it belongs to the same namespace.
            $result = rtrim($this->namespaceName, self::NAMESPACE_SEPARATOR).self::NAMESPACE_SEPARATOR. $classNameJoin;
        }
        return $result;
    }
    
    /**
     * Retrieves the reference column name from the provided join information.
     *
     * This method checks if the join array contains a specific key for the reference column name.
     * If the key exists, it returns that value; otherwise, it returns the standard column name.
     *
     * @param array $join The join column definition, which may include keys for reference and standard column names.
     * @return string The reference column name, either from the specific key or the standard name.
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
     * Retrieves the property name corresponding to the given reference column name from a joined class.
     *
     * This method checks the columns of the specified class, and returns the property name that matches
     * the reference column name. If no match is found, it returns the reference column name itself.
     *
     * @param string $classNameJoin The name of the class to join with.
     * @param string $referenceColumName The name of the reference column to look up.
     * @param PicoTableInfo $info The table information containing metadata about the columns.
     * @return string|null The corresponding property name if found, otherwise the reference column name.
     */
    private function getJoinKeyName($classNameJoin, $referenceColumName, $info)
    {
        $className = $this->getRealClassName($classNameJoin, $info);
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
     * Prepares the join cache for a specified class name.
     *
     * This method checks if the join cache for the given class name exists. If it does not exist,
     * it initializes an empty array for caching join data. It ensures that the join cache is ready
     * for storing results from subsequent queries.
     *
     * @param string $classNameJoin The class name for which to prepare the join cache.
     * @return self Returns the current instance for method chaining.
     */
    private function prepareJoinCache($classNameJoin)
    {
        if(!isset($this->joinCache[$classNameJoin]))
        {
            $this->joinCache[$classNameJoin] = array();
        }
        return $this;
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
     * @param PicoTableInfo $info Table information
     * @return MagicObject|null Returns the retrieved MagicObject if found, or null if not found.
     */
    private function getJoinData($classNameJoin, $referenceColumName, $joinKeyValue, $info)
    {
        $className = $this->getRealClassName($classNameJoin, $info);
        $persist = new self(null, new $className());
        $info = $persist->getTableInfo();
        $noCache = isset($info) ? $info->getNoCache() : false;
        
        // Check if caching is disabled or if the data is not already cached
        if($noCache || !isset($this->joinCache[$classNameJoin]) || !isset($this->joinCache[$classNameJoin][$joinKeyValue]))
        {       
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
     * Joins data based on the specified join columns from the provided row.
     *
     * This method retrieves related data by following the join definitions specified in the 
     * `PicoTableInfo` object. It populates the given data object with the joined entities
     * based on the annotations defined in the join columns.
     *
     * @param mixed $data The original object or array to be populated with joined data.
     * @param array $row The row of data containing column values.
     * @param PicoTableInfo $info The table information that includes join column metadata.
     * @return mixed The updated object or array with joined data.
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
                $joinKeyName = $this->getJoinKeyName($classNameJoin, $referenceColumName, $info);
                try
                {
                    if(isset($row[$columnName]))
                    {
                        $this->prepareJoinCache($classNameJoin);
                        $obj = $this->getJoinData($classNameJoin, $joinKeyName, $row[$columnName], $info);
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
     * Adds a property to the original data array or object.
     *
     * This method sets a property with the specified name to the given value. It handles
     * both arrays and objects, ensuring that the property is added correctly based on the type.
     *
     * @param array|object $data The original data (array or object).
     * @param string $propName The name of the property to add.
     * @param mixed $value The value to assign to the property.
     * @return array|object The updated data array or object with the new property.
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
     * Validates whether the given filter is acceptable.
     *
     * This method checks if the provided filter is not null, not empty, and not a whitespace string.
     *
     * @param string $filter The filter string to validate.
     * @return bool true if the filter is valid; otherwise, false.
     */
    private function isValidFilter($filter)
    {
        return $this->notNullAndNotEmptyAndNotSpace($filter);
    }

    /**
     * Checks if the provided value is not null, not empty, and not just whitespace.
     *
     * This method trims the input value and performs checks to determine if it is 
     * a valid, non-empty string.
     *
     * @param string $value The value to check.
     * @return bool true if the value is valid; otherwise, false.
     */
    private function notNullAndNotEmptyAndNotSpace($value)
    {
        return $value != null && !empty(trim($value));
    }

    /**
     * Fixes the data types of the input data based on the table information.
     *
     * This method maps the input data to the appropriate types as defined in the 
     * provided `PicoTableInfo`. It ensures that the data types are correct according to 
     * the column definitions.
     *
     * @param array $data The input data to be fixed.
     * @param PicoTableInfo $info The table information containing type definitions.
     * @return array The data with fixed types.
     */
    public function fixDataType($data, $info)
    {
        $result = array();
        $typeMap = $this->createTypeMap($info);
        foreach($info->getColumns() as $prop=>$column)
        {
            $columnName = $column[self::KEY_NAME];
            $value = $data[$columnName];
            $value = $this->fixTimeZone($value, $column, $this->timeZoneSystem, $this->timeZone);
            
            if(isset($typeMap[$columnName]))
            {
                $result[$prop] = $this->fixData($value, $typeMap[$columnName]);
            }
        }
        return $result;
    }

    /**
     * Fixes the value to the specified data type.
     *
     * This method converts the input value to the appropriate type based on the provided
     * data type. It handles various types including boolean, integer, double, and DateTime.
     *
     * @param mixed $value The input value to be fixed.
     * @param string $type The expected data type of the value.
     * @return mixed The value converted to the specified type.
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
     * Converts the input value to a boolean.
     *
     * This method checks if the input value is equivalent to `1` or `'1'` to determine
     * if it should return `true`; otherwise, it returns `false`.
     *
     * @param mixed $value The input value to convert.
     * @return bool true if the value is equivalent to `1`; otherwise, false.
     */
    private function boolval($value)
    {
        return $value == 1 || $value == '1';
    }
    
    /**
     * Converts the input value to an integer.
     *
     * This method returns the integer value of the input. If the input is null, it
     * returns null instead.
     *
     * @param mixed $value The input value to convert.
     * @return mixed The integer value or null if the input is null.
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
     * Converts the input value to a double.
     *
     * This method returns the double value of the input. If the input is null, it
     * returns null instead.
     *
     * @param mixed $value The input value to convert.
     * @return mixed The double value or null if the input is null.
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
     * Fixes the input value based on its type and applies timezone adjustments if necessary.
     *
     * If the input value is an instance of DateTime, it adjusts the timezone and formats the date 
     * according to the specified column format. If the input value is not a DateTime, it simply 
     * adjusts the timezone without formatting the value. If a date format is specified in the column, 
     * it will format the DateTime accordingly.
     *
     * @param mixed $value The input value to fix, which can be a DateTime object, string, or other types.
     * @param array $column The column information containing potential date format and type.
     * @return mixed The formatted date string if the input is a DateTime, or the original value after timezone adjustment.
     */
    private function fixInput($value, $column)
    {
        if($value instanceof DateTime)
        {
            $value = $this->fixTimeZone($value, $column, $this->timeZone, $this->timeZoneSystem);
            if(isset($column[self::DATE_TIME_FORMAT]))
            {
                return (string) $value->format($column[self::DATE_TIME_FORMAT]);
            }
            else
            {
                return (string) $value->format(self::SQL_DATETIME_FORMAT);
            }
        }
        else
        {
            $value = $this->fixTimeZone($value, $column, $this->timeZone, $this->timeZoneSystem);
            return $value;
        }
    }

    /**
     * Adjusts the input value from the source timezone to the target timezone if necessary.
     *
     * This method checks if the column type is related to a timestamp and the database is SQLite.
     * If so, it adjusts the input value (which can be a DateTime object, string, or Unix timestamp)
     * from the source timezone to the target timezone. Otherwise, it returns the original value.
     *
     * @param mixed $value The value to be adjusted. Can be a DateTime, string, or Unix timestamp.
     * @param array $column The column information containing potential date format and type.
     * @param string|DateTimeZone $timeZoneFrom The source timezone (either a string or DateTimeZone object).
     * @param string|DateTimeZone $timeZoneTo The target timezone (either a string or DateTimeZone object).
     * @return mixed The adjusted value, either a formatted DateTime or the original value if no adjustment is necessary.
     */
    private function fixTimeZone($value, $column, $timeZoneFrom, $timeZoneTo)
    {
        if(isset($column[self::KEY_PROPERTY_TYPE]) 
        && stripos($column[self::KEY_PROPERTY_TYPE], "timestamp") !== false 
        && $timeZoneFrom != $timeZoneTo
        && $this->database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            return PicoTimeZoneChanger::changeTimeZone($value, $timeZoneFrom, $timeZoneTo);
        }
        return $value;
    }
    
    /**
     * Checks if the given datetime value represents a null or empty datetime.
     *
     * This method checks specific string representations of null or default datetime values.
     *
     * @param string $value The value to check.
     * @return bool true if the value represents a null datetime; otherwise, false.
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
     * Creates a mapping of column names to their corresponding property types.
     *
     * This method generates an associative array where keys are column names and values
     * are their associated property types based on the provided PicoTableInfo.
     *
     * @param PicoTableInfo $info The table information containing column metadata.
     * 
     * @return array An associative array mapping column names to property types.
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
     * Selects record from the database based on the defined criteria.
     *
     * This method builds a query to retrieve records from the database using the
     * current table information, filters, specifications, and pagination settings.
     *
     * @return mixed The result set of the query.
     * @throws EntityException If an error occurs while selecting records.
     */
    public function select()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_select($info, $queryBuilder, $where, $this->specification, $this->pageable, $this->sortable);
    }

    /**
     * Selects all records from the database.
     *
     * This method constructs and executes a query to retrieve all records from the 
     * specified table in the database.
     *
     * @return mixed The result set containing all records.
     * @throws EntityException If an error occurs during the selection process.
     */
    public function selectAll()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_selectAll($info, $queryBuilder, $where, $this->specification, $this->pageable, $this->sortable);
    }

    /**
     * Builds a query to select data without executing it.
     *
     * This method prepares a select query using the specified table information and 
     * filtering criteria but does not execute the query.
     *
     * @return PicoDatabaseQueryBuilder The query builder with the select query prepared.
     * @throws EntityException If an error occurs while preparing the query.
     */
    public function selectQuery()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_selectQuery($info, $queryBuilder, $where);
    }

    /**
     * Selects a record from the database based on primary keys.
     *
     * This method constructs and executes a select query using the provided table information
     * and filtering criteria. It returns the first matching record or null if none found.
     *
     * @param PicoTableInfo|null $info The table information. If null, fetched internally.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder The query builder. If null, created internally.
     * @param string|null $where The where clause for the query. If null, fetched internally.
     * @param mixed|null $specification Optional specifications for the query.
     * @param mixed|null $pageable Optional pagination settings for the query.
     * @param mixed|null $sortable Optional sorting settings for the query.
     * @return mixed The matching record or null if not found.
     * @throws EntityException If an error occurs during the selection process.
     * @throws InvalidFilterException If the provided filter is invalid.
     * @throws EmptyResultException If no result is found.
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
            if($this->matchRow($stmt, $this->database->getDatabaseType()))
            {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if($row === false)
                {
                    // SQLite database
                    $data = null;
                }
                else
                {
                    $data = $this->fixDataType($row, $info);
                    $data = $this->join($data, $row, $info);
                }
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
     * Selects all matching records from the database.
     *
     * This method constructs and executes a select query to retrieve all records that match 
     * the specified filtering criteria. It returns an array of results.
     *
     * @param PicoTableInfo|null $info The table information. If null, fetched internally.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder The query builder. If null, created internally.
     * @param string|null $where The where clause for the query. If null, fetched internally.
     * @param mixed|null $specification Optional specifications for the query.
     * @param mixed|null $pageable Optional pagination settings for the query.
     * @param mixed|null $sortable Optional sorting settings for the query.
     * @return array An array of matching records.
     * @throws EntityException If an error occurs during the selection process.
     * @throws InvalidFilterException If the provided filter is invalid.
     * @throws EmptyResultException If no results are found.
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
            if($this->matchRow($stmt, $this->database->getDatabaseType()))
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
     * Prepares a query to select data without executing it.
     *
     * This method constructs a select query using the specified table information and 
     * filtering criteria without executing it, allowing for further modifications if needed.
     *
     * @param PicoTableInfo|null $info The table information. If null, fetched internally.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder The query builder. If null, created internally.
     * @param string|null $where The where clause for the query. If null, fetched internally.
     * @return PicoDatabaseQueryBuilder The query builder with the select query prepared.
     * @throws InvalidFilterException If the provided filter is invalid.
     * @throws EntityException If an error occurs while preparing the query.
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
     * Updates records in the database.
     *
     * This method constructs and executes an update query to modify records in the 
     * specified table. It accepts an optional flag to include null values in the update.
     *
     * @param bool $includeNull Optional. If true, null values are included in the update.
     * @return PDOStatement The executed update statement.
     * @throws EntityException If an error occurs during the update process.
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
     * Prepares an update query without executing it.
     *
     * This method constructs an update query using the specified table information and
     * returns the query builder for further modifications.
     *
     * @param bool $includeNull Optional. If true, null values are included in the update.
     * @return PicoDatabaseQueryBuilder The query builder with the update query prepared.
     * @throws EntityException If an error occurs while preparing the query.
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
     * Updates a record in the database based on primary keys.
     *
     * This method constructs and executes an update query using the provided table information 
     * and filtering criteria.
     *
     * @param PicoTableInfo|null $info The table information. If null, fetched internally.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder The query builder. If null, created internally.
     * @param string|null $where The where clause for the query. If null, fetched internally.
     * @return PDOStatement The executed update statement.
     * @throws InvalidFilterException If the provided filter is invalid.
     */
    private function _update($info = null, $queryBuilder = null, $where = null)
    {
        $sqlQuery = $this->_updateQuery($info, $queryBuilder, $where);
        return $this->database->executeUpdate($sqlQuery);
    }

    /**
     * Prepares an update query without executing it.
     *
     * This method constructs an update query using the specified table information and filtering criteria,
     * returning the query builder for further modifications.
     *
     * @param PicoTableInfo|null $info The table information. If null, fetched internally.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder The query builder. If null, created internally.
     * @param string|null $where The where clause for the query. If null, fetched internally.
     * @return PicoDatabaseQueryBuilder The query builder with the update query prepared.
     * @throws InvalidFilterException If the provided filter is invalid.
     * @throws EntityException If an error occurs while preparing the query.
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
     * Deletes a record from the database.
     *
     * This method constructs and executes a delete query to remove a record
     * from the specified table based on the provided filtering criteria.
     *
     * @return PDOStatement The executed delete statement.
     * @throws EntityException If an error occurs during the deletion process.
     */
    public function delete()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_delete($info, $queryBuilder, $where);
    }

    /**
     * Prepares a delete query without executing it.
     *
     * This method constructs a delete query using the specified table information 
     * and filtering criteria without executing it, allowing for further modifications.
     *
     * @return PicoDatabaseQueryBuilder The query builder with the delete query prepared.
     * @throws EntityException If an error occurs while preparing the query.
     */
    public function deleteQuery()
    {
        $queryBuilder = new PicoDatabaseQueryBuilder($this->database);
        $info = $this->getTableInfo();
        $where = $this->getWhere($info, $queryBuilder);
        return $this->_deleteQuery($info, $queryBuilder, $where);
    }

    /**
     * Deletes a record from the database based on primary keys.
     *
     * This method constructs and executes a delete query using the provided table 
     * information and filtering criteria.
     *
     * @param PicoTableInfo|null $info The table information. If null, fetched internally.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder The query builder. If null, created internally.
     * @param string|null $where The where clause for the query. If null, fetched internally.
     * @return PDOStatement The executed delete statement.
     */
    private function _delete($info = null, $queryBuilder = null, $where = null)
    {
        $sqlQuery = $this->_deleteQuery($info, $queryBuilder, $where);
        return $this->database->executeDelete($sqlQuery);
    }

    /**
     * Prepares a delete query without executing it.
     *
     * This method constructs a delete query using the specified table information and 
     * filtering criteria, returning the query builder for further modifications.
     *
     * @param PicoTableInfo|null $info The table information. If null, fetched internally.
     * @param PicoDatabaseQueryBuilder|null $queryBuilder The query builder. If null, created internally.
     * @param string|null $where The where clause for the query. If null, fetched internally.
     * @return PicoDatabaseQueryBuilder The query builder with the delete query prepared.
     * @throws InvalidFilterException If the provided filter is invalid.
     * @throws EntityException If an error occurs while preparing the query.
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
     * Retrieves a MagicObject with a WHERE specification.
     *
     * This method creates a new instance of PicoDatabasePersistenceExtended
     * and configures it with a WHERE clause derived from the provided specification.
     *
     * @param PicoSpecification $specification The specification used to define the WHERE clause.
     * @return PicoDatabasePersistenceExtended The configured persistence object with the WHERE clause.
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
     * Checks if the given value is an array.
     *
     * This method verifies whether the provided value is set and is an array.
     *
     * @param mixed $value The value to be checked.
     * @return bool true if the value is an array, false otherwise.
     */
    public static function isArray($value)
    {
        return isset($value) && is_array($value);
    }
    
    /**
     * Check if the given input is not empty.
     *
     * This function determines if the provided input is set and not empty,
     * returning true if it contains a non-empty value, and false otherwise.
     *
     * @param mixed $input The input value to check.
     * @return bool true if the input is not empty, false otherwise.
     */
    public static function isNotEmpty($input)
    {
        return isset($input) && !empty($input);
    }

    /**
     * Get the timezone offset for the current session, represented in the format '+hh:mm' or '-hh:mm'.
     *
     * @return  string
     */ 
    public function getTimeZoneOffset()
    {
        return $this->timeZoneOffset;
    }

    /**
     * Set the timezone offset for the current session, represented in the format '+hh:mm' or '-hh:mm'.
     *
     * @param  string  $timeZoneOffset  The timezone offset for the current session, represented in the format '+hh:mm' or '-hh:mm'.
     *
     * @return  self
     */ 
    public function setTimeZoneOffset($timeZoneOffset)
    {
        $this->timeZoneOffset = $timeZoneOffset;

        return $this;
    }

    /**
     * Get the system's timezone offset in the format '+hh:mm' or '-hh:mm'.
     *
     * This method returns the system's timezone offset, which is stored as a string in the format 
     * of hours and minutes (e.g., '+07:00', '-03:00').
     *
     * @return string The system's timezone offset.
     */
    public function getTimeZoneOffsetSystem()
    {
        return $this->timeZoneOffsetSystem;
    }

    /**
     * Set the system's timezone offset in the format '+hh:mm' or '-hh:mm'.
     *
     * This method sets the system's timezone offset, which should be provided as a string 
     * in the format of hours and minutes (e.g., '+07:00', '-03:00').
     *
     * @param string $timeZoneOffsetSystem The timezone offset to set for the system.
     *                                    Must be in the format '+hh:mm' or '-hh:mm'.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setTimeZoneOffsetSystem($timeZoneOffsetSystem)
    {
        $this->timeZoneOffsetSystem = $timeZoneOffsetSystem;

        return $this;
    }


    /**
     * Get the timezone used for time-related operations within the session.
     *
     * This method returns the timezone identifier (e.g., 'Asia/Jakarta') that is set for the current session 
     * and is used for all time-related operations.
     *
     * @return string The timezone identifier for the current session (e.g., 'Asia/Jakarta').
     */
    public function getTimeZone()
    {
        return $this->timeZone;
    }

    /**
     * Set the timezone used for time-related operations within the session.
     *
     * This method sets the timezone identifier (e.g., 'Asia/Jakarta') to be used for all time-related operations 
     * in the current session.
     *
     * @param string $timeZone The timezone identifier to be used for time-related operations (e.g., 'Asia/Jakarta').
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setTimeZone($timeZone)
    {
        $this->timeZone = $timeZone;

        return $this;
    }

    /**
     * Get the timezone typically used for system-level time settings.
     *
     * This method returns the system's timezone identifier (e.g., 'Europe/London') which is generally used for 
     * system-level time settings and configurations.
     *
     * @return string The system's timezone identifier (e.g., 'Europe/London').
     */
    public function getTimeZoneSystem()
    {
        return $this->timeZoneSystem;
    }

    /**
     * Set the timezone typically used for system-level time settings.
     *
     * This method sets the system's timezone identifier (e.g., 'Europe/London') that is typically used for system 
     * configurations and time settings.
     *
     * @param string $timeZoneSystem The timezone identifier to be used for system-level time settings (e.g., 'Europe/London').
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setTimeZoneSystem($timeZoneSystem)
    {
        $this->timeZoneSystem = $timeZoneSystem;

        return $this;
    }

}
