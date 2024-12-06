<?php

use AppBuilder\AppInstaller;
use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once __DIR__ . "/inc.app/auth.php";

$appInstaller = new AppInstaller();

$databaseCredentials = new SecretObject();
$databaseCredentials->setDriver("sqlite");
$databaseFilePath = __DIR__."/inc.cfg/database.sqlite";
$databaseCredentials->setDatabaseFilePath($databaseFilePath);
$database = new PicoDatabase($databaseCredentials);
try
{
    $database->connect();

    $sql = $appInstaller->generateInstallerQuery($database, $cacheDir);
    
    $queries = PicoDatabaseUtil::splitSql($sql);
    try
    {
        foreach($queries as $query)
        {
            $query = $query['query'];
            if(stripos($query, "CREATE TABLE ") === 0)
            {
                $query = "CREATE TABLE IF NOT EXISTS ".substr($query, strlen("CREATE TABLE "));
            }
            $database->execute($query);
            echo "<pre>";
            echo $query;
            echo "</pre>";
        }
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
    }

    
}
catch(Exception $e)
{
    echo $e->getMessage();
}