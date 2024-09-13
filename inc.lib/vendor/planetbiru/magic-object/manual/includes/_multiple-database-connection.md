## Multiple Database Connection

MagicObject requires a database connection for object construction using database connections. In this way, users can use multiple database connections in a module very easily.

Example:

```php
$album = new Album(null, $database1);
try
{
	$album->find("123456");
	$album->currentDatabase($database2);
	$album->save();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

From the example above, it can be seen that an entity can even use two databases interchangeably.

When constructed, the `$album` object uses the `$database1` database connection. The application retrieves data with the primary key `123456` from `$database1` and then stores it in `$database2` without having to change many parameters and lots of code. Please note that to save `$album` data, you must use the `save` method instead of `insert` or `update` because there is a possibility that data with the same primary key already exists in `$database2`.

```yml
database_target:
  driver: mysql
  host: server1.domain.com
  port: 3306
  username: root
  password: Tantrum2025
  database_name: sipro
  databseSchema: public
  timeZone: Asia/Jakarta
database_source:
  driver: mysql
  host: server1.domain.com
  port: 3306
  username: root
  password: Tantrum2025
  database_name: sipro_ori
  databseSchema: public
  timeZone: Asia/Jakarta
maximum_record: 100
```

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());

$database = new PicoDatabase($databaseCredentials, 
    function($sql, $type) //NOSONAR
    {
        // callback when execute query that modify data
    }, 
    function($sql) //NOSONAR
    {
        // callback when execute all query
    }
);

try
{
    $database->connect();
    $shutdownManager = new ShutdownManager($database);
    $shutdownManager->registerShutdown();
}
catch(Exception $e)
{
    // do nothing
}
