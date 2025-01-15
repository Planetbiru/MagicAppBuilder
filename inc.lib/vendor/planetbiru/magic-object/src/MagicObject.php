<?php

namespace MagicObject;

use Exception;
use PDOException;
use PDOStatement;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseEntity;
use MagicObject\Database\PicoDatabasePersistence;
use MagicObject\Database\PicoDatabasePersistenceExtended;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseQueryTemplate;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoPageData;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Database\PicoTableInfo;
use MagicObject\Exceptions\FileNotFoundException;
use MagicObject\Exceptions\FindOptionException;
use MagicObject\Exceptions\InvalidAnnotationException;
use MagicObject\Exceptions\InvalidQueryInputException;
use MagicObject\Exceptions\InvalidReturnTypeException;
use MagicObject\Exceptions\NoDatabaseConnectionException;
use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\Util\ClassUtil\PicoAnnotationParser;
use MagicObject\Util\ClassUtil\PicoObjectParser;
use MagicObject\Util\Database\NativeQueryUtil;
use MagicObject\Util\Database\PicoDatabaseUtil;
use MagicObject\Util\PicoArrayUtil;
use MagicObject\Util\PicoEnvironmentVariable;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;
use PDO;
use ReflectionClass;
use ReflectionMethod;
use stdClass;
use Symfony\Component\Yaml\Yaml;

/**
 * Class for creating a magic object.
 * A magic object is an instance created from any class, allowing the user to add any property with any name and value. It can load data from INI files, YAML files, JSON files, and databases.
 * Users can create entities from database tables and perform insert, select, update, and delete operations on records in the database.
 * Users can also create properties from other entities using the full name of the class (namespace + class name).
 * 
 * @author Kamshory
 * @package MagicObject
 * @link https://github.com/Planetbiru/MagicObject
 */
class MagicObject extends stdClass // NOSONAR
{
    // Message constants
    const MESSAGE_NO_DATABASE_CONNECTION = "No database connection provided";
    const MESSAGE_NO_RECORD_FOUND = "No record found";
    
    // Property naming strategy
    const PROPERTY_NAMING_STRATEGY = "property-naming-strategy";
    const PROPERTY_NAMING_STRATEGY_CAMEL = "propertyNamingStrategy";
    
    // Key constants
    const KEY_PROPERTY_TYPE = "propertyType";
    const KEY_NAME = "name";
    const KEY_VALUE = "value";
    const KEY_TYPE = "type";
    const KEY_LENGTH = "length";
    const KEY_DEFAULT_VALUE = "defaultValue";

    // Format constants
    const JSON = 'JSON';
    const YAML = 'Yaml';

    // Attribute constants
    const ATTR_CHECKED = ' checked="checked"';
    const ATTR_SELECTED = ' selected="selected"';

    // Find option constants
    const FIND_OPTION_DEFAULT = 0;
    const FIND_OPTION_NO_COUNT_DATA = 1;
    const FIND_OPTION_NO_FETCH_DATA = 2;

    const SNAKE_CASE = 'SNAKE_CASE';
    const UPPER_CAMEL_CASE = 'UPPER_CAMEL_CASE';

    /**
     * Indicates whether the object is read-only.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     * 
     * @var bool
     */
    private $_readonly = false; // NOSONAR

    /**
     * Database connection instance.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var PicoDatabase
     */
    private $_database; // NOSONAR

    /**
     * Class containing a database entity.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var PicoDatabaseEntity|null
     */
    private $_databaseEntity; // NOSONAR

    /**
     * Class parameters.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var array
     */
    private $_classParams = array(); // NOSONAR

    /**
     * List of null properties.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var array
     */
    private $_nullProperties = array(); // NOSONAR

    /**
     * Property labels.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var array
     */
    private $_label = array(); // NOSONAR

    /**
     * Table information instance.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var PicoTableInfo|null
     */
    private $_tableInfoProp = null; // NOSONAR

    /**
     * Database persistence instance.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var PicoDatabasePersistence|null
     */
    private $_persistProp = null; // NOSONAR

    /**
     * Retrieves the list of null properties.
     *
     * @return array The list of properties that are currently null.
     */
    public function nullPropertyList()
    {
        return $this->_nullProperties;
    }

