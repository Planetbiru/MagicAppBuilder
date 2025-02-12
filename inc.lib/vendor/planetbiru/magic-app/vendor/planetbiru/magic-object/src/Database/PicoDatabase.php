<?php

namespace MagicObject\Database;

use DateTime;
use DateTimeZone;
use Exception;
use PDO;
use PDOException;
use PDOStatement;
use MagicObject\Exceptions\InvalidDatabaseConfiguration;
use MagicObject\Exceptions\NullPointerException;
use MagicObject\Exceptions\UnsupportedDatabaseException;
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
 * **Example:**
 * ```php
 * <?php
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
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $dbType = self::getDbType($driver);
        $database = new self(new SecretObject());
        $database->databaseConnection = $pdo;
        $database->databaseType = $dbType;
        $database->databaseCredentials = self::getDatabaseCredentialsFromPdo($pdo, $driver, $dbType);
        $database->connected = true;
        return $database;
    }

    /**
     * Retrieves detailed information about a PDO database connection.
     *
     * This method extracts and organizes connection details, including:
     * - Database driver (e.g., 'mysql', 'pgsql', 'sqlite').
     * - Host and port (if available).
     * - Database name (derived from the connection DSN).
     * - Schema (for applicable databases like PostgreSQL).
     * - Time zone (calculated from the database offset or default PHP time zone).
     *
     * The extraction process dynamically adapts to the type of database (e.g., MySQL, PostgreSQL, SQLite).
     * For PostgreSQL, the schema is determined using a database query. Time zone information is calculated 
     * by converting the database offset to a corresponding PHP time zone where possible.
     *
     * The resulting connection details are encapsulated in a `SecretObject` for secure handling and organized access.
     *
     * @param PDO    $pdo    The PDO connection object.
     * @param string $driver The name of the database driver (e.g., 'mysql', 'pgsql', 'sqlite').
     * @param string $dbType The database type constant as defined in `PicoDatabaseType`.
     *
     * @return SecretObject A `SecretObject` instance containing the following properties:
     *                      - `driver`: The database driver (e.g., 'mysql', 'pgsql').
     *                      - `host`: The database host (e.g., 'localhost').
     *                      - `port`: The database port (e.g., 3306 for MySQL, 5432 for PostgreSQL).
     *                      - `databaseName`: The name of the database.
     *                      - `databaseSchema`: The schema name (if applicable, e.g., 'public' for PostgreSQL).
     *                      - `timeZone`: The database time zone (e.g., 'UTC+02:00').
     *
     * @throws PDOException If an error occurs during database interaction, such as a query failure or
     *                      attribute access issue.
     */
    private static function getDatabaseCredentialsFromPdo($pdo, $driver, $dbType)
    {
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
        
        // Retrieve the schema and time zone based on the database type
        if ($dbType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
            // For PostgreSQL, fetch the current schema and time zone using queries
            $stmt = $pdo->query('SELECT current_schema()');
            $schema = $stmt->fetchColumn(); // Fetch the schema name
            $timezone = self::convertOffsetToTimeZone(self::getTimeZoneOffset($pdo));
        }
        else if ($dbType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $dbType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
            // For MySQL, the schema is the same as the database name
            $schema = $databaseName; // MySQL schema is the database name
            $timezone = self::convertOffsetToTimeZone(self::getTimeZoneOffset($pdo));
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
     * Retrieves the timezone offset from the database.
     *
     * This function detects the database type (MySQL, MariaDB, or PostgreSQL) from the given PDO connection
     * and executes the appropriate query to determine the timezone offset from UTC. It returns the
     * offset as a string in the format "+HH:MM" or "-HH:MM". If the database type is unsupported or
     * an error occurs, it defaults to "00:00".
     *
     * @param PDO $pdo The PDO connection object.
     * @return string The timezone offset as a string (e.g., "+08:00", "-05:30"), or "00:00" on failure.
     */
    private static function getTimeZoneOffset($pdo)
    {
        $defaultValue = '00:00';
        try {
            // Detect the database driver
            $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

            // Map the driver to a recognized database type
            $dbType = self::getDbType($driver);

            // Prepare the query based on the database type
            if ($dbType === PicoDatabaseType::DATABASE_TYPE_PGSQL) {
                // Query to retrieve timezone offset in PostgreSQL
                $query = "SELECT (EXTRACT(TIMEZONE FROM NOW()) / 3600)::TEXT || ':00' AS offset";
            } elseif (
                $dbType === PicoDatabaseType::DATABASE_TYPE_MYSQL ||
                $dbType === PicoDatabaseType::DATABASE_TYPE_MARIADB
            ) {
                // Query to retrieve timezone offset in MySQL or MariaDB
                $query = "SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP()) AS offset";
            } else {
                // Return default offset for unsupported database types
                return $defaultValue;
            }

            // Execute the query and fetch the result
            $stmt = $pdo->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Return the offset value if available
            return isset($result['offset']) ? $result['offset'] : $defaultValue;
        } catch (Exception $e) {
            // Handle any exceptions and return the default offset
            return $defaultValue;
        }
    }

    /**
     * Converts a timezone offset string to a corresponding PHP timezone name.
     *
     * This method takes a timezone offset string (e.g., "+08:00" or "-05:30") and computes
     * the total offset in seconds. It then attempts to map the offset to a standard PHP 
     * timezone name. If no matching timezone is found, it falls back to returning a 
     * UTC-based timezone string in the same offset format.
     *
     * Examples:
     * - Input: "+07:00" -> Output: "Asia/Jakarta" (if mapping exists).
     * - Input: "-05:30" -> Output: "UTC-05:30" (fallback if no mapping exists).
     *
     * @param string $offset The timezone offset string (e.g., "+07:00", "-05:30").
     * @return string The corresponding PHP timezone name, or a fallback UTC offset string (e.g., "UTC+07:00").
     */
    private static function convertOffsetToTimeZone($offset)
    {
        try {
            // Extract the sign ('+' or '-') from the offset
            $sign = substr($offset, 0, 1); // Get the first character ('+' or '-')
            
            // Split the offset into hours and minutes (e.g., "+08:00" -> [8, 0])
            $parts = explode(':', substr($offset, 1)); // Remove the sign and split
            $hours = (int)$parts[0]; // Parse the hours
            $minutes = isset($parts[1]) ? (int)$parts[1] : 0; // Parse the minutes if available
            
            // Calculate the total offset in seconds
            $totalOffsetSeconds = ($hours * 3600) + ($minutes * 60);
            if ($sign === '-') {
                $totalOffsetSeconds = -$totalOffsetSeconds; // Negate if the offset is negative
            }

            // Attempt to retrieve the PHP timezone name using the offset
            $timeZone = timezone_name_from_abbr("", $totalOffsetSeconds, 0);

            // Fallback: if no matching timezone is found, use a UTC-based string
            if ($timeZone === false) {
                $timeZone = "UTC" . $offset; // Example: "UTC+08:00"
            }

            return $timeZone;
        } catch (Exception $e) {
            return "UTC+00:00";
        }
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
        $this->databaseType = self::getDbType($this->databaseCredentials->getDriver());
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
     * Establishes a connection to an RDMS database using the provided credentials. Optionally, a specific 
     * database is selected based on the provided flag. This method also configures the time zone, character set, 
     * and schema settings (for PostgreSQL) after the connection is established.
     *
     * - The time zone is set based on the current offset (`date("P")`), or a configured value.
     * - For PostgreSQL, the client encoding (charset) is set using `SET CLIENT_ENCODING`, and the schema is set 
     *   using `SET search_path`.
     * - For MySQL, the time zone and charset are set using `SET time_zone` and `SET NAMES`.
     *
     * @param bool $withDatabase Flag to specify whether to select a database upon connection (default is true).
     *                            If true, the database is selected; otherwise, only the connection is made.
     * @return bool True if the connection is successfully established, false otherwise.
     * @throws InvalidDatabaseConfiguration If the database username is missing from the configuration.
     * @throws PDOException If an error occurs during the connection process.
     */
    private function connectRDMS($withDatabase = true)
    {
        $connected = false;
        $timeZoneOffset = date("P");
        try {
            $connectionString = $this->constructConnectionString($withDatabase);

            // Check if the database username is provided
            if (!$this->databaseCredentials->issetUsername()) {
                throw new InvalidDatabaseConfiguration("Database username may not be empty. Please check your database configuration!");
            }

            // Get charset from the database credentials
            $charset = addslashes($this->databaseCredentials->getCharset());

            // Handle PostgreSQL-specific connection settings
            if ($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
                $this->connectPostgreSql($connectionString, $timeZoneOffset, $charset);
            }
            // Handle MySQL-specific connection settings
            else if ($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_MARIADB || $this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
                $this->connectMySql($connectionString, $timeZoneOffset, $charset);
            }
            // Handle SQL Server-specific connection settings
            else if ($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_SQLSERVER) {
                $this->connectSqlServer($connectionString);
            }
            // If the database type is neither MySQL nor PostgreSQL, throw an exception
            else {
                throw new PDOException("Unsupported database type: " . $this->getDatabaseType());
            }

            // Log successful connection
            $connected = true;
            $this->connected = $connected;
        } catch (Exception $e) {
            error_log('ERR ' . $e->getMessage());
            // Handle connection errors
            throw new PDOException($e->getMessage(), intval($e->getCode()));
        }
        return $connected;
    }

    /**
     * Establish a connection to a MySQL or MariaDB database.
     *
     * This method sets up a connection to a MySQL or MariaDB database, configuring the time zone
     * and character set (charset) as needed. It runs initial queries to set the correct time zone 
     * and charset, and then establishes a PDO connection to the database.
     *
     * @param string $connectionString The connection string used to connect to the database.
     * @param string $timeZoneOffset The time zone offset to be used in the database session.
     * @param string $charset The character set (charset) to be used for the database connection.
     *
     * @return void
     *
     * @throws PDOException If there is an error while establishing the connection or executing the initial queries.
     */
    private function connectMySql($connectionString, $timeZoneOffset, $charset)
    {
        $initialQueries = array();
        // Set time zone for MySQL
        $initialQueries[] = "SET time_zone='$timeZoneOffset';";
                        
        // Add charset to the initial queries for MySQL
        if ($charset) {
            $initialQueries[] = "SET NAMES '$charset';";  // Set charset for MySQL
        }

        // MySQL connection setup
        $this->databaseConnection = new PDO(
            $connectionString,
            $this->databaseCredentials->getUsername(),
            $this->databaseCredentials->getPassword(),
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            )
        );

        if (!empty($initialQueries)) {
            foreach($initialQueries as $initialQuery)
            {
                $this->databaseConnection->exec($initialQuery);
            }
        }
    }

    /**
     * Establish a connection to a PostgreSQL database.
     *
     * This method sets up a connection to a PostgreSQL database, configuring the time zone,
     * character set (charset), and schema (search path) as needed. It runs initial queries 
     * to set the correct time zone, charset, and schema for the session, and then establishes 
     * a PDO connection to the database.
     *
     * @param string $connectionString The connection string used to connect to the PostgreSQL database.
     * @param string $timeZoneOffset The time zone offset to be used in the database session.
     * @param string $charset The character set (charset) to be used for the PostgreSQL connection.
     *
     * @return void
     *
     * @throws PDOException If there is an error while establishing the connection or executing the initial queries.
     */
    private function connectPostgreSql($connectionString, $timeZoneOffset, $charset)
    {
        $initialQueries = array();
        // Set time zone for PostgreSQL
        $initialQueries[] = "SET TIMEZONE TO '$timeZoneOffset';";

        // Set the client encoding (charset) for PostgreSQL
        if (isset($charset) && !empty($charset)) {
            $initialQueries[] = "SET CLIENT_ENCODING TO '$charset';";
        }

        // Set schema if provided for PostgreSQL
        if ($this->databaseCredentials->getDatabaseSchema() != null && $this->databaseCredentials->getDatabaseSchema() != "") {
            $initialQueries[] = "SET search_path TO " . $this->databaseCredentials->getDatabaseSchema() . ";";
        }

        // PostgreSQL connection setup
        $this->databaseConnection = new PDO(
            $connectionString,
            $this->databaseCredentials->getUsername(),
            $this->databaseCredentials->getPassword(),
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            )
        );

        // Execute the initial queries (timezone, charset, schema) in PostgreSQL
        if (!empty($initialQueries)) {
            foreach($initialQueries as $initialQuery)
            {
                $this->databaseConnection->exec($initialQuery);
            }
        }
    }
    
    /**
     * Establish a connection to a SQL Server database.
     *
     * This method sets up a connection to a SQL Server database and then establishes a PDO connection to the database.
     *
     * @param string $connectionString The connection string used to connect to the SQL Server database.
     *
     * @return void
     *
     * @throws PDOException If there is an error while establishing the connection or executing the initial queries.
     */
    private function connectSqlServer($connectionString)
    {
        $initialQueries = array();

        // SQL Server connection setup
        $this->databaseConnection = new PDO(
            $connectionString,
            $this->databaseCredentials->getUsername(),
            $this->databaseCredentials->getPassword(),
            array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            )
        );

        // Execute the initial queries (timezone, charset) in SQL Server
        if (!empty($initialQueries)) {
            foreach ($initialQueries as $initialQuery) {
                $this->databaseConnection->exec($initialQuery);
            }
        }
    }

    
    /**
     * Determine the database type from a string.
     *
     * This method evaluates the provided string to identify common database types such as SQLite, PostgreSQL, 
     * MariaDB, MySQL, and SQL Server. It returns the corresponding constant from the `PicoDatabaseType` class.
     * If the provided database type is not supported, it throws an `UnsupportedDatabaseException`.
     *
     * @param string $databaseType The database type string (e.g., 'SQLite', 'PostgreSQL', 'MariaDB', 'MySQL', 'SQLServer').
     * @return string The corresponding `PicoDatabaseType` constant, one of:
     *                - `PicoDatabaseType::DATABASE_TYPE_SQLITE`
     *                - `PicoDatabaseType::DATABASE_TYPE_PGSQL`
     *                - `PicoDatabaseType::DATABASE_TYPE_MARIADB`
     *                - `PicoDatabaseType::DATABASE_TYPE_SQLSERVER`
     *                - `PicoDatabaseType::DATABASE_TYPE_MYSQL`
     * @throws UnsupportedDatabaseException If the database type is unsupported.
     */
    private static function getDbType($databaseType) // NOSONAR
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
        else if(stripos($databaseType, 'mysql') !== false)
        {
            return PicoDatabaseType::DATABASE_TYPE_MYSQL;
        }
        else if(stripos($databaseType, 'sqlsrv') !== false)
        {
            return PicoDatabaseType::DATABASE_TYPE_SQLSERVER;
        }
        else
        {
            throw new UnsupportedDatabaseException("Unsupported database type: $databaseType");
        }
    }

    /**
     * Determines the database driver based on the provided database type.
     *
     * This function takes a string representing the database type and returns 
     * the corresponding database driver constant from the `PicoDatabaseType` class.
     * It supports SQLite, PostgreSQL, MySQL/MariaDB, and SQL Server types.
     *
     * @param string $databaseType The type of the database (e.g., 'sqlite', 'postgres', 'pgsql', 'mysql', 'mariadb', 'sqlsrv').
     * 
     * @return string The corresponding database driver constant, one of:
     *                - `PicoDatabaseType::DATABASE_TYPE_SQLITE`
     *                - `PicoDatabaseType::DATABASE_TYPE_PGSQL`
     *                - `PicoDatabaseType::DATABASE_TYPE_MYSQL`
     *                - `PicoDatabaseType::DATABASE_TYPE_SQLSERVER`
     */
    private function getDbDriver($databaseType) // NOSONAR
    {
        if (stripos($databaseType, 'sqlite') !== false) {
            return PicoDatabaseType::DATABASE_TYPE_SQLITE;
        } else if (stripos($databaseType, 'postgre') !== false || stripos($databaseType, 'pgsql') !== false) {
            return PicoDatabaseType::DATABASE_TYPE_PGSQL;
        } else if (stripos($databaseType, 'sqlsrv') !== false) {
            return PicoDatabaseType::DATABASE_TYPE_SQLSERVER;
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
    private function constructConnectionString($withDatabase = true) // NOSONAR
    {
        $emptyDriver = !$this->databaseCredentials->issetDriver();
        $emptyHost = !$this->databaseCredentials->issetHost();
        $emptyPort = !$this->databaseCredentials->issetPort();
        $emptyName = !$this->databaseCredentials->issetDatabaseName();
        $emptyValue = "";

        // Append missing components to the emptyValue string
        $emptyValue .= $emptyDriver ? "{driver}" : "";
        $emptyValue .= $emptyHost ? "{host}" : "";
        $emptyValue .= $emptyPort ? "{port}" : "";

        // Check if there are missing components
        $invalidParam1 = $emptyDriver || $emptyHost || ($emptyPort && stripos($this->databaseCredentials->getDriver(), "sqlsrv") === false);

        if ($withDatabase) {
            // If database is required and there are invalid parameters or missing database name, throw an exception
            if ($invalidParam1 || $emptyName) {
                $emptyValue .= $emptyName ? "{database_name}" : "";
                throw new InvalidDatabaseConfiguration("Invalid database configuration. $emptyValue. Please check your database configuration!");
            }

            // Construct connection string for a database with database name
            if (stripos($this->databaseCredentials->getDriver(), "sqlsrv") !== false) {
                return sprintf(
                    '%s:Server=%s;Database=%s',
                    $this->getDbDriver($this->databaseCredentials->getDriver()),
                    $this->databaseCredentials->getHost(),
                    $this->databaseCredentials->getDatabaseName()
                );
            } 
            else
            {
                return sprintf(
                    '%s:host=%s;port=%d;dbname=%s',
                    $this->getDbDriver($this->databaseCredentials->getDriver()),
                    $this->databaseCredentials->getHost(),
                    (int) $this->databaseCredentials->getPort(),
                    $this->databaseCredentials->getDatabaseName()
                );
            }
        } else {
            // If database is not required but parameters are missing, throw an exception
            if ($invalidParam1) {
                throw new InvalidDatabaseConfiguration("Invalid database configuration. $emptyValue. Please check your database configuration!");
            }

            // Construct connection string without database name
            if (stripos($this->databaseCredentials->getDriver(), "sqlsrv") !== false) {
                return sprintf(
                    '%s:Server=%s',
                    $this->getDbDriver($this->databaseCredentials->getDriver()),
                    $this->databaseCredentials->getHost()
                );
            } else {
                return sprintf(
                    '%s:host=%s;port=%d',
                    $this->getDbDriver($this->databaseCredentials->getDriver()),
                    $this->databaseCredentials->getHost(),
                    (int) $this->databaseCredentials->getPort()
                );
            }
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
        if($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_PGSQL)
        {
            $sql = "SET TIMEZONE TO '$timeZoneOffset'";
            $this->execute($sql);
        }
        else if($this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_MARIADB || $this->getDatabaseType() == PicoDatabaseType::DATABASE_TYPE_MYSQL)
        {
            $sql = "SET time_zone='$timeZoneOffset'";
            $this->execute($sql);
        }
        
        return $this;
    }
    
    /**
     * Set the time zone offset for the database session.
     *
     * This method sets the time zone offset for the current database session. This is useful for ensuring that 
     * any time-related operations (such as querying and storing timestamps) are adjusted to the correct time zone.
     * The method generates the appropriate SQL command based on the type of the database (e.g., PostgreSQL, MySQL, etc.)
     * and executes it to apply the time zone setting.
     *
     * @param string $timeZoneOffset The time zone offset to set for the session. It can either be a valid UTC offset (e.g., '+00:00')
     *                               or a named time zone (e.g., 'Europe/London').
     * @return self Returns the current instance for method chaining.
     */
    public function setTimeZone($timezone)
    {
        return $this->setTimeZoneOffset(self::getTimeZoneOffsetFromString($timezone));
    }
    
    /**
     * Converts a timezone name (e.g., 'Asia/Jakarta') to its corresponding UTC offset (e.g., '+07:00' or '-03:00').
     *
     * This function will return the timezone offset without affecting the current PHP runtime timezone.
     *
     * @param string $timezone The name of the timezone, e.g., 'Asia/Jakarta'.
     * @return string The UTC offset corresponding to the provided timezone, e.g., '+07:00' or '-03:00'.
     */
    public static function getTimeZoneOffsetFromString($timezone)
    {
        // Create a DateTimeZone object for the provided timezone
        $zone = new DateTimeZone($timezone);
        
        // Get the current time in the given timezone
        $now = new DateTime("now", $zone);
        
        // Get the offset in seconds from UTC
        $offsetInSeconds = $zone->getOffset($now);

        // Calculate hours and minutes from the offset in seconds
        $hours = floor($offsetInSeconds / 3600);
        $minutes = abs(floor(($offsetInSeconds % 3600) / 60));

        // Format the offset to be positive or negative
        $sign = $hours < 0 ? '-' : '+';

        // Return the offset as a string in the format (+hh:mm or -hh:mm)
        return $sign . str_pad(abs($hours), 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0', STR_PAD_LEFT);
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
     * Start a new database transaction.
     *
     * This method begins a new transaction, allowing subsequent database operations
     * to be grouped together. The changes made during the transaction are not permanent
     * until the transaction is committed.
     *
     * @return bool Returns `true` if the transaction was successfully started, `false` otherwise.
     */
    public function startTransaction()
    {
        return $this->databaseConnection->query((new PicoDatabaseQueryBuilder($this))->startTransaction());
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
        return $this->databaseConnection->query((new PicoDatabaseQueryBuilder($this))->commit());
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
        return $this->databaseConnection->query((new PicoDatabaseQueryBuilder($this))->rollback());
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
     * Retrieves the time zone used by the database.
     *
     * This function calls the `getTimeZone()` method from the `databaseCredentials`
     * object to fetch the time zone configured for the database connection.
     *
     * @return string The time zone of the database (e.g., "UTC", "America/New_York").
     */
    public function getDatabaseTimeZone()
    {
        return $this->databaseCredentials->getTimeZone();
    }

    /**
     * Retrieves the time zone offset of the database connection.
     *
     * This function retrieves the time zone offset by calling the static method
     * `getTimeZoneOffset()` with the `databaseConnection` as an argument.
     * The offset is returned in seconds from UTC.
     *
     * @return string The time zone offset, typically in hours and minutes (e.g., "+02:00").
     */
    public function getDatabaseTimeZoneOffset()
    {
        return self::getTimeZoneOffset($this->databaseConnection);
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