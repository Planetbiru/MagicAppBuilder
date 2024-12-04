## Database 


### Overview

`PicoDatabase` is a PHP class designed for simplified database interactions using PDO (PHP Data Objects). It provides methods to connect to a database, execute SQL commands, manage transactions, and fetch results in various formats. This manual outlines how to use the class, its features, and provides examples for reference.

### Features

-   **Connection Management**: Establish and manage database connections.
-   **SQL Execution**: Execute various SQL commands such as INSERT, UPDATE, DELETE, and SELECT.
-   **Transaction Handling**: Support for committing and rolling back transactions.
-   **Result Fetching**: Fetch results in different formats (array, object, etc.).
-   **Callbacks**: Support for custom callback functions for query execution and debugging.
-   **Unique ID Generation**: Generate unique identifiers for database records.

### Database Support

MagicObject supports the following databases:

1. **MySQL**
    
    One of the most popular open-source relational databases, known for its speed, reliability, and ease of use. MySQL is widely used in web applications and offers strong performance, security features, and support for SQL standards.

2. **MariaDB**
    
    A fork of MySQL, created by the original developers of MySQL after concerns over Oracle’s acquisition of MySQL. MariaDB is designed to maintain compatibility with MySQL while adding new features and optimizations. It is fully open-source and highly regarded for its performance and stability.

3. **PostgreSQL**
    
    A powerful, open-source relational database system known for its robustness, SQL compliance, and extensive feature set, including ACID compliance, JSON support, and advanced indexing mechanisms.

4. **SQLite**
    
    A lightweight, serverless, self-contained SQL database engine that is highly portable. It is often used for embedded systems or small-scale applications due to its minimal setup and resource usage. Despite its simplicity, SQLite supports a wide range of SQL features and is widely used in mobile apps and other local storage scenarios.

MagicObject’s compatibility with these databases enables flexible, scalable, and efficient data management across different platforms and environments.

### Installation

To use the `PicoDatabase` class, ensure you have PHP with PDO support. Include the class file in your project, and you can instantiate it with your database credentials.

```php
use MagicObject\Database\PicoDatabase;

// Example credentials setup
$credentials = new SecretObject();
$db = new PicoDatabase($credentials);
```

**Credentials**

To create database credentials, please see the `SecretObject` section.

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

/**
 * PicoDatabaseCredentials class
 * 
 * This class encapsulates database credentials and utilizes the SecretObject to encrypt all attributes,
 * ensuring the security of database configuration details from unauthorized access.
 * 
 * It provides getter methods to retrieve database connection parameters such as driver, host, port,
 * username, password, database name, schema, and application time zone.
 * 
 * Example usage:
 * ```php
 * $credentials = new PicoDatabaseCredentials();
 * $credentials->setHost('localhost');
 * $credentials->setUsername('user');
 * $credentials->setPassword('password');
 * ```
 * 
 * The attributes are automatically encrypted when set, providing a secure way to handle sensitive
 * information within your application.
 * 
 * @author Kamshory
 * @package MagicObject\Database
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoDatabaseCredentials extends SecretObject
{
    /**
     * Database driver (e.g., 'mysql', 'pgsql').
     *
     * @var string
     */
    protected $driver = 'mysql';

    /**
     * Database file path for SQLite database only
     *
     * @var string
     */
    protected $databaseFilePath;

    /**
     * Database server host.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $host = 'localhost';

    /**
     * Database server port.
     *
     * @var int
     */
    protected $port = 3306;

    /**
     * Database username.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $username = "";

    /**
     * Database user password.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $password = "";

    /**
     * Database name.
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseName = "";

    /**
     * Database schema (default: 'public').
     *
     * @EncryptIn
     * @DecryptOut
     * @var string
     */
    protected $databaseSchema = "public"; 

    /**
     * Application time zone.
     *
     * @var string
     */
    protected $timeZone = "Asia/Jakarta";

    /**
     * Charset
     *
     * @var string
     */
    protected $charset = "utf8";
}
```

### Class Methods

#### Constructor

```php
public function __construct($databaseCredentials, $callbackExecuteQuery = null, $callbackDebugQuery = null)
```

**Parameters:**

-   `SecretObject $databaseCredentials`: Database credentials object.
-   `callable|null $callbackExecuteQuery`: Optional callback for executing modifying queries.
    -   If the callback has **3 parameters**, it will be:
        -   `$sqlQuery`: The SQL query being executed.
        -   `$params`: The parameters used in the SQL query.
        -   `$type`: The type of query (e.g., `PicoDatabase::QUERY_INSERT`).
    -   If the callback has **2 parameters**, it will be:
        -   `$sqlQuery`: The SQL query being executed.
        -   `$type`: The type of query.
-   `callable|null $callbackDebugQuery`: Optional callback for debugging queries.
    -   If the callback has **2 parameters**, it will be:
        -   `$sqlQuery`: The SQL query being debugged.
        -   `$params`: The parameters used in the SQL query.
    -   If the callback has **1 parameter**, it will be:
        -   `$sqlQuery`: The SQL query being debugged.

To use setter methods for configuring the `callbackExecuteQuery` and `callbackDebugQuery` in the PicoDatabase class, follow the example below. This allows you to set the callbacks after instantiation

**Setting Callbacks with Setters**

Here's how you can set the callbacks using setter methods:

```php
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;

