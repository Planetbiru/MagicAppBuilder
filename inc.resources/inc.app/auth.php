<?php

use MagicApp\AppUserActivityLogger;
use MagicApp\Field;
use MagicAppTemplate\AppLanguageImpl;
use MagicAppTemplate\ApplicationMenu;
use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicAppTemplate\Entity\App\AppAdminRoleImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicAppTemplate\Entity\App\AppUserActivityImpl;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\SecretObject;
use MagicObject\SetterGetter;
use MagicObject\Util\File\FileUtil;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

require_once __DIR__ . "/app.php";
require_once __DIR__ . "/session.php";

$appModule = new AppModuleImpl(null, $database);
$appUserRole = new AppAdminRoleImpl(null, $database);
$currentUser = new AppAdminImpl(null, $database);

$languageId = 'en';

if($appConfig->getBypassRole() === true || $appConfig->getBypassRole() === "true")
{
    $currentUser = new AppAdminImpl(null, $database);
    $currentUser->setAdminId("superuser");
    $currentUser->setUsername("superuser");
    $currentUser->setPassword(sha1(sha1("superuser")));
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
        $appSessionUsername = $sessions->username;
        $appSessionPassword = $sessions->userPassword;
        if(empty($appSessionUsername) || empty($appSessionPassword))
        {
            require_once __DIR__ . "/login-form.php";
            exit();
        }
        else
        {
            $appSpecsLogin = PicoSpecification::getInstance()
                ->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower(Field::of()->username), strtolower($appSessionUsername)))
                ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->password, sha1($appSessionPassword)))
                ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->active, true))
                ->addAnd(PicoSpecification::getInstance()
                    ->addOr(PicoPredicate::getInstance()->equals(Field::of()->blocked, false))
                    ->addOr(PicoPredicate::getInstance()->equals(Field::of()->blocked, null))
                )
            ;
            $currentUser->findOne($appSpecsLogin); 
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
$appMenu = new ApplicationMenu($database, $appConfig, $currentUser, $appMenuData->valueArray(), $curretHref, $appLanguage);

$userActivityLogger = new AppUserActivityLogger($appConfig, new AppUserActivityImpl(null, $database));
