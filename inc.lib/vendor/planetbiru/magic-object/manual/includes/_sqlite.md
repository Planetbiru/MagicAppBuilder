## PicoSqlite

### Overview

`PicoSqlite` is a PHP class designed for simplified interactions with SQLite databases using PDO (PHP Data Objects). This class extends `PicoDatabase` and provides methods for connecting to the database, creating tables, and performing basic CRUD (Create, Read, Update, Delete) operations.

Here are some advantages of using SQLite:

1.  **Lightweight**: SQLite is a serverless, self-contained database engine that requires minimal setup and uses a single file to store the entire database, making it easy to manage and deploy.
    
2.  **Easy to Use**: Its simple API allows for straightforward integration with PHP, enabling quick database operations without the overhead of complex configurations.
    
3.  **No Server Required**: Unlike other database systems, SQLite does not require a separate server process, which simplifies the development process and reduces resource usage.
    
4.  **Cross-Platform**: SQLite databases are cross-platform and can be used on various operating systems without compatibility issues.
    
5.  **Fast Performance**: For smaller databases and applications, SQLite often outperforms more complex database systems, thanks to its lightweight architecture.
    
6.  **ACID Compliance**: SQLite provides full ACID (Atomicity, Consistency, Isolation, Durability) compliance, ensuring reliable transactions and data integrity.
    
7.  **Rich Feature Set**: Despite being lightweight, SQLite supports many advanced features like transactions, triggers, views, and complex queries.
    
8.  **No Configuration Required**: SQLite is easy to set up and requires no configuration, allowing developers to focus on building applications rather than managing the database server.
    
9.  **Great for Prototyping**: Its simplicity makes it ideal for prototyping applications before moving to a more complex database system.
    
10.  **Good for Read-Heavy Workloads**: SQLite performs well in read-heavy scenarios, making it suitable for applications where data is frequently read but rarely modified.
    

These features make SQLite a popular choice for many PHP applications, especially for smaller projects or for applications that need a lightweight database solution.

SQLite has a slightly different method for determining whether a SELECT query returns matching rows. While other databases often utilize the rowCount() method to get this information, SQLite does not support this functionality in the same way. To address this limitation, MagicObject has implemented a solution that seamlessly handles row checking for users. With MagicObject, developers can interact with SQLite without needing to worry about the intricacies of row counting. This allows for a more intuitive and efficient experience when working with SQLite, enabling users to focus on their application logic rather than the underlying database mechanics.

### Requirements

-    PHP 7.0 or higher
-    PDO extension enabled

### Installation

To use the `PicoSqlite` class, include it in your PHP project. Ensure that your project structure allows for proper namespace loading.

```php
use MagicObject\Database\PicoSqlite;

// Example usage:
$db = new PicoSqlite('path/to/database.sqlite');
```

### Class Methods

#### Constructor

```php
public function __construct($databaseFilePath)
```

**Parameters:**

    string $databaseFilePath: The path to the SQLite database file.

**Throws:** PDOException if the connection fails.

**Usage Example:**

```php
$sqlite = new PicoSqlite('path/to/database.sqlite');
```

#### Connecting to the Database

```php
public function connect($withDatabase = true)
```

**Parameters:**
-    bool $withDatabase: Optional. Default is true. Indicates whether to select the database when connecting.

**Returns:** `bool` - True if the connection is successful, false otherwise.

**Usage Example:**

```php
if ($sqlite->connect()) {
    echo "Connected to database successfully.";
} else {
    echo "Failed to connect.";
}
```

#### Check Table

```php
public function tableExists($tableName) : bool
```

**Parameters:**

-    string $tableName: The name of the table to check.

**Returns:** `bool` - True if the table exists, false otherwise.

**Usage Example:**

```php
if ($sqlite->tableExists('users')) {
    echo "Table exists.";
} else {
    echo "Table does not exist.";
}
```

#### Create Table

```php
public function createTable($tableName, $columns) : int|false
```

**Parameters:**

-    string $tableName: The name of the table to create.
-    string[] $columns: An array of columns in the format 'column_name TYPE'.

**Returns:** `int|false` - Number of rows affected or false on failure.

**Usage Example:**

```php
$columns = ['id INTEGER PRIMARY KEY', 'name TEXT', 'email TEXT'];
$sqlite->createTable('users', $columns);
```

#### Insert

```php
public function insert($tableName, $data) : array 
```

**Parameters:**

-    string $tableName: The name of the table to insert into.
-    array $data: An associative array of column names and values to insert.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$data = ['name' => 'John Doe', 'email' => 'john@example.com'];
$sqlite->insert('users', $data);
```

```php
public function update($tableName, $data, $conditions) : bool
```

**Parameters:**

-    string $tableName: The name of the table to update.
    array $data: An associative array of column names and new values.
-    array $conditions: An associative array of conditions for the WHERE clause.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$data = ['name' => 'John Smith'];
$conditions = ['id' => 1];
$sqlite->update('users', $data, $conditions);
```

#### Delete

```php
public function delete($tableName, $conditions) : bool 
```

**Parameters:**

-    string $tableName: The name of the table to delete from.
-    array $conditions: An associative array of conditions for the WHERE clause.

