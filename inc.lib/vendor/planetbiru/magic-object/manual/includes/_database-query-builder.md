## Database Query Builder

Database Query Builder is a feature for creating object-based database queries. The output of the Database Query Builder is a query that can be directly executed by the database used.

Database Query Builder is actually designed for all relational databases but is currently only available in two languages, namely MySQL and PostgreSQL. MagicObject internally uses the Database Query Builder to create queries based on given methods and parameters.

Database Query Builder is required to create native queries that cannot be created automatically by MagicObject. Apart from that, Database Query Builder is also used for performance reasons where native queries will run faster because they only retrieve the desired columns and do not perform unnecessary joins.

```php
<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;
use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Data\Entity\Album;

require_once dirname(__DIR__)."/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());
$database = new PicoDatabase($databaseCredentials);
try
{
    $database->connect();
  
    $queryBuilder = new PicoDatabaseQueryBuilder($database);
  
    $queryBuilder
        ->newQuery()
        ->select("u.*")
        ->from("user")
        ->alias("u")
        ->where("u.username = ? and u.password = ? and u.active = ?", $username, $password, true)
        ;
    $stmt = $database->executeQuery($queryBuilder);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $user)
    {
        var_dump($user);
    }
  
}
catch(Ecxeption $e)
{
  
}
```

### Methods

**newQuery()**

`newQuery()` is method to clear all properties from previous query. Allways invoke this method before create new query to ensure the query is correct.

**insert()**

`insert()` is method to start the `INSERT` query.

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')` will be executed by the database as is. You have to escape them manually before use it.

**into($query)**

`into($query)` is method for `INTO`

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

**fields($query)**

`fields($query)` is method to send field on query `INSERT`. The parameter can be an array or string.

**values($query)**

`values($query)` is method to send values on query `INSERT`. The parameter can be an array or string.

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')` will be executed by the database as is. You have to escape them manually before use it.

Example 2:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("(?, ?, ?, ?)", '123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12');
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be escaped before being executed by the database. You don't need to escape it first.

Example 3:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields(array("song_id", "name", "title", "time_create"))
    ->values(array('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be executed by the database as is. You have to escape them manually before use it.


Example 4:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values(array('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be executed by the database as is. You have to escape them manually before use it.


Example 5:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$data = array(
    "song_id"=>'123456', 
    "name"=>'Lagu 0001', 
    "title"=>'Membendung Rindu', 
    "time_create"=>'2024-03-03 10:11:12'
    );
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields(array_keys($data))
    ->values(array_values($data));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be escaped before being executed by the database. You don't need to escape it first.


**select($query)**

`select($query)` is metod for query `SELECT`

**alias($query)**

`alias($query)` is method for query `AS`

**delete()**

`delete` is method for query `DELETE`

**from($query)**

`from($query)` is method for query `FROM`

**where($query)**

`from($query)` is method for query `WHERE`

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("*")
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
select *
from song
where time_create > '2023-01-01'
*/

$queryBuilder->newQuery()
    ->detele()
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
delete
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be executed by the database as is. You have to escape them manually before use it.


Example 2:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be executed by the database as is. You have to escape them manually before use it.


Example 3:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create > ? ", '2023-01-01');
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-01'
*/

$queryBuilder->newQuery()
    ->delete()
    ->from("song")
    ->where("time_create > ? ", '2023-01-01');
/*
delete
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be escaped before being executed by the database. You don't need to escape it first.


Example 4:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create < ? ", date('Y-m-d H:i:s'));
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-00 12:12:12'
*/

$queryBuilder->newQuery()
    ->delete()
    ->from("song")
    ->where("time_create < ? ", date('Y-m-d H:i:s'));
/*
delete
from song
where time_create > '2023-01-00 12:12:12'
*/
```

**join($query)**

**leftJoin($query)**

**rightJoin($query)**

**innerJoin($query)**

**outerJoin($query)**

Example

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$active = true;
$queryBuilder->newQuery()
    ->select("song.*, album.name as album_name")
    ->from("song")
    ->leftJoin("album")
    ->on("album.album_id = song.album_id")
    ->where("song.active = ? ", $active);
/*
select song.*, album.name as album_name
from song
left join album
on album.album_id = song.album_id
where song.active = true
*/
```

This way, `$active` will be escaped before being executed by the database. You don't need to escape it first.
