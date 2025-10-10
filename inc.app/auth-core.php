<?php

use AppBuilder\EntityInstaller\EntityAdmin;
use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\EntityInstaller\EntityWorkspace;
use AppBuilder\Util\FileDirUtil;
use MagicObject\SecretObject;
use MagicObject\SetterGetter;

require_once __DIR__ . "/app.php";
require_once __DIR__ . "/database-builder.php";
require_once __DIR__ . "/sessions.php";

$activeWorkspace = new EntityWorkspace();
$activeApplication = new EntityApplication();

$userLoggedIn = false;
$appBaseConfigPath = "";
$configTemplatePath = "";
$entityAdmin = new EntityAdmin(null, $databaseBuilder);

// Initialize action context with user ID, IP address, and timestamp.
// This context will be used when updating the application database.
$currentAction = new SetterGetter();

$currentAction->setIp($_SERVER['REMOTE_ADDR']);
$currentAction->setTime(date('Y-m-d H:i:s'));
if(isset($databaseBuilder) && isset($sessions->magicUsername) && isset($sessions->magicUserPassword))
{
    try
    {
        $entityAdmin->findOneByUsernameAndPassword($sessions->magicUsername, sha1($sessions->magicUserPassword));
        if($entityAdmin->issetWorkspace())
        {
            $activeWorkspace = $entityAdmin->getWorkspace();
        }
        if($entityAdmin->issetApplication())
        {
            $activeApplication = $entityAdmin->getApplication();
        }
        $userLoggedIn = true;
        $currentAction->setUserId($entityAdmin->getAdminId());

        $appBaseConfigPath = $activeWorkspace->getDirectory()."/applications";
        $configTemplatePath = dirname(__DIR__)."/inc.cfg/application-template.yml";
    }
    catch(Exception $e)
    {
        $userLoggedIn = false;
        error_log($e->getMessage());
    }
}

$appConfig = new SecretObject(null);

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

