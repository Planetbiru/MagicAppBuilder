<?php

use AppBuilder\Entity\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

try
{
	$inputPost = new InputPost();
	$appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    $applicationToUpdate = new EntityApplication(null, $databaseBuilder);

    $applicationToUpdate->find($appId);

	$applicationName = $inputPost->getName();
	$description = $inputPost->getDescription();
	$architecture = $inputPost->getArchitecture();
	
	$databaseConfig = new SecretObject($inputPost->getDatabase());
	$sessionsConfig = new SecretObject($inputPost->getSessions());
	
    // fix data type
	$databaseConfig->setPort(intval($databaseConfig->getPort()));
	$sessionsConfig->setMaxLifeTime(intval($sessionsConfig->getMaxLifeTime()));


    $yml = FileDirUtil::normalizePath($applicationToUpdate->getProjectDirectory()."/default.yml");
    $appConfig = new SecretObject(null);

    if(file_exists($yml))
    {
        $appConfig->loadYamlFile($yml, false, true, true);
    }
    else
    {
        $appConfig->loadYamlFile($configTemplatePath);
    }
    $app = $appConfig->getApplication();

    if(!isset($app))
    {
        $app = new SecretObject();
    }

	if($appConfig->getApplication() != null)
	{
		$appConfig->getApplication()->setName($applicationName);
		$appConfig->getApplication()->setDescription($description);
		$appConfig->getApplication()->setArchitecture($architecture);


        if($inputPost->getBaseApplicationDirectory() != null)
        {
            $baseApplicationDirectory = $inputPost->getBaseApplicationDirectory();
            $baseApplicationDirectory = rtrim($baseApplicationDirectory);
            $baseEntityDirectory = $baseApplicationDirectory . '/inc.lib/classes';
            $baseLanguageDirectory = $baseApplicationDirectory . '/inc.lang';

            $appConfig->getApplication()->setBaseApplicationDirectory($baseApplicationDirectory);
            $appConfig->getApplication()->setBaseEntityDirectory($baseEntityDirectory);
            $appConfig->getApplication()->setBaseLanguageDirectory($baseLanguageDirectory);

        }

	}

    $moduleLocations = $inputPost->getModuleLocation();
    foreach($moduleLocations as $i=>$v)
    {
        $moduleLocations[$i]['active'] = $moduleLocations[$i]['active'] == "true";
    }
    $appConfig->getApplication()->setBaseModuleDirectory($moduleLocations);

    $existingDatabase = $appConfig->getDatabase();
    if($existingDatabase == null)
    {
        $existingDatabase = $databaseConfig;
    }
    else
    {
        $keys = array_keys($databaseConfig->valueArray());
        foreach($keys as $key)
        {
            if($databaseConfig->get($key) != "")
            {
                $existingDatabase->set($key, $databaseConfig->get($key));
            }
        }
    }

    $existingSessions = $appConfig->getSessions();
    if($existingSessions == null)
    {
        $existingSessions = $sessionsConfig;
    }
    else
    {
        $keys = array_keys($sessionsConfig->valueArray());
        foreach($keys as $key)
        {
            if($sessionsConfig->get($key) != "")
            {
                $existingSessions->set($key, $sessionsConfig->get($key));
            }
        }
    }
	
	$appConfig->setDatabase($existingDatabase);
	$appConfig->setSessions($existingSessions);
	$appConfig->setEntityInfo($inputPost->getEntityInfo());

    $yamlData = $appConfig->dumpYaml();

    file_put_contents($applicationToUpdate->getProjectDirectory()."/default.yml", $yamlData);

	$workspaceDirectory = $activeWorkspace->getDirectory();
    if(PicoStringUtil::startsWith($workspaceDirectory, "./"))
    {
        $workspaceDirectory = dirname(__DIR__) . "/" . substr($workspaceDirectory, 2);
    }


    $applicationToUpdate
    ->setName($applicationName)
    ->setDescription($description)
    ->setArchitecture($architecture)
    ->setBaseApplicationDirectory($baseApplicationDirectory)
    ->update();
    
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
ResponseUtil::sendJSON(new stdClass);