<?php

use AppBuilder\AppSecretObject;
use AppBuilder\Util\FileDirUtil;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

if(!isset($_SERVER['REMOTE_ADDR']))
{
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}

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
    baseEntityNamespace: 'MagicAdmin\Entity'
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
    connectionTimeout: 10
sessions:
    name: MAGICAPP
    maxLifetime: 86400
    saveHandler: files
    savePath: ''
    cookiePath: /
    cookieDomain: ''
    cookieSecure: false
    cookieHttpOnly: false
    cookieSameSite: Strict
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
    prettify_module_data: false

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
    memoryLimit: 256M
exportFileMaxAge: 21600

notification:
    enabled: true
    auth_token: default_token
    ws_port: 8081
    ws_url: ws://localhost:8081/notify
    http_port: 8080
    http_url: http://localhost:8080/notify
    http_request_timeout: 10

iddleDuration: 3000
", true, true, true);
    $builderConfig->getDatabase()->setDatabaseFilePath($defaultDatabasePath);
    $yaml = $builderConfig->dumpYaml();
    file_put_contents($builderConfigPath, $yaml);
}

if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}
