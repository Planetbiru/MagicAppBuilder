<?php

use AppBuilder\Util\FileDirUtil;
use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$appConfig = new SecretObject(null);
try
{
    $yml = FileDirUtil::normalizePath($activeApplication->getProjectDirectory()."/default.yml");
    if(file_exists($yml))
    {
        $appConfig->loadYamlFile($yml, false, true, true);
        $app = $appConfig->getApplication();
        $databaseConfig = new SecretObject($appConfig->getDatabase());
    }
    if(!isset($app))
    {
        $app = new SecretObject();
    }
    if(!isset($databaseConfig))
    {
        $databaseConfig = new SecretObject();
    }

    $database = new PicoDatabase($databaseConfig);
    $databaseName = $databaseConfig->getDatabaseName();
    $schemaName = $databaseConfig->getDatabaseSchema();
    

    $database->connect();
    $databaseType = $database->getDatabaseType();
}
catch(Exception $e)
{
    // do nothing
}
