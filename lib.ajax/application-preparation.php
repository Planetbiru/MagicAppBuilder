<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\ScriptGenerator;
use AppBuilder\Util\FileDirUtil;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$path2 = $dir2 . "/default.yml";
$dir2 = FileDirUtil::normalizePath($activeWorkspace->getDirectory()."/applications/$newAppId");
if (!file_exists($dir2)) 
{
    mkdir($dir2, 0755, true);
}
$baseDir = $appConf->getBaseApplicationDirectory();

$newApp = new SecretObject();

$newApp->loadYamlFile($path2, false, true, true);

$entityApplication = new EntityApplication(null, $databaseBuilder);
try
{
    $entityApplication = new EntityApplication(null, $databaseBuilder);
    $entityApplication->findOneByApplicationId($newAppId);

    $scriptGenerator = new ScriptGenerator();
    $scriptGenerator->prepareApplication($builderConfig, $newApp->getApplication(), $baseDir, $onlineInstallation, $magicObjectVersion, $entityApplication);
}
catch(Exception $e)
{
    error_log($e->getMessage());
}


