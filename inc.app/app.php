<?php

use AppBuilder\AppSecretObject;
use AppBuilder\Util\FileDirUtil;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$builderConfig = new AppSecretObject(null);
if($builderConfig->getApplication() == null)
{
    $builderConfig->setApplication(new SecretObject());
}

$cacheDir = dirname(__DIR__) . "/.cache/";
$builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";

if(!file_exists($builderConfigPath))
{
    $defaultDatabasePath = FileDirUtil::normalizePath(dirname(__DIR__) . "/inc.database/database.sqlite");
    if(!file_exists(dirname($defaultDatabasePath)))
    {
        mkdir(dirname($defaultDatabasePath), 0755, true);
    }
    $builderConfig->loadYamlString("
application:
    entityBaseNamespace: 'MagicAdmin\Entity'
    name: 'MagicAppBuilder'
dataLimit: 20
database:
    driver: sqlite
    host: ''
    port: 3306
    username: ''
    password: ''
    databaseName: ''
    databaseSchema: public
    timeZone: Asia/Jakarta
    databaseFilePath: ''

role:
    bypassRole: true

data:
    pageSize: 20
    pageMargin: 3
    twoUserApproval: true
    prev: '<i class=\"fa-solid fa-angle-left\"></i>'
    next: '<i class=\"fa-solid fa-angle-right\"></i>'
    first: '<i class=\"fa-solid fa-angles-left\"></i>'
    last: '<i class=\"fa-solid fa-angles-right\"></i>'

languages:
    -
        name: English
        code: en
    -
        name: Indonesia
        code: id

dateFormatDetail: 'j F Y H:i:s'
phpIni:
    maxExecutionTime: 600
", true, true, true);
    $builderConfig->getDatabase()->setDatabaseFilePath($defaultDatabasePath);
    $yaml = $builderConfig->dumpYaml();
    file_put_contents($builderConfigPath, $yaml);
}

if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}
