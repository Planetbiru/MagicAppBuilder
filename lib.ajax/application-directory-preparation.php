<?php

use AppBuilder\AppBuilder;
use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\ScriptGenerator;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);


if(!isset($applicationId) || empty($applicationId))
{
    $applicationId = $activeApplication->getApplicationId();
}

if($applicationId != null)
{
    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        $appBaseConfigPath = $activeWorkspace->getDirectory()."/applications";

        $application->findOneByApplicationId($applicationId);
        $applicationDirectory = $application->getBaseApplicationDirectory();
        $appConfig = AppBuilder::loadOrCreateConfig($application->getApplicationId(), $appBaseConfigPath, $configTemplatePath); 
        $scriptGenerator = new ScriptGenerator();
        $scriptGenerator->prepareApplication($builderConfig, $appConfig->getApplication(), $applicationDirectory, false);
    }
    catch(Exception $e)
    {
        // Do noting
    }
}
