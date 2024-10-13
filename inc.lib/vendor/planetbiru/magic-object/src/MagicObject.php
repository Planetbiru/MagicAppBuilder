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
 * Class for creating a magic object.
 * A magic object is an instance created from any class, allowing the user to add any property with any name and value. It can load data from INI files, YAML files, JSON files, and databases.
 * Users can create entities from database tables and perform insert, select, update, and delete operations on records in the database.
 * Users can also create properties from other entities using the full name of the class (namespace + class name).
 * 
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
     * Indicates whether the object is read-only.
     *
     * @var bool
     */
    private $_readonly = false; // NOSONAR

    /**
     * Database connection instance.
     *
     * @var PicoDatabase
     */
    private $_database; // NOSONAR

    /**
     * Class parameters.
     *
     * @var array
     */
    private $_classParams = array(); // NOSONAR

    /**
     * List of null properties.
     *
     * @var array
     */
    private $_nullProperties = array(); // NOSONAR

    /**
     * Property labels.
     *
     * @var array
     */
    private $_label = array(); // NOSONAR

    /**
     * Table information instance.
     *
     * @var PicoTableInfo|null
     */
    private $_tableInfoProp = null; // NOSONAR

    /**
     * Database persistence instance.
     *
     * @var PicoDatabasePersistence|null
     */
    private $_persistProp = null; // NOSONAR

    /**
     * Retrieves the list of null properties.
     *
     * @return array The list of null properties.
     */
    public function nullPropertyList()
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
     * Load data into the object.
     *
     * @param mixed $data Data to load
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
     * Load data from an INI string.
     *
     * @param string $rawData Raw INI data
     * @param bool $systemEnv Flag to indicate whether to use environment variables
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
     * Load data from an INI file.
     *
     * @param string $path File path to the INI file
     * @param bool $systemEnv Flag to indicate whether to use environment variables
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
     * Load data from a YAML string.
     *
     * @param string $rawData YAML string
     * @param bool $systemEnv Replace all environment variable values
     * @param bool $asObject Result as an object instead of an array
     * @param bool $recursive Convert all objects to MagicObject
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
     * Load data from a YAML file.
     *
     * @param string $path File path to the YAML file
     * @param bool $systemEnv Replace all environment variable values
     * @param bool $asObject Result as an object instead of an array
     * @param bool $recursive Convert all objects to MagicObject
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
     * Load data from a JSON string.
     *
     * @param string $rawData JSON string
     * @param bool $systemEnv Replace all environment variable values
     * @param bool $asObject Result as an object instead of an array
     * @param bool $recursive Convert all objects to MagicObject
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
     * Load data from a JSON file.
     *
     * @param string $path File path to the JSON file
     * @param bool $systemEnv Replace all environment variable values
     * @param bool $asObject Result as an object instead of an array
     * @param bool $recursive Convert all objects to MagicObject
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
     * Set the read-only state of the object.
     *
     * When set to read-only, setters will not change the value of its properties,
     * but loadData will still function normally.
     *
     * @param bool $readonly Flag to set the object as read-only
     * @return self
     */
    protected function readOnly($readonly)
    {
        $this->_readonly = $readonly;
        return $this;
    }

    /**
     * Set the database connection.
     *
     * @param PicoDatabase $database Database connection
     * @return self
     */
    public function withDatabase($database)
    {
        $this->_database = $database;
        return $this;
    }

    /**
     * Set or get the current database connection.
     *
     * If the parameter is not empty, set the current database to the provided value.
     * Otherwise, return the current database or null.
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
     * Remove properties except for the specified ones.
     *
     * @param object|array $sourceData Data to filter
     * @param array $propertyNames Names of properties to retain
     * @return object|array Filtered data
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
     * Save to database.
     *
     * @param bool $includeNull If TRUE, all properties will be saved to the database, including null. If FALSE, only columns with non-null values will be saved.
     * @return PDOStatement
     * @throws NoDatabaseConnectionException|NoRecordFoundException|PDOException
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
     * Query to save data.
     *
     * @param bool $includeNull If TRUE, all properties will be saved to the database, including null. If FALSE, only columns with non-null values will be saved.
     * @return PicoDatabaseQueryBuilder
     * @throws NoDatabaseConnectionException|NoRecordFoundException
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
     * Select data from the database.
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
     * Select all data from the database.
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
     * Query to select data.
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
     * Insert into the database.
     *
     * @param bool $includeNull If TRUE, all properties will be saved to the database, including null. If FALSE, only columns with non-null values will be saved.
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
     * Get the query for inserting data.
     *
     * @param bool $includeNull If TRUE, all properties will be saved to the database, including null. If FALSE, only columns with non-null values will be saved.
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
     * Update data in the database.
     *
     * @param bool $includeNull If TRUE, all properties will be saved to the database, including null. If FALSE, only columns with non-null values will be saved.
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
     * Get the query for updating data.
     *
     * @param bool $includeNull If TRUE, all properties will be saved to the database, including null. If FALSE, only columns with non-null values will be saved.
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
     * Delete data from the database.
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
     * Get the query for deleting data.
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
     * Get MagicObject with WHERE specification.
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
     * Modify null properties.
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
     * Set property value.
     *
     * @param string $propertyName Property name
     * @param mixed|null $propertyValue Property value
     * @param bool $skipModifyNullProperties Skip modifying null properties
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
     * Adds an element to the end of an array property.
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self
     */
    public function push($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->$var))
        {
            $this->$var = array();
        }
        array_push($this->$var, $propertyValue);
        return $this;
    }
    
    /**
     * Adds an element to the end of an array property (alias for push).
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self
     */
    public function append($propertyName, $propertyValue)
    {
        return $this->push($propertyName, $propertyValue);
    }
    
    /**
     * Adds an element to the beginning of an array property.
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self
     */
    public function unshift($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->$var))
        {
            $this->$var = array();
        }
        array_unshift($this->$var, $propertyValue);
        return $this;
    }
    
    /**
     * Adds an element to the beginning of an array property (alias for unshift).
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self
     */
    public function prepend($propertyName, $propertyValue)
    {
        return $this->unshift($propertyName, $propertyValue);
    }

    /**
     * Remove the last element of an array property and return it.
     *
     * @param string $propertyName Property name
     * @return mixed
     */
    public function pop($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->$var) && is_array($this->$var))
        {
            return array_pop($this->$var);
        }
        return null;
    }
    
    /**
     * Remove the first element of an array property and return it.
     *
     * @param string $propertyName Property name
     * @return mixed
     */
    public function shift($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->$var) && is_array($this->$var))
        {
            return array_shift($this->$var);
        }
        return null;
    }

    /**
     * Get property value.
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
     * Get property value or a default value if not set.
     *
     * @param string $propertyName Property name
     * @param mixed|null $defaultValue Default value
     * @return mixed|null
     */
    public function getOrDefault($propertyName, $defaultValue = null)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->$var) ? $this->$var : $defaultValue;
    }

    /**
     * Set property value (magic setter).
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     */
    public function __set($propertyName, $propertyValue)
    {
        return $this->set($propertyName, $propertyValue);
    }

    /**
     * Get property value (magic getter).
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
     * Check if a property has been set or not (including null).
     *
     * @param string $propertyName Property name
     * @return bool
     */
    public function __isset($propertyName)
    {
        $propertyName = lcfirst($propertyName);
        return isset($this->$propertyName);
    }

    /**
     * Unset property value.
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
     * Copy values from another object.
     *
     * @param self|mixed $source Source data
     * @param array|null $filter Filter
     * @param bool $includeNull Flag to include null values
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
     * Remove property value and set it to null.
     *
     * @param string $propertyName Property name
     * @param bool $skipModifyNullProperties Skip modifying null properties
     * @return self
     */
    private function removeValue($propertyName, $skipModifyNullProperties = false)
    {
        return $this->set($propertyName, null, $skipModifyNullProperties);
    }

    /**
     * Get table information
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
     * Get default values for properties
     *
     * @param boolean $snakeCase Flag indicating whether to convert property names to snake case
     * @return stdClass An object containing default values
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
     * Get the object values
     *
     * @param boolean $snakeCase Flag indicating whether to convert property names to snake case
     * @return stdClass An object containing the values of the properties
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
     * Get the object value as a specified format
     *
     * @param boolean|null $snakeCase Flag indicating whether to convert property names to snake case; if null, default behavior is used
     * @return stdClass An object representing the value of the instance
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
     * Get the object value as an associative array
     *
     * @param boolean $snakeCase Flag indicating whether to convert property names to snake case
     * @return array An associative array representing the object values
     */
    public function valueArray($snakeCase = false)
    {
        $value = $this->value($snakeCase);
        return json_decode(json_encode($value), true);
    }

    /**
     * Get the object value as an associative array with the first letter of each key in upper camel case
     *
     * @return array An associative array with keys in upper camel case
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
     * Check if the JSON naming strategy is snake case
     *
     * @return boolean True if the naming strategy is snake case; otherwise, false
     */
    protected function _snakeJson()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], 'SNAKE_CASE') == 0
            ;
    }

    /**
     * Check if the YAML naming strategy is snake case
     *
     * @return boolean True if the naming strategy is snake case; otherwise, false
     */
    protected function _snakeYaml()
    {
        return isset($this->_classParams[self::YAML])
            && isset($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY], 'SNAKE_CASE') == 0
            ;
    }

    /**
     * Check if the JSON naming strategy is upper camel case
     *
     * @return boolean True if the naming strategy is upper camel case; otherwise, false
     */
    protected function isUpperCamel()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY])
            && strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], 'UPPER_CAMEL_CASE') == 0
            ;
    }

    /**
     * Check if the JSON naming strategy is camel case
     *
     * @return boolean True if the naming strategy is camel case; otherwise, false
     */
    protected function _camel()
    {
        return !$this->_snakeJson();
    }

    /**
     * Check if the JSON output should be prettified
     *
     * @return boolean True if JSON output is set to be prettified; otherwise, false
     */
    protected function _pretty()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON]['prettify'])
            && strcasecmp($this->_classParams[self::JSON]['prettify'], 'true') == 0
            ;
    }

    /**
     * Check if a value is not null and not empty
     *
     * @param mixed $value The value to check
     * @return boolean True if the value is not null and not empty; otherwise, false
     */
    private function _notNullAndNotEmpty($value)
    {
        return $value != null && !empty($value);
    }

    /**
     * Get a list of properties
     *
     * @param boolean $reflectSelf Flag indicating whether to reflect properties of the current class
     * @param boolean $asArrayProps Flag indicating whether to return properties as an array
     * @return array An array of property names or ReflectionProperty objects
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
     * List all records
     *
     * @param PicoSpecification|null $specification The specification for filtering
     * @param PicoPageable|string|null $pageable The pagination information
     * @param PicoSortable|string|null $sortable The sorting criteria
     * @param boolean $passive Flag indicating whether the object is passive
     * @param array|null $subqueryMap An optional map of subqueries
     * @return PicoPageData The paginated data
     * @throws NoRecordFoundException if no records are found
     * @throws NoDatabaseConnectionException if no database connection is established
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
     * Count the data based on specifications
     *
     * @param PicoDatabasePersistence $persist The persistence object
     * @param PicoSpecification|null $specification The specification for filtering
     * @param PicoPageable|string|null $pageable The pagination information
     * @param PicoSortable|string|null $sortable The sorting criteria
     * @param int $findOption The find option
     * @param array|null $result The result set
     * @return int The count of matching records
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
     * Find one record based on specifications
     *
     * @param PicoSpecification|null $specification The specification for filtering
     * @param PicoSortable|string|null $sortable The sorting criteria
     * @param array|null $subqueryMap An optional map of subqueries
     * @return self The found instance
     * @throws NoRecordFoundException if no record is found
     * @throws NoDatabaseConnectionException if no database connection is established
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
     * Find all records based on specifications
     *
     * @param PicoSpecification|null $specification The specification for filtering
     * @param PicoPageable|string|null $pageable The pagination information
     * @param PicoSortable|string|null $sortable The sorting criteria
     * @param boolean $passive Flag indicating whether the object is passive
     * @param array|null $subqueryMap An optional map of subqueries
     * @param int $findOption The find option
     * @return PicoPageData The paginated data
     * @throws NoRecordFoundException if no records are found
     * @throws NoDatabaseConnectionException if no database connection is established
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
     * Find all records without filters, sorted by primary key in ascending order
     *
     * @return PicoPageData The paginated data
     */
    public function findAllAsc()
    {
        $persist = new PicoDatabasePersistence($this->_database, $this);
        $result = $persist->findAll(null, null, PicoSort::ORDER_TYPE_ASC);
        $startTime = microtime(true);
        return new PicoPageData($this->toArrayObject($result, false), $startTime);
    }

    /**
     * Find all records without filters, sorted by primary key in descending order
     *
     * @return PicoPageData The paginated data
     */
    public function findAllDesc()
    {
        $persist = new PicoDatabasePersistence($this->_database, $this);
        $result = $persist->findAll(null, null, PicoSort::ORDER_TYPE_DESC);
        $startTime = microtime(true);
        return new PicoPageData($this->toArrayObject($result, false), $startTime);
    }

    /**
     * Find specific records
     *
     * @param string $selected The selected field(s)
     * @param PicoSpecification|null $specification The specification for filtering
     * @param PicoPageable|string|null $pageable The pagination information
     * @param PicoSortable|string|null $sortable The sorting criteria
     * @param boolean $passive Flag indicating whether the object is passive
     * @param array|null $subqueryMap An optional map of subqueries
     * @param int $findOption The find option
     * @return PicoPageData The paginated data
     * @throws NoRecordFoundException if no records are found
     * @throws NoDatabaseConnectionException if no database connection is established
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
     * Count all records based on specifications
     *
     * @param PicoSpecification|null $specification The specification for filtering
     * @param PicoPageable|null $pageable The pagination information
     * @param PicoSortable|null $sortable The sorting criteria
     * @return int|false The count of records or false on error
     * @throws NoRecordFoundException if no records are found
     * @throws NoDatabaseConnectionException if no database connection is established
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
     * Build a query to find all records
     *
     * @param PicoSpecification|null $specification The specification for filtering
     * @param PicoPageable|string|null $pageable The pagination information
     * @param PicoSortable|string|null $sortable The sorting criteria
     * @return PicoDatabaseQueryBuilder The query builder
     * @throws NoRecordFoundException if no record is found
     * @throws NoDatabaseConnectionException if no database connection is established
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
     * Find one record by primary key value
     *
     * @param mixed $params The parameters for the search
     * @return self The found instance
     * @throws NoRecordFoundException if no record is found
     * @throws NoDatabaseConnectionException if no database connection is established
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
     * Find one record if it exists by primary key value
     *
     * @param array $params The parameters for the search
     * @return self The found instance or the current instance if not found
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
     * Find records by specified parameters
     *
     * @param string $method The method to find by
     * @param mixed $params The parameters for the search
     * @param PicoSpecification|null $specification The specification for filtering
     * @param PicoPageable|string|null $pageable The pagination information
     * @param PicoSortable|string|null $sortable The sorting criteria
     * @param boolean $passive Flag indicating whether the object is passive
     * @param array|null $subqueryMap An optional map of subqueries
     * @param int $findOption The find option
     * @return PicoPageData The paginated data
     * @throws NoRecordFoundException if no records are found
     * @throws NoDatabaseConnectionException if no database connection is established
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
     * Count data from the database.
     *
     * @param string $method The method used for finding.
     * @param mixed $params The parameters to use for the count.
     * @return int The count of matching records.
     * @throws NoDatabaseConnectionException If there is no database connection.
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
     * Delete records based on parameters.
     *
     * @param string $method The method used for finding.
     * @param mixed $params The parameters to use for the deletion.
     * @return int The number of deleted records.
     * @throws NoDatabaseConnectionException If there is no database connection.
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
     * Find one record using the primary key value.
     *
     * @param mixed $primaryKeyVal The primary key value.
     * @param array|null $subqueryMap Optional subquery map for additional queries.
     * @return self The found instance.
     * @throws NoRecordFoundException If no record is found.
     * @throws NoDatabaseConnectionException If there is no database connection.
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
     * Find one record based on specified parameters.
     *
     * @param string $method The method used for finding.
     * @param mixed $params The parameters to use for the search.
     * @param PicoSortable|string|null $sortable Optional sorting criteria.
     * @return object The found instance.
     * @throws NoRecordFoundException If no record is found.
     * @throws NoDatabaseConnectionException If there is no database connection.
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
     * Find one record if it exists based on parameters.
     *
     * @param string $method The method used for finding.
     * @param mixed $params The parameters to use for the search.
     * @param PicoSortable|string|null $sortable Optional sorting criteria.
     * @return object The found instance or the current instance if not found.
     * @throws NoDatabaseConnectionException If there is no database connection.
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
     * Delete one record based on specified parameters.
     *
     * @param string $method The method used for finding.
     * @param mixed $params The parameters to use for the deletion.
     * @return boolean True on success; otherwise, false.
     * @throws NoDatabaseConnectionException If there is no database connection.
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
     * Check if a record exists based on specified parameters.
     *
     * @param string $method The method used for finding.
     * @param mixed $params The parameters to use for the search.
     * @return boolean True if the record exists; otherwise, false.
     * @throws NoDatabaseConnectionException If there is no database connection.
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
     * Convert a boolean value to text based on the specified property name.
     *
     * @param string $propertyName The property name to check.
     * @param string[] $params The text representations for true and false.
     * @return string The corresponding text representation.
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
     * Convert the result to an array of objects.
     *
     * @param array $result The result set to convert.
     * @param boolean $passive Flag indicating whether the objects are passive.
     * @return array An array of objects.
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
     * Get the number of properties of the object.
     *
     * @return int The number of properties.
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
     * Magic method called when a user calls any undefined method. The __call method checks the prefix of the called method and calls the appropriate method according to its name and parameters.
     * hasValue &raquo; Checks if the property has a value. 
     * isset &raquo; Checks if the property has a value.
     * is &raquo; Retrieves the property value as a boolean. A number will return true if its value is 1. The string will be converted to a number first. 
     * equals &raquo; Checks if the property value is equal to the given value. 
     * get &raquo; Retrieves the property value. 
     * set &raquo; Sets the property value. 
     * unset &raquo; Unsets the property value. 
     * push &raquo; Adds array elements to a property at the end. 
     * append &raquo; Appends array elements to a property at the end. 
     * unshift &raquo; Adds array elements to a property at the beginning. 
     * prepend &raquo; Prepends array elements to a property at the beginning. 
     * pop &raquo; Removes the last element from the property. 
     * shift &raquo; Removes the first element from the property. 
     * findOneBy &raquo; Searches for data in the database and returns one record. This method requires a database connection.
     * findOneIfExistsBy &raquo; Searches for data in the database by any column values and returns one record. This method requires a database connection.
     * deleteOneBy &raquo; Deletes data from the database by any column values and returns one record. This method requires a database connection.
     * findFirstBy &raquo; Searches for data in the database by any column values and returns the first record. This method requires a database connection.
     * findFirstIfExistsBy &raquo; Searches for data in the database by any column values and returns the first record. This method requires a database connection.
     * findLastBy &raquo; Searches for data in the database by any column values and returns the last record. This method requires a database connection.
     * findLastIfExistsBy &raquo; Searches for data in the database by any column values and returns the last record. This method requires a database connection.
     * findBy &raquo; Searches for multiple records in the database by any column values. This method requires a database connection.
     * findAscBy &raquo; Searches for multiple records in the database, ordered by primary keys in ascending order. This method requires a database connection.
     * findDescBy &raquo; Searches for multiple records in the database, ordered by primary keys in descending order. This method requires a database connection.
     * listBy &raquo; Searches for multiple records in the database. Similar to findBy, but the returned object does not contain a connection to the database, so objects cannot be saved directly to the database. This method requires a database connection.
     * listAscBy &raquo; Searches for multiple records in the database, ordered by primary keys in ascending order. Similar to findAscBy, but the returned object does not contain a connection to the database, so objects cannot be saved directly to the database. This method requires a database connection.
     * listDescBy &raquo; Searches for multiple records in the database, ordered by primary keys in descending order. Similar to findDescBy, but the returned object does not contain a connection to the database, so objects cannot be saved directly to the database. This method requires a database connection.
     * listAllAsc &raquo; Searches for multiple records in the database without filtering, ordered by primary keys in ascending order. Similar to findAllAsc, but the returned object does not contain a connection to the database, so objects cannot be saved directly to the database. This method requires a database connection.
     * listAllDesc &raquo; Searches for multiple records in the database without filtering, ordered by primary keys in descending order. Similar to findAllDesc, but the returned object does not contain a connection to the database, so objects cannot be saved directly to the database. This method requires a database connection.
     * countBy &raquo; Counts data from the database.
     * existsBy &raquo; Checks for data in the database. This method requires a database connection.
     * deleteBy &raquo; Deletes data from the database without reading it first. This method requires a database connection.
     * booleanToTextBy &raquo; Converts a boolean value to yes/no or true/false depending on the parameters given. Example: $result = booleanToTextByActive("Yes", "No"); If $obj->active is true, $result will be "Yes"; otherwise, it will be "No". 
     * booleanToSelectedBy &raquo; Creates the attribute selected="selected" for a form. 
     * booleanToCheckedBy &raquo; Creates the attribute checked="checked" for a form. 
     * startsWith &raquo; Checks if the value starts with a given string. 
     * endsWith &raquo; Checks if the value ends with a given string. 
     * createSelected &raquo; Creates the selected="selected" attribute if the property is true.
     * createChecked &raquo; Creates the checked="checked" attribute if the property is true.
     * label &raquo; Retrieves the label of a property defined in the Label annotation.
     * option &raquo; Takes the first parameter if the property is true and the second parameter if the property is false.
     * notNull &raquo; Returns true if the property is not null.
     * notEmpty &raquo; Returns true if the property is not empty.
     * notZero &raquo; Returns true if the property is not zero.
     * notEquals &raquo; Returns true if the property is not equal to the one given in the parameter.
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
            return $this->push($var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "append", 6) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 6));
            return $this->append($var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "unshift", 7) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 7));
            return $this->unshift($var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "prepend", 7) === 0 && isset($params) && is_array($params) && !$this->_readonly) {
            $var = lcfirst(substr($method, 7));
            return $this->prepend($var, isset($params) && is_array($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "pop", 3) === 0) {
            $var = lcfirst(substr($method, 3));
            return $this->pop($var);
        }
        else if (strncasecmp($method, "shift", 5) === 0) {
            $var = lcfirst(substr($method, 5));
            return $this->shift($var);
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
            if(isset($params) && isset($params[0])) {
                return isset($this->$var) && $this->$var == $params[0] ? self::ATTR_SELECTED : '';
            }
            else {
                return isset($this->$var) && $this->$var == 1 ? self::ATTR_SELECTED : '';
            }
        }
        else if (strncasecmp($method, "createChecked", 13) === 0) {
            $var = lcfirst(substr($method, 13));
            if(isset($params) && isset($params[0])) {
                return isset($this->$var) && $this->$var == $params[0] ? self::ATTR_CHECKED : '';
            } else {
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
     * Magic method to convert the object to a string.
     *
     * @return string A JSON representation of the object.
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
     * Recursively stringify an object or array of objects.
     *
     * @param self $value The object to stringify.
     * @param boolean $snake Flag to indicate whether to convert property names to snake_case.
     * @return mixed The stringified object or array.
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
     * The dump method, when supplied with an array, converts it into a friendly YAML format.
     *
     * @param int|null $inline The level at which to switch to inline YAML. If NULL, the maximum depth will be used.
     * @param int $indent The number of spaces to use for indentation of nested nodes.
     * @param int $flags A bit field of DUMP_* constants to customize the dumped YAML string.
     *
     * @return string A YAML string representing the original PHP value.
     */
    public function dumpYaml($inline = null, $indent = 4, $flags = 0)
    {
        $snake = $this->_snakeYaml();
        $input = $this->valueArray($snake);
        return PicoYamlUtil::dump($input, $inline, $indent, $flags);
    }
}
