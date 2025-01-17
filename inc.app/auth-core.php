<?php

use AppBuilder\Entity\EntityAdmin;
use AppBuilder\Entity\EntityApplication;
use AppBuilder\Entity\EntityWorkspace;

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
        }
    }


}
require_once __DIR__ . "/database.php";
