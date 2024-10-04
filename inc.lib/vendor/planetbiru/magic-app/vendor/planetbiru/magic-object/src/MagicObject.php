<?php

namespace MagicObject;

use Exception;
use PDOException;
use PDOStatement;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabasePersistence;
use MagicObject\Database\PicoDatabasePersistenceExtended;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoPageData;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Database\PicoTableInfo;
use MagicObject\Exceptions\FindOptionException;
use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Exceptions\NoDatabaseConnectionException;
use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoObjectParser;
use MagicObject\Util\Database\PicoDatabaseUtil;
use MagicObject\Util\PicoArrayUtil;
use MagicObject\Util\PicoEnvironmentVariable;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;
use ReflectionClass;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Class to create magic object.
 * Magic object is an object created from any class so that user can add any property with any name and value, load data from INI file, Yaml file, JSON file and database.
 * User can create entity from a table of database, insert, select, update and delete record from database.
 * User can also create property from other entity with full name of class (namespace + class name)
 * @link https://github.com/Planetbiru/MagicObject
 */
class MagicObject extends stdClass // NOSONAR
{
    const MESSAGE_NO_DATABASE_CONNECTION = "No database connection provided";
    const MESSAGE_NO_RECORD_FOUND = "No record found";
    const PROPERTY_NAMING_STRATEGY = "property-naming-strategy";
    const KEY_PROPERTY_TYPE = "propertyType";
    const KEY_DEFAULT_VALUE = "default_value";
    const KEY_NAME = "name";
    const KEY_VALUE = "value";
    const JSON = 'JSON';
    const YAML = 'Yaml';

    const ATTR_CHECKED = ' checked="checked"';
    const ATTR_SELECTED = ' selected="selected"';

    const FIND_OPTION_DEFAULT = 0;
    const FIND_OPTION_NO_COUNT_DATA = 1;
    const FIND_OPTION_NO_FETCH_DATA = 2;

    /**
     * Flag readonly
     *
     * @var boolean
     */
    private $_readonly = false; // NOSONAR

    /**
     * Database connection
     *
     * @var PicoDatabase
     */
    private $_database; // NOSONAR
    /**
     * Class params
     *
     * @var array
     */
    private $_classParams = array(); // NOSONAR

    /**
     * Null properties
     *
     * @var array
     */
    private $_nullProperties = array(); // NOSONAR

    /**
     * Property label
     *
     * @var array
     */
    private $_label = array(); // NOSONAR

    /**
     * Table information
     *
     * @var PicoTableInfo
     */
    private $_tableInfoProp = null; // NOSONAR

    /**
     * Database persistence
     *
     * @var PicoDatabasePersistence
     */
    private $_persistProp = null; // NOSONAR

    /**
     * Get null properties
     *
     * @return array
     */
    public function nullPropertiyList()
    {
        return $this->_nullProperties;
    }

    /**
     * Constructor
     *
     * @param self|array|stdClass|object $data Initial data
     * @param PicoDatabase $database Database connection
     */
    public function __construct($data = null, $database = null)
    {
        $jsonAnnot = new PicoAnnotationParser(get_class($this));
        $params = $jsonAnnot->getParameters();
        foreach($params as $paramName=>$paramValue)
        {
            try
            {
                $vals = $jsonAnnot->parseKeyValue($paramValue);
                $this->_classParams[$paramName] = $vals;
            }
            catch(InvalidQueryInputException $e)
            {
                throw new InvalidAnnotationException("Invalid annotation @".$paramName);
            }
        }
        if($data != null)
        {
            if(is_array($data))
            {
                $data = PicoArrayUtil::camelize($data);
            }
            $this->loadData($data);
        }
        if($database != null)
        {
            $this->_database = $database;
        }
    }