    /**
     * Constructor.
     *
     * Initializes the object with the provided data and optionally connects to a database.
     * The constructor can accept different types of data to populate the object and can 
     * also accept a PDO connection or a PicoDatabase instance to set up the database connection.
     *
     * @param self|array|stdClass|object|null $data Initial data to populate the object. This can be:
     *        - `self`: An instance of the same class to clone data.
     *        - `array`: An associative array of data, which will be camel-cased.
     *        - `stdClass`: A standard object to populate the properties.
     *        - `object`: A generic object to populate the properties.
     *        - `null`: No data, leaving the object empty.
     * 
     * @param PicoDatabase|PDO|null $database A database connection instance, either:
     *        - `PicoDatabase`: An already instantiated PicoDatabase object.
     *        - `PDO`: A PDO connection object, which will be converted into a PicoDatabase instance using `PicoDatabase::fromPdo()`.
     *        - `null`: No database connection.
     * 
     * @throws InvalidAnnotationException If the annotations are invalid or cannot be parsed.
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
            catch(InvalidAnnotationException $e)
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
            if($database instanceof PicoDatabase)
            {
                $this->_database = $database;
            }
            else if($database instanceof PDO)
            {
                $this->_database = PicoDatabase::fromPdo($database);
            }
        }
    }

    /**
     * Loads data into the object.
     *
     * @param mixed $data Data to load, which can be another MagicObject, an array, or an object.
     * @return self Returns the current instance for method chaining.
     */
    public function loadData($data)
    {
        if($data != null)
        {
            if($data instanceof self)
            {
                $values = $data->value();
                foreach ($values as $key => $value) 
                {
                    $key2 = PicoStringUtil::camelize($key);
                    $this->set($key2, $value, true);
                }
            }
            else if (is_array($data) || is_object($data)) 
            {
                foreach ($data as $key => $value) 
                {
                    $key2 = PicoStringUtil::camelize($key);
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
     * @return self Returns the current instance for method chaining.
     */
    public function loadIniString($rawData, $systemEnv = false)
    {
        // Parse without sections
        $data = PicoIniUtil::parseIniString($rawData);
        if($this->_isNotNullAndNotEmpty($data))
        {
            $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
            if($systemEnv)
            {
                $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
            }
            $data = PicoArrayUtil::camelize($data);
            $this->loadData($data);
        }
        return $this;
    }

    /**
     * Load data from an INI file.
     *
     * @param string $path File path to the INI file
     * @param bool $systemEnv Flag to indicate whether to use environment variables
     * @return self Returns the current instance for method chaining.
     */
    public function loadIniFile($path, $systemEnv = false)
    {
        // Parse without sections
        $data = PicoIniUtil::parseIniFile($path);
        if($this->_isNotNullAndNotEmpty($data))
        {
            $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);
            if($systemEnv)
            {
                $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
            }
            $data = PicoArrayUtil::camelize($data);
            $this->loadData($data);
        }
        return $this;
    }

    /**
     * Load data from a YAML string.
     *
     * @param string $rawData YAML string
     * @param bool $systemEnv Replace all environment variable values
     * @param bool $asObject Result as an object instead of an array
     * @param bool $recursive Convert all objects to MagicObject
     * @return self Returns the current instance for method chaining.
     */
    public function loadYamlString($rawData, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = Yaml::parse($rawData);
        if($this->_isNotNullAndNotEmpty($data))
        {
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
     * @return self Returns the current instance for method chaining.
     */
    public function loadYamlFile($path, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = Yaml::parseFile($path);
        if($this->_isNotNullAndNotEmpty($data))
        {
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
     * @return self Returns the current instance for method chaining.
     */
    public function loadJsonString($rawData, $systemEnv = false, $asObject = false, $recursive = false)
    {
        $data = json_decode($rawData);
        if($this->_isNotNullAndNotEmpty($data))
        {
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
        }
        return $this;
    }

    /**
     * Loads data from a JSON file and processes it based on the provided options.
     *
     * @param string $path The file path to the JSON file.
     * @param bool $systemEnv Whether to replace system environment variables in the data (default: `false`).
     * @param bool $asObject Whether to return the result as an object instead of an associative array (default: `false`).
     * @param bool $recursive Whether to recursively convert all objects into MagicObject instances (default: `false`).
     * 
     * @return self Returns the current instance for method chaining.
     * 
     * @throws FileNotFoundException If the specified JSON file does not exist.
     */
    public function loadJsonFile($path, $systemEnv = false, $asObject = false, $recursive = false)
    {
        // Check if the file exists
        if (!file_exists($path)) {
            throw new FileNotFoundException("Specified file not found [{$path}]");
        }

        // Decode the JSON file contents as an associative array
        $data = json_decode(file_get_contents($path), true); // true to decode as associative array

        // If data is valid and not empty, process it
        if (!empty($data)) 
        {
            // Replace Pico environment variables in the data
            $data = PicoEnvironmentVariable::replaceValueAll($data, $data, true);

            // If systemEnv is true, replace system environment variables
            if ($systemEnv) 
            {
                $data = PicoEnvironmentVariable::replaceSysEnvAll($data, true);
            }

            // Camelize the data keys (e.g., 'user_name' to 'userName')
            $data = PicoArrayUtil::camelize($data);

            // Load the processed data (object or array, recursively if needed)
            return $this->loadJsonData($data, $asObject, $recursive);
        }

        return $this;
    }

    /**
     * Loads processed JSON data and optionally converts it to objects or parses recursively.
     *
     * @param mixed $data The processed data to load (array or object).
     * @param bool $asObject Whether to return the result as an object.
     * @param bool $recursive Whether to recursively convert all objects into MagicObject instances.
     * 
     * @return self Returns the current instance for method chaining.
     */
    private function loadJsonData($data, $asObject, $recursive)
    {
        if ($asObject) 
        {
            // Convert data to object
            $data = json_decode(json_encode($data), false); // Convert array to object
        }

        // Load data, applying recursion if needed
        $dataToLoad = $recursive ? PicoObjectParser::parseRecursiveObject($data) : $data;

        // Call the loadData method to process the data
        $this->loadData($dataToLoad);

        return $this;
    }

    /**
     * Set the read-only state of the object.
     *
     * When set to read-only, setters will not change the value of its properties,
     * but loadData will still function normally.
     *
     * @param bool $readonly Flag to set the object as read-only
     * @return self Returns the current instance for method chaining.
     */
    protected function readOnly($readonly)
    {
        $this->_readonly = $readonly;
        return $this;
    }

    /**
     * Check if database is connected or not
     *
     * @return bool Returns `true` if the database is connected, `false` otherwise.
     */
    private function _databaseConnected()
    {
        return $this->_database != null && $this->_database->isConnected();
    }

    /**
     * Set the database connection.
     *
     * @param PicoDatabase $database Database connection
     * @return self Returns the current instance for method chaining.
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
        if($database != null && $database instanceof PicoDatabase)
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
     * Set or get the database entity.
     *
     * If a database entity is provided, it will be set; otherwise, the current database entity will be returned.
     *
     * @param MagicObject|PicoDatabaseEntity|null $databaseEntity The database entity to set or null to get the current entity.
     * @return self|PicoDatabaseEntity Returns the current instance for method chaining, or the current database entity if no parameter is provided.
     */
    public function databaseEntity($databaseEntity = null)
    {
        if ($databaseEntity !== null) 
        {
            if ($databaseEntity instanceof PicoDatabaseEntity) 
            {
                $this->_databaseEntity = $databaseEntity;
            } elseif ($databaseEntity instanceof MagicObject) 
            {
                $db = $databaseEntity->currentDatabase();
                if (isset($db) && $db->isConnected()) 
                {
                    if (!isset($this->_databaseEntity)) 
                    {
                        $this->_databaseEntity = new PicoDatabaseEntity();
                        // Set default database connection
                        $this->_databaseEntity->setDefaultDatabase($this->_database);
                    }
                    $this->_databaseEntity->add($databaseEntity, $db);
                }
            }
            return $this; // Returning self for method chaining
        } else {
            return $this->_databaseEntity; // Returning the current database entity
        }
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
                    $resultData->{$key} = $val;
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
     * Save the current object to the database.
     *
     * This method persists the current object to the database. If `$includeNull` is TRUE, 
     * all properties of the object, including those with null values, will be saved. 
     * If FALSE, only the properties with non-null values will be saved.
     *
     * @param bool $includeNull If TRUE, all properties, including null, will be saved. 
     *                          If FALSE, only non-null values will be saved.
     * @return PDOStatement Returns a PDOStatement object for further database interaction.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws NoRecordFoundException If no corresponding record is found.
     * @throws PDOException If a database error occurs.
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
     * Generate a query to save data to the database.
     *
     * This method prepares a query to persist the current object to the database. 
     * If `$includeNull` is TRUE, properties with null values will be included in the query. 
     * If FALSE, only properties with non-null values will be included.
     *
     * @param bool $includeNull If TRUE, all properties, including null, will be saved. 
     *                          If FALSE, only non-null values will be included in the query.
     * @return PicoDatabaseQueryBuilder Returns a PicoDatabaseQueryBuilder object for query construction.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws NoRecordFoundException If no corresponding record is found.
     */
    public function saveQuery($includeNull = false)
    {
        if($this->_databaseConnected())
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
     * This method retrieves data from the database. If no data is found, a `NoRecordFoundException` will be thrown. 
     * The retrieved data is then loaded into the current instance for further use.
     *
     * @return self Returns the current instance for method chaining.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws NoRecordFoundException If no records are found in the database.
     * @throws PDOException If a database error occurs.
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
     * This method retrieves all data from the database. If no data is found, a `NoRecordFoundException` will be thrown. 
     * The retrieved data is then loaded into the current instance for further use.
     *
     * @return self Returns the current instance for method chaining.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws NoRecordFoundException If no records are found in the database.
     * @throws PDOException If a database error occurs.
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
     * Generate a query to select data.
     *
     * This method prepares a query to select data from the database. The query can then be used to execute the retrieval 
     * of data manually if needed.
     *
     * @return PicoDatabaseQueryBuilder Returns a PicoDatabaseQueryBuilder object for building the select query.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws PDOException If a database error occurs.
     */
    public function selectQuery()
    {
        if($this->_databaseConnected())
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
     * Executes a database query based on the parameters and annotations from the caller function.
     *
     * This method uses reflection to extract the query string and return type from the caller's 
     * docblock, binds the provided parameters, and executes the query against the database.
     *
     * It analyzes the parameters and return type of the caller function to enable dynamic query 
     * execution tailored to the specified return type. Supported return types include:
     * - `void`: Returns null.
     * - `int` or `integer`: Returns the number of affected rows.
     * - `object` or `stdClass`: Returns a single result as an object.
     * - `stdClass[]`: Returns all results as an array of stdClass objects.
     * - `array`: Returns all results as an associative array.
     * - `string`: Returns the JSON-encoded results.
     * - `PDOStatement`: Returns the prepared statement for further operations if needed.
     * - `MagicObject` and its derived classes: If the return type is a class name or an array of 
     *   class names, instances of that class will be created for each row fetched.
     * - `MagicObject[]` and its derived classes: Instances of the corresponding class will be 
     *   created for each row fetched.
     *
     * @return mixed The result based on the return type of the caller function:
     *               - null if the return type is void.
     *               - integer for the number of affected rows if the return type is int.
     *               - object for a single result if the return type is object.
     *               - an array of associative arrays for multiple results if the return type is array.
     *               - a JSON string if the return type is string.
     *               - instances of a specified class if the return type matches a class name.
     *
     * @throws PDOException If there is an error executing the database query.
     * @throws InvalidQueryInputException If there is no query to be executed or if the input is invalid.
     * @throws InvalidReturnTypeException If the return type specified in the docblock is invalid or unrecognized.
     */
    protected function executeNativeQuery()
    {
        // Retrieve caller trace information
        $trace = debug_backtrace();
        $traceCaller = $trace[1];

        // Extract the caller's parameters
        $callerParamValues = isset($traceCaller['args']) ? $traceCaller['args'] : array();
        
        // Get the caller's function and class names
        $callerFunctionName = $traceCaller['function'];
        $callerClassName = $traceCaller['class'];

         // Use reflection to retrieve docblock annotations from the caller function
        $reflection = new ReflectionMethod($callerClassName, $callerFunctionName);
        
        // Get parameter information from the caller function
        $callerParams = $reflection->getParameters();

        // Get the docblock comment for the caller function
        $docComment = $reflection->getDocComment();

        $nativeQueryUtil = new NativeQueryUtil();

        // Extract the query string and return type from the docblock
        $queryString = $nativeQueryUtil->extractQueryString($docComment, $callerParamValues);
        $returnType = $nativeQueryUtil->extractReturnType($docComment, $callerClassName);    
        
        $params = array();

        try {
            // Apply query parameters (pagination, sorting, etc.)
            $queryString = $nativeQueryUtil->applyQueryParameters($this->_database->getDatabaseType(), $queryString, $callerParams, $callerParamValues);

            // Prepare the query using the database connection
            $pdo = $this->_database->getDatabaseConnection();
            $stmt = $pdo->prepare($queryString);

            // Bind the parameters to the prepared statement
            foreach ($callerParamValues as $index => $paramValue) {
                if (isset($callerParams[$index]) && !($paramValue instanceof PicoPageable) && !($paramValue instanceof PicoSortable) && !($paramValue instanceof PicoDatabaseQueryTemplate)) {
                    // Bind the parameter name and type to the statement
                    $paramName = $callerParams[$index]->getName();
                    if (!is_array($paramValue)) {
                        $mapped = $nativeQueryUtil->mapToPdoParamType($paramValue);
                        $paramType = $mapped->type;
                        $paramValue = $mapped->value;

                        // Debugging: store parameter values for query inspection
                        $params[$paramName] = $paramValue;
                        $stmt->bindValue(":" . $paramName, $paramValue, $paramType);
                    }
                }
            }

            // Log the query for debugging
            $nativeQueryUtil->debugQuery($this->_database, $stmt, $params);

            // Execute the query
            $stmt->execute();

            // Handle and return the result based on the specified return type
            return $nativeQueryUtil->handleReturnObject($stmt, $returnType);          
        } 
        catch (PDOException $e) {
            // Log and rethrow the exception if a database error occurs
            throw new PDOException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Insert data into the database.
     *
     * This method inserts the current object’s data into the database. If `$includeNull` is TRUE, 
     * properties with null values will also be included in the insert query. If FALSE, only properties with non-null values will be inserted.
     *
     * @param bool $includeNull If TRUE, all properties, including null, will be inserted. If FALSE, only non-null values will be inserted.
     * @return PDOStatement Returns a PDOStatement object for further database interaction.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws PDOException If there is an error executing the insert query.
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
     * This method prepares a query to insert data into the database. It can be used to manually execute the query.
     * If `$includeNull` is TRUE, properties with null values will be included in the query. If FALSE, only non-null properties will be inserted.
     *
     * @param bool $includeNull If TRUE, all properties, including null, will be included in the insert query. If FALSE, only non-null properties will be included.
     * @return PicoDatabaseQueryBuilder Returns a PicoDatabaseQueryBuilder object for building the insert query.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     */
    public function insertQuery($includeNull = false)
    {
        if($this->_databaseConnected())
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
     * This method updates the current object's data in the database. If `$includeNull` is TRUE, 
     * properties with null values will be included in the update query. If FALSE, only properties with non-null values will be updated.
     *
     * @param bool $includeNull If TRUE, all properties, including null, will be updated. If FALSE, only non-null values will be updated.
     * @return PDOStatement Returns a PDOStatement object for further database interaction.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws PDOException If there is an error executing the update query.
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
     * This method prepares a query to update data in the database. It can be used to manually execute the query.
     * If `$includeNull` is TRUE, properties with null values will be included in the update query. If FALSE, only non-null properties will be updated.
     *
     * @param bool $includeNull If TRUE, all properties, including null, will be included in the update query. If FALSE, only non-null properties will be included.
     * @return PicoDatabaseQueryBuilder Returns a PicoDatabaseQueryBuilder object for building the update query.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     */
    public function updateQuery($includeNull = false)
    {
        if($this->_databaseConnected())
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
     * This method deletes data associated with the current object from the database.
     *
     * @return PDOStatement Returns a PDOStatement object for further database interaction.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws PDOException If there is an error executing the delete query.
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
     * This method prepares a query to delete data from the database. It can be used to manually execute the query.
     *
     * @return PicoDatabaseQueryBuilder Returns a PicoDatabaseQueryBuilder object for building the delete query.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     */
    public function deleteQuery()
    {
        if($this->_databaseConnected())
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
     * Starts a database transaction.
     *
     * This method begins a new database transaction. It delegates the actual transaction 
     * initiation to the `transactionalCommand` method, passing the "start" command.
     *
     * @return self The current instance of the class for method chaining.
     * 
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws PDOException If there is an error while starting the transaction.
     */
    public function startTransaction()
    {
        $this->_database->startTransaction();
        return $this;
    }

    /**
     * Commits the current database transaction.
     *
     * This method commits the current transaction. If successful, it makes all database
     * changes made during the transaction permanent. It delegates to the `transactionalCommand` method 
     * with the "commit" command.
     *
     * @return self The current instance of the class for method chaining.
     * 
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws PDOException If there is an error during the commit process.
     */
    public function commit()
    {
        $this->_database->commit();
        return $this;
    }

    /**
     * Rolls back the current database transaction.
     *
     * This method rolls back the current transaction, undoing all database changes made
     * during the transaction. It calls the `transactionalCommand` method with the "rollback" command.
     *
     * @return self The current instance of the class for method chaining.
     * 
     * @throws NoDatabaseConnectionException If there is no active database connection.
     * @throws PDOException If there is an error during the rollback process.
     */
    public function rollback()
    {
        $this->_database->rollback();
        return $this;
    }

    /**
     * Get a MagicObject with a WHERE specification.
     *
     * This method applies a WHERE condition to the database query using the provided specification.
     * The specification is an instance of `PicoSpecification` which defines the filtering criteria.
     *
     * @param PicoSpecification $specification The specification to define the WHERE condition.
     * @return PicoDatabasePersistenceExtended Returns an instance of `PicoDatabasePersistenceExtended` 
     *         that applies the WHERE condition based on the provided specification.
     * @throws NoDatabaseConnectionException If there is no active database connection.
     */
    public function where($specification)
    {
        if($this->_databaseConnected())
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
     * Modify properties with null values.
     *
     * This method tracks properties that are assigned a null value, storing them in a `_nullProperties` array. 
     * If a property is set to null, it is added to the `_nullProperties` array; if it's set to a non-null value, it is removed from the array.
     *
     * @param string $propertyName The name of the property to check and modify.
     * @param mixed $propertyValue The value to be assigned to the property.
     * @return self Returns the current instance for method chaining.
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
        return $this;
    }

    /**
     * Set property value.
     *
     * @param string $propertyName Property name
     * @param mixed|null $propertyValue Property value
     * @param bool $skipModifyNullProperties Skip modifying null properties
     * @return self Returns the current instance for method chaining.
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
     * @return self Returns the current instance for method chaining.
     */
    public function push($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->{$var}))
        {
            $this->{$var} = array();
        }
        array_push($this->{$var}, $propertyValue);
        return $this;
    }
    
    /**
     * Adds an element to the end of an array property (alias for push).
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self Returns the current instance for method chaining.
     */
    public function append($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return $this->push($var, $propertyValue);
    }
    
    /**
     * Adds an element to the beginning of an array property.
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self Returns the current instance for method chaining.
     */
    public function unshift($propertyName, $propertyValue)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(!isset($this->{$var}))
        {
            $this->{$var} = array();
        }
        array_unshift($this->{$var}, $propertyValue);
        return $this;
    }
    
    /**
     * Adds an element to the beginning of an array property (alias for unshift).
     *
     * @param string $propertyName Property name
     * @param mixed $propertyValue Property value
     * @return self Returns the current instance for method chaining.
     */
    public function prepend($propertyName, $propertyValue)
    {
        return $this->unshift($propertyName, $propertyValue);
    }

    /**
     * Remove and return the last element of an array property.
     *
     * This method removes the last element from an array property and returns it.
     * If the specified property is not an array or does not exist, null is returned.
     *
     * @param string $propertyName The name of the property (array) from which the last element will be removed.
     * @return mixed The last element of the array, or null if the property is not an array or doesn't exist.
     */
    public function pop($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->{$var}) && is_array($this->{$var}))
        {
            return array_pop($this->{$var});
        }
        return null;
    }
    
    /**
     * Remove and return the first element of an array property.
     *
     * This method removes the first element from an array property and returns it.
     * If the specified property is not an array or does not exist, null is returned.
     *
     * @param string $propertyName The name of the property (array) from which the first element will be removed.
     * @return mixed The first element of the array, or null if the property is not an array or doesn't exist.
     */
    public function shift($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        if(isset($this->{$var}) && is_array($this->{$var}))
        {
            return array_shift($this->{$var});
        }
        return null;
    }

    /**
     * Get the value of a property.
     *
     * This method retrieves the value of the specified property. If the property does not exist, it returns null.
     *
     * @param string $propertyName The name of the property to retrieve.
     * @return mixed|null The value of the property, or null if the property is not set.
     */
    public function get($propertyName)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->{$var}) ? $this->{$var} : null;
    }

    /**
     * Get the value of a property or a default value if the property is not set.
     *
     * This method retrieves the value of the specified property. If the property is not set, the provided default value is returned.
     *
     * @param string $propertyName The name of the property to retrieve.
     * @param mixed|null $defaultValue The default value to return if the property is not set.
     * @return mixed|null The value of the property, or the default value if the property is not set.
     */
    public function getOrDefault($propertyName, $defaultValue = null)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return isset($this->{$var}) ? $this->{$var} : $defaultValue;
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
     * Magic method to get the value of a property.
     *
     * This method is automatically called when an undefined or inaccessible property is accessed.
     * It checks if the property has been set (including null values) using the `__isset` method and
     * retrieves its value via the `get` method if it exists.
     *
     * @param string $propertyName The name of the property to retrieve.
     * @return mixed|null The value of the property if it is set, or null if the property is not set or accessible.
     */
    public function __get($propertyName)
    {
        $propertyName = PicoStringUtil::camelize($propertyName);
        if($this->__isset($propertyName))
        {
            return $this->get($propertyName);
        }
    }

    /**
     * Magic method to check if a property is set (including null).
     *
     * This method is automatically called when checking if an undefined or inaccessible property is set
     * using `isset()`. It checks if the property exists and is set (even if its value is null).
     *
     * @param string $propertyName The name of the property to check.
     * @return bool True if the property is set (including null), false otherwise.
     */
    public function __isset($propertyName)
    {
        $propertyName = PicoStringUtil::camelize($propertyName);
        return isset($this->{$propertyName});
    }

    /**
     * Magic method to unset a property.
     *
     * This method is automatically called when a property is unset using `unset()`.
     * It unsets the specified property from the object.
     *
     * @param string $propertyName The name of the property to unset.
     * @return void
     */
    public function __unset($propertyName)
    {
        $propertyName = PicoStringUtil::camelize($propertyName);
        unset($this->{$propertyName});
    }

    /**
     * Copy values from another object to the current instance.
     *
     * This method copies property values from the provided source object to the current instance.
     * Optionally, a filter can be applied to specify which properties to copy, and whether null values should be included.
     *
     * @param self|mixed $source The source object or data from which values will be copied. If a non-object is provided, this may result in unexpected behavior.
     * @param array|null $filter An optional array of property names to filter which properties are copied. If null, all properties are copied.
     * @param bool $includeNull Flag indicating whether to include properties with null values. If false, properties with null values will be excluded from the copy.
     * @return self Returns the current instance for method chaining after copying the values.
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
        return $this;
    }

    /**
     * Remove property value and set it to null.
     *
     * @param string $propertyName Property name
     * @param bool $skipModifyNullProperties Skip modifying null properties
     * @return self Returns the current instance for method chaining.
     */
    private function removeValue($propertyName, $skipModifyNullProperties = false)
    {
        $var = PicoStringUtil::camelize($propertyName);
        return $this->set($var, null, $skipModifyNullProperties);
    }

    /**
     * Retrieve table information for the current instance.
     *
     * This method retrieves the table information (e.g., schema, columns) associated with the current
     * object. It lazily loads the table information, meaning it will be fetched only once and cached
     * for future calls to improve performance.
     *
     * @return PicoTableInfo Returns an instance of the `PicoTableInfo` class containing the table schema and other related metadata.
     */
    public function tableInfo()
    {
        if(!isset($this->_tableInfoProp))
        {
            $this->_persistProp = new PicoDatabasePersistence($this->_database, $this);
            $this->_tableInfoProp = $this->_persistProp->getTableInfo();
        }
        return $this->_tableInfoProp;
    }

    /**
     * Get default values for properties
     *
     * @param bool $snakeCase Flag indicating whether to convert property names to snake case
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
                    $defaultValue->{$col} = $this->_persistProp->fixData($column[self::KEY_VALUE], $column[self::KEY_PROPERTY_TYPE]);
                }
            }
        }
        return $defaultValue;
    }

    /**
     * Get the object values
     *
     * @param bool $snakeCase Flag indicating whether to convert property names to snake case
     * @return stdClass An object containing the values of the properties
     */
    public function value($snakeCase = false)
    {
        $parentProps = $this->propertyList(true, true);
        $value = new stdClass;
        foreach ($this as $key => $val) 
        {
            if(!in_array($key, $parentProps))
            {
                $value->{$key} = $val;
            }
        }
        if($snakeCase)
        {
            $value2 = new stdClass;
            foreach ($value as $key => $val) {
                $key2 = PicoStringUtil::snakeize($key);
                $value2->{$key2} = PicoStringUtil::snakeizeObject($val);
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
        $upperCamel = $this->_upperCamel();
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
     * @param bool $snakeCase Flag indicating whether to convert property names to snake case
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
     * @return bool True if the naming strategy is snake case; otherwise, false
     */
    protected function _snakeJson()
    {
        return isset($this->_classParams[self::JSON])
            && (
                isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY]) &&
                strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], self::SNAKE_CASE) == 0
            || isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY_CAMEL]) && 
                strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY_CAMEL], self::SNAKE_CASE) == 0
            );
    }

    /**
     * Check if the YAML naming strategy is snake case
     *
     * @return bool True if the naming strategy is snake case; otherwise, false
     */
    protected function _snakeYaml()
    {
        return isset($this->_classParams[self::YAML])
            && (
                isset($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY]) &&
                strcasecmp($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY], self::SNAKE_CASE) == 0
            || isset($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY_CAMEL]) && 
                strcasecmp($this->_classParams[self::YAML][self::PROPERTY_NAMING_STRATEGY_CAMEL], self::SNAKE_CASE) == 0
            );
    }

    /**
     * Check if the JSON naming strategy is upper camel case
     *
     * @return bool True if the naming strategy is upper camel case; otherwise, false
     */
    protected function _upperCamel()
    {
        return isset($this->_classParams[self::JSON])
            && (
                isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY]) &&
                strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY], self::UPPER_CAMEL_CASE) == 0
            || isset($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY_CAMEL]) && 
                strcasecmp($this->_classParams[self::JSON][self::PROPERTY_NAMING_STRATEGY_CAMEL], self::UPPER_CAMEL_CASE) == 0
            );
    }

    /**
     * Check if the JSON naming strategy is camel case
     *
     * @return bool True if the naming strategy is camel case; otherwise, false
     */
    protected function _camel()
    {
        return !$this->_snakeJson();
    }

    /**
     * Check if the JSON output should be prettified
     *
     * @return bool True if JSON output is set to be prettified; otherwise, false
     */
    protected function _pretty()
    {
        return isset($this->_classParams[self::JSON])
            && isset($this->_classParams[self::JSON]['prettify'])
            && strcasecmp($this->_classParams[self::JSON]['prettify'], 'true') == 0
            ;
    }

    /**
     * Checks if the provided parameter is an array.
     *
     * This function verifies if the given parameter is set and is of type array. It is a helper method 
     * used to validate the type of data before performing any operations on it that require an array.
     *
     * @param mixed $params The parameter to check.
     * @return bool Returns `true` if the parameter is set and is an array, otherwise returns `false`.
     */
    private function _isArray($params)
    {
        return isset($params) && is_array($params);
    }

    /**
     * Check if a value is not null and not empty
     *
     * @param mixed $value The value to check
     * @return bool True if the value is not null and not empty; otherwise, false
     */
    private function _isNotNullAndNotEmpty($value)
    {
        return $value != null && !empty($value);
    }

    /**
     * Get a list of properties
     *
     * @param bool $reflectSelf Flag indicating whether to reflect properties of the current class
     * @param bool $asArrayProps Flag indicating whether to return properties as an array
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
     * @param bool $passive Flag indicating whether the object is passive
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
     * @return self The found instance.
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
     * @param bool $passive Flag indicating whether the object is passive
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
            if($this->_databaseConnected())
            {
                $pageData = new PicoPageData(array(), $startTime);
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

                    $match = $this->countDataCustomWithPagable($persist, $specification, $pageable, $sortable, $findOption, $result);
                    $pageData = new PicoPageData($this->toArrayObject($result, $passive), $startTime, $match, $pageable, $stmt, $this, $subqueryMap);
                }
                else
                {
                    $match = $this->countDataCustom($persist, $specification, $pageable, $sortable, $findOption, $result);
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
     * @param bool $passive Flag indicating whether the object is passive
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
            if($this->_databaseConnected())
            {
                $pageData = new PicoPageData(array(), $startTime);
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
                    $match = $this->countDataCustomWithPagable($persist, $specification, $pageable, $sortable, $findOption, $result);
                    $pageData = new PicoPageData($this->toArrayObject($result, $passive), $startTime, $match, $pageable, $stmt, $this, $subqueryMap);
                }
                else
                {
                    $match = $this->countDataCustom($persist, $specification, $pageable, $sortable, $findOption, $result);
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
     * Counts the total number of records for a given query, with special handling for SQLite.
     * 
     * @param PicoDatabasePersistence $persist The persistence object to interact with the database.
     * @param PicoSpecification|null $specification The specification used to filter the results.
     * @param PicoPageable|string|null $pageable Pagination information.
     * @param PicoSortable|string|null $sortable Sorting information.
     * @param int $findOption The find option flag indicating how the query should be executed.
     * @param mixed $result The result set to count.
     * 
     * @return int The total number of matching records.
     */
    private function countDataCustom($persist, $specification, $pageable, $sortable, $findOption, $result)
    {
        if($this->_database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            $match = isset($result) && is_array($result) ? count($result) : 1;
        }
        else
        {
            $match = $this->countData($persist, $specification, $pageable, $sortable, $findOption, $result);
        }
        return $match;
    }

    /**
     * Counts the total number of records for a given query with pagination, with special handling for SQLite.
     * 
     * @param PicoDatabasePersistence $persist The persistence object to interact with the database.
     * @param PicoSpecification|null $specification The specification used to filter the results.
     * @param PicoPageable|string|null $pageable Pagination information.
     * @param PicoSortable|string|null $sortable Sorting information.
     * @param int $findOption The find option flag indicating how the query should be executed.
     * @param mixed $result The result set to count.
     * 
     * @return int The total number of matching records considering pagination.
     */
    private function countDataCustomWithPagable($persist, $specification, $pageable, $sortable, $findOption, $result)
    {
        if($this->_database->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            $match = $pageable->getPage()->getOffset() + $pageable->getPage()->getPageSize() + 1;
        }
        else
        {
            $match = $this->countData($persist, $specification, $pageable, $sortable, $findOption, $result);
        }
        return $match;
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
     * @return self The found instance.
     * @throws NoRecordFoundException if no record is found
     * @throws NoDatabaseConnectionException if no database connection is established
     */
    public function find($params)
    {
        if($this->_databaseConnected())
        {
            $persist = new PicoDatabasePersistence($this->_database, $this);
            $result = $persist->find($params);
            if($this->_isNotNullAndNotEmpty($result))
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
     * @return self The found instance. or the current instance if not found
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
     * @param bool $passive Flag indicating whether the object is passive
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
            if($this->_isNotNullAndNotEmpty($result))
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
            if($this->_isNotNullAndNotEmpty($result))
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
     * @return bool True on success; otherwise, false.
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
     * @return bool True if the record exists; otherwise, false.
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
     * @param bool $passive Flag indicating whether the objects are passive.
     * @return array An array of objects.
     */
    private function toArrayObject($result, $passive = false)
    {
        $instance = array();
        $index = 0;
        if($this->_isArray($result))
        {
            foreach($result as $value)
            {
                $className = get_class($this);
                $obj = new $className($value);
                if(!$passive)
                {
                    $dbEnt = $this->databaseEntity();
                    $db = null;
                    if(isset($dbEnt))
                    {
                        $db = $dbEnt->getDatabase(get_class($obj));
                    }
                    if(!isset($db) || !$db->isConnected())
                    {
                        $db = $this->_database;
                    }
                    $obj->currentDatabase($db);                  
                    $obj->databaseEntity($dbEnt);              
                }
                $instance[$index] = $obj;
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
     * Magic method called when a user calls any undefined method. 
     * The __call method checks the prefix of the called method and 
     * invokes the appropriate method according to its name and parameters.
     *
     * Method Descriptions:
     *
     * - **hasValue**: Checks if the property has a value.
     *   - Example: `$object->hasValuePropertyName();`
     *
     * - **isset**: Checks if the property is set.
     *   - Example: `$object->issetPropertyName();`
     *
     * - **is**: Retrieves the property value as a boolean.
     *   - Example: `$isActive = $object->isActive();`
     *
     * - **equals**: Checks if the property value equals the given value.
     *   - Example: `$isEqual = $object->equalsPropertyName($value);`
     *
     * - **get**: Retrieves the property value.
     *   - Example: `$value = $object->getPropertyName();`
     *
     * - **set**: Sets the property value.
     *   - Example: `$object->setPropertyName($value);`
     *
     * - **unset**: Unsets the property value.
     *   - Example: `$object->unsetPropertyName();`
     *
     * - **push**: Adds array elements to a property at the end.
     *   - Example: `$object->pushPropertyName($newElement);`
     *
     * - **append**: Appends array elements to a property at the end.
     *   - Example: `$object->appendPropertyName($newElement);`
     *
     * - **unshift**: Adds array elements to a property at the beginning.
     *   - Example: `$object->unshiftPropertyName($newElement);`
     *
     * - **prepend**: Prepends array elements to a property at the beginning.
     *   - Example: `$object->prependPropertyName($newElement);`
     *
     * - **pop**: Removes the last element from the property.
     *   - Example: `$removedElement = $object->popPropertyName();`
     *
     * - **shift**: Removes the first element from the property.
     *   - Example: `$removedElement = $object->shiftPropertyName();`
     *
     * - **findOneBy**: Searches for data in the database and returns one record.
     *   - Example: `$record = $object->findOneByPropertyName($value);`
     *   - *Requires a database connection.*
     *
     * - **findOneIfExistsBy**: Searches for data in the database by any column values and returns one record.
     *   - Example: `$record = $object->findOneIfExistsByPropertyName($value, $sortable);`
     *   - *Requires a database connection.*
     *
     * - **deleteOneBy**: Deletes data from the database by any column values and returns one record.
     *   - Example: `$deletedRecord = $object->deleteOneByPropertyName($value, $sortable);`
     *   - *Requires a database connection.*
     *
     * - **findFirstBy**: Searches for data in the database by any column values and returns the first record.
     *   - Example: `$firstRecord = $object->findFirstByColumnName($value);`
     *   - *Requires a database connection.*
     *
     * - **findFirstIfExistsBy**: Similar to `findFirstBy`, but returns the first record if it exists.
     *   - Example: `$firstRecord = $object->findFirstIfExistsByPropertyName($value, $sortable);`
     *   - *Requires a database connection.*
     *
     * - **findLastBy**: Searches for data in the database by any column values and returns the last record.
     *   - Example: `$lastRecord = $object->findLastByColumnName($value);`
     *   - *Requires a database connection.*
     *
     * - **findLastIfExistsBy**: Similar to `findLastBy`, but returns the last record if it exists.
     *   - Example: `$lastRecord = $object->findLastIfExistsByPropertyName($value, $sortable);`
     *   - *Requires a database connection.*
     *
     * - **findBy**: Searches for multiple records in the database by any column values.
     *   - Example: `$records = $object->findByColumnName($value);`
     *   - *Requires a database connection.*
     *
     * - **countBy**: Counts data from the database.
     *   - Example: `$count = $object->countByColumnName();`
     *
     * - **existsBy**: Checks for data in the database.
     *   - Example: `$exists = $object->existsByColumn($column);`
     *   - *Requires a database connection.*
     *
     * - **deleteBy**: Deletes data from the database without reading it first.
     *   - Example: `$object->deleteByPropertyName($value);`
     *   - *Requires a database connection.*
     *
     * - **booleanToTextBy**: Converts a boolean value to "yes/no" or "true/false" based on given parameters.
     *   - Example: `$result = $object->booleanToTextByActive("Yes", "No");`
     *   - *If $obj->active is true, $result will be "Yes"; otherwise, it will be "No".*
     *
     * - **startsWith**: Checks if the value starts with a given string.
     *   - Example: `$startsWith = $object->startsWithPropertyName("prefix");`
     *
     * - **endsWith**: Checks if the value ends with a given string.
     *   - Example: `$endsWith = $object->endsWithPropertyName("suffix");`
     *
     * - **label**: Retrieves the label associated with the given property.
     *   - If the label is not set, it attempts to fetch it from annotations.
     *   - Example: `$label = $object->labelPropertyName();`
     *
     * - **option**: Returns the first parameter if the property is set to `true` or equals `1`; otherwise returns the second parameter.
     *   - Example: `$option = $object->optionPropertyName("Yes", "No");`
     *
     * - **notNull**: Checks if the specified property is set (not null).
     *   - Example: `$isNotNull = $object->notNullPropertyName();`
     *
     * - **notEmpty**: Checks if the specified property is set and not empty.
     *   - Example: `$isNotEmpty = $object->notEmptyPropertyName();`
     *
     * - **notZero**: Checks if the specified property is set and not equal to zero.
     *   - Example: `$isNotZero = $object->notZeroPropertyName();`
     *
     * - **notEquals**: Checks if the specified property is set and does not equal the given value.
     *   - Example: `$isNotEqual = $object->notEqualsPropertyName($value);`
     *
     * @param string $method Method name
     * @param mixed $params Parameters for the method
     * @return mixed|null The result of the called method, or null if not applicable
     */

    public function __call($method, $params) // NOSONAR
    {
        if (strncasecmp($method, "hasValue", 8) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 8));
            return isset($this->{$var});
        }
        else if (strncasecmp($method, "isset", 5) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 5));
            return isset($this->{$var});
        }
        else if (strncasecmp($method, "is", 2) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 2));
            return isset($this->{$var}) ? $this->{$var} == 1 : false;
        }
        else if (strncasecmp($method, "equals", 6) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 6));
            return isset($this->{$var}) && $this->{$var} == $params[0];
        }
        else if (strncasecmp($method, "get", 3) === 0) {
            return $this->get(substr($method, 3));
        }
        else if (strncasecmp($method, "set", 3) === 0 && $this->_isArray($params) && !empty($params) && !$this->_readonly) {
            $var = substr($method, 3);
            $this->set($var, $params[0]);
            $this->modifyNullProperties($var, $params[0]);
            return $this;
        }
        else if (strncasecmp($method, "unset", 5) === 0 && !$this->_readonly) {
            $var = substr($method, 5);
            $this->removeValue($var);
            return $this;
        }
        else if (strncasecmp($method, "push", 4) === 0 && $this->_isArray($params) && !$this->_readonly) {
            $var = substr($method, 4);
            return $this->push($var, $this->_isArray($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "append", 6) === 0 && $this->_isArray($params) && !$this->_readonly) {
            $var = substr($method, 6);
            return $this->append($var, $this->_isArray($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "unshift", 7) === 0 && $this->_isArray($params) && !$this->_readonly) {
            $var = substr($method, 7);
            return $this->unshift($var, $this->_isArray($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "prepend", 7) === 0 && $this->_isArray($params) && !$this->_readonly) {
            $var = substr($method, 7);
            return $this->prepend($var, $this->_isArray($params) && isset($params[0]) ? $params[0] : null);
        }
        else if (strncasecmp($method, "pop", 3) === 0) {
            $var = substr($method, 3);
            return $this->pop($var);
        }
        else if (strncasecmp($method, "shift", 5) === 0) {
            $var = substr($method, 5);
            return $this->shift($var);
        }
        else if (strncasecmp($method, "findOneBy", 9) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 9));
            $sortable = PicoDatabaseUtil::sortableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findOneBy($var, $parameters, $sortable);
        }
        else if (strncasecmp($method, "findOneIfExistsBy", 17) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 17));
            $sortable = PicoDatabaseUtil::sortableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findOneIfExistsBy($var, $parameters, $sortable);
        }
        else if (strncasecmp($method, "deleteOneBy", 11) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 11));
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->deleteOneBy($var, $parameters);
        }
        else if (strncasecmp($method, "findFirstBy", 11) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 11));
            return $this->findOneBy($var, $params, PicoDatabasePersistence::ORDER_ASC);
        }
        else if (strncasecmp($method, "findFirstIfExistsBy", 19) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 19));
            return $this->findOneIfExistsBy($var, $params, PicoDatabasePersistence::ORDER_ASC);
        }
        else if (strncasecmp($method, "findLastBy", 10) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 10));
            return $this->findOneBy($var, $params, PicoDatabasePersistence::ORDER_DESC);
        }
        else if (strncasecmp($method, "findLastIfExistsBy", 18) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 18));
            return $this->findOneIfExistsBy($var, $params, PicoDatabasePersistence::ORDER_DESC);
        }
        else if (strncasecmp($method, "findBy", 6) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 6));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // get sortable
            $sortable = PicoDatabaseUtil::sortableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, $sortable);
        }
        else if (strncasecmp($method, "findAscBy", 9) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 9));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, PicoDatabasePersistence::ORDER_ASC);
        }
        else if (strncasecmp($method, "findDescBy", 10) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 10));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, PicoDatabasePersistence::ORDER_DESC);
        }
        else if (strncasecmp($method, "listBy", 6) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 6));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // get sortable
            $sortable = PicoDatabaseUtil::sortableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, $sortable, true);
        }
        else if (strncasecmp($method, "listAscBy", 9) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 9));
            // get pageable
            $pageable = PicoDatabaseUtil::pageableFromParams($params);
            // filter param
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->findBy($var, $parameters, $pageable, PicoDatabasePersistence::ORDER_ASC, true);
        }
        else if (strncasecmp($method, "listDescBy", 10) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 10));
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
            $var = PicoStringUtil::camelize(substr($method, 7));
            $parameters = PicoDatabaseUtil::valuesFromParams($params);
            return $this->countBy($var, $parameters);
        }
        else if (strncasecmp($method, "existsBy", 8) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 8));
            return $this->existsBy($var, $params);
        }
        else if (strncasecmp($method, "deleteBy", 8) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 8));
            return $this->deleteBy($var, $params);
        }
        else if (strncasecmp($method, "booleanToTextBy", 15) === 0) {
            $prop = PicoStringUtil::camelize(substr($method, 15));
            return $this->booleanToTextBy($prop, $params);
        }
        else if (strncasecmp($method, "booleanToSelectedBy", 19) === 0) {
            $prop = PicoStringUtil::camelize(substr($method, 19));
            return $this->booleanToTextBy($prop, array(self::ATTR_SELECTED, ''));
        }
        else if (strncasecmp($method, "booleanToCheckedBy", 18) === 0) {
            $prop = PicoStringUtil::camelize(substr($method, 18));
            return $this->booleanToTextBy($prop, array(self::ATTR_CHECKED, ''));
        }
        else if (strncasecmp($method, "createSelected", 14) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 14));
            if(isset($params) && isset($params[0])) {
                return isset($this->{$var}) && $this->{$var} == $params[0] ? self::ATTR_SELECTED : '';
            }
            else {
                return isset($this->{$var}) && $this->{$var} == 1 ? self::ATTR_SELECTED : '';
            }
        }
        else if (strncasecmp($method, "createChecked", 13) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 13));
            if(isset($params) && isset($params[0])) {
                return isset($this->{$var}) && $this->{$var} == $params[0] ? self::ATTR_CHECKED : '';
            } else {
                return isset($this->{$var}) && $this->{$var} == 1 ? self::ATTR_CHECKED : '';
            }
        }
        else if (strncasecmp($method, "startsWith", 10) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 10));
            $value = $params[0];
            $caseSensitive = isset($params[1]) && $params[1];
            $haystack = $this->{$var};
            return PicoStringUtil::startsWith($haystack, $value, $caseSensitive);
        }
        else if (strncasecmp($method, "endsWith", 8) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 8));
            $value = $params[0];
            $caseSensitive = isset($params[1]) && $params[1];
            $haystack = $this->{$var};
            return PicoStringUtil::endsWith($haystack, $value, $caseSensitive);
        }
        else if (strncasecmp($method, "label", 5) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 5));
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
            $var = PicoStringUtil::camelize(substr($method, 6));
            return isset($this->{$var}) && ($this->{$var} == 1 || $this->{$var} === true) ? $params[0] : $params[1];
        }
        else if(strncasecmp($method, "notNull", 7) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 7));
            return isset($this->{$var});
        }
        else if(strncasecmp($method, "notEmpty", 8) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 8));
            return isset($this->{$var}) && !empty($this->{$var});
        }
        else if(strncasecmp($method, "notZero", 7) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 7));
            return isset($this->{$var}) && $this->{$var} != 0;
        }
        else if (strncasecmp($method, "notEquals", 9) === 0) {
            $var = PicoStringUtil::camelize(substr($method, 9));
            return isset($this->{$var}) && $this->{$var} != $params[0];
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
        $upperCamel = $this->_upperCamel();
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
     * @param bool $snake Flag to indicate whether to convert property names to snake_case.
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