**Returns:** `bool` - True on success, false on failure.

**Usage Example:**

```php
$conditions = ['id' => 1];
$sqlite->delete('users', $conditions);
```

### Entity with PicoSqlite

```php
<?php

use MagicObject\Database\PicoSqlite;
use MagicObject\MagicObject;
use MagicObject\Util\Database\PicoDatabaseUtilSqlite;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=false)
 * @Table(name="album")
 * @Cache(enable="true")
 * @package MusicProductionManager\Data\Entity
 */
class Album extends MagicObject
{
    /**
     * Album ID
     * 
     * @Id
     * @GeneratedValue(strategy=GenerationType.UUID)
     * @NotNull
     * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
     * @Label(content="Album ID")
     * @var string
     */
    protected $albumId;

    /**
     * Name
     * 
     * @Column(name="name", type="varchar(50)", length=50, nullable=true)
     * @Label(content="Name")
     * @var string
     */
    protected $name;

    /**
     * Title
     * 
     * @Column(name="title", type="text", nullable=true)
     * @Label(content="Title")
     * @var string
     */
    protected $title;

    /**
     * Description
     * 
     * @Column(name="description", type="longtext", nullable=true)
     * @Label(content="Description")
     * @var string
     */
    protected $description;

    /**
     * Producer ID
     * 
     * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
     * @Label(content="Producer ID")
     * @var string
     */
    protected $producerId;

    /**
     * Release Date
     * 
     * @Column(name="release_date", type="date", nullable=true)
     * @Label(content="Release Date")
     * @var string
     */
    protected $releaseDate;

    /**
     * Number Of Song
     * 
     * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
     * @Label(content="Number Of Song")
     * @var int
     */
    protected $numberOfSong;

    /**
     * Duration
     * 
     * @Column(name="duration", type="float", nullable=true)
     * @Label(content="Duration")
     * @var float
     */
    protected $duration;

    /**
     * Image Path
     * 
     * @Column(name="image_path", type="text", nullable=true)
     * @Label(content="Image Path")
     * @var string
     */
    protected $imagePath;

    /**
     * Sort Order
     * 
     * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
     * @Label(content="Sort Order")
     * @var int
     */
    protected $sortOrder;

    /**
     * Time Create
     * 
     * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
     * @Label(content="Time Create")
     * @var string
     */
    protected $timeCreate;

    /**
     * Time Edit
     * 
     * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
     * @Label(content="Time Edit")
     * @var string
     */
    protected $timeEdit;

    /**
     * Admin Create
     * 
     * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
     * @Label(content="Admin Create")
     * @var string
     */
    protected $adminCreate;

    /**
     * Admin Edit
     * 
     * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
     * @Label(content="Admin Edit")
     * @var string
     */
    protected $adminEdit;

    /**
     * IP Create
     * 
     * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
     * @Label(content="IP Create")
     * @var string
     */
    protected $ipCreate;

    /**
     * IP Edit
     * 
     * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
     * @Label(content="IP Edit")
     * @var string
     */
    protected $ipEdit;

    /**
     * Active
     * 
     * @Column(name="active", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
     * @DefaultColumn(value="1")
     * @var bool
     */
    protected $active;

    /**
     * As Draft
     * 
     * @Column(name="as_draft", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
     * @DefaultColumn(value="1")
     * @var bool
     */
    protected $asDraft;

}

$database = new PicoSqlite(__DIR__ . "/db.sqlite", null, function($sql){
    //echo $sql."\r\n";
});
try
{
    $database->connect();

    $album = new Album(null, $database);

    // create table if not exists
    $util = new PicoDatabaseUtilSqlite();
    $tableStructure = $util->showCreateTable($album, true);
    $database->query($tableStructure);

    $album->setAlbumId("1235");
    $album->setName("Meraih Mimpi 2 ");
    $album->setTitle("Meraih Mimpi 2");
    $album->setDescription("Album pertama dengan judul Meraih Mimpi 2");
    $album->setProducerId("5678");
    $album->setReleaseDate("2024-09-09");
    $album->setNumberOfSong(10);
    $album->duration(185*60);
    $album->setSortOrder(1);
    $album->setIpCreate("::1");
    $album->setIpEdit("::1");
    $album->setTimeCreate(date("Y-m-d H:i:s"));
    $album->setTimeEdit(date("Y-m-d H:i:s"));
    $album->setAdminCreate("1");
    $album->setAdminEdit("1");
    $album->setIpCreate("::1");
    $album->setActive(true);
    $album->setAsDraft(false);
    echo $album."\r\n--------\r\n";
    $album->save();

    $album2 = new Album(null, $database);
    
    $res = $album2->findAll();
    foreach($res->getResult() as $row)
    {
        echo $row."\r\n";
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
}
```

### Error Handling

If an operation fails, `PicoSqlite` may throw exceptions or return false. It is recommended to implement error handling using try-catch blocks to catch `PDOException` for connection-related issues.

### Conclusion

`PicoSqlite` provides an efficient way to interact with SQLite databases. Its straightforward API allows developers to perform common database operations with minimal code. For more advanced database operations, consider extending the class or using additional PDO features.