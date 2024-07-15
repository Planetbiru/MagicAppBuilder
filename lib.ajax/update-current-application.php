<?php
use AppBuilder\AppBuilder;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

try
{
	$inputPost = new InputPost();
	$appId = $builderConfig->getCurrentApplication()->getId();

	$appConfig = AppBuilder::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath); 
	$appConfig->setDatabase($inputPost->getDatabase());
	$appConfig->setSessions($inputPost->getSessions());
	$appConfig->setEntityInfo($inputPost->getEntityInfo());

	AppBuilder::updateConfig($appId, $appBaseConfigPath, $appConfig);
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
