<?php

use MagicApp\AppUserActivityLogger;
use MagicAppTemplate\AppLanguageImpl;
use MagicAppTemplate\ApplicationMenu;
use MagicAppTemplate\Entity\App\AppAdminRoleImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicAppTemplate\Entity\App\AppUserActivityImpl;
use MagicObject\SecretObject;
use MagicObject\SetterGetter;
use MagicObject\Util\File\FileUtil;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;
use MagicAppTemplate\AppAccountSecurity;
use MagicAppTemplate\Entity\App\AppAdminLoginImpl;

require_once __DIR__ . "/app.php";
require_once __DIR__ . "/session.php";

$appModule = new AppModuleImpl(null, $database);
$appUserRole = new AppAdminRoleImpl(null, $database);
$currentUser = new AppAdminLoginImpl(null, $database);

$languageId = 'en';

if($appConfig->getBypassRole() === true || $appConfig->getBypassRole() === "true")
{
    $currentUser->setAdminId("superuser");
    $currentUser->setUsername("superuser");
    $hashPassword2 = AppAccountSecurity::generateHash($appConfig, "superuser", 2);
    $currentUser->setPassword($hashPassword2);
    $currentUser->setName("Super User");
    $languageId = $sessions->languageId;        
    if(!isset($languageId) || empty($languageId))
    {
        $languageId = 'en';
    }
    $currentUser->setLanguageId($languageId);
    $currentUser->setActive(true);
}
else 
{
    try
    {
        $logedIn = $sessions->logedIn;
        
        if(!$logedIn)
        {
            require_once __DIR__ . "/login-form.php";
            exit();
        }
        else
        {
            $currentUser->unserialize($sessions->userData); 
            if(!$currentUser->validPasswordVersion())
            {
                require_once __DIR__ . "/login-form.php";
                exit();
            }
        }  
    }
    catch(Exception $e)
    {
        require_once __DIR__ . "/login-form.php";
        exit();
    }
}
$currentAction = new SetterGetter();
$currentAction->setTime(date('Y-m-d H:i:s'));
$currentAction->setIp($_SERVER['REMOTE_ADDR']);
$currentAction->setUserId($currentUser->getAdminId());

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $currentAction->setRequestViaAjax(true);
} 
else
{
    $currentAction->setRequestViaAjax(false);
}

if($currentUser->getLanguageId() == null || $currentUser->getLanguageId() == "")
{
    // Default value
    $currentUser->setLanguageId("en");
}
if($appConfig->getApplication() == null)
{
    $appConfig->setApplication(new SecretObject());
}
$appConfig->getApplication()->setBaseLanguageDirectory(dirname(__DIR__) . "/inc.lang");

if($appConfig->getLanguages() == null)
{
    $appConfig->setLanguages([new SecretObject()]);
}

$appLanguage = new AppLanguageImpl(
    $appConfig->getApplication(),
    $currentUser->getLanguageId(),
    function($var, $value)
    {
        $inputSource = dirname(__DIR__) . "/inc.lang/source/app.ini";
        $inputSource = FileUtil::fixFilePath($inputSource);
        if(!file_exists(dirname($inputSource)))
        {
            mkdir(dirname($inputSource), 0755, true);
        }
        $sourceData = null;
        if(file_exists($inputSource) && filesize($inputSource) > 3)
        {
            $sourceData = PicoIniUtil::parseIniFile($inputSource);
        }
        if($sourceData == null || $sourceData === false)
        {
            $sourceData = array();
        }   
        $output = array_merge($sourceData, array(PicoStringUtil::snakeize($var) => $value));
        PicoIniUtil::writeIniFile($output, $inputSource);
    }
);

$appMenuData = new SecretObject();

$appMenuPath = dirname(__DIR__) . "/inc.cfg/menu.yml";
if(file_exists($appMenuPath))
{
    $appMenuData->loadYamlFile($appMenuPath, false, true, true);
}
$curretHref = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$appMenu = new ApplicationMenu($database, $appConfig, $currentUser, $appMenuData->valueArray(), $curretHref);

$userActivityLogger = new AppUserActivityLogger($appConfig, new AppUserActivityImpl(null, $database));