    /**
     * Load data to object
     * @param mixed $data Data
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
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->set($key2, $value, true);
                }
            }
            else if (is_array($data) || is_object($data)) {
                foreach ($data as $key => $value) {
                    $key2 = PicoStringUtil::camelize(str_replace("-", "_", $key));
                    $this->set($key2, $value, true);
                }
            }
        }
        return $this;
    }

    /**
     * Load data from INI string
     *
     * @param string $rawData Raw data
     * @param boolean $systemEnv Flag to use environment variable
     * @return self
     */
    public function loadIniString($rawData, $systemEnv = false)
    {
        // Parse without sections
        $data = parse_ini_string($rawData);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);
        $this->loadData($data);
        return $this;
    }

    /**
     * Load data from INI file
     *
     * @param string $path File path
     * @param boolean $systemEnv Flag to use environment variable
     * @return self
     */
    public function loadIniFile($path, $systemEnv = false)
    {
        // Parse without sections
        $data = parse_ini_file($path);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);
        $this->loadData($data);
        return $this;
    }

    /**
     * Load data from Yaml string
     *
     * @param string $rawData String of Yaml
     * @param boolean $systemEnv Replace all environment variable value
     * @param boolean $asObject Result is object instead of array
     * @param boolean $recursive Convert all object to MagicObject
     * @return self
     */
    public function loadYamlString($rawData, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = Yaml::parse($rawData);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);
        if($asObject)
        {
            // convert to object
            $obj = json_decode(json_encode((object) $data), false);
            if($recursive)
            {
                $this->loadData(PicoObjectParser::parseRecursiveObject($obj));
            }
            else
            {
                $this->loadData($obj);
            }
        }
        else
        {
            if($recursive)
            {
                $this->loadData(PicoObjectParser::parseRecursiveObject($data));
            }
            else
            {
                $this->loadData($data);
            }
        }
        return $this;
    }

    /**
     * Load data from Yaml file
     *
     * @param string $path Yaml file path
     * @param boolean $systemEnv Replace all environment variable value
     * @param boolean $asObject Result is object instead of array
     * @param boolean $recursive Convert all object to MagicObject
     * @return self
     */
    public function loadYamlFile($path, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = Yaml::parseFile($path);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);
        if($asObject)
        {
            // convert to object
            $obj = json_decode(json_encode((object) $data), false);
            if($recursive)
            {
                $this->loadData(PicoObjectParser::parseRecursiveObject($obj));
            }
            else
            {
                $this->loadData($obj);
            }
        }
        else
        {
            if($recursive)
            {
                $this->loadData(PicoObjectParser::parseRecursiveObject($data));
            }
            else
            {
                $this->loadData($data);
            }
        }
        return $this;
    }

    /**
     * Load data from JSON string
     *
     * @param string $rawData JSON string
     * @param boolean $systemEnv Replace all environment variable value
     * @param boolean $recursive Convert all object to MagicObject
     * @return self
     */
    public function loadJsonString($rawData, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = json_decode($rawData);
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);
        if($asObject)
        {
            // convert to object
            $obj = json_decode(json_encode((object) $data), false);
            if($recursive)
            {
                $this->loadData(PicoObjectParser::parseRecursiveObject($obj));
            }
            else
            {
                $this->loadData($obj);
            }
        }
        else
        {
            if($recursive)
            {
                $this->loadData(PicoObjectParser::parseRecursiveObject($data));
            }
            else
            {
                $this->loadData($data);
            }
        }
        return $this;
    }

    /**
     * Load data from JSON file
     *
     * @param string $path JSON file path
     * @param boolean $systemEnv Replace all environment variable value
     * @param boolean $recursive Convert all object to MagicObject
     * @return self
     */
    public function loadJsonFile($path, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = json_decode(file_get_contents($path));
        $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
        if($systemEnv)
        {
            $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
        }
        $data = PicoArrayUtil::camelize($data);
        if($asObject)
        {
            // convert to object
            $obj = json_decode(json_encode((object) $data), false);
            if($recursive)
            {
                $this->loadData(PicoObjectParser::parseRecursiveObject($obj));
            }
            else
            {
                $this->loadData($obj);
            }
        }
        else
        {
            if($recursive)
            {
                $this->loadData(PicoObjectParser::parseRecursiveObject($data));
            }
            else
            {
                $this->loadData($data);
            }
        }
        return $this;
    }

    /**
     * Set readonly. When object is set to readonly, setter will not change value of its properties but loadData still works fine
     *
     * @param boolean $readonly Flag to set object to be readonly
     * @return self
     */
    protected function readOnly($readonly)
    {
        $this->_readonly = $readonly;
        return $this;
    }

    /**
     * Set database connection
     * @var PicoDatabase $database Database connection
     * @return self
     */
    public function withDatabase($database)
    {
        $this->_database = $database;
        return $this;
    }

    /**
     * Set or get current database. If parameter is not empty, set current database with database given. Return current database or null.
     *
     * @param PicoDatabase|null $database Database connection
     * @return PicoDatabase|null
     */
    public function currentDatabase($database = null)
    {
        if($database != null)
        {
            $this->withDatabase($database);
        }
        if(!isset($this->_database))
        {
            return null;
        }
        return $this->_database;
    }

    /**
     * Remove property
     *
     * @param object $sourceData Data
     * @param array $propertyNames Properties name
     * @return mixed
     */
    public function removePropertyExcept($sourceData, $propertyNames)
    {
        if(is_object($sourceData))
        {
            // iterate
            $resultData = new stdClass;
            foreach($sourceData as $key=>$val)
            {
                if(in_array($key, $propertyNames))
                {
                    $resultData->$key = $val;
                }
            }
            return $resultData;
        }
        if(is_array($sourceData))
        {
            // iterate
            $resultData = array();
            foreach($sourceData as $key=>$val)
            {
                if(in_array($key, $propertyNames))
                {
                    $resultData[$key] = $val;
                }
            }
            return $resultData;
        }
        return new stdClass;
    }

    /**
     * Save to database
     * @param boolean $includeNull If TRUE, all properties will be saved to database include null. If FALSE, only column with not null value will be saved to database
     * @return PDOStatement
     * NoDatabaseConnectionException|NoRecordFoundException|PDOException
     */
    public function save($includeNull = false)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->save($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Query of save data
     * @param boolean $includeNull If TRUE, all properties will be saved to database include null. If FALSE, only column with not null value will be saved to database
     * @return PicoDatabaseQueryBuilder
     * NoDatabaseConnectionException|NoRecordFoundException
     */
    public function saveQuery($includeNull = false)
    {
        if($this->_database != null && ($this->_database->getDatabaseType() != null && $this->_database->getDatabaseType() != ""))
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->saveQuery($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Select data from database
     *
     * @return self
     * @throws NoDatabaseConnectionException|NoRecordFoundException|PDOException
     */
    public function select()
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            $data = $persist->select();
            if($data == null)
            {
                throw new NoRecordFoundException(self::MESSAGE_NO_RECORD_FOUND);
            }
            $this->loadData($data);
            return $this;
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Select data from database
     *
     * @return self
     * @throws NoDatabaseConnectionException|NoRecordFoundException|PDOException
     */
    public function selectAll()
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            $data = $persist->selectAll();
            if($data == null)
            {
                throw new NoRecordFoundException(self::MESSAGE_NO_RECORD_FOUND);
            }
            $this->loadData($data);
            return $this;
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Query of select data
     *
     * @return PicoDatabaseQueryBuilder
     * @throws NoDatabaseConnectionException|NoRecordFoundException|PDOException
     */
    public function selectQuery()
    {
        if($this->_database != null && ($this->_database->getDatabaseType() != null && $this->_database->getDatabaseType() != ""))
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->selectQuery();
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Insert into database
     *
     * @param boolean $includeNull If TRUE, all properties will be saved to database include null. If FALSE, only column with not null value will be saved to database
     * @return PDOStatement
     * @throws NoDatabaseConnectionException|PDOException
     */
    public function insert($includeNull = false)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->insert($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Get query of insert data
     *
     * @param boolean $includeNull If TRUE, all properties will be saved to database include null. If FALSE, only column with not null value will be saved to database
     * @return PicoDatabaseQueryBuilder
     * @throws NoDatabaseConnectionException
     */
    public function insertQuery($includeNull = false)
    {
        if($this->_database != null && ($this->_database->getDatabaseType() != null && $this->_database->getDatabaseType() != ""))
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->insertQuery($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Update data on database
     *
     * @param boolean $includeNull If TRUE, all properties will be saved to database include null. If FALSE, only column with not null value will be saved to database
     * @return PDOStatement
     * @throws NoDatabaseConnectionException|PDOException
     */
    public function update($includeNull = false)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->update($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Get query of update data
     *
     * @param boolean $includeNull If TRUE, all properties will be saved to database include null. If FALSE, only column with not null value will be saved to database
     * @return PicoDatabaseQueryBuilder
     * @throws NoDatabaseConnectionException
     */
    public function updateQuery($includeNull = false)
    {
        if($this->_database != null && ($this->_database->getDatabaseType() != null && $this->_database->getDatabaseType() != ""))
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->updateQuery($includeNull);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Delete data from database
     *
     * @return PDOStatement
     * @throws NoDatabaseConnectionException|PDOException
     */
    public function delete()
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->delete();
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Query of delete dat
     *
     * @return PicoDatabaseQueryBuilder
     * @throws NoDatabaseConnectionException
     */
    public function deleteQuery()
    {
        if($this->_database != null && ($this->_database->getDatabaseType() != null && $this->_database->getDatabaseType() != ""))
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->deleteQuery();
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Get MagicObject with WHERE specification
     *
     * @param PicoSpecification $specification Specification
     * @return PicoDatabasePersistenceExtended
     */
    public function where($specification)
    {
        if($this->_database != null && ($this->_database->getDatabaseType() != null && $this->_database->getDatabaseType() != ""))
        {
            $persist = new PicoDatabasePersistenceExtended($this->_database, $this);
            return $persist->whereWithSpecification($specification);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Modify null properties
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return void
     */
    private function modifyNullProperties($propertyName, $propertyValue)
    {
        if($propertyValue === null && !isset($this->_nullProperties[$propertyName]))
        {
            $this->_nullProperties[$propertyName] = true;
        }
        if($propertyValue != null && isset($this->_nullProperties[$propertyName]))
        {
            unset($this->_nullProperties[$propertyName]);
        }
    }

    /**
     * Set property value
     *
     * @param string $propertyName Property name
     * @param mixed|null
     * @param boolean $skipModifyNullProperties Skip modify null properties
     * @return self
     */
    public function set($propertyName, $propertyValue, $skipModifyNullProperties = false)
    {
        $var = PicoStringUtil::camelize($propertyName);
        $this->{$var} = $propertyValue;
        if(!$skipModifyNullProperties && $propertyValue === null)
        {
            $this->modifyNullProperties($var, $propertyValue);
        }
        return $this;
    }

    /**
     * Get property value
     *
     * @param string $propertyName Property name
     * @return mixed|null
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : null;
    }

    /**
     * Get property value
     *
     * @param string $propertyName Property name
     * @param mixed|null $defaultValue Property value
     * @return mixed|null
     */
    public function getOrDefault($propertyName, $defaultValue = null)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : $defaultValue;
    }

    /**
     * Set property value
     *
     * @param string $propertyName Property name
     * @param self
     */
    public function __set($propertyName, $propertyValue)
    {
        return $this->set($propertyName, $propertyValue);
    }

    /**
     * Get property value
     *
     * @param string $propertyName Property name
     * @return mixed|null
     */
    public function __get($propertyName)
    {
        $propertyName = lcfirst($propertyName);
        if($this->__isset($propertyName))
        {
            return $this->get($propertyName);
        }
    }

    /**
     * Check if property has been set or not or has null value
     *
     * @param string $propertyName Property name
     * @return boolean
     */
    public function __isset($propertyName)
    {
        $propertyName = lcfirst($propertyName);
        return isset($this->$propertyName);
    }

    /**
     * Unset property value
     *
     * @param string $propertyName Property name
     * @return void
     */
    public function __unset($propertyName)
    {
        $propertyName = lcfirst($propertyName);
        unset($this->$propertyName);
    }

    /**
     * Copy value from other object
     *
     * @param self|mixed $source Source data
     * @param array $filter Filter
     * @param boolean $includeNull Flag include null
     * @return void
     */
    public function copyValueFrom($source, $filter = null, $includeNull = false)
    {
        if($filter != null)
        {
            $tmp = array();
            $index = 0;
            foreach($filter as $val)
            {
                $tmp[$index] = trim(PicoStringUtil::camelize($val));
                $index++;
            }
            $filter = $tmp;
        }
        $values = $source->value();
        foreach($values as $property=>$value)
        {
            if(
                ($filter == null || (is_array($filter) && !empty($filter) && in_array($property, $filter)))
                &&
                ($includeNull || $value != null)
                )
            {
                $this->set($property, $value);
            }
        }
    }

    /**
     * Unset property value
     *
     * @param string $propertyName Property name
     * @param boolean $skipModifyNullProperties Skip modify null properties
     * @return self
     */
    private function removeValue($propertyName, $skipModifyNullProperties = false)
    {
        return $this->set($propertyName, null, $skipModifyNullProperties);
    }

    /**
     * Get table info
     *
     * @return PicoTableInfo
     */
    public function tableInfo()
    {
        if(!isset($this->tableInfo))
        {
            $this->_persistProp = new PicoDatabasePersistence($this->_database, $this);
            $this->_tableInfoProp = $this->_persistProp->getTableInfo();
        }
        return $this->_tableInfoProp;
    }

    /**
     * Get default value
     *
     * @param boolean $snakeCase Flag to snake case property
     * @return stdClass
     */
    public function defaultValue($snakeCase = false)
    {
        $defaultValue = new stdClass;
        $tableInfo = $this->tableInfo();
        if(isset($tableInfo) && $tableInfo->getDefaultValue() != null)
        {
            foreach($tableInfo->getDefaultValue() as $column)
            {
                if(isset($column[self::KEY_NAME]))
                {
                    $columnName = trim($column[self::KEY_NAME]);
                    if($snakeCase)
                    {
                        $col = PicoStringUtil::snakeize($columnName);
                    }
                    else
                    {
                        $col = $columnName;
                    }
                    $defaultValue->$col = $this->_persistProp->fixData($column[self::KEY_VALUE], $column[self::KEY_PROPERTY_TYPE]);
                }
            }
        }
        return $defaultValue;
    }

    /**
     * Get object value
     *
     * @param boolean $snakeCase Flag to snake case property
     * @return stdClass
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                $value->$key = $val;
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val) {
                $key2 = PicoStringUtil::snakeize($key);
                $value2->$key2 = PicoStringUtil::snakeizeObject($val);
            }
            return $value2;
        }
        return $value;
    }

    /**
     * Get object value
     *
     * @param boolean $snakeCase Flag to snake case property
     * @return stdClass
     */
    public function valueObject($snakeCase = null)
    {
        if($snakeCase === null)
        {
            $snake = $this->_snakeJson();
        }
        else
        {
            $snake = $snakeCase;
        }
        $obj = clone $this;
        foreach($obj as $key=>$value)
        {
            if($value instanceof self)
            {
                $value = $this->stringifyObject($value, $snake);
                $obj->set($key, $value);
            }
        }
        $upperCamel = $this->isUpperCamel();
        if($upperCamel)
        {
            return json_decode(json_encode($this->valueArrayUpperCamel()));
        }
        else
        {
            return $obj->value($snake);
        }
    }

    /**
     * Get object value as associative array
     *
     * @param boolean $snakeCase Flag to snake case property
     * @return array
     */
    public function valueArray($snakeCase = false)
    {
        $value = $this->value($snakeCase);
        return json_decode(json_encode($value), true);
    }

    /**
     * Get object value as associated array with upper case first
     *
     * @return array
     */
    public function valueArrayUpperCamel()
    {
        $obj = clone $this;
        $array = (array) $obj->value();
        $renameMap = array();
        $keys = array_keys($array);
        foreach($keys as $key)
        {
            $renameMap[$key] = ucfirst($key);
        }
        $array = array_combine(array_map(function($el) use ($renameMap) {
            return $renameMap[$el];
        }, array_keys($array)), array_values($array));
        return $array;
    }

    /**
     * Check if JSON naming strategy is snake case or not
     *
     * @return boolean
     */
    protected function _snakeJson()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], 'SNAKE_CASE') == 0
            ;
    }

    /**
     * Check if Yaml naming strategy is snake case or not
     *
     * @return boolean
     */
    protected function _snakeYaml()
    {
        return isset($this->_classParams[self::YAML])
            && isset($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY], 'SNAKE_CASE') == 0
            ;
    }

    /**
     *  Check if JSON naming strategy is upper camel case or not
     *
     * @return boolean
     */
    protected function isUpperCamel()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], 'UPPER_CAMEL_CASE') == 0
            ;
    }

    /**
     * Check if JSON naming strategy is camel case or not
     *
     * @return boolean
     */
    protected function _camel()
    {
        return !$this->_snakeJson();
    }

    /**
     * Check if JSON naming strategy is snake case or not
     *
     * @return boolean
     */
    protected function _pretty()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON]['prettify'])
            && strcasecmp($this->_classParams[self::JSON]['prettify'], 'true') == 0
            ;
    }

    /**
     * Check if data is not null and not empty
     *
     * @param mixed $value Value to be checked
     * @return boolean
     */
    private function _notNullAndNotEmpty($value)
    {
        return $value != null && !empty($value);
    }

    /**
     * Property list
     * @var boolean $reflectSelf Flag to reflect self
     * @var boolean $asArrayProps Flag to convert properties as array
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
     * List all
     *
     * @param PicoSpecification $specification Specification
     * @param PicoPageable|string $pageable Pageable
     * @param PicoSortable|string $sortable Sortable
     * @param boolean $passive Flag that object is passive
     * @param array $subqueryMap Subquery map
     * @return PicoPageData
     * @throws NoRecordFoundException if no record found
     * @throws NoDatabaseConnectionException if no database connection
     */
    public function listAll($specification = null, $pageable = null, $sortable = null, $passive = false, $subqueryMap = null)
    {
        return $this->findAll($specification, $pageable, $sortable, $passive, $subqueryMap);
    }

    /**
     * Check if database is connected or not
     *
     * @return boolean
     */
    private function _databaseConnected()
    {
        return $this->_database != null && $this->_database->isConnected();
    }

    /**
     * Count data
     *
     * @param PicoDatabasePersistence $persist Database persistence
     * @param PicoSpecification $specification Specification
     * @param PicoPageable|string $pageable Pageable
     * @param PicoSortable $sortable Sortable
     * @param integer $findOption Find option
     * @param array $result Result
     * @return integer
     */
    private function countData($persist, $specification, $pageable, $sortable, $findOption = 0, $result = null)
    {
        if($findOption & self::FIND_OPTION_NO_COUNT_DATA)
        {
            if(isset($result) && is_array($result))
            {
                $match = count($result);
            }
            else
            {
                $match = 0;
            }
        }
        else
        {
            $match = $persist->countAll($specification, $pageable, $sortable);
        }
        return $match;
    }

    /**
     * Find all
     *
     * @param PicoSpecification $specification Specification
     * @param PicoSortable|string $sortable Sortable
     * @param array $subqueryMap Subquery map
     * @param integer $findOption Find option
     * @return self
     * @throws NoRecordFoundException if no record found
     * @throws NoDatabaseConnectionException if no database connection
     */
    public function findOne($specification = null, $sortable = null, $subqueryMap = null)
    {
        try
        {
            if($this->_databaseConnected())
            {
                $persist = new PicoDatabasePersistence($this->_database, $this);
                $result = $persist->findOne($specification, $sortable, $subqueryMap);
                if(isset($result) && is_array($result) && !empty($result))
                {
                    $this->loadData($result[0]);
                    return $this;
                }
                else
                {
                    throw new NoRecordFoundException("No record found");
                }
            }
            else
            {
                throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
            }
        }
        catch(FindOptionException $e)
        {
            throw new FindOptionException($e->getMessage());
        }
        catch(NoRecordFoundException $e)
        {
            throw new NoRecordFoundException($e->getMessage());
        }
        catch(Exception $e)
        {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * Find all
     *
     * @param PicoSpecification $specification Specification
     * @param PicoPageable|string $pageable Pageable
     * @param PicoSortable|string $sortable Sortable
     * @param boolean $passive Flag that object is passive
     * @param array $subqueryMap Subquery map
     * @param integer $findOption Find option
     * @return PicoPageData
     * @throws NoRecordFoundException if no record found
     * @throws NoDatabaseConnectionException if no database connection
     */
    public function findAll($specification = null, $pageable = null, $sortable = null, $passive = false, $subqueryMap = null, $findOption = self::FIND_OPTION_DEFAULT)
    {
        $startTime = microtime(true);
        try
        {
            $pageData = new PicoPageData(array(), $startTime);
            if($this->_databaseConnected())
            {
                $persist = new PicoDatabasePersistence($this->_database, $this);
                if($findOption & self::FIND_OPTION_NO_FETCH_DATA)
                {
                    $result = null;
                    $stmt = $persist->createPDOStatement($specification, $pageable, $sortable, $subqueryMap);
                }
                else
                {
                    $result = $persist->findAll($specification, $pageable, $sortable, $subqueryMap);
                    $stmt = null;
                }

                if($pageable != null && $pageable instanceof PicoPageable)
                {
                    $match = $this->countData($persist, $specification, $pageable, $sortable, $findOption, $result);
                    $pageData = new PicoPageData($this->toArrayObject($result, $passive), $startTime, $match, $pageable, $stmt, $this, $subqueryMap);
                }
                else
                {
                    $match = $this->countData($persist, $specification, $pageable, $sortable, $findOption, $result);
                    $pageData = new PicoPageData($this->toArrayObject($result, $passive), $startTime, $match, null, $stmt, $this, $subqueryMap);
                }
                return $pageData->setFindOption($findOption);
            }
            else
            {
                throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
            }
        }
        catch(FindOptionException $e)
        {
            throw new FindOptionException($e->getMessage());
        }
        catch(NoRecordFoundException $e)
        {
            throw new NoRecordFoundException($e->getMessage());
        }
        catch(Exception $e)
        {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * Find all record without filter and sort by primary key asc
     *
     * @return PicoPageData
     */
    public function findAllAsc()
    {
        $persist = new PicoDatabasePersistence($this->_database, $this);
        $result = $persist->findAll(null, null, PicoSort::ORDER_TYPE_ASC);
        $startTime = microtime(true);
        return new PicoPageData($this->toArrayObject($result, false), $startTime);
    }

    /**
     * Find all record without filter and sort by primary key desc
     *
     * @return PicoPageData
     */
    public function findAllDesc()
    {
        $persist = new PicoDatabasePersistence($this->_database, $this);
        $result = $persist->findAll(null, null, PicoSort::ORDER_TYPE_DESC);
        $startTime = microtime(true);
        return new PicoPageData($this->toArrayObject($result, false), $startTime);
    }

    /**
     * Find specific
     *
     * @param string $selected Selected field
     * @param PicoSpecification $specification Specification
     * @param PicoPageable|string $pageable Pageable
     * @param PicoSortable|string $sortable Sortable
     * @param boolean $passive Flag that object is passive
     * @param array $subqueryMap Subquery map
     * @param integer $findOption Find option
     * @return PicoPageData
     * @throws NoRecordFoundException if no record found
     * @throws NoDatabaseConnectionException if no database connection
     */
    public function findSpecific($selected, $specification = null, $pageable = null, $sortable = null, $passive = false, $subqueryMap = null, $findOption = self::FIND_OPTION_DEFAULT)
    {
        $startTime = microtime(true);
        try
        {
            $pageData = new PicoPageData(array(), $startTime);
            if($this->_databaseConnected())
            {
                $persist = new PicoDatabasePersistence($this->_database, $this);
                if($findOption & self::FIND_OPTION_NO_FETCH_DATA)
                {
                    $result = null;
                    $stmt = $persist->createPDOStatement($specification, $pageable, $sortable, $subqueryMap, $selected);
                }
                else
                {
                    $result = $persist->findSpecificWithSubquery($selected, $specification, $pageable, $sortable, $subqueryMap);
                    $stmt = null;
                }

                if($pageable != null && $pageable instanceof PicoPageable)
                {
                    $match = $this->countData($persist, $specification, $pageable, $sortable, $findOption, $result);
                    $pageData = new PicoPageData($this->toArrayObject($result, $passive), $startTime, $match, $pageable, $stmt, $this, $subqueryMap);
                }
                else
                {
                    $match = $this->countData($persist, $specification, $pageable, $sortable, $findOption, $result);
                    $pageData = new PicoPageData($this->toArrayObject($result, $passive), $startTime, $match, null, $stmt, $this, $subqueryMap);
                }
                return $pageData->setFindOption($findOption);
            }
            else
            {
                throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
            }
        }
        catch(FindOptionException $e)
        {
            throw new FindOptionException($e->getMessage());
        }
        catch(NoRecordFoundException $e)
        {
            throw new NoRecordFoundException($e->getMessage());
        }
        catch(Exception $e)
        {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * Count all record
     *
     * @param PicoSpecification $specification Specification
     * @param PicoPageable $pageable Pageable
     * @param PicoSortable $sortable Sortable
     * @return integer|false
     * @throws NoRecordFoundException if no record found
     * @throws NoDatabaseConnectionException if no database connection
     */
    public function countAll($specification = null, $pageable = null, $sortable = null)
    {
        $result = false;
        try
        {
            if($this->_databaseConnected())
            {
                $persist = new PicoDatabasePersistence($this->_database, $this);
                if($specification != null && $specification instanceof PicoSpecification)
                {
                    $result = $persist->countAll($specification, $pageable, $sortable);
                }
                else
                {
                    $result = $persist->countAll(null, null, null);
                }
            }
            else
            {
                throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
            }
        }
        catch(Exception $e)
        {
            $result = false;
        }
        return $result;
    }

    /**
     * Find all query
     *
     * @param PicoSpecification $specification Specification
     * @param PicoPageable|string $pageable Pageable
     * @param PicoSortable|string $sortable Sortable
     * @return PicoDatabaseQueryBuilder
     * @throws NoRecordFoundException if no record found
     * @throws NoDatabaseConnectionException if no database connection
     */
    public function findAllQuery($specification = null, $pageable = null, $sortable = null)
    {
        try
        {
            if($this->_databaseConnected())
            {
                $persist = new PicoDatabasePersistence($this->_database, $this);
                $result = $persist->findAllQuery($specification, $pageable, $sortable);
            }
            else
            {
                $result = new PicoDatabaseQueryBuilder($this->_database);
            }
            return $result;
        }
        catch(Exception $e)
        {
            return new PicoDatabaseQueryBuilder($this->_database);
        }
    }

    /**
     * Find one record by primary key value.
     *
     * @param mixed $params Parameters
     * @return self
     */
    public function find($params)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            $result = $persist->find($params);
            if($this->_notNullAndNotEmpty($result))
            {
                $this->loadData($result);
                return $this;
            }
            else
            {
                throw new NoRecordFoundException(self::MESSAGE_NO_RECORD_FOUND);
            }
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Find one record if exists by primary key value.
     *
     * @param array $params Parameters
     * @return self
     */
    public function findIfExists($params)
    {
        try
        {
            return $this->find($params);
        }
        catch(NoRecordFoundException $e)
        {
            return $this;
        }
        catch(NoDatabaseConnectionException $e)
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /**
     * Find by params
     *
     * @param string $method Find method
     * @param mixed $params Parameters
     * @param PicoPageable $pageable Pageable
     * @param PicoSortable|string $sortable Sortable
     * @param boolean $passive Flag that object is passive
     * @return PicoPageData
     */
    private function findBy($method, $params, $pageable = null, $sortable = null, $passive = false)
    {
        $startTime = microtime(true);
        try
        {
            $pageData = null;
            if($this->_databaseConnected())
            {
                $persist = new PicoDatabasePersistence($this->_database, $this);
                $result = $persist->findBy($method, $params, $pageable, $sortable);
                if($pageable != null && $pageable instanceof PicoPageable)
                {
                    $match = $persist->countBy($method, $params);
                    $pageData = new PicoPageData($this->toArrayObject($result, $passive), $startTime, $match, $pageable);
                }
                else
                {
                    $pageData = new PicoPageData($this->toArrayObject($result, $passive), $startTime);
                }
            }
            else
            {
                $pageData = new PicoPageData(array(), $startTime);
            }
            return $pageData->setFindOption(self::FIND_OPTION_DEFAULT);
        }
        catch(Exception $e)
        {
            return new PicoPageData(array(), $startTime);
        }
    }

    /**
     * Count data from database
     *
     * @param string $method Find method
     * @param mixed $params Parameters
     * @return integer
     */
    private function countBy($method, $params)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->countBy($method, $params);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Delete by params
     *
     * @param string $method Find method
     * @param mixed $params Parameters
     * @return integer
     */
    private function deleteBy($method, $params)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->deleteBy($method, $params);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Find one with primary key value
     *
     * @param mixed $primaryKeyVal Primary key value
     * @param array $subqueryMap Subquery map
     * @return self
     */
    public function findOneWithPrimaryKeyValue($primaryKeyVal, $subqueryMap = null)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            $result = $persist->findOneWithPrimaryKeyValue($primaryKeyVal, $subqueryMap);
            if($this->_notNullAndNotEmpty($result))
            {
                $this->loadData($result);
                return $this;
            }
            else
            {
                throw new NoRecordFoundException(self::MESSAGE_NO_RECORD_FOUND);
            }
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Find one by params
     *
     * @param string $method Method
     * @param mixed $params Parameters
     * @param PicoSortable|string $sortable
     * @return object
     */
    private function findOneBy($method, $params, $sortable = null)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            $result = $persist->findOneBy($method, $params, $sortable);
            if($this->_notNullAndNotEmpty($result))
            {
                $this->loadData($result);
                return $this;
            }
            else
            {
                throw new NoRecordFoundException(self::MESSAGE_NO_RECORD_FOUND);
            }
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Find one if existsby params
     *
     * @param string $method Method
     * @param mixed $params Parameters
     * @param PicoSortable|string $sortable
     * @return object
     */
    private function findOneIfExistsBy($method, $params, $sortable = null)
    {
        try
        {
            return $this->findOneBy($method, $params, $sortable);
        }
        catch(NoRecordFoundException $e)
        {
            return $this;
        }
        catch(NoDatabaseConnectionException $e)
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
        catch(Exception $e)
        {
            throw $e;
        }
    }

    /**
     * Delete one by params
     *
     * @param string $method Method
     * @param mixed $params Parameters
     * @param PicoSortable|string $sortable
     * @return boolean
     */
    private function deleteOneBy($method, $params)
    {
        if($this->_databaseConnected())
        {
            try
            {
                $data = $this->findOneBy($method, $params);
                $data->delete();
                return true;
            }
            catch(NoRecordFoundException $e)
            {
                return false;
            }
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Exists by params
     *
     * @param string $method Method
     * @param mixed $params Parameters
     * @param string $orderType Order type
     * @return boolean
     */
    private function existsBy($method, $params)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            return $persist->existsBy($method, $params);
        }
        else
        {
            throw new NoDatabaseConnectionException(self::MESSAGE_NO_DATABASE_CONNECTION);
        }
    }

    /**
     * Convert boolean to text
     *
     * @param string $propertyName Property name
     * @param string[] $params Parameters
     * @return string
     */
    private function booleanToTextBy($propertyName, $params)
    {
        $value = $this->get($propertyName);
        if(!isset($value))
        {
            $boolVal = false;
        }
        else
        {
            $boolVal = $value === true || $value == 1 || $value = "1";
        }
        return $boolVal?$params[0]:$params[1];
    }

    /**
     * Convert to array object
     *
     * @param array $result Result
     * @param boolean $passive Flag that object is passive
     * @return array
     */
    private function toArrayObject($result, $passive = false)
    {
        $instance = array();
        $index = 0;
        if(isset($result) && is_array($result))
        {
            foreach($result as $value)
            {
                $className = get_class($this);
                $instance[$index] = new $className($value, $passive ? null : $this->_database);
                $index++;
            }
        }
        return $instance;
    }

    /**
     * Get number of property of the object
     *
     * @return integer
     */
    public function size()
    {
        $parentProps = $this->propertyList(true, true);
        $length = 0;
        foreach ($this as $key => $val) {
            if(!in_array($key, $parentProps))
            {
                $length++;
            }
        }
        return $length;
    }

    /**
     * Magic method called when user call any undefined method. __call method will check the prefix of called method and call appropriated method according to its name and its parameters.
     * hasValue &raquo; check if property has value. This method not require database connection.
     * isset &raquo; gcheck if property has value. String will be convert to number first. This method not require database connection.
     * is &raquo; get property value as boolean. Number will true if it's value is 1. String will be convert to number first. This method not require database connection.
     * equals &raquo; check if property value is equals to given value. This method not require database connection.
     * get &raquo; get property value. This method not require database connection.
     * set &raquo; set property value. This method not require database connection.
     * unset &raquo; unset property value. This method not require database connection.
     * push &raquo; add new array element of property. This method not require database connection.
     * pop &raquo; remove last element of property. This method not require database connection.
     * findOneBy &raquo; search data from database and return one record. This method require database connection.
     * findOneIfExistsBy &raquo; search data from database by any column values and return one record. This method require database connection.
     * deleteOneBy &raquo; delete data from database by any column values and return one record. This method require database connection.
     * findFirstBy &raquo; search data from database by any column values and return first record. This method require database connection.
     * findFirstIfExistsBy &raquo; search data from database by any column values and return first record. This method require database connection.
     * findLastBy &raquo; search data from database by any column values and return last record. This method require database connection.
     * findLastIfExistsBy &raquo; search data from database by any column values and return last record. This method require database connection.
     * findBy &raquo; search multiple record data from database by any column values. This method require database connection.
     * findAscBy &raquo; search multiple record data from database order by primary keys ascending. This method require database connection.
     * findDescBy &raquo; search multiple record data from database order by primary keys descending. This method require database connection.
     * listBy &raquo; search multiple record data from database. Similar to findBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.
     * listAscBy &raquo; search multiple record data from database order by primary keys ascending. Similar to findAscBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.
     * listDescBy &raquo; search multiple record data from database order by primary keys descending. Similar to findDescBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.
     * listAllAsc &raquo; search multiple record data from database without filter order by primary keys ascending. Similar to findAllAsc but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.
     * listAllDesc &raquo; search multiple record data from database without filter order by primary keys descending. Similar to findAllDesc but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.
     * countBy &raquo; count data from database.
     * countBy &raquo; count data from database.
     * existsBy &raquo; check data from database. This method require database connection.
     * deleteBy &raquo; delete data from database without read it first. This method require database connection.
     * booleanToTextBy &raquo; convert boolean value to yes/no or true/false depend on parameters given. Example: $result = booleanToTextByActive("Yes", "No"); If $obj->active is true, $result will be "Yes" otherwise "No". This method not require database connection.
     * booleanToSelectedBy &raquo; Create attribute selected="selected" for form. This method not require database connection.
     * booleanToCheckedBy &raquo; Create attribute checked="checked" for form. This method not require database connection.
     * startsWith &raquo; Check that value starts with any string. This method not require database connection.
     * endsWith &raquo; Check that value ends with any string. This method not require database connection.
     *
     * @param string $method Method name
     * @param mixed $params Parameters
     * @return mixed|null
     */
    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "hasValue", 8) === 0) {
            $var = lcfirst(substr($method, 8));
            return isset($this->$var);
        }
        else if (strncasecmp($method, "isset", 5) === 0) {
            $var = lcfirst(substr($method, 5));
            return isset($this->$var);
        }
        else if (strncasecmp($method, "is", 2) === 0) {
            $var = lcfirst(substr($method, 2));
            return isset($this->$var) ? $this->$var == 1 : false;
        }
        else if (strncasecmp($method, "equals", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            return isset($this->$var) && $this->$var == $params[0];
        }
        else if (strncasecmp($method, "get", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return isset($this->$var) ? $this->$var : null;
        }
        else if (strncasecmp($method, "set", 3) === 0 && isset($params) && isset($params[0]) && !$this->_readonly) {
            $var = lcfirst(substr($method, 3));
            $this->$var = $params[0];
            $this->modifyNullProperties($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "unset", 5) === 0 && !$this->_readonly) {
            $var = lcfirst(substr($method, 5));
            $this->removeValue($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "push", 4) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 4));
            if(!isset($this->$var) || !is_array($this->$var))
            {
                $this->$var = array();
            }
            array_push($this->$var, isset($params) && isset($params[0]) ? $params[0] : null);
            return $this;
        }
        else if (strncasecmp($method, "pop", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            if(isset($this->$var) && is_array($this->$var))
            {
                return array_pop($this->$var);
            }
            return null;
        }
        else if (strncasecmp($method, "findOneBy", 9) === 0) {
            $var = lcfirst(substr($method, 9));
            $sortable = PicoDatabaseUtil::sortableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findOneBy($var, $parameters, $sortable);
        }
        else if (strncasecmp($method, "findOneIfExistsBy", 17) === 0) {
            $var = lcfirst(substr($method, 17));
            $sortable = PicoDatabaseUtil::sortableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findOneIfExistsBy($var, $parameters, $sortable);
        }
        else if (strncasecmp($method, "deleteOneBy", 11) === 0) {
            $var = lcfirst(substr($method, 11));
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->deleteOneBy($var, $parameters);
        }
        else if (strncasecmp($method, "findFirstBy", 11) === 0) {
            $var = lcfirst(substr($method, 11));
            return $this->findOneBy($var, $params, PicoDatabasePersistence::ORDER_ASC);
        }
        else if (strncasecmp($method, "findFirstIfExistsBy", 19) === 0) {
            $var = lcfirst(substr($method, 19));
            return $this->findOneIfExistsBy($var, $params, PicoDatabasePersistence::ORDER_ASC);
        }
        else if (strncasecmp($method, "findLastBy", 10) === 0) {
            $var = lcfirst(substr($method, 10));
            return $this->findOneBy($var, $params, PicoDatabasePersistence::ORDER_DESC);
        }
        else if (strncasecmp($method, "findLastIfExistsBy", 18) === 0) {
            $var = lcfirst(substr($method, 18));
            return $this->findOneIfExistsBy($var, $params, PicoDatabasePersistence::ORDER_DESC);
        }
        else if (strncasecmp($method, "findBy", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // get sortable
            $sortable = PicoDatabaseUtil::sortableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, $sortable);
        }
        else if (strncasecmp($method, "findAscBy", 9) === 0) {
            $var = lcfirst(substr($method, 9));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, PicoDatabasePersistence::ORDER_ASC);
        }
        else if (strncasecmp($method, "findDescBy", 10) === 0) {
            $var = lcfirst(substr($method, 10));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, PicoDatabasePersistence::ORDER_DESC);
        }
        else if (strncasecmp($method, "listBy", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // get sortable
            $sortable = PicoDatabaseUtil::sortableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, $sortable, true);
        }
        else if (strncasecmp($method, "listAscBy", 9) === 0) {
            $var = lcfirst(substr($method, 9));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, PicoDatabasePersistence::ORDER_ASC, true);
        }
        else if (strncasecmp($method, "listDescBy", 10) === 0) {
            $var = lcfirst(substr($method, 10));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, PicoDatabasePersistence::ORDER_DESC, true);
        }
        else if ($method == "listAllAsc") {
            // get spefification
            $specification = PicoDatabaseUtil::specificationFromParams($params);
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            return $this->findAll($specification, $pageable, PicoDatabasePersistence::ORDER_ASC, true);
        }
        else if ($method == "listAllDesc") {
            // get spefification
            $specification = PicoDatabaseUtil::specificationFromParams($params);
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            return $this->findAll($specification, $pageable, PicoDatabasePersistence::ORDER_DESC, true);
        }
        else if (strncasecmp($method, "countBy", 7) === 0) {
            $var = lcfirst(substr($method, 7));
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->countBy($var, $parameters);
        }
        else if (strncasecmp($method, "existsBy", 8) === 0) {
            $var = lcfirst(substr($method, 8));
            return $this->existsBy($var, $params);
        }
        else if (strncasecmp($method, "deleteBy", 8) === 0) {
            $var = lcfirst(substr($method, 8));
            return $this->deleteBy($var, $params);
        }
        else if (strncasecmp($method, "booleanToTextBy", 15) === 0) {
            $prop = lcfirst(substr($method, 15));
            return $this->booleanToTextBy($prop, $params);
        }
        else if (strncasecmp($method, "booleanToSelectedBy", 19) === 0) {
            $prop = lcfirst(substr($method, 19));
            return $this->booleanToTextBy($prop, array(self::ATTR_SELECTED, ''));
        }
        else if (strncasecmp($method, "booleanToCheckedBy", 18) === 0) {
            $prop = lcfirst(substr($method, 18));
            return $this->booleanToTextBy($prop, array(self::ATTR_CHECKED, ''));
        }
        else if (strncasecmp($method, "createSelected", 14) === 0) {
            $var = lcfirst(substr($method, 14));
            if(isset($params) && isset($params[0]))
            {
                return isset($this->$var) && $this->$var == $params[0] ? self::ATTR_SELECTED : '';
            }
            else
            {
                return isset($this->$var) && $this->$var == 1 ? self::ATTR_SELECTED : '';
            }
        }
        else if (strncasecmp($method, "createChecked", 13) === 0) {
            $var = lcfirst(substr($method, 13));
            if(isset($params) && isset($params[0]))
            {
                return isset($this->$var) && $this->$var == $params[0] ? self::ATTR_CHECKED : '';
            }
            else
            {
                return isset($this->$var) && $this->$var == 1 ? self::ATTR_CHECKED : '';
            }
        }
        else if (strncasecmp($method, "startsWith", 10) === 0) {
            $var = lcfirst(substr($method, 10));
            $value = $params[0];
            $caseSensitive = isset($params[1]) && $params[1];
            $haystack = $this->$var;
            return PicoStringUtil::startsWith($haystack, $value, $caseSensitive);
        }
        else if (strncasecmp($method, "endsWith", 8) === 0) {
            $var = lcfirst(substr($method, 8));
            $value = $params[0];
            $caseSensitive = isset($params[1]) && $params[1];
            $haystack = $this->$var;
            return PicoStringUtil::endsWith($haystack, $value, $caseSensitive);
        }
        else if (strncasecmp($method, "label", 5) === 0) {
            $var = lcfirst(substr($method, 5));
            if(empty($var))
            {
                $var = PicoStringUtil::camelize($params[0]);
            }
            if(!empty($var) && !isset($this->_label[$var]))
            {
                $reflexProp = new PicoAnnotationParser(get_class($this), $var, PicoAnnotationParser::PROPERTY);
                $parameters = $reflexProp->getParameters();
                if(isset($parameters['Label']))
                {
                    $label = $reflexProp->parseKeyValueAsObject($parameters['Label']);
                    $this->_label[$var] = $label->getContent();
                }
            }
            if(isset($this->_label[$var]))
            {
                return $this->_label[$var];
            }
            return "";
        }
        else if(strncasecmp($method, "option", 6) === 0) {
            $var = lcfirst(substr($method, 6));
            return isset($this->$var) && ($this->$var == 1 || $this->$var === true) ? $params[0] : $params[1];
        }
        else if(strncasecmp($method, "notNull", 7) === 0) {
            $var = lcfirst(substr($method, 7));
            return isset($this->$var);
        }
        else if(strncasecmp($method, "notEmpty", 8) === 0) {
            $var = lcfirst(substr($method, 8));
            return isset($this->$var) && !empty($this->$var);
        }
        else if(strncasecmp($method, "notZero", 7) === 0) {
            $var = lcfirst(substr($method, 7));
            return isset($this->$var) && $this->$var != 0;
        }
        else if (strncasecmp($method, "notEquals", 9) === 0) {
            $var = lcfirst(substr($method, 9));
            return isset($this->$var) && $this->$var != $params[0];
        }
    }

    /**
     * Magic method to stringify object
     *
     * @return string
     */
    public function __toString()
    {
        $snake = $this->_snakeJson();
        $pretty = $this->_pretty();
        $flag = $pretty ? JSON_PRETTY_PRINT : 0;
        $obj = clone $this;
        foreach($obj as $key=>$value)
        {
            if($value instanceof self)
            {
                $value = $this->stringifyObject($value, $snake);
                $obj->set($key, $value);
            }
        }
        $upperCamel = $this->isUpperCamel();
        if($upperCamel)
        {
            $value = $this->valueArrayUpperCamel();
            return json_encode($value, $flag);
        }
        else
        {
            return json_encode($obj->value($snake), $flag);
        }
    }

    /**
     * Stringify object
     *
     * @param self $value Value
     * @param boolean $snake Flag to snake case property
     * @return mixed
     */
    private function stringifyObject($value, $snake)
    {
        if(is_array($value))
        {
            foreach($value as $key2=>$val2)
            {
                if($val2 instanceof self)
                {
                    $value[$key2] = $val2->stringifyObject($val2, $snake);
                }
            }
        }
        else if(is_object($value))
        {
            foreach($value as $key2=>$val2)
            {
                if($val2 instanceof self)
                {

                    $value->{$key2} = $val2->stringifyObject($val2, $snake);
                }
            }
        }
        return $value->value($snake);
    }

    /**
     * Dumps a PHP value to a YAML string.
     *
     * The dump method, when supplied with an array, will do its best
     * to convert the array into friendly YAML.
     *
     * @param int|null $inline The level where you switch to inline YAML. If $inline set to NULL, MagicObject will use maximum value of array depth
     * @param int $indent The amount of spaces to use for indentation of nested nodes
     * @param int $flags  A bit field of DUMP_* constants to customize the dumped YAML string
     *
     * @return string A YAML string representing the original PHP value
     */
    public function dumpYaml($inline = null, $indent = 4, $flags = 0)
    {
        $snake = $this->_snakeYaml();
        $input = $this->valueArray($snake);
        return PicoYamlUtil::dump($input, $inline, $indent, $flags);
    }
}
