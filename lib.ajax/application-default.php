<?php

use AppBuilder\AppSecretObject;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

try
{
	$inputPost = new InputPost();
	$appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
	$builderConfig = new AppSecretObject(null);

    $builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";
    if(file_exists($builderConfigPath))
    {
        $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
    }

    $workspaceDirectory = $builderConfig->getWorkspaceDirectory();
    if(PicoStringUtil::startsWith($workspaceDirectory, "./"))
    {
        $workspaceDirectory = dirname(__DIR__) . "/" . substr($workspaceDirectory, 2);
    }

    $currentApplication = new AppSecretObject();
    $currentApplication->setId($appId);

    $builderConfig->setCurrentApplication($currentApplication);
    $appBaseConfigPath = $workspaceDirectory."/applications";

    $configYml = PicoYamlUtil::dump($builderConfig->valueArray(), null, 4, 0);
    file_put_contents($builderConfigPath, $configYml);

    $appList = new AppSecretObject();
    $appListPath = $workspaceDirectory."/application-list.yml";
    if(file_exists($appListPath))
    {
        $appList->loadYamlFile($appListPath, false, true, true);
        $arr = $appList->valueArray();
        foreach($arr as $idx => $app)
        {
            $arr[$idx]['selected'] = false;
            if($appId == $app['id'])
            {
                $arr[$idx]['selected'] = true;
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