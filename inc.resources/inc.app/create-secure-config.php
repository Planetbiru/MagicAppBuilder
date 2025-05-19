<?php

use MagicAppTemplate\AppConfigOut;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$appConfigPath = dirname(__DIR__)."/inc.cfg/application.yml";
$appConfigSecurePath = dirname(__DIR__)."/inc.cfg/application-secure.yml";

if(file_exists($appConfigPath))
{
    $appConfig = new SecretObject();
    $appConfig->loadYamlFile($appConfigPath, false, true, true);

    $appConfigSecure = new AppConfigOut($appConfig, function(){
        // You can use environment variable such as
        // return getenv('MAGIC_SECRET_KEY');
        return "12345678901234561234567890123456";
    });
    file_put_contents($appConfigSecurePath, $appConfigSecure->dumpYaml());
}

