<?php
use AppBuilder\AppBuilder;
use MagicObject\MagicObject;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

try
{
	$inputPost = new InputPost();
	$appId = $inputPost->getApplicationId();

	$appConfig = AppBuilder::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath); 
	
	$databaseConfig = new MagicObject($inputPost->getDatabase());
	$sessionConfig = new MagicObject($inputPost->getSessions());
	
	$databaseConfig->setPort(intval($databaseConfig->getPort()));
	$sessionConfig->setLifetime(intval($sessionConfig->getLifetime()));
	
	$appConfig->setDatabase($databaseConfig);
	$appConfig->setSessions($sessionConfig);
	$appConfig->setEntityInfo($inputPost->getEntityInfo());

	AppBuilder::updateConfig($appId, $appBaseConfigPath, $appConfig);
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}