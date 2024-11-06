<?php

namespace MagicObject\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use MagicObject\Exceptions\InvalidDatabaseConfiguration;
use MagicObject\Exceptions\NullPointerException;
use MagicObject\SecretObject;
use ReflectionFunction;
use stdClass;

/**
 * PicoDatabase provides an interface for database interactions using PDO.
 * 
 * This class manages database connections, query execution, and transactions.
 * It supports callbacks for query execution and debugging, allowing developers 
 * to handle SQL commands and responses effectively.
 * 
 * Features include:
 * - Establishing and managing a database connection.
 * - Executing various SQL commands (INSERT, UPDATE, DELETE, etc.).
 * - Transaction management with commit and rollback functionality.
 * - Fetching results in different formats (array, object, etc.).
 * - Generating unique IDs and retrieving the last inserted ID.
 * 
 * Example usage:
 * ```php
 * $db = new PicoDatabase($credentials);
 * $db->connect();
 * $result = $db->fetch("SELECT * FROM users WHERE id = 1");
 * ```
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabase //NOSONAR
{
    const QUERY_INSERT = "insert";
    const QUERY_UPDATE = "update";
    const QUERY_DELETE = "delete";
    const QUERY_TRANSACTION = "transaction";
    const DATABASE_NONECTION_IS_NULL = "Database connection is null";

    /**
     * Database credential.
     *
     * @var SecretObject
     */
    protected $databaseCredentials;

    /**
     * Indicates whether the database is connected or not.
     *
     * @var bool
     */
    protected $connected = false;

    /**
     * Autocommit setting.
     *
     * @var bool
     */
    protected $autocommit = true;

    /**
     * Database connection.
     *
     * @var PDO
     */
    protected $databaseConnection;

    /**
     * Database type.
     *
     * @var string
     */
    protected $databaseType = "";

    /**
     * Callback function when executing queries that modify data.
     *
     * @var callable|null
     */
    protected $callbackExecuteQuery = null;

    /**
     * Callback function when executing any query.
     *
     * @var callable|null
     */
    protected $callbackDebugQuery = null;

    /**
     * Constructor to initialize the PicoDatabase object.
     *
     * @param SecretObject $databaseCredentials Database credentials.
     * @param callable|null $callbackExecuteQuery Callback for executing modifying queries. Parameter 1 is SQL, parameter 2 is one of query type (PicoDatabase::QUERY_INSERT, PicoDatabase::QUERY_UPDATE, PicoDatabase::QUERY_DELETE, PicoDatabase::QUERY_TRANSACTION).
     * @param callable|null $callbackDebugQuery Callback for debugging queries. Parameter 1 is SQL.
     */
    public function __construct($databaseCredentials, $callbackExecuteQuery = null, $callbackDebugQuery = null)
    {
        $this->databaseCredentials = $databaseCredentials;
        if ($callbackExecuteQuery !== null && is_callable($callbackExecuteQuery)) {
            $this->callbackExecuteQuery = $callbackExecuteQuery;
        }
        if ($callbackDebugQuery !== null && is_callable($callbackDebugQuery)) {
            $this->callbackDebugQuery = $callbackDebugQuery;
        }
    }

    /**
     * Connect to the database.
     *
     * Establishes a connection to the specified database type. Optionally selects a database if the 
     * connection is to an RDMS and the flag is set.
     *
     * @param bool $withDatabase Flag to select the database when connected (default is true).
     * @return bool True if the connection is successful, false if it fails.
     */
    public function connect($withDatabase = true)
    {
        $databaseTimeZone = $this->databaseCredentials->getTimeZone();      
        if ($databaseTimeZone !== null && !empty($databaseTimeZone)) {
            date_default_timezone_set($this->databaseCredentials->getTimeZone());
        }
        $this->databaseType = $this->getDbType($this->databaseCredentials->getDriver());
        if ($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            return $this->connectSqlite();
        }
        else
        {
            return $this->connectRDMS($withDatabase);
        }
    }
    
    /**
     * Connect to SQLite database.
     *
     * Establishes a connection to an SQLite database using the specified file path in the credentials.
     * Throws an exception if the database path is not set or is empty.
     *
     * @return bool True if the connection is successful, false if it fails.
     * @throws InvalidDatabaseConfiguration If the database path is empty.
     * @throws PDOException If the connection fails with an error.
     */
    private function connectSqlite()
    {
        $connected = false;
        $path = $this->databaseCredentials->getDatabaseFilePath();
        if(!isset($path) || empty($path))
        {
            throw new InvalidDatabaseConfiguration("Database path may not be empty. Please check your database configuration!");
        }
        try {
            $this->databaseConnection = new PDO("sqlite:" . $path);
            $this->databaseConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $connected = true;
            $this->connected = true;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
        return $connected;
    }
    
    /**
     * Connect to the RDMS (Relational Database Management System).
     *
     * Establishes a connection to an RDMS database using the provided credentials and optionally selects 
     * a specific database based on the provided flag. Sets the time zone for the connection and handles 
     * schema settings for PostgreSQL.
     *
     * @param bool $withDatabase Flag to select the database when connected (default is true).
     * @return bool True if the connection is successful, false if it fails.
     * @throws InvalidDatabaseConfiguration If the database username is empty.
     * @throws PDOException If the connection fails with an error.
     */
    private function connectRDMS($withDatabase = true)
    {
        $connected = false;
        $timeZoneOffset = date("P");
        try {
            $connectionString = $this->constructConnectionString($withDatabase);
            if (!$this->databaseCredentials->issetUsername()) {
                throw new InvalidDatabaseConfiguration("Database username may not be empty. Please check your database configuration!");
            }
            $initialQueries = "SET time_zone = '$timeZoneOffset';";
            if ($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_POSTGRESQL &&
                $this->databaseCredentials->getDatabaseSchema() != null && 
                $this->databaseCredentials->getDatabaseSchema() != "") {
                $initialQueries .= "SET search_path TO " . $this->databaseCredentials->getDatabaseSchema();
            }
            $this->databaseConnection = new PDO(
                $connectionString,
                $this->databaseCredentials->getUsername(),
                $this->databaseCredentials->getPassword(),
                [
                    PDO::MYSQL_ATTR_INIT_COMMAND => $initialQueries,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_FOUND_ROWS => true
                ]
            );
            $connected = true;
            $this->connected = $connected;
        } catch (Exception $e) {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
        return $connected;
    }
    
    /**
     * Determine the database type based on the provided database type string.
     *
     * This method checks the input string for common database type identifiers (SQLite, PostgreSQL, 
     * MariaDB, MySQL) and returns the corresponding constant from the PicoDatabaseType class.
     *
     * @param string $databaseType The database type string to evaluate.
     * @return string The corresponding database type constant from PicoDatabaseType.
     */
    private function getDbType($databaseType) // NOSONAR
    {
        if(stripos($databaseType, 'sqlite') !== false)
        {
            return PicoDatabaseType::DATABASE_TYPE_SQLITE;
        }
        else if(stripos($databaseType, 'postgre') !== false || stripos($databaseType, 'pgsql') !== false)
        {
            return PicoDatabaseType::DATABASE_TYPE_POSTGRESQL;
        }
        else if(stripos($databaseType, 'maria') !== false)
        {
            return PicoDatabaseType::DATABASE_TYPE_MARIADB;
        }
        else
        {
            return PicoDatabaseType::DATABASE_TYPE_MYSQL;
        }
    }

    /**
     * Create a connection string.
     *
     * @param bool $withDatabase Flag to select the database when connected.
     * @return string The constructed connection string.
     * @throws InvalidDatabaseConfiguration If database configuration is invalid.
     */
    private function constructConnectionString($withDatabase = true)
    {
        $emptyDriver = !$this->databaseCredentials->issetDriver();
        $emptyHost = !$this->databaseCredentials->issetHost();
        $emptyPort = !$this->databaseCredentials->issetPort();
        $emptyName = !$this->databaseCredentials->issetDatabaseName();
        $emptyValue = "";
        $emptyValue .= $emptyDriver ? "{driver}" : "";
        $emptyValue .= $emptyHost ? "{host}" : "";
        $emptyValue .= $emptyPort ? "{port}" : "";
        $invalidParam1 = $emptyDriver || $emptyHost || $emptyPort;

        if ($withDatabase) {
            if ($invalidParam1 || $emptyName) {
                $emptyValue .= $emptyName ? "{database_name}" : "";
                throw new InvalidDatabaseConfiguration("Invalid database configuration. $emptyValue. Please check your database configuration!");
            }
            return $this->databaseCredentials->getDriver() . ':host=' . $this->databaseCredentials->getHost() . '; port=' . ((int) $this->databaseCredentials->getPort()) . '; dbname=' . $this->databaseCredentials->getDatabaseName();
        } else {
            if ($invalidParam1) {
                throw new InvalidDatabaseConfiguration("Invalid database configuration. $emptyValue. Please check your database configuration!");
            }
            return $this->databaseCredentials->getDriver() . ':host=' . $this->databaseCredentials->getHost() . '; port=' . ((int) $this->databaseCredentials->getPort());
        }
    }

    /**
     * Disconnect from the database.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function disconnect()
    {
        $this->databaseConnection = null;
        return $this;
    }

    /**
     * Set the time zone offset.
     *
     * @param string $timeZoneOffset Client time zone.
     * @return self Returns the current instance for method chaining.
     */
    public function setTimeZoneOffset($timeZoneOffset)
    {
        $sql = "SET time_zone='$timeZoneOffset';";
        $this->execute($sql);
        return $this;
    }

    /**
     * Change the database.
     *
     * @param string $databaseName Database name.
     * @return self Returns the current instance for method chaining.
     */
    public function useDatabase($databaseName)
    {
        $sql = "USE $databaseName;";
        $this->execute($sql);
        return $this;
    }

    /**
     * Set autocommit ON or OFF.
     *
     * @param bool $autocommit Flag autocommit.
     * @return bool True if autocommit is set successfully, false otherwise.
     */
    public function setAudoCommit($autocommit)
    {
        $this->autocommit = $autocommit;
        return $this->databaseConnection->setAttribute(PDO::ATTR_AUTOCOMMIT, $this->autocommit ? 1 : 0);
    }

    /**
     * Commit the transaction.
     *
     * @return bool True if the transaction was committed successfully, false otherwise.
     */
    public function commit()
    {
        return $this->databaseConnection->commit();
    }

    /**
     * Rollback the transaction.
     *
     * @return bool True if the transaction was rolled back successfully, false otherwise.
     */
    public function rollback()
    {
        return $this->databaseConnection->rollback();
    }

    /**
     * Get the database connection.
     *
     * @return PDO Represents a connection between PHP and a database server.
     */
    public function getDatabaseConnection()
    {
        return $this->databaseConnection;
    }

    /**
     * Execute a query.
     *
     * @param string $sql SQL to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or false on failure.
     * @throws PDOException If an error occurs while executing the query.
     */
    public function query($sql, $params = null)
    {
        return $this->executeQuery($sql, $params);
    }

    /**
     * Fetch a result.
     *
     * @param string $sql SQL to be executed.
     * @param int $tentativeType Tentative type for fetch mode (e.g., PDO::FETCH_ASSOC).
     * @param mixed $defaultValue Default value to return if no results found.
     * @param array|null $params Optional parameters for the SQL query.
     * @return array|object|stdClass|null Returns the fetched result as an array, object, or stdClass, or the default value if no results are found.
     */
    public function fetch($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null, $params = null)
    {
        if ($this->databaseConnection == null) {
            throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
        }
        
        $result = [];
        $this->executeDebug($sql);
        $stmt = $this->databaseConnection->prepare($sql);
        
        try {
            $stmt->execute($params);
            if($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
            {
                $result = $stmt->fetch($tentativeType);
                if($result === false)
                {
                    $result = $defaultValue;
                }
            }
            else
            {
                $result = $stmt->rowCount() > 0 ? $stmt->fetch($tentativeType) : $defaultValue;
            }
        } catch (PDOException $e) {
            $result = $defaultValue;
        }
        
        return $result;
    }

    /**
     * Check if a record exists.
     *
     * @param string $sql SQL to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @return bool True if the record exists, false otherwise.
     * @throws NullPointerException If the database connection is null.
     */
    public function isRecordExists($sql, $params = null)
    {
        if ($this->databaseConnection == null) {
            throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
        }
        
        $this->executeDebug($sql);
        $stmt = $this->databaseConnection->prepare($sql);
        
        try {
            $stmt->execute($params);
            if($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
            {
                $result = $stmt->fetch();
                return $result !== false;
            }
            else
            {
                return $stmt->rowCount() > 0;
            }
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
    }

    /**
     * Fetch all results.
     *
     * @param string $sql SQL to be executed.
     * @param int $tentativeType Tentative type for fetch mode (e.g., PDO::FETCH_ASSOC).
     * @param mixed $defaultValue Default value to return if no results found.
     * @param array|null $params Optional parameters for the SQL query.
     * @return array|null Returns an array of results or the default value if no results are found.
     */
    public function fetchAll($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null, $params = null)
    {
        if ($this->databaseConnection == null) {
            throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
        }
        
        $result = [];
        $this->executeDebug($sql);
        $stmt = $this->databaseConnection->prepare($sql);
        
        try {
            $stmt->execute($params);
            if($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLITE)
            {
                $result = $stmt->fetch($tentativeType);
                if($result === false)
                {
                    $result = $defaultValue;
                }
            }
            else
            {
                $result = $stmt->rowCount() > 0 ? $stmt->fetchAll($tentativeType) : $defaultValue;
            }
        } catch (PDOException $e) {
            $result = $defaultValue;
        }
        
        return $result;
    }

    /**
     * Execute a query without returning anything.
     *
     * @param string $sql Query string to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @throws NullPointerException If the database connection is null.
     */
    public function execute($sql, $params = null)
    {
        if ($this->databaseConnection == null) {
            throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
        }
        
        $this->executeDebug($sql, $params);
        $stmt = $this->databaseConnection->prepare($sql);
        
        try {
            $stmt->execute($params);
        } catch (PDOException $e) {
            // Handle exception as needed
        }
    }

    /**
     * Execute a query and return the statement.
     *
     * @param string $sql Query string to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or false on failure.
     * @throws NullPointerException If the database connection is null.
     * @throws PDOException If an error occurs while executing the query.
     */
    public function executeQuery($sql, $params = null)
    {
        if ($this->databaseConnection == null) {
            throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
        }
        
        $this->executeDebug($sql, $params);
        $stmt = $this->databaseConnection->prepare($sql);
        
        try {
            $stmt->execute($params);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
        
        return $stmt;
    }

    /**
     * Execute an insert query.
     *
     * @param string $sql Query string to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or false on failure.
     */
    public function executeInsert($sql, $params = null)
    {
        $stmt = $this->executeQuery($sql, $params);
        $this->executeCallback($sql, $params, self::QUERY_INSERT);
        return $stmt;
    }

    /**
     * Execute an update query.
     *
     * @param string $sql Query string to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or false on failure.
     */
    public function executeUpdate($sql, $params = null)
    {
        $stmt = $this->executeQuery($sql, $params);
        $this->executeCallback($sql, $params, self::QUERY_UPDATE);
        return $stmt;
    }

    /**
     * Execute a delete query.
     *
     * @param string $sql Query string to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or false on failure.
     */
    public function executeDelete($sql, $params = null)
    {
        $stmt = $this->executeQuery($sql, $params);
        $this->executeCallback($sql, $params, self::QUERY_DELETE);
        return $stmt;
    }

    /**
     * Execute a transaction query.
     *
     * @param string $sql Query string to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or false on failure.
     */
    public function executeTransaction($sql, $params = null)
    {
        $stmt = $this->executeQuery($sql, $params);
        $this->executeCallback($sql, $params, self::QUERY_TRANSACTION);
        return $stmt;
    }

    /**
     * Execute a callback query function.
     *
     * @param string $query SQL to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     * @param string|null $type Query type.
     */
    private function executeCallback($query, $params = null, $type = null)
    {
        if ($this->callbackExecuteQuery !== null && is_callable($this->callbackExecuteQuery)) {
            $reflection = new ReflectionFunction($this->callbackDebugQuery);

            // Get number of parameters
            $numberOfParams = $reflection->getNumberOfParameters();
            if($numberOfParams == 3)
            {
                call_user_func($this->callbackDebugQuery, $query, $params, $type);
            }
            else
            {
                call_user_func($this->callbackDebugQuery, $query);
            }
            call_user_func($this->callbackExecuteQuery, $query, $type);
        }
    }

    /**
     * Execute a debug query function.
     *
     * @param string $query SQL to be executed.
     * @param array|null $params Optional parameters for the SQL query.
     */
    private function executeDebug($query, $params = null)
    {
        if ($this->callbackDebugQuery !== null && is_callable($this->callbackDebugQuery)) {

            $reflection = new ReflectionFunction($this->callbackDebugQuery);

            // Get number of parameters
            $numberOfParams = $reflection->getNumberOfParameters();

            if($numberOfParams == 2)
            {
                call_user_func($this->callbackDebugQuery, $query, $params);
            }
            else
            {
                call_user_func($this->callbackDebugQuery, $query);
            }
            
        }
    }

    /**
     * Generate a unique 20-byte ID.
     *
     * @return string 20 bytes unique identifier.
     */
    public function generateNewId()
    {
        $uuid = uniqid();
        if ((strlen($uuid) % 2) == 1) {
            $uuid = '0' . $uuid;
        }
        $random = sprintf('%06x', mt_rand(0, 16777215));
        return sprintf('%s%s', $uuid, $random);
    }

    /**
     * Get the last inserted ID.
     *
     * @param string|null $name Sequence name (e.g., PostgreSQL).
     * @return string|false Returns the last inserted ID as a string, or false if there was an error.
     */
    public function lastInsertId($name = null)
    {
        return $this->databaseConnection->lastInsertId($name);
    }

    /**
     * Get the value of databaseCredentials.
     *
     * @return SecretObject Returns the database credentials object.
     */
    public function getDatabaseCredentials()
    {
        return $this->databaseCredentials;
    }

    /**
     * Get indication whether the database is connected or not.
     *
     * @return bool Returns true if connected, false otherwise.
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Get the database type.
     *
     * @return string Returns the type of the database (e.g., MySQL, PostgreSQL).
     */
    public function getDatabaseType()
    {
        return $this->databaseType;
    }

    /**
     * Convert the object to a JSON string representation for debugging.
     *
     * This method is intended for debugging purposes only and provides 
     * a JSON representation of the object's state.
     *
     * @return string The JSON representation of the object.
     */
    public function __toString()
    {
        $val = new stdClass;
        $val->databaseType = $this->databaseType;
        $val->autocommit = $this->autocommit;
        $val->connected = $this->connected;
        return json_encode($val);
    }


    /**
     * Get callback function when executing queries that modify data.
     *
     * @return callable|null
     */ 
    public function getCallbackExecuteQuery()
    {
        return $this->callbackExecuteQuery;
    }

    /**
     * Set callback function when executing queries that modify data.
     *
     * @param callable|null  $callbackExecuteQuery  Callback function when executing queries that modify data.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setCallbackExecuteQuery($callbackExecuteQuery)
    {
        $this->callbackExecuteQuery = $callbackExecuteQuery;

        return $this;
    }

    /**
     * Get callback function when executing any query.
     *
     * @return callable|null
     */ 
    public function getCallbackDebugQuery()
    {
        return $this->callbackDebugQuery;
    }

    /**
     * Set callback function when executing any query.
     *
     * @param callable|null  $callbackDebugQuery  Callback function when executing any query.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setCallbackDebugQuery($callbackDebugQuery)
    {
        $this->callbackDebugQuery = $callbackDebugQuery;

        return $this;
    }
}
