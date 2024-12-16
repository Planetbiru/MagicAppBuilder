## Native Query

In MagicObject version 2, native queries have been introduced as an efficient way to interact with the database.

Native queries offer significant performance improvements when handling large volumes of data, allowing users to craft highly efficient queries that meet diverse requirements.

Native queries can perform several tasks such as:
1. performing INSERT operations
2. performing SELECT operations with complex queries such as JOIN, UNION, IF-ELSE, CASE, and others
3. counting the number of rows without reading the data in detail
4. performing UPDATE operations
5. performing DELETE operations
6. calling functions
7. calling procedures and stored procedures

Native queries do not support multiple database connections. This means that all operations performed using native queries must be executed within a single, active database connection. This design choice ensures data consistency and integrity by preventing potential conflicts that may arise from attempting to manage multiple connections simultaneously. Users should ensure that their application logic accommodates this limitation, potentially leveraging connection pooling or other techniques to optimize database interaction when needed.

### Parameters

The parameters accepted by the native query function are as follows:

1. `string`
2. `int` or `integer`
3. `float`
4. `bool` or `boolean`
5. `null`
6. `DateTime`
7. `array` of `string`, `int`, `bool` and `DateTime`

For columns with data type `DATETIME` and `TIMESTAMP`, users can use either `string` or `DateTime` parameters. `DateTime` will be first converted to 'Y-m-d H:i:s' format automatically by MagicObject. Don't forget to define DateTimeZone for DateTime object. Also note the time resolution for the `in` and `=` criteria.

### Return Type

This method executes a database query using the parameters and annotations defined in the caller function.
It leverages reflection to access the query string specified in the caller's docblock, binds the relevant parameters,
and then runs the query against the database.

By analyzing the parameters and return type of the calling function, this method enables dynamic query execution tailored to the parameters and return type specified in the @return annotation. Supported return types include:

- **void**: The method will return `null`.
- **int** or **integer**: The method will return the number of affected rows.
- **object** or **stdClass**: The method will return a single result as an object.
- **stdClass[]**: The method will return all results as an array of `stdClass` objects, similar to `PDO::fetchAll(PDO::FETCH_OBJ)`.
- **array**: The method will return all results as an associative array, similar to `PDO::fetchAll(PDO::FETCH_ASSOC)`.
- **string**: The method will return the result as a JSON-encoded string.
- **PDOStatement**: The method will return a prepared statement for further operations if necessary.
- **MagicObject** and its derived classes: If the return type is a class name or an array of class names, instances of the specified class will be created for each row fetched.

MagicObject also supports return types `self` and `self[]` which will represent the respective class.

The method returns a mixed type result, which varies based on the caller function's return type:
- It will return `null` for void types.
- An integer representing the number of affected rows for int types.
- An object for single result types.
- An array of associative arrays for array types.
- A JSON string for string types.
- Instances of a specified class for class name matches.

If there is an error executing the database query, a **PDOException** will be thrown.

Native query must be a function of a class that extends from the MagicObject class. In its definition, this method must call `$this->executeNativeQuery()`. `MagicObject::executeNativeQuery()` will analyze the docblock, parameters, and return type to process the given query. For ease and flexibility in writing code, the `MagicObject::executeNativeQuery()` function call does not pass parameters. Instead, the `MagicObject::executeNativeQuery()` function takes parameters from the calling function. Thus, changes to the parameters of the calling function do not require changes to the function definition.

Native queries can be created on entities used by the application. If in the previous version the entity only contained properties, then in version 2.0, the entity can also contain functions for native queries. However, entities in versions 1 and 2 both support functions but functions with native queries are only supported in version 2.0.

### Pagination and Sorting

In **MagicObject version 2.7**, support for **pageable** and **sortable** functionality has been added to native queries. Previously, native queries did not support pagination and sorting directly. Instead, users had to manually include `SORT BY` and `LIMIT OFFSET` clauses in their queries, which made them less flexible. This approach was problematic because each Database Management System (DBMS) has its own syntax for writing queries, making it cumbersome to adapt queries for different platforms.

With the introduction of pageable and sortable support in version 2.7, users can now easily pass **pagination** parameters using the `PicoPageable` type and **sorting** parameters using the `PicoSortable` type directly into their native queries. These parameters can be placed anywhere within the query, but it is recommended to position them either at the beginning or the end of the query for optimal readability and organization.

