<?php

use MagicObject\Database\PicoDatabaseType;
use MagicObject\Util\Database\PicoDatabaseConverter;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$converter = new PicoDatabaseConverter();

$mySql = <<<SQL
CREATE TABLE IF NOT EXISTS `admin` (
  `admin_id` bigint(40) NOT NULL auto_increment,
  `name` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `admin_level_id` varchar(40) DEFAULT NULL,
  `gender` varchar(1) DEFAULT NULL,
  `birth_day` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(100) DEFAULT NULL,
  `language_id` varchar(40) DEFAULT NULL,
  `validation_code` text,
  `last_reset_password` timestamp NULL DEFAULT NULL,
  `blocked` tinyint(1) DEFAULT '0',
  `time_create` timestamp NULL DEFAULT NULL,
  `time_edit` timestamp NULL DEFAULT NULL,
  `admin_create` varchar(40) DEFAULT NULL,
  `admin_edit` varchar(40) DEFAULT NULL,
  `ip_create` varchar(50) DEFAULT NULL,
  `ip_edit` varchar(40) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;


$postgreSql = $converter->translateCreateTable($mySql, PicoDatabaseType::DATABASE_TYPE_MARIADB, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL);
$sqlite = $converter->translateCreateTable($mySql, PicoDatabaseType::DATABASE_TYPE_MARIADB, PicoDatabaseType::DATABASE_TYPE_SQLITE);

echo "MySQL:\n";
echo $mySql . "\n\n";

echo "PostgreSQL:\n";
echo $postgreSql . "\n\n";

echo "SQLite:\n";
echo $sqlite . "\n\n";

echo "Now, let's convert the PostgreSQL back to MySQL:\n";
$mySqlConverted = $converter->translateCreateTable($postgreSql, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL, PicoDatabaseType::DATABASE_TYPE_MARIADB);
echo $mySqlConverted . "\n\n";



echo "Now, let convert PostgreSQL to SQLite:\n";
$sqliteConverted = $converter->translateCreateTable($postgreSql, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL, PicoDatabaseType::DATABASE_TYPE_SQLITE);   
echo $sqliteConverted . "\n\n";



echo "Now, let convert SQLite to MySQL:\n";
$mysqlConverted2 = $converter->translateCreateTable($sqliteConverted, PicoDatabaseType::DATABASE_TYPE_SQLITE, PicoDatabaseType::DATABASE_TYPE_MYSQL);   
echo $mysqlConverted2 . "\n\n";



echo "Now, let convert SQLite to PostgreSQL:\n";
$postgresqlConverted2 = $converter->translateCreateTable($sqliteConverted, PicoDatabaseType::DATABASE_TYPE_SQLITE, PicoDatabaseType::DATABASE_TYPE_POSTGRESQL);   
echo $postgresqlConverted2 . "\n\n";