// Example credentials setup
$credentials = new PicoDatabaseCredentials();
$credentials->setHost('localhost');
$credentials->setUsername('user');
$credentials->setPassword('password');

// Instantiate PicoDatabase
$db = new PicoDatabase($credentials);

// Set callback for executing queries
$db->setCallbackExecuteQuery(function($sqlQuery, $params, $type) {
    echo "Executing query: $sqlQuery\n";
    echo "Parameters: " . json_encode($params) . "\n";
});

// Set callback for debugging queries
$db->setCallbackDebugQuery(function($sqlQuery, $params) {
    echo "Debugging query: $sqlQuery\n";
    echo "Parameters: " . json_encode($params) . "\n";
});

```

**Disabling Callbacks Using Setters**

You can also disable the callbacks by setting them to `null`:

```php
// Instantiate PicoDatabase
$dbWithoutCallbacks = new PicoDatabase($credentials);

// Disable callbacks
$dbWithoutCallbacks->setCallbackExecuteQuery(null);
$dbWithoutCallbacks->setCallbackDebugQuery(null);

```

#### Connecting to the Database

```php
public function connect($withDatabase = true): bool
```

**Parameters**:

-   `bool $withDatabase`: Whether to select the database upon connection.

**Returns**: `true` if connection is successful, `false` otherwise.

#### Disconnecting from the Database

```php
public function disconnect(): self
```

**Returns**: Current instance for method chaining.

#### Query Execution

```php
public function query($sql, $params = null)
```


**Parameters**:
-   `string $sql`: SQL command to be executed.
-   `array|null $params`: Optional parameters for the SQL query.
**Returns**: PDOStatement object or `false` on failure.

#### Fetch a Single Result

```php
public function fetch($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null, $params = null)
```

**Parameters**:
-   `string $sql`: SQL command.
-   `int $tentativeType`: Fetch mode (default is `PDO::FETCH_ASSOC`).
-   `mixed $defaultValue`: Default value if no results found.
-   `array|null $params`: Optional parameters.
**Returns**: Fetched result or default value.

#### Fetch All Results

```php
public function fetchAll($sql, $tentativeType = PDO::FETCH_ASSOC, $defaultValue = null, $params = null)
```

Similar to fetch, but returns all matching results as an array.

### Transaction Management

#### Commit Transaction

```php
public function commit(): bool
```

**Returns:** true if successful.

#### Rollback Transaction

```php
public function rollback(): bool
```

**Returns:** true if successful.

#### Unique ID Generation

```php
public function generateNewId(): string
```

**Returns:** A unique 20-byte ID.

#### Last Inserted ID

```php
public function lastInsertId($name = null): string|false
```

**Parameters:**

- string|null $name: Sequence name (for PostgreSQL).

**Returns:** The last inserted ID or false on error.

### Connection Status

#### Check Connection

```php
public function isConnected(): bool
```

### Example Usage

#### Connecting and Fetching Data

```php
// Instantiate PicoDatabase
$db = new PicoDatabase($credentials);

// Connect to the database
if ($db->connect()) {
    // Fetch a user by ID
    $user = $db->fetch("SELECT * FROM users WHERE id = ?", 1);
    print_r($user);
    
    // Disconnect
    $db->disconnect();
}
```

#### Executing a Transaction

```php
$db->connect();
$db->setAudoCommit(false); // Disable autocommit

try {
    $db->executeInsert("INSERT INTO users (name) VALUES (?)", ['John Doe']);
    $db->commit(); // Commit the transaction
} catch (Exception $e) {
    $db->rollback(); // Rollback on error
}
```

### Conclusion

`PicoDatabase` is a robust class for managing database operations in PHP applications. By following the examples and method descriptions provided in this manual, you can effectively utilize its features for your database interactions. For further assistance, refer to the source code and documentation available at [MagicObject GitHub](https://github.com/Planetbiru/MagicObject).