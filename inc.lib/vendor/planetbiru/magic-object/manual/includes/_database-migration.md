## Database Migration

MagicObject allows users to import data from a database with different table names and column names between the source database and the destination database. This feature is used by developers who develop applications that are already used in production environments.

On the one hand, the application requires a new database structure according to what is defined by the developer. On the other hand, users want to use existing data.

Instead of altering tables from an existing database according to the new data, users can use the data import feature provided by MagicObject. Users can define column mappings and also define queries that will be executed after the database import is performed.

Users simply create an import data configuration file dan import script as follows:

**Import Configuration**

File `import.yml`

```yml
database_target:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro
  databaseSchema: public
  timeZone: Asia/Jakarta
database_source:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro_ori
  databaseSchema: public
  timeZone: Asia/Jakarta
maximum_record: 100
table:
  - source: modul
    target: modul
    map: 
    - 'default_data : default'
    - 'sort_order : order'
    pre_import_script: 
    - "truncate modul"
    maximum_record: 2000
  - source: hak_akses
    target: hak_akses
    map:
    - 'allowed_detail : view'
    - 'allowed_create : insert'
    - 'allowed_update : update'
    - 'allowed_delete : delete'
    pre_import_script: 
    - "truncate hak_akses"
    post_import_script: 
    - "update hak_akses set allowed_list = true, allowed_approve = true, allowed_sort_order = true"
    maximum_record: 50
```

**Explanation**

- `database_source` is the source database configuration
- `database_target` is the target database configuration
- `table` is an array containing all the tables to be imported. Tables not listed in `table` will not be imported.
- `maximum_record` is the maximum number of records in a single insert query. Note that MagicObject does not care about the size of the data in bytes. If you need to adjust the maximum records per table, specify `maximum_record` on the table you want to set.

1. `source` (required)

Table name of the source database

2. `target` (required)

Table name of the target database

3. `maximum_record` (optional)

`maximum records` on a table is used to reset the number of records per `insert` query on a table for that table. This setting will override the global setting.

Table name of the target database

4. `map` (optional)

`map` is an array of text separated by colons. On the left side of the colon are the column names in the target table and database while on the right side of the colon are the column names in the source table and database. 

5. `pre_import_script` (optional)

`pre_import_script` is an array of queries that will be executed before the data import begins. `pre_import_script` is usually used to clear data from a table and reset all sequence or auto increment values ​​from the target table.

6. `post_import_script` (optional)

`post_import_script` is an array of queries that will be executed after the data import is complete. `post_import_script` can be used for various purposes such as fixing some data on the target table including taking values ​​from other tables. Therefore post_script must be run after all tables have been successfully imported.

**Import Script**

File `import.php`

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

$fp = fopen(__DIR__.'/db.sql', 'w');
fclose($fp);
$sql = PicoDatabaseUtilMySql::importData($config, function($sql, $source, $target){
    $fp = fopen(__DIR__.'/db.sql', 'a');
    fwrite($fp, $sql.";\r\n\r\n");
    fclose($fp);
});
```

**Executing Script**

```bash
php import.php
```

MagicObject will create a database query that is saved into a file named `db.sql`. The data is taken from the `database_source` but the table and column names have been adjusted to the `database_target`. This query can be run in the `database_target`. If you want to empty a table before importing data, you can add a pre_import_script to each table. Keep in mind that all pre_import_scripts will be executed before MagicObject starts importing data.

If the database is too complex, users can use the PicoDatabaseUtilMySql::autoConfigureImportData() method to create a configuration template to avoid missing table and column names. Users simply specify the source database and the target database. MagicObject will check the tables and columns in both databases. If a table exists in the target database but not in the source database, MagicObject will write ??? as its source name. Users can manually change the name of this table. In the same table, if a column exists in the target database but not in the source database, MagicObject will write ??? as its source name. Users can manually change the name of this column.

Here is an example of how to create a database import configuration template.

**Import Configuration**

File `import.yml`

```yml
database_target:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro
  databaseSchema: public
  timeZone: Asia/Jakarta
database_source:
  driver: mysql
  host: server1.domain.tld
  port: 3306
  username: root
  password: Jenglotsaurus
  database_name: sipro_ori
  databaseSchema: public
  timeZone: Asia/Jakarta
maximum_record: 100
```

**Import Template Script**

File `configure-import.php`

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

PicoDatabaseUtilMySql::autoConfigureImportData($config);
file_put_contents('import.yml', $config->dumpYaml(0, 2));
```

**Executing Script**

```bash
php configure-import.php
```