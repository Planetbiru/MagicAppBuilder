<?php
use AppBuilder\AppBuilder;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";


$inputPost = new InputPost();
if($inputPost->getAction() == "update")
{
    try
    {
        $appId = $activeApplication->getApplicationId();
        $appConfig = AppBuilder::loadOrCreateConfig($appId, $activeWorkspace->getDirectory()."/applications", $configTemplatePath); 
        
        $paths = $inputPost->getPaths();
        $currentPaths = [];
        if(is_array($paths) && !empty($paths))
        {
            $appConf = $appConfig->getApplication();
            if(!isset($appConf))
            {
                $appConf = new SecretObject();
            }
            $currentPaths = [];
            $selected = '';
            foreach($paths as $p)
            {
                $currentPaths[] = new SecretObject($p);
            }
            foreach($currentPaths as $idx=>$p)
            {
                if($p->getActive() == 'true' || $p->getActive() == 1)
                {
                    $selected = $p->getPath();
                }
                $currentPaths[$idx]->setActive(false);
            }
            foreach($currentPaths as $idx=>$p)
            {
                if($p->getPath() == $selected)
                {
                    $currentPaths[$idx]->setActive(true);
                }
            }
            $appConf->setBaseModuleDirectory($currentPaths);
            AppBuilder::updateConfig($appId, $activeWorkspace->getDirectory()."/applications", $appConfig);
        }
        ResponseUtil::sendJSON($currentPaths);
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
        // do nothing
        ResponseUtil::sendJSON(new stdClass);
    }
}
else if($inputPost->getAction() == "get")
{
    try
    {
        $appId = $activeApplication->getApplicationId();
        $appConfig = AppBuilder::loadOrCreateConfig($appId, $activeWorkspace->getDirectory()."/applications", $configTemplatePath);
        $appConf = $appConfig->getApplication(); 
        $currentPaths = $appConf->getBaseModuleDirectory();
        if(!isset($currentPaths) || !is_array($currentPaths))
        {
            $currentPaths = [];
        }
        ResponseUtil::sendJSON($currentPaths);
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
        // do nothing
        ResponseUtil::sendJSON(new stdClass);
    }
}
else if($inputPost->getAction() == "default")
{
    $selected = $inputPost->getSelectedPath();
    try
    {
        if(isset($activeApplication))
        {
            $appId = $activeApplication->getApplicationId();
            $appConfig = AppBuilder::loadOrCreateConfig($appId, $activeWorkspace->getDirectory()."/applications", $configTemplatePath);      
            $appConf = $appConfig->getApplication();
            if(!isset($appConf))
            {
                $appConf = new SecretObject();
            }
            $currentPaths = $appConf->getBaseModuleDirectory();
            if(!isset($currentPaths) || !is_array($currentPaths))
            {
                $currentPaths = [];
            }
            foreach($currentPaths as $idx=>$p)
            {
                $currentPaths[$idx]->setActive(false);
                if($p->getPath() == $selected)
                {
                    $currentPaths[$idx]->setActive(true);
                }
            }
            $appConf->setBaseModuleDirectory($currentPaths);
            AppBuilder::updateConfig($appId, $activeWorkspace->getDirectory()."/applications", $appConfig);
            ResponseUtil::sendJSON($currentPaths);
        }
        else
        {
            ResponseUtil::sendJSON([]);
        }
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
        // do nothing
        ResponseUtil::sendJSON(new stdClass);
    }
}
else
{
    ResponseUtil::sendJSON(new stdClass);
}