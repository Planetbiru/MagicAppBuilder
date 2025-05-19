<?php

use MagicAppTemplate\AppConfigIn;
use MagicAppTemplate\AppIpForwarder;
use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

// To enable secure configuration, set $enableSecureConfig to true.
$enableSecureConfig = true;

$appConfig = new SecretObject();
$appConfigPath = dirname(__DIR__)."/inc.cfg/application.yml";

if ($enableSecureConfig) {
    $appConfig = new AppConfigIn(null, function() {
        // You can use environment variables, for example:
        // return getenv('MAGIC_SECRET_KEY');
        return "12345678901234561234567890123456";
    });
    $appConfigPath = dirname(__DIR__)."/inc.cfg/application-secure.yml";
}

// -------------------------------------------------------------------

// Instructions to create a secure configuration:
// 1. Navigate to your application's inc.app directory:
//    cd your-app/inc.app
// 2. Execute the PHP script:
//    php create-secure-config.php
// Important: Delete the your-app/inc.cfg/application.yml file after using secure configuration.

if(file_exists($appConfigPath))
{
    $appConfig->loadYamlFile($appConfigPath, false, true, true);

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

// Forward IP Address
AppIpForwarder::apply($appConfig->getIpForwarding());

if($appConfig->getAccessLocalhostOnly())
{
    $allowedIps = ['127.0.0.1', '::1'];
    if (!in_array($_SERVER['REMOTE_ADDR'], $allowedIps)) {
        require_once __DIR__ . "/403.php";
        exit;
    }
}
$appCurrentTheme = $appConfig->getApplication()->getActiveTheme();
$appConfig->setAssets("lib.themes/".$appCurrentTheme."/assets/");
