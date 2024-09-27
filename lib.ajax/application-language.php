<?php
use AppBuilder\AppBuilder;
use MagicObject\Request\InputPost;
use MagicObject\Response\PicoResponse;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();
if($inputPost->getAction() == "update")
{
    try
    {
        $appId = $builderConfig->getCurrentApplication()->getId();
        $appConfig = AppBuilder::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath); 
        
        $languages = $inputPost->getLanguages();
        $currentLanguages = array();
        if(is_array($languages) && !empty($languages))
        {
            $currentLanguages = array();
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
            AppBuilder::updateConfig($appId, $appBaseConfigPath, $appConfig);
        }
        PicoResponse::sendJSON($currentLanguages);
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
        // do nothing
    }
}
else if($inputPost->getAction() == "get")
{
    try
    {
        $appId = $builderConfig->getCurrentApplication()->getId();
        $appConfig = AppBuilder::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath);
        $currentLanguages = $appConfig->getLanguages();
        if(!isset($currentLanguages) || !is_array($currentLanguages))
        {
            $currentLanguages = array();
        }
        PicoResponse::sendJSON($currentLanguages);
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
        // do nothing
    }
}
else if($inputPost->getAction() == "default")
{
    $selected = $inputPost->getSelectedLanguage();
    try
    {
        $appId = $builderConfig->getCurrentApplication()->getId();
        $appConfig = AppBuilder::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath);      

        $currentLanguages = $appConfig->getLanguages();
        if(!isset($currentLanguages) || !is_array($currentLanguages))
        {
            $currentLanguages = array();
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
        AppBuilder::updateConfig($appId, $appBaseConfigPath, $appConfig);
        PicoResponse::sendJSON($currentLanguages);
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
        // do nothing
    }
}