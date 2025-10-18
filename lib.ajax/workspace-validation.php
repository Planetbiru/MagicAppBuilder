<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();

try
{

    $workspaceId = $inputGet->getWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    $applicationFinder = new EntityApplication(null, $databaseBuilder);

    // Find one by workspace ID
    $workspaceId = $activeWorkspace->getWorkspaceId();

    $workspaceDirectory = $activeWorkspace->getDirectory();
    if(PicoStringUtil::startsWith($workspaceDirectory, "./"))
    {
        $workspaceDirectory = dirname(__DIR__) . "/" . substr($workspaceDirectory, 2);
    }

    $pageData = $applicationFinder->findByWorkspaceId($workspaceId);

	if($pageData->getTotalResult() > 0)
    {
        foreach($pageData->getResult() as $applicationToUpdate)
        {
            // Check if application is valid
            $rootDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory());
            if(file_exists($rootDir))
            {
                $appDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.app");
                $classesDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.lib/classes");
                $vendorDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.lib/vendor");
                $yml = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.cfg/application.yml");
                $rootDirExists = true;
                $appDirExists = file_exists($appDir);
                $classesDirExists = file_exists($classesDir);
                $vendorDirExists = file_exists($vendorDir);
                $ymlExists = file_exists($yml);
            }
            else
            {
                $rootDirExists = false;
                $appDirExists = false;
                $classesDirExists = false;
                $vendorDirExists = false;
                $ymlExists = false;
            }

            if(!$rootDirExists || !$appDirExists || !$classesDirExists || !$vendorDirExists || !$ymlExists)
            {
                $applicationValid = false;
            }
            else
            {
                $applicationValid = true;
            }

            $applicationToUpdate
                ->setApplicationValid($applicationValid)
                ->setDirectoryExists($rootDirExists)
                ->update();
        }
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
ResponseUtil::sendJSON(new stdClass);