<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\EntityInstaller\EntityWorkspace;
use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\Request\InputGet;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

if(isset($entityAdmin) && $entityAdmin->issetAdminId())
{
    $inputGet = new InputGet();
    $workspaceId = $inputGet->getWorkspaceId();
    $workspace = new EntityWorkspace(null, $databaseBuilder);
    try
    {
        $workspace->find($workspaceId);
        $adminId = $entityAdmin->getAdminId();
        $author = $entityAdmin->getName();
        $workspaceDirectory = FileDirUtil::normalizePath($workspace->getDirectory()."/applications");
        $dirs = FileDirUtil::scanDirectory($workspaceDirectory);
        $now = date("Y-m-d H:i:s");
        if(!empty($dirs))
        {
            foreach($dirs as $dir)
            {
                $yml = FileDirUtil::normalizePath($dir."/default.yml");
                if(file_exists($yml))
                {
                    $config = new SecretObject(null);
                    $config->loadYamlFile($yml, false, true, true);
                    $app = $config->getApplication();
                    if(!isset($app))
                    {
                        $app = new SecretObject();
                    }

                    $applicationId = $app->getId();
                    $applicationName = $app->getName();
                    $projectDirectory = FileDirUtil::normalizePath($dir);
                    $applicationDirectory = $app->getBaseApplicationDirectory();
                    $applicationArchitecture = $app->getArchitecture();
                    $applicationDescription = $app->getDescription();

                    $url = "http://".$_SERVER['SERVER_NAME']."/".$applicationId."/";

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
                        $application->setUrl($url);
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
    catch(Exception $e)
    {
        // Do nothing
    }
}
ResponseUtil::sendJSON(new stdClass);