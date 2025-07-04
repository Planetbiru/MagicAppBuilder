<?php

use AppBuilder\AppBuilderBase;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$appBaseConfigPath = $activeWorkspace->getDirectory()."/applications";
if($inputPost->getUserAction() == "update")
{
    try
    {
        $appId = $activeApplication->getApplicationId();
        $appConfigBuilder = AppBuilderBase::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath); 
    
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
            $defaultLanguage = '';
            foreach($currentLanguages as $idx=>$p)
            {
                if($p->getCode() == $selected)
                {
                    $currentLanguages[$idx]->setActive(true);
                    $defaultLanguage = $p->getCode();
                }
            }
            $appConfigBuilder->setLanguages($currentLanguages);
            AppBuilderBase::updateConfig($appId, $appBaseConfigPath, $appConfigBuilder);
            
            // Update the application.yml file
            $appConfigPath = $activeApplication->getBaseApplicationDirectory()."/inc.cfg/application.yml";
            $appConfig = new SecretObject();
            $appConfig->loadYamlFile($appConfigPath, false, true, true);
            $appConfig->setLanguages($currentLanguages);
            $appConfig->setDefaultLanguage($defaultLanguage);
            $yml = $appConfig->dumpYaml();
            file_put_contents($appConfigPath, $yml);
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
else if($inputPost->getUserAction() == "get")
{
    try
    {
        $appId = $activeApplication->getApplicationId();
        $appConfigBuilder = AppBuilderBase::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath);
        $currentLanguages = $appConfigBuilder->getLanguages();
        if(!isset($currentLanguages) || !is_array($currentLanguages))
        {
            $currentLanguages = array();
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
else if($inputPost->getUserAction() == "default")
{
    $selected = $inputPost->getSelectedLanguage();
    try
    {
        $appId = $activeApplication->getApplicationId();
        $appConfigBuilder = AppBuilderBase::loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath);      

        $currentLanguages = $appConfigBuilder->getLanguages();
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
        $appConfigBuilder->setLanguages($currentLanguages);
        AppBuilderBase::updateConfig($appId, $appBaseConfigPath, $appConfigBuilder);
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