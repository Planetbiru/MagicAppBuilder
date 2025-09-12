## Database Migration

MagicObject allows users to import data from a database with different table names and column names between the source database and the destination database. This feature is used by developers who develop applications that are already used in production environments.

On the one hand, the application requires a new database structure according to what is defined by the developer. On the other hand, users want to use existing data.

Instead of altering tables from an existing database according to the new data, users can use the data import feature provided by MagicObject. Users can define column mappings and also define queries that will be executed after the database import is performed.

Users simply create an import data configuration file dan import script as follows:

**Import Configuration**

File `import.yml`

```yml
databaseTarget:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro
  databaseSchema: public
  timeZone: Asia/Jakarta
databaseSource:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro_ori
  databaseSchema: public
  timeZone: Asia/Jakarta
maximumRecord: 100
table:
  - source: modul
    target: modul
    map: 
    - 'default_data : default'
    - 'sort_sort_order : order'
    preImportScript: 
    - "truncate modul"
    maximumRecord: 2000
  - source: hak_akses
    target: hak_akses
    map:
    - 'allowed_detail : view'
    - 'allowed_create : insert'
    - 'allowed_update : update'
    - 'allowed_delete : delete'
    preImportScript: 
    - "truncate hak_akses"
    postImportScript: 
    - "update hak_akses set allowed_list = true, allowed_approve = true, allowed_sort_order = true"
    maximumRecord: 50
```

**Explanation**

- `databaseSource` is the source database configuration
- `databaseTarget` is the target database configuration
- `table` is an array containing all the tables to be imported. Tables not listed in `table` will not be imported.
- `maximumRecord` is the maximum number of records in a single insert query. Note that MagicObject does not care about the size of the data in bytes. If you need to adjust the maximum records per table, specify `maximumRecord` on the table you want to set.

1. `source` (required)

Table name of the source database

2. `target` (required)

Table name of the target database

3. `maximumRecord` (optional)

`maximum records` on a table is used to reset the number of records per `insert` query on a table for that table. This setting will override the global setting.

Table name of the target database

4. `map` (optional)

`map` is an array of text separated by colons. On the left side of the colon are the column names in the target table and database while on the right side of the colon are the column names in the source table and database. 

5. `preImportScript` (optional)

`preImportScript` is an array of queries that will be executed before the data import begins. `preImportScript` is usually used to clear data from a table and reset all sequence or auto increment values ​​from the target table.

6. `postImportScript` (optional)

`postImportScript` is an array of queries that will be executed after the data import is complete. `postImportScript` can be used for various purposes such as fixing some data on the target table including taking values ​​from other tables. Therefore post_script must be run after all tables have been successfully imported.


**Import Script**

File `import.php`

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

$fp = fopen(__DIR__.'/db.sql', 'w');
fclose($fp);
$tool = new PicoDatabaseUtilMySql();

$tableName = "transaction";
$limit = 200;
$offset = 400;

$sql = $tool->importData($config, function($sql, $tableNameSource, $tableNameTarget, $databaseSource, $databaseTarget){
    $fp = fopen(__DIR__.'/db.sql', 'a');
    fwrite($fp, $sql.";\r\n\r\n");
    fclose($fp);
}, $tableName = null, $limit = null, $offset = null);

```

**Executing Script**

```bash
php import.php
```

MagicObject will generate SQL queries based on the configuration in `import.yml`. These queries are written into `db.sql`. The data is read from the `databaseSource`, while table and column names are automatically mapped to the `databaseTarget`. The resulting `db.sql` file can then be executed on the `databaseTarget`.

**Pre- and Post-Import Scripts**

If you want to clear or prepare a table before importing data, you can define a **`preImportScript`** for that table.

* A `preImportScript` will only run once, before the first chunk of that table is imported.
* Similarly, you can define a **`postImportScript`**, which will run once after the last chunk of a table is imported.

This ensures setup and cleanup scripts are not executed repeatedly during chunked imports.

**Chunked Import**

For large tables, you can split the import into **chunks** by setting the `$limit` and `$offset` parameters:

* `$limit` → maximum number of records per batch.
* `$offset` → the starting record index.

Each call to `importData()` processes only a portion of the data.
You can run the script multiple times with different offsets to process the entire dataset in smaller parts.

MagicObject will automatically return an indicator showing whether there are still records left after the current chunk.
This makes it easy to implement **auto-chunk loops** until all data is imported.

For more complex databases, you can use the method `PicoDatabaseUtilMySql::autoConfigureImportData()` to generate a configuration template. This method compares the source and target databases and automatically maps tables and columns. If a table exists in the target but not in the source, MagicObject will mark its source as `???`. Likewise, if a column exists in the target table but not in the source, its source will be marked as `???`. You can then manually adjust these placeholders in the configuration file.

In addition to generating an SQL file, users can also choose to **execute the generated queries directly on the target database**. This allows the import process to be performed automatically without the need to run the resulting `db.sql` file manually.

**Direct Execution**

In addition to writing an SQL file, you can also choose to **execute the generated queries directly** on the target database:

```php
$sql = $tool->importData(
    $config,
    function($sql, $tableNameSource, $tableNameTarget, $databaseSource, $databaseTarget) {
        $databaseTarget->exec($sql); // run query immediately
    }
);
```

This allows the import process to run automatically, without the need to manually execute `db.sql`.

Here is an example of how to create a database import configuration template.

**Import Configuration**

File `import.yml`

```yml
databaseTarget:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro
  databaseSchema: public
  timeZone: Asia/Jakarta
  charset: utf8
databaseSource:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro_ori
  databaseSchema: public
  timeZone: Asia/Jakarta
  charset: utf8
maximumRecord: 100
table:
  - 
    target: acuan_pengawasan
    source: acuan_pengawasan
    map:
      - "acuan_pengawasan_id : acuan_pengawasan_id"
      - "nama : nama"
      - "sort_order : order"
      - "default_data : default"
      - "aktif : aktif"
  - 
    target: acuan_pengawasan_pekerjaan
    source: acuan_pengawasan_pekerjaan
    map:
      - "acuan_pengawasan_pekerjaan_id : acuan_pengawasan_pekerjaan_id"
      - "pekerjaan_id : pekerjaan_id"
      - "acuan_pengawasan_id : acuan_pengawasan_id"
      - "aktif : aktif"
  - 
    target: akhir_pekan
    source: akhir_pekan
    preImportScript:
      - "TRUNCATE akhir_pekan"
    postImportScript:
      - "UPDATE akhir_pekan SET default_data = TRUE WHERE aktif = TRUE"
    map:
      - "akhir_pekan_id : akhir_pekan_id"
      - "nama : nama"
      - "kode_hari : kode_hari"
      - "sort_order : order"
      - "default_data : default"
      - "aktif : aktif"
```

**Create Configuration**

Users can create an import configuration using **MagicAppBuilder**. This feature has been available since version **1.20.0**. Users can map table names, column names, and define pre-import and post-import scripts.

MagicAppBuilder provides a starter configuration if users change their table or column naming strategy. Users only need to adjust the parts that are necessary. If there are no column name changes in a table, users do not need to map the column names—mapping the table name alone is sufficient.