This enhancement makes native queries more flexible and easier to maintain, as the logic for pagination and sorting is handled automatically, without requiring manual intervention for each DBMS. As a result, users can now write cleaner, more efficient, and database-agnostic native queries.

### Debug Query

MagicObject checks if the database connection has a debugging function for queries. If available, it sends the executed query along with the parameter values to this function, aiding users in identifying errors during query definition and execution.

**Example:**

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__)) . "/test.yml", false, true, true);
$database = new PicoDatabase($databaseCredential->getDatabase(), null, function($sql){
    error_log($sql); // Debug query here
});
$database->connect();

class Supervisor extends MagicObject
{
    /**
     * Native query 1
     *
     * This method will return null.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return void
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native1($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 2
     *
     * This method will return the number of affected rows.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return int
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native2($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 3
     *
     * This method will return a single result as an object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return stdClass
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native3($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 4
     *
     * This method will return an array of stdClass objects.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return stdClass[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native4($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 5
     *
     * This method will return an associative array.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return array
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native5($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 6
     *
     * This method will return a JSON-encoded string.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return string
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native6($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 7
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native7($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 8
     *
     * This method will return an object of Supervisor.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return Supervisor
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native8($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 9
     *
     * This method will return an array of Supervisor object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return Supervisor[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native9($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 10
     *
     * This method will return an object of Supervisor.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return self
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native10($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 11
     *
     * This method will return an array of Supervisor object.
     *
     * @param int $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return self[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id = :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native11($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
    
    /**
     * Native query 12
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param int[] $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id in :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native7($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }

    /**
     * Native query 13
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param PicoPagebale $pageable
     * @param PicoSortable $sortable
     * @param bool $aktif The active status to filter results.
     * @return MagicObject[]
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id in :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native13($pageable, $sortable, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

$obj = new Supervisor(null, $database);

$native1 = $obj->native1(1, true);

$native2 = $obj->native2(1, true);
echo "\r\nnative2:\r\n";
print_r($native2);

$native3 = $obj->native3(1, true);
echo "\r\nnative3:\r\n";
print_r($native3);

$native4 = $obj->native4(1, true);
echo "\r\nnative4:\r\n";
print_r($native4);

$native5 = $obj->native5(1, true);
echo "\r\nnative5:\r\n";
print_r($native5);

$native6 = $obj->native6(1, true);
echo "\r\nnative6:\r\n";
print_r($native6);

$native7 = $obj->native7(1, true);
echo "\r\nnative7:\r\n";
print_r($native7->fetchAll(PDO::FETCH_ASSOC));

$native8 = $obj->native8(1, true);
echo "\r\nnative8:\r\n";
print_r($native8);

$native9 = $obj->native9(1, true);
echo "\r\nnative9:\r\n";
print_r($native9);

$native10 = $obj->native10(1, true);
echo "\r\nnative10:\r\n";
print_r($native10);

$native11 = $obj->native11(1, true);
echo "\r\nnative11:\r\n";
print_r($native11);

// For the MagicObject return type, users can utilize the features of the MagicObject except for 
// interacting with the database again because native queries are designed for a different purpose.

echo "Alamat: " . $native8->getTelepon() . "\r\n";
echo "Alamat: " . $native9[0]->getTelepon() . "\r\n";
echo "Alamat: " . $native10->getTelepon() . "\r\n";
echo "Alamat: " . $native11[0]->getTelepon() . "\r\n";


$sortable = new PicoSortable();
$sortable->addSortable(new PicoSort("nama", PicoSort::ORDER_TYPE_ASC));
$pageable = new PicoPageable(new PicoPage(3, 20));

try
{
    $native13 = $obj->native13($pageable, $sortable, true);
    echo "\r\nnative13:\r\n";
    foreach($native13 as $sup)
    {
        echo $sup."\r\n\r\n";
    }
}
catch(Exception $e)
{
    echo $e->getMessage();
}
```

For the purpose of exporting large amounts of data, use the PDOStatement return type. PDOStatement allows users to read one by one and process it immediately, allowing PHP to release memory from the previous process. PHP does not need to store very large data in a variable.

Example 12 shows how to use array parameters.

For example:

```sql
SELECT supervisor.*
FROM supervisor
WHERE supervisor.supervisor_id in (1, 2, 3, 4)
AND supervisor.aktif = true;
```

So the method is:

```php
    /**
     * Native query 12
     *
     * This method will return a prepared statement for further operations if necessary.
     *
     * @param int[] $supervisorId The ID of the table to search for.
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.supervisor_id in :supervisorId 
      AND supervisor.aktif = :aktif
     ")
     */
    public function native7($supervisorId, $aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
```

**Example 1**

Using MagicObject

```php
class SupervisorExport extends MagicObject
{
    /**
     * Export active supervisor
     *
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.aktif = :aktif
      ORDER BY supervisor.waktu_buat ASC
     ")
     */
    public function exportActive($aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

function generateHeader($fp, $map)
{
    $fields = [];
    $fields[] = "No";
    foreach($map as $column)
    {
        $fields[] = PicoStringUtil::snakeToTitle($column);
    }
    fputcsv($fp, $fields);
}
function generateHeader($fp, $map, $data, $iteration)
{
    $fields = [];
    $fields[] = $iteration;
    foreach($map as $column)
    {
        $fields[] = $data->get($column);
    }
    fputcsv($fp, $fields);
}

$map = [
    "supervisor_id",
    "nama",
    "tanggal_lahir",
    "telepon",
    "email"
];

$path = "/var/www/export.csv";
$fp = fopen($path, "w");

$exporter = new SupervisorExport(null, $database);

$stmt = $exporter->exportActive(true);

$iteration = 0;
while($row = $stmt->fetch(PDO::FETCH_OBJ))
{
    $data = new SupervisorExport($row);
    if($iteration == 0)
    {
        generateHeader($fp, $map);
    }
    $iteration++;
    generateData($fp, $map, $data, $iteration);
}
fclose($fp);
```

**Example 2**

Using stdClass

```php
class SupervisorExport extends MagicObject
{
    /**
     * Export active supervisor
     *
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.aktif = :aktif
      ORDER BY supervisor.waktu_buat ASC
     ")
     */
    public function exportActive($aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

function generateHeader($fp, $map)
{
    $fields = [];
    $fields[] = "No";
    foreach($map as $column)
    {
        $fields[] = PicoStringUtil::snakeToTitle($column);
    }
    fputcsv($fp, $fields);
}
function generateData($fp, $map, $data, $iteration)
{
    $fields = [];
    $fields[] = $iteration;
    foreach($map as $column)
    {
        $fields[] = $data->{$column};
    }
    fputcsv($fp, $fields);
}

$map = [
    "supervisor_id",
    "nama",
    "tanggal_lahir",
    "telepon",
    "email"
];

$path = "/var/www/export.csv";
$fp = fopen($path, "w");

$exporter = new SupervisorExport(null, $database);

$stmt = $exporter->exportActive(true);

$iteration = 0;
while($row = $stmt->fetch(PDO::FETCH_OBJ))
{
    if($iteration == 0)
    {
        generateHeader($fp, $map);
    }
    $iteration++;
    generateData($fp, $map, $row, $iteration);
}
fclose($fp);
```

**Example 3**

Using associated array

```php
class SupervisorExport extends MagicObject
{
    /**
     * Export active supervisor
     *
     * @param bool $aktif The active status to filter results.
     * @return PDOStatement
     * @query("
      SELECT supervisor.* 
      FROM supervisor 
      WHERE supervisor.aktif = :aktif
      ORDER BY supervisor.waktu_buat ASC
     ")
     */
    public function exportActive($aktif)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}

function generateHeader($fp, $map)
{
    $fields = [];
    $fields[] = "No";
    foreach($map as $column)
    {
        $fields[] = PicoStringUtil::snakeToTitle($column);
    }
    fputcsv($fp, $fields);
}
function generateHeader($fp, $map, $data, $iteration)
{
    $fields = [];
    $fields[] = $iteration;
    foreach($map as $column)
    {
        $fields[] = $data[$column];
    }
    fputcsv($fp, $fields);
}

$map = [
    "supervisor_id",
    "nama",
    "tanggal_lahir",
    "telepon",
    "email"
];

$path = "/var/www/export.csv";
$fp = fopen($path, "w");

$exporter = new SupervisorExport(null, $database);

$stmt = $exporter->exportActive(true);

$iteration = 0;
while($row = $stmt->fetch(PDO::FETCH_ASSOC))
{
    if($iteration == 0)
    {
        generateHeader($fp, $map);
    }
    $iteration++;
    generateData($fp, $map, $row, $iteration);
}
fclose($fp);
```

### Dynamic Query Template for Native Query

MagicObject version 2.11 introduces a new feature for native queries called the dynamic query template, which allows users to dynamically pass a query template to the native query method.

**Example 4**

```php
<?php

use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

class SupervisorExport extends MagicObject
{
    /**
     * Exports active supervisors based on the given active status.
     *
     * @param bool $aktif The active status filter (true for active, false for inactive).
     * @param PicoPageable $pageable Pagination details.
     * @param PicoSortable $sortable Sorting details.
     * @return PDOStatement The result of the executed query.
     * @query("
           SELECT supervisor.* 
           FROM supervisor 
           WHERE supervisor.aktif = :aktif
      ")
    */
    public function exportActive($aktif, $pageable, $sortable)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}
```

In certain cases, developers need to dynamically insert a query template. This cannot be done with the above method. Therefore, MagicObject provides a way to pass the query template as a parameter. Since every parameter besides `PicoPageable` and `PicoSortable` is considered as a value for the query, MagicObject uses the `PicoDatabaseQueryTemplate` data type to pass the query template within the parameter. With this, the native query method definition becomes as follows:

```php
<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseQueryTemplate;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\MagicObject;

class SupervisorExport extends MagicObject
{
    /**
     * Exports active supervisors based on the given active status.
     *
     * @param bool $aktif The active status filter (true for active, false for inactive).
     * @param PicoPageable $pageable Pagination details.
     * @param PicoSortable $sortable Sorting details.
     * @param PicoDatabaseQueryTemplate $template Query template.
     * @return PDOStatement The result of the executed query.
    */
    public function exportActive($aktif, $pageable, $sortable, $template)
    {
        // Call parent method to execute the query
        return $this->executeNativeQuery();
    }
}
```

Similar to `PicoPageable` and `PicoSortable`, parameters of type `PicoDatabaseQueryTemplate` can be placed anywhere.

To call this method, use the following approach:


```php
<?php
$explort = new SupervisorExport(null, $database);

$aktif = true;
$sortable = new PicoSortable();
$sortable->add(new PicoSort('name', PicoSort::ORDER_TYPE_ASC));
$pageable = new PicoPageable(new PicoPage(1, 1), $sortable);

$builder = new PicoDatabaseQueryBuilder($database);
$builder->newQuery()
    ->select("supervisor.*")
    ->from("supervisor")
    ->where("supervisor.aktif = :aktif");

$template = new PicoDatabaseQueryTemplate($builder);
$result = $explort->exportActive($aktif, $pageable, $sortable, $template);
```

In the example above, the `PicoDatabaseQueryTemplate` object is initialized using a `PicoDatabaseQueryBuilder` object.

```php
<?php
$explort = new SupervisorExport(null, $database);

$aktif = true;
$sortable = new PicoSortable();
$sortable->add(new PicoSort('name', PicoSort::ORDER_TYPE_ASC));
$pageable = new PicoPageable(new PicoPage(1, 1), $sortable);

$builder = "SELECT supervisor.* FROM supervisor WHERE supervisor.aktif = :aktif";

$template = new PicoDatabaseQueryTemplate($builder);
$result = $explort->exportActive($aktif, $pageable, $sortable, $template);
```

In the example above, the `PicoDatabaseQueryTemplate` object is initialized using a string.


### Best Practices

1. **Utilize Prepared Statements**: Always prefer using prepared statements for security against SQL injection.
2. **Error Handling**: Wrap database calls in try-catch blocks to handle exceptions gracefully.
3. **Efficient Data Retrieval**: Use PDOStatement for large datasets to process rows one by one.
4. **Debugging**: Implement logging for SQL queries to troubleshoot issues more effectively.
5. **Keep Queries Simple**: Break down complex queries into simpler components if possible, making them easier to maintain and debug.

By leveraging the native query feature in MagicObject, you can create efficient and maintainable database interactions, enhancing your application's performance and security.