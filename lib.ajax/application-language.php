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
        
        $languages = $inputPost->getLanguages();
        $currentLanguages = [];
        if(is_array($languages) && !empty($languages))
        {
            $currentLanguages = [];
            $selected = '';
            foreach($languages as $p)
            {
                $currentLanguages[] = new SecretObject($p);
            }
            foreach($currentLanguages as $idx=>$p)
            {
                if($p->getActive() == 'true' || $p->getActive() == 1)
                {
                    $selected = $p->getCode();
                }
                $currentLanguages[$idx]->setActive(false);
            }
            foreach($currentLanguages as $idx=>$p)
            {
                if($p->getCode() == $selected)
                {
                    $currentLanguages[$idx]->setActive(true);
                }
            }
            $appConfig->setLanguages($currentLanguages);
            AppBuilder::updateConfig($appId, $activeWorkspace->getDirectory()."/applications", $appConfig);
        }
        ResponseUtil::sendJSON($currentLanguages);
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
        $currentLanguages = $appConfig->getLanguages();
        if(!isset($currentLanguages) || !is_array($currentLanguages))
        {
            $currentLanguages = [];
        }
        ResponseUtil::sendJSON($currentLanguages);
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
    $selected = $inputPost->getSelectedLanguage();
    try
    {
        $appId = $activeApplication->getApplicationId();
        $appConfig = AppBuilder::loadOrCreateConfig($appId, $activeWorkspace->getDirectory()."/applications", $configTemplatePath);      

        $currentLanguages = $appConfig->getLanguages();
        if(!isset($currentLanguages) || !is_array($currentLanguages))
        {
            $currentLanguages = [];
        }
        foreach($currentLanguages as $idx=>$p)
        {
            $currentLanguages[$idx]->setActive(false);
            if($p->getCode() == $selected)
            {
                $currentLanguages[$idx]->setActive(true);
            }
        }
        $appConfig->setLanguages($currentLanguages);
        AppBuilder::updateConfig($appId, $activeWorkspace->getDirectory()."/applications", $appConfig);
        ResponseUtil::sendJSON($currentLanguages);
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