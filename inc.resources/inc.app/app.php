<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;

require_once dirname(dirname(__DIR__)) . "/inc.lib/vendor/autoload.php";

$appConfig = new SecretObject();
$appConfigPath = dirname(__DIR__)."/inc.cfg/application.yml";
if(file_exists($appConfigPath))
{
    $appConfig->loadYamlFile(dirname(__DIR__)."/inc.cfg/application.yml", false, true, true);

    $dataControlConfig = new SecretObject($appConfig->getData());
    $entityInfo = $appConfig->getEntityInfo();
    $entityApvInfo = $appConfig->getEntityApvInfo();

    $database = new PicoDatabase($appConfig->getDatabase());
    try
    {
        $database->connect();
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
    }
}
else
{
    require_once __DIR__ . "/500.php";
    exit();
}
if($appConfig->getAccessLocalhostOnly())
{
    $allowedIps = ['127.0.0.1', '::1'];
    if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIps)) {
        require_once __DIR__ . "/403.php";
        exit;
    }
}
$appCurrentTheme = $appConfig->getApplication()->getActiveTheme();