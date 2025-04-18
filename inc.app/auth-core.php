<?php

use AppBuilder\EntityInstaller\EntityAdmin;
use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\EntityInstaller\EntityWorkspace;
use AppBuilder\Util\FileDirUtil;
use MagicObject\SecretObject;

require_once __DIR__ . "/app.php";
require_once __DIR__ . "/database-builder.php";
require_once __DIR__ . "/sessions.php";

$activeWorkspace = new EntityWorkspace();
$activeApplication = new EntityApplication();

$userLoggedIn = false;

$appBaseConfigPath = "";
$configTemplatePath = "";
if(isset($databaseBuilder))
{
    $entityAdmin = new EntityAdmin(null, $databaseBuilder);
    if(isset($sessions->username) && isset($sessions->userPassword))
    {
        try
        {
            $entityAdmin->findOneByUsernameAndPassword($sessions->username, sha1($sessions->userPassword));
            if($entityAdmin->issetWorkspace())
            {
                $activeWorkspace = $entityAdmin->getWorkspace();
            }
            if($entityAdmin->issetApplication())
            {
                $activeApplication = $entityAdmin->getApplication();
            }
            $userLoggedIn = true;

            $appBaseConfigPath = $activeWorkspace->getDirectory()."/applications";
            $configTemplatePath = dirname(__DIR__)."/inc.cfg/application-template.yml";
        }
        catch(Exception $e)
        {
            $userLoggedIn = false;
            error_log($e->getMessage());
        }
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