<?php

use AppBuilder\Entity\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";




if(isset($entityAdmin) && $entityAdmin->issetWorkspace())
{
    $adminId = $entityAdmin->getAdminId();
    $workspaceId = $entityAdmin->getWorkspaceId();
    $author = $entityAdmin->getName();
    $workspaceDirectory = FileDirUtil::normalizePath($entityAdmin->getWorkspace()->getDirectory()."/applications");
    $dirs = FileDirUtil::scanDirectory($workspaceDirectory);
    $now = date("Y-m-d H:i:s");
    if(!empty($dirs))
    {
        foreach($dirs as $dir)
        {
            $yml = FileDirUtil::normalizePath($dir."/default.yml");
            if(file_exists($yml))
            {
                $config = new MagicObject(null);
                $config->loadYamlFile($yml, false, true, true);
                $app = $config->getApplication();
                if(!isset($app))
                {
                    $app = new MagicObject();
                }

                $applicationId = $app->getId();
                $applicationName = $app->getName();
                $projectDirectory = FileDirUtil::normalizePath($dir);
                $applicationDirectory = $app->getBaseApplicationDirectory();
                $applicationArchitecture = $app->getArchitecture();
                $applicationDescription = $app->getDescription();

                $application = new EntityApplication(null, $databaseBuilder);

                try
                {
                    $application->findOneByApplicationIdAndWorkspaceId($applicationId, $workspaceId);
                }
                catch(NoRecordFoundException $e)
                {
                    $application->setApplicationId($applicationId);
                    $application->setName($applicationName);
                    $application->setDescription($applicationDescription);
                    $application->setProjectDirectory($projectDirectory);
                    $application->setBaseApplicationDirectory($applicationDirectory);
                    $application->setArchitecture($applicationArchitecture);
                    $application->setAuthor($author);

                    $application->setAdminId($adminId);
                    $application->setWorkspaceId($workspaceId);
                    $application->setAdminCreate($adminId);
                    $application->setAdminEdit($adminId);   
                    $application->setTimeCreate($now);
                    $application->setTimeEdit($now);
                    $application->setIpCreate($_SERVER['REMOTE_ADDR']);
                    $application->setIpEdit($_SERVER['REMOTE_ADDR']);
                    $application->setActive(true);
                    $application->insert();
                }
                catch(Exception $e)
                {
                    // do nothing
                }
            }
        }
    }
}