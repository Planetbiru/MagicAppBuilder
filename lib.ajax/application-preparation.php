<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\ScriptGenerator;
use AppBuilder\Util\FileDirUtil;
use MagicObject\SecretObject;

$array = json_decode(base64_decode($_SERVER['argv'][1]));

parse_str($array[0], $_COOKIE);

error_log("Application preparation started");

require_once dirname(__DIR__) . "/inc.app/auth.php";

error_log("Application preparation started 2");

$newAppId = $array[1];
$onlineInstallation = $array[2] == 'true';
$magicObjectVersion = $array[3];

$entityApplication = new EntityApplication(null, $databaseBuilder);
try
{
    error_log("Application preparation started 3");
    $entityApplication = new EntityApplication(null, $databaseBuilder);
    $entityApplication->findOneByApplicationId($newAppId);

    $dir2 = FileDirUtil::normalizePath($activeWorkspace->getDirectory()."/applications/$newAppId");

    $path2 = $dir2 . "/default.yml";
    $dir2 = FileDirUtil::normalizePath($activeWorkspace->getDirectory()."/applications/$newAppId");
    if (!file_exists($dir2)) 
    {
        mkdir($dir2, 0755, true);
    }

    $newApp = new SecretObject();

    $newApp->loadYamlFile($path2, false, true, true);
    $appConf = $newApp->getApplication();

    $baseDir = $appConf->getBaseApplicationDirectory();

    error_log("Application preparation started 4");
    $scriptGenerator = new ScriptGenerator();
    $scriptGenerator->prepareApplication($builderConfig, $newApp->getApplication(), $baseDir, $onlineInstallation, $magicObjectVersion, $entityApplication);
    error_log("Application preparation started 5");

}
catch(Exception $e)
{
    error_log($e->getMessage());
}
