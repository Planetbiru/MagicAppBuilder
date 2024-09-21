<?php
use AppBuilder\AppBuilder;
use AppBuilder\AppSecretObject;
use MagicObject\Request\InputPost;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

try
{
	$inputPost = new InputPost();
	$appId = $inputPost->getApplicationId();

	$appConfig = AppBuilder::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath); 

	$applicationName = $inputPost->getName();
	$description = $inputPost->getDescription();
	$type = $inputPost->getType();
	
	$databaseConfig = new SecretObject($inputPost->getDatabase());
	$sessionsConfig = new SecretObject($inputPost->getSessions());
	
	$databaseConfig->setPort(intval($databaseConfig->getPort()));
	$sessionsConfig->setLifetime(intval($sessionsConfig->getLifetime()));

	if($appConfig->getApplication() != null)
	{
		$appConfig->getApplication()->setName($applicationName);
		$appConfig->getApplication()->setDescription($description);
		$appConfig->getApplication()->setType($type);
	}

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

    $existingSessions = $appConfig->getDatabase();
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

	AppBuilder::updateConfig($appId, $appBaseConfigPath, $appConfig);

	$workspaceDirectory = $builderConfig->getWorkspaceDirectory();
    if(PicoStringUtil::startsWith($workspaceDirectory, "./"))
    {
        $workspaceDirectory = dirname(__DIR__) . "/" . substr($workspaceDirectory, 2);
    }

    $appListPath = $workspaceDirectory."/application-list.yml";

    $currentApplication = new AppSecretObject();

    $currentApplication->setId($appId);

    $builderConfig->setCurrentApplication($currentApplication);
    $appBaseConfigPath = $workspaceDirectory."/applications";

    $configYml = PicoYamlUtil::dump($builderConfig->valueArray(), null, 4, 0);
    file_put_contents($builderConfigPath, $configYml);

    $appList = new AppSecretObject();
    if(file_exists($appListPath))
    {
        $appList->loadYamlFile($appListPath, false, true, true);
        $arr = $appList->valueArray();
        foreach($arr as $idx => $app)
        {
            if($appId == $app['id'])
            {
                $arr[$idx]['name'] = $applicationName;
				$arr[$idx]['description'] = $description;
				$arr[$idx]['type'] = $type;
            }
        }
        file_put_contents($appListPath, PicoYamlUtil::dump($arr, null, 4, 0));
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}