<?php
use AppBuilder\AppBuilder;
use AppBuilder\AppSecretObject;
use AppBuilder\Entity\EntityApplication;
use MagicApp\Field;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";


try
{
	$inputPost = new InputPost();
	$appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
	$applicationName = $inputPost->getName();
	$description = $inputPost->getDescription();
	$architecture = $inputPost->getArchitecture();
	
	$databaseConfig = new SecretObject($inputPost->getDatabase());
	$sessionsConfig = new SecretObject($inputPost->getSessions());
	
    // fix data type
	$databaseConfig->setPort(intval($databaseConfig->getPort()));
	$sessionsConfig->setMaxLifeTime(intval($sessionsConfig->getMaxLifeTime()));

    $appConfig = AppBuilder::loadOrCreateConfig($appId, $activeWorkspace->getDirectory(), $configTemplatePath); 

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

	AppBuilder::updateConfig($appId, $appBaseConfigPath, $appConfig);

	$workspaceDirectory = $activeWorkspace->getDirectory();
    if(PicoStringUtil::startsWith($workspaceDirectory, "./"))
    {
        $workspaceDirectory = dirname(__DIR__) . "/" . substr($workspaceDirectory, 2);
    }





    $entityApplication = new EntityApplication(null, $databaseBuilder);
    $entityApplication->where(PicoSpecification::getInstance()->addAnd(PicoPredicate::getInstance()->equals(Field::of()->applicationId, $appId)))
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