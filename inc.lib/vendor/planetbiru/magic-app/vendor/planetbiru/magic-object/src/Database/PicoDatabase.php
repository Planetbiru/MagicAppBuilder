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
class PicoDatabase // NOSONAR
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
     * Creates a PicoDatabase instance from an existing PDO connection.
     *
     * This static method accepts a PDO connection object, initializes a new 
     * PicoDatabase instance, and sets up the database connection and type.
     * It also marks the database as connected and returns the configured 
     * PicoDatabase object.
     *
     * @param PDO $pdo The PDO connection object representing an active connection to the database.
     * @return PicoDatabase Returns a new instance of the PicoDatabase class, 
     *         with the PDO connection and database type set.
     */
    public static function fromPdo($pdo)
    {
        $database = new self(new SecretObject());
        $database->databaseConnection = $pdo;
        $database->databaseType = $database->getDbType($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        $database->connected = true;
        $database->databaseCredentials = $database->getDatabaseCredentialsFromPdo($pdo);
        return $database;
    }

    /**
     * Get PDO connection details, including driver, host, port, database name, schema, and time zone.
     *
     * This function retrieves information about the PDO connection, such as the database driver, host, port, 
     * database name, schema, and time zone based on the type of database (e.g., MySQL, PostgreSQL, SQLite).
     *
     * It uses the PDO connection's attributes and queries the database if necessary to obtain the schema name and time zone.
     *
     * @param PDO $pdo The PDO connection object.
     * @return SecretObject Returns a SecretObject containing the connection details (driver, host, port, database name, schema, and time zone).
     * 
     * @throws PDOException If there is an error with the PDO query or connection.
     */
    private function getDatabaseCredentialsFromPdo($pdo)
    {
        // Get the driver name (e.g., mysql, pgsql, sqlite)
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        // Get the connection status, which includes the DSN (Data Source Name)
        $dsn = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
        $dsnParts = parse_url($dsn);

        // Extract the host from the DSN (if available)
        $host = isset($dsnParts['host']) ? $dsnParts['host'] : null;

        // Extract the port from the DSN (if available)
        $port = isset($dsnParts['port']) ? $dsnParts['port'] : null;

        // Get the database name from the DSN (usually found at the end of the DSN after host and port)
        $databaseName = isset($dsnParts['path']) ? ltrim($dsnParts['path'], '/') : null;

        // Initialize the schema and time zone
        $schema = null;
        $timezone = null;

        // Determine the database type
        $dbType = $this->getDbType($driver);
        
        // Retrieve the schema and time zone based on the database type
        if ($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
            // For PostgreSQL, fetch the current schema and time zone using queries
            $stmt = $pdo->query('SELECT current_schema()');
            $schema = $stmt->fetchColumn(); // Fetch the schema name
            
            $stmtTimezone = $pdo->query('SHOW timezone');
            $systemTimeZone = $stmtTimezone->fetchColumn(); // Fetch the time zone

            $timezone = $this->pgsqlToPhpTimezone($systemTimeZone);
        }
        elseif ($dbType == PicoDatabaseType::DATABASE_TYPE_MYSQL || $dbType == PicoDatabaseType::DATABASE_TYPE_MARIADB) {
            // For MySQL, the schema is the same as the database name
            $schema = $databaseName; // MySQL schema is the database name
            
            // Retrieve the global time zone from MySQL
            $stmtTimezone = $pdo->query('SELECT @@global.time_zone');
            $timezone = $stmtTimezone->fetchColumn(); // Fetch the global time zone

            // If the time zone is set to 'SYSTEM', retrieve the system's time zone and convert it
            if ($timezone == 'SYSTEM') {
                $stmtSystemTimeZone = $pdo->query('SELECT @@system_time_zone');
                $systemTimeZone = $stmtSystemTimeZone->fetchColumn();

                // Convert MySQL system time zone to PHP-compatible time zone (e.g., 'Asia/Jakarta')
                // This conversion may require a lookup table, as MySQL system time zones
                // (e.g., 'CST', 'PST') are not directly equivalent to PHP time zones (e.g., 'Asia/Jakarta').
                // Here, we will simply return the system time zone as a placeholder:
                $timezone = $this->mysqlToPhpTimezone($systemTimeZone);
            }
        }
        else {
            // For other drivers, set schema and time zone to null (or handle it as needed)
            $schema = null;
            $timezone = date_default_timezone_get();
        }

        // Create and populate the SecretObject with the connection details
        $databaseCredentials = new SecretObject();
        $databaseCredentials->setDriver($driver);
        $databaseCredentials->setHost($host);
        $databaseCredentials->setPort($port);
        $databaseCredentials->setDatabaseName($databaseName);
        $databaseCredentials->setDatabaseSchema($schema);
        $databaseCredentials->setTimeZone($timezone);

        // Return the populated SecretObject containing the connection details
        return $databaseCredentials;
    }

    /**
     * Map MySQL system time zone or abbreviations like 'WIB' to a valid PHP time zone.
     *
     * This function converts time zone abbreviations (like 'WIB', 'WITA', 'WIT') or system time zones
     * to a recognized PHP time zone format (e.g., 'Asia/Jakarta').
     *
     * @param string $timezoneAbbr The time zone abbreviation or system time zone (e.g., 'WIB', 'SYSTEM').
     * @return string|null Returns a PHP-compatible time zone (e.g., 'Asia/Jakarta') or null if not recognized.
     */
    private function mysqlToPhpTimezone($timezoneAbbr)
    {
        $timezoneMapping = [
            // Indonesia
            'WIB'  => 'Asia/Jakarta',   // Western Indonesia Time (e.g., Jakarta, Bali)
            'WITA' => 'Asia/Makassar',  // Central Indonesia Time (e.g., Bali, Sulawesi)
            'WIT'  => 'Asia/Jayapura',  // Eastern Indonesia Time (e.g., Papua)

            // Common USA Time Zones
            'PST'  => 'America/Los_Angeles', // Pacific Standard Time (Standard Time)
            'PDT'  => 'America/Los_Angeles', // Pacific Daylight Time (Daylight Saving Time)
            'MST'  => 'America/Denver',      // Mountain Standard Time
            'MDT'  => 'America/Denver',      // Mountain Daylight Time
            'CST'  => 'America/Chicago',     // Central Standard Time
            'CDT'  => 'America/Chicago',     // Central Daylight Time
            'EST'  => 'America/New_York',    // Eastern Standard Time
            'EDT'  => 'America/New_York',    // Eastern Daylight Time
            'AKST' => 'America/Anchorage',   // Alaska Standard Time
            'AKDT' => 'America/Anchorage',   // Alaska Daylight Time
            'HST'  => 'Pacific/Honolulu',    // Hawaii Standard Time

            // United Kingdom
            'GMT'  => 'Europe/London',      // Greenwich Mean Time (Standard Time)
            'BST'  => 'Europe/London',      // British Summer Time (Daylight Saving Time)

            // Central Europe
            'CET'  => 'Europe/Paris',       // Central European Time
            'CEST' => 'Europe/Paris',       // Central European Summer Time (Daylight Saving Time)

            // Central Asia and Russia
            'MSK'  => 'Europe/Moscow',      // Moscow Standard Time
            'MSD'  => 'Europe/Moscow',      // Moscow Daylight Time (not used anymore)

            // Australia
            'AEST' => 'Australia/Sydney',   // Australian Eastern Standard Time
            'AEDT' => 'Australia/Sydney',   // Australian Eastern Daylight Time
            'ACST' => 'Australia/Adelaide', // Australian Central Standard Time
            'ACDT' => 'Australia/Adelaide', // Australian Central Daylight Time
            'AWST' => 'Australia/Perth',    // Australian Western Standard Time

            // Africa
            'CAT'  => 'Africa/Harare',      // Central Africa Time
            'EAT'  => 'Africa/Nairobi',     // East Africa Time
            'WAT'  => 'Africa/Algiers',     // West Africa Time

            // India
            'IST'  => 'Asia/Kolkata',       // Indian Standard Time

            // China and East Asia
            'CST'  => 'Asia/Shanghai',      // China Standard Time
            'JST'  => 'Asia/Tokyo',         // Japan Standard Time
            'KST'  => 'Asia/Seoul',         // Korea Standard Time

            // Other time zones
            'UTC'  => 'UTC',                // Coordinated Universal Time
            'Z'    => 'UTC',                // Zulu time (same as UTC)
            'ART'  => 'Africa/Argentina',   // Argentina Time
            'NFT'  => 'Pacific/Norfolk',    // Norfolk Time Zone (Australia)

            // Time zones used in specific areas
            'NST'  => 'Asia/Kolkata',       // Newfoundland Standard Time (if used as an abbreviation)
        ];

        // Return the mapped PHP time zone or null if not found
        return isset($timezoneMapping[$timezoneAbbr]) ? $timezoneMapping[$timezoneAbbr] : null;
    }

    /**
     * Converts a PostgreSQL timezone string to a PHP-compatible timezone identifier.
     *
     * @param string $pgsqlTimezone The PostgreSQL timezone string (e.g., from SHOW timezone).
     * @return string|null The PHP timezone identifier or null if no match is found.
     */
    private function pgsqlToPhpTimezone($pgsqlTimezone)
    {
        // Comprehensive map of PostgreSQL timezones to PHP-compatible timezone identifiers
        $timezoneMap = [
            // UTC and variants
            'UTC' => 'UTC',
            'Etc/UTC' => 'UTC',
            'Etc/GMT' => 'UTC',

            // Common regions
            'US/Eastern' => 'America/New_York',
            'US/Central' => 'America/Chicago',
            'US/Mountain' => 'America/Denver',
            'US/Pacific' => 'America/Los_Angeles',
            'US/Alaska' => 'America/Anchorage',
            'US/Hawaii' => 'Pacific/Honolulu',
            'America/Chicago' => 'America/Chicago',
            'America/New_York' => 'America/New_York',
            'America/Los_Angeles' => 'America/Los_Angeles',
            'America/Denver' => 'America/Denver',
            'America/Anchorage' => 'America/Anchorage',
            'Pacific/Honolulu' => 'Pacific/Honolulu',

            // European timezones
            'Europe/London' => 'Europe/London',
            'Europe/Berlin' => 'Europe/Berlin',
            'Europe/Paris' => 'Europe/Paris',
            'Europe/Madrid' => 'Europe/Madrid',
            'Europe/Rome' => 'Europe/Rome',
            'Europe/Moscow' => 'Europe/Moscow',

            // Asia timezones
            'Asia/Tokyo' => 'Asia/Tokyo',
            'Asia/Shanghai' => 'Asia/Shanghai',
            'Asia/Kolkata' => 'Asia/Kolkata',
            'Asia/Dubai' => 'Asia/Dubai',
            'Asia/Singapore' => 'Asia/Singapore',
            'Asia/Hong_Kong' => 'Asia/Hong_Kong',
            'Asia/Seoul' => 'Asia/Seoul',

            // Australia timezones
            'Australia/Sydney' => 'Australia/Sydney',
            'Australia/Melbourne' => 'Australia/Melbourne',
            'Australia/Brisbane' => 'Australia/Brisbane',
            'Australia/Perth' => 'Australia/Perth',
            'Australia/Adelaide' => 'Australia/Adelaide',

            // Africa timezones
            'Africa/Johannesburg' => 'Africa/Johannesburg',
            'Africa/Cairo' => 'Africa/Cairo',
            'Africa/Lagos' => 'Africa/Lagos',

            // South America
            'America/Sao_Paulo' => 'America/Sao_Paulo',
            'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos_Aires',
            'America/Santiago' => 'America/Santiago',

            // Additional aliases or abbreviations
            'EST' => 'America/New_York',
            'CST' => 'America/Chicago',
            'MST' => 'America/Denver',
            'PST' => 'America/Los_Angeles',
        ];

        // Check for exact matches in the map
        if (isset($timezoneMap[$pgsqlTimezone])) {
            return $timezoneMap[$pgsqlTimezone];
        }

        // Use PHP's timezone database as a fallback for direct matches
        if (in_array($pgsqlTimezone, timezone_identifiers_list(), true)) {
            return $pgsqlTimezone;
        }

        // Return null if no match is found
        return null;
    }

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
            throw new InvalidDatabaseConfiguration("Database path may not be empty. Please check your database configuration on {database_file_path}!");
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
            if ($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_PGSQL &&
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
     * This method evaluates the given string to identify common database type names
     * (e.g., SQLite, PostgreSQL, MariaDB, MySQL) and returns the corresponding 
     * constant from the `PicoDatabaseType` class that represents the type of database.
     * The function performs case-insensitive string matching using `stripos` to check for
     * keywords like "sqlite", "postgre", "pgsql", "maria", and defaults to MySQL if no match is found.
     *
     * @param string $databaseType The database type string to evaluate, such as 'SQLite', 'PostgreSQL', 'MariaDB', or 'MySQL'.
     * @return string The corresponding database type constant from `PicoDatabaseType`:
     *                - `PicoDatabaseType::DATABASE_TYPE_SQLITE`
     *                - `PicoDatabaseType::DATABASE_TYPE_PGSQL`
     *                - `PicoDatabaseType::DATABASE_TYPE_MARIADB`
     *                - `PicoDatabaseType::DATABASE_TYPE_MYSQL`
     */
    private function getDbType($databaseType) // NOSONAR
    {
        if(stripos($databaseType, 'sqlite') !== false)
        {
            return PicoDatabaseType::DATABASE_TYPE_SQLITE;
        }
        else if(stripos($databaseType, 'postgre') !== false || stripos($databaseType, 'pgsql') !== false)
        {
            return PicoDatabaseType::DATABASE_TYPE_PGSQL;
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
     * Determines the database driver based on the provided database type.
     *
     * This function takes a string representing the database type and returns 
     * the corresponding database driver constant from the `PicoDatabaseType` class.
     * It supports SQLite, PostgreSQL, and MySQL/MariaDB types.
     *
     * @param string $databaseType The type of the database (e.g., 'sqlite', 'postgres', 'pgsql', 'mysql', 'mariadb').
     * 
     * @return string The corresponding database driver constant, one of:
     *                - `sqlite`
     *                - `pgsql`
     *                - `mysql`
     */
    private function getDbDriver($databaseType)
    {
        if (stripos($databaseType, 'sqlite') !== false) {
            return PicoDatabaseType::DATABASE_TYPE_SQLITE;
        } else if (stripos($databaseType, 'postgre') !== false || stripos($databaseType, 'pgsql') !== false) {
            return PicoDatabaseType::DATABASE_TYPE_PGSQL;
        } else {
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
            return $this->getDbDriver($this->databaseCredentials->getDriver()) . ':host=' . $this->databaseCredentials->getHost() . '; port=' . ((int) $this->databaseCredentials->getPort()) . '; dbname=' . $this->databaseCredentials->getDatabaseName();
        } else {
            if ($invalidParam1) {
                throw new InvalidDatabaseConfiguration("Invalid database configuration. $emptyValue. Please check your database configuration!");
            }
            return $this->getDbDriver($this->databaseCredentials->getDriver()) . ':host=' . $this->databaseCredentials->getHost() . '; port=' . ((int) $this->databaseCredentials->getPort());
        }
    }

    /**
     * Disconnect from the database.
     *
     * This method sets the database connection to `null`, effectively closing the connection to the database.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function disconnect()
    {
        $this->databaseConnection = null;
        return $this;
    }

    /**
     * Set the time zone offset for the database session.
     *
     * This method sets the time zone offset for the current session, which can be useful for time-related operations.
     *
     * @param string $timeZoneOffset The time zone offset to set for the session (e.g., '+00:00', 'Europe/London').
     * @return self Returns the current instance for method chaining.
     */
    public function setTimeZoneOffset($timeZoneOffset)
    {
        $sql = "SET time_zone='$timeZoneOffset';";
        $this->execute($sql);
        return $this;
    }

    /**
     * Switch to a different database.
     *
     * This method changes the currently active database to the specified one.
     *
     * @param string $databaseName The name of the database to switch to.
     * @return self Returns the current instance for method chaining.
     */
    public function useDatabase($databaseName)
    {
        $sql = "USE $databaseName;";
        $this->execute($sql);
        return $this;
    }

    /**
     * Set autocommit mode for transactions.
     *
     * This method enables or disables autocommit mode for database transactions. When autocommit is off,
     * you must explicitly call `commit()` or `rollback()` to finalize or revert the transaction.
     *
     * @param bool $autocommit Flag indicating whether autocommit should be enabled (`true`) or disabled (`false`).
     * @return bool Returns `true` if the autocommit setting was successfully updated, `false` otherwise.
     */
    public function setAudoCommit($autocommit)
    {
        $this->autocommit = $autocommit;
        return $this->databaseConnection->setAttribute(PDO::ATTR_AUTOCOMMIT, $this->autocommit ? 1 : 0);
    }

    /**
     * Commit the current transaction.
     *
     * This method commits the transaction, making all changes made during the transaction permanent.
     *
     * @return bool Returns `true` if the transaction was successfully committed, `false` otherwise.
     */
    public function commit()
    {
        return $this->databaseConnection->commit();
    }

    /**
     * Rollback the current transaction.
     *
     * This method rolls back the transaction, undoing any changes made during the transaction.
     *
     * @return bool Returns `true` if the transaction was successfully rolled back, `false` otherwise.
     */
    public function rollback()
    {
        return $this->databaseConnection->rollback();
    }

    /**
     * Get the current database connection.
     *
     * This method returns the active PDO connection object, which can be used for executing queries directly.
     *
     * @return PDO The active PDO connection object representing the connection to the database server.
     */
    public function getDatabaseConnection()
    {
        return $this->databaseConnection;
    }

    /**
     * Execute a SQL query.
     *
     * This method executes a SQL query with optional parameters and returns the resulting PDO statement object.
     *
     * @param string $sql The SQL query to execute.
     * @param array|null $params Optional parameters to bind to the query.
     * @return PDOStatement|false Returns a `PDOStatement` object if the query was executed successfully, 
     *                             or `false` if the execution failed.
     * @throws PDOException If an error occurs while executing the query.
     */
    public function query($sql, $params = null)
    {
        return $this->executeQuery($sql, $params);
    }

    /**
     * Fetch a result from the database.
     *
     * This method executes a query and returns a single result. If no result is found, the default value is returned.
     *
     * @param string $sql SQL query to be executed.
     * @param int $tentativeType The fetch mode to be used (e.g., PDO::FETCH_ASSOC).
     * @param mixed $defaultValue The default value to return if no results are found.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return array|object|stdClass|null Returns the fetched result (array, object, or stdClass), or the default value if no results are found.
     */
    public function fetch($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null, $params = null)
    {
        if ($this->databaseConnection == null) {
            throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
        }
        
        $result = array();
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
     * Check if a record exists in the database.
     *
     * This method executes a query and checks if any record is returned.
     *
     * @param string $sql SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return bool Returns `true` if the record exists, `false` otherwise.
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
     * Fetch all results from the database.
     *
     * This method executes a query and returns all matching results. If no results are found, the default value is returned.
     *
     * @param string $sql SQL query to be executed.
     * @param int $tentativeType The fetch mode to be used (e.g., PDO::FETCH_ASSOC).
     * @param mixed $defaultValue The default value to return if no results are found.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return array|null Returns an array of results or the default value if no results are found.
     */
    public function fetchAll($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null, $params = null)
    {
        if ($this->databaseConnection == null) {
            throw new NullPointerException(self::DATABASE_NONECTION_IS_NULL);
        }
        
        $result = array();
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
     * Execute a SQL query without returning any results.
     *
     * This method executes a query without expecting any result, typically used for non-SELECT queries (INSERT, UPDATE, DELETE).
     *
     * @param string $sql SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or `false` on failure.
     * @throws NullPointerException If the database connection is null.
     * @throws PDOException If an error occurs while executing the query.
     */
    public function execute($sql, $params = null)
    {
        return $this->executeQuery($sql, $params);
    }

    /**
     * Execute a SQL query and return the statement object.
     *
     * This method executes a query and returns the PDOStatement object, which can be used to fetch results or retrieve row count.
     *
     * @param string $sql SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or `false` on failure.
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
     * Execute an insert query and return the statement.
     *
     * This method executes an insert query and returns the PDOStatement object.
     *
     * @param string $sql SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or `false` on failure.
     */
    public function executeInsert($sql, $params = null)
    {
        $stmt = $this->executeQuery($sql, $params);
        $this->executeCallback($sql, $params, self::QUERY_INSERT);
        return $stmt;
    }

    /**
     * Execute an update query and return the statement.
     *
     * This method executes an update query and returns the PDOStatement object.
     *
     * @param string $sql SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or `false` on failure.
     */
    public function executeUpdate($sql, $params = null)
    {
        $stmt = $this->executeQuery($sql, $params);
        $this->executeCallback($sql, $params, self::QUERY_UPDATE);
        return $stmt;
    }

    /**
     * Execute a delete query and return the statement.
     *
     * This method executes a delete query and returns the PDOStatement object.
     *
     * @param string $sql SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or `false` on failure.
     */
    public function executeDelete($sql, $params = null)
    {
        $stmt = $this->executeQuery($sql, $params);
        $this->executeCallback($sql, $params, self::QUERY_DELETE);
        return $stmt;
    }

    /**
     * Execute a transaction query and return the statement.
     *
     * This method executes a query as part of a transaction and returns the PDOStatement object.
     *
     * @param string $sql SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @return PDOStatement|false Returns the PDOStatement object if successful, or `false` on failure.
     */
    public function executeTransaction($sql, $params = null)
    {
        $stmt = $this->executeQuery($sql, $params);
        $this->executeCallback($sql, $params, self::QUERY_TRANSACTION);
        return $stmt;
    }

    /**
     * Execute a callback query function after executing the query.
     *
     * This method calls the provided callback function after executing a query.
     *
     * @param string $query SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
     * @param string|null $type Type of the query (e.g., INSERT, UPDATE, DELETE, etc.).
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
     * This method calls a debug callback function if it is set.
     *
     * @param string $query SQL query to be executed.
     * @param array|null $params Optional parameters to bind to the SQL query.
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
     * This method generates a unique ID by concatenating a 13-character string
     * from `uniqid()` with a 6-character random hexadecimal string, ensuring
     * the resulting string is 20 characters in length.
     *
     * @return string A unique 20-byte identifier.
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
     * This method retrieves the ID of the last inserted record. Optionally,
     * you can provide a sequence name (e.g., for PostgreSQL) to fetch the last
     * inserted ID from a specific sequence.
     *
     * @param string|null $name The sequence name (e.g., PostgreSQL). Default is null.
     * @return string|false Returns the last inserted ID as a string, or false if there was an error.
     */
    public function lastInsertId($name = null)
    {
        return $this->databaseConnection->lastInsertId($name);
    }

    /**
     * Get the value of database credentials.
     *
     * This method returns the object containing the database credentials used
     * to establish the database connection.
     *
     * @return SecretObject The database credentials object.
     */
    public function getDatabaseCredentials()
    {
        return $this->databaseCredentials;
    }

    /**
     * Check whether the database is connected.
     *
     * This method returns a boolean value indicating whether the database
     * connection is currently active.
     *
     * @return bool Returns true if connected, false otherwise.
     */
    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * Get the type of the database.
     *
     * This method returns the type of the database that is currently connected.
     * The possible values are constants from the `PicoDatabaseType` class:
     * - `PicoDatabaseType::DATABASE_TYPE_MYSQL`
     * - `PicoDatabaseType::DATABASE_TYPE_MARIADB`
     * - `PicoDatabaseType::DATABASE_TYPE_PGSQL`
     * - `PicoDatabaseType::DATABASE_TYPE_SQLITE`
     *
     * @return string The type of the database.
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
     * Get the callback function to be executed when modifying data with queries.
     *
     * This function returns the callback that is invoked when executing queries 
     * that modify data (e.g., `INSERT`, `UPDATE`, `DELETE`).
     *
     * @return callable|null The callback function, or null if no callback is set.
     */
    public function getCallbackExecuteQuery()
    {
        return $this->callbackExecuteQuery;
    }

    /**
     * Set the callback function to be executed when modifying data with queries.
     *
     * This method sets the callback to be invoked when executing queries 
     * that modify data (e.g., `INSERT`, `UPDATE`, `DELETE`).
     *
     * @param callable|null $callbackExecuteQuery The callback function to set, or null to unset the callback.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setCallbackExecuteQuery($callbackExecuteQuery)
    {
        $this->callbackExecuteQuery = $callbackExecuteQuery;

        return $this;
    }

    /**
     * Get the callback function to be executed when executing any query.
     *
     * This function returns the callback that is invoked for any type of query, 
     * whether it's a read (`SELECT`) or modify (`INSERT`, `UPDATE`, `DELETE`).
     *
     * @return callable|null The callback function, or null if no callback is set.
     */
    public function getCallbackDebugQuery()
    {
        return $this->callbackDebugQuery;
    }

    /**
     * Set the callback function to be executed when executing any query.
     *
     * This method sets the callback to be invoked for any type of query, 
     * whether it's a read (`SELECT`) or modify (`INSERT`, `UPDATE`, `DELETE`).
     *
     * @param callable|null $callbackDebugQuery The callback function to set, or null to unset the callback.
     * @return self Returns the current instance for method chaining.
     */
    public function setCallbackDebugQuery($callbackDebugQuery)
    {
        $this->callbackDebugQuery = $callbackDebugQuery;

        return $this;
    }
}
