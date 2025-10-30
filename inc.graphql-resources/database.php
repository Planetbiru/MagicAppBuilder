<?php

// TODO: Your code here

// Please relpace all codes bellow

$cfgDbDriver         = '{DB_DRIVER}';
$cfgDbHost           = '{DB_HOST}';
$cfgDbDatabaseName   = '{DB_NAME}';
$cfgDbDatabaseSchema = '{DB_NAME}';
$cfgDbDatabaseFile   = '{DB_FILE}';
$cfgDbUser           = '{DB_USER}';
$cfgDbPass           = '{DB_PASS}';
$cfgDbCharset        = '{DB_CHARSET}';
$cfgDbPort           = '{DB_PORT}';
$cfgDbTimeZone       = '{DB_TIMEZONE}';

if(isset($cfgDbTimeZone) && !empty($cfgDbTimeZone) && $cfgDbTimeZone != '{DB_TIMEZONE}')
{
     date_default_timezone_set($cfgDbTimeZone); // Set default timezone
}

if(stripos($cfgDbDriver, 'mysql') !== false || stripos($cfgDbDriver, 'mariadb') !== false) {
     $cfgDbDriver = 'mysql'; // Normalize to mysql
     $cfgDbDsn = "mysql:host=$cfgDbHost;dbname=$cfgDbDatabaseName;charset=$cfgDbCharset";

     $options = [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false,
     ];

     try {
          $db = new PDO($cfgDbDsn, $cfgDbUser, $cfgDbPass, $options);
          if(isset($cfgDbTimeZone) && !empty($cfgDbTimeZone))
          {
               $db->exec("SET time_zone = '".$cfgDbTimeZone."'");
          }
     } catch (\PDOException $e) {
          throw new \PDOException($e->getMessage(), (int)$e->getCode());
     }
} 
else if(stripos($cfgDbDriver, 'sqlite') !== false) 
{
     try {
          $db = new PDO("sqlite:" . $cfgDbDatabaseFile);
          $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     } catch (\PDOException $e) {
          throw new \PDOException($e->getMessage(), (int)$e->getCode());
     }
} 
else if(stripos($cfgDbDriver, 'sqlsrv') !== false) 
{
     $cfgDbDsn = "sqlsrv:Server=$cfgDbHost,$cfgDbPort;Database=$cfgDbDatabaseName";
     $options = [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     ];
     try {
          $db = new PDO($cfgDbDsn, $cfgDbUser, $cfgDbPass, $options);
     } catch (\PDOException $e) {
          throw new \PDOException($e->getMessage(), (int)$e->getCode());
     }
} 
else if(stripos($cfgDbDriver, 'pgsql') !== false) 
{
     $cfgDbDsn = "pgsql:host=$cfgDbHost;port=$cfgDbPort;dbname=$cfgDbDatabaseName";
     $options = [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     ];
     try {
          $db = new PDO($cfgDbDsn, $cfgDbUser, $cfgDbPass, $options);
          if(isset($cfgDbTimeZone) && !empty($cfgDbTimeZone))
          {
               $db->exec("SET TIMEZONE = '".$cfgDbTimeZone."'");
          }
          if(isset($cfgDbDatabaseSchema) && !empty($cfgDbDatabaseSchema) && $cfgDbDatabaseSchema != '{DB_NAME}')
          {
               $db->exec("SET search_path TO ".$cfgDbDatabaseSchema);
          }
     } catch (\PDOException $e) {
          throw new \PDOException($e->getMessage(), (int)$e->getCode());
     }
}
