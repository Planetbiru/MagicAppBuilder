<?php

use MagicApp\AppLanguage;
use MagicApp\Entity\AppModule;
use MagicApp\Entity\AppUser;
use MagicApp\Entity\AppUserRole;
use MagicObject\SetterGetter;
use MagicObject\Util\File\FileUtil;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

require_once dirname(dirname(__DIR__))."/inc.app/auth-core.php";

if(!isset($entityAdmin) || $entityAdmin->getAdminLevelId() != "superuser")
{
    exit(); // Bye non superuser
}

$appModule = new AppModule();
$appUserRole = new AppUserRole();
$currentUser = new AppUser($entityAdmin);

$appConfig = $builderConfig;

$database = $databaseBuilder;

$dataControlConfig = $builderConfig->getData();

$currentAction = new SetterGetter();
$currentAction->setTime(date('Y-m-d H:i:s'));
$currentAction->setIp($_SERVER['REMOTE_ADDR']);
$currentAction->setUserId($entityAdmin->getAdminId());

if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $currentAction->setRequestViaAjax(true);
} 
else
{
    $currentAction->setRequestViaAjax(false);
}

if($entityAdmin->getLanguageId() == null || $entityAdmin->getLanguageId() == "")
{
    $entityAdmin->setLanguageId("en");
}

$appConfig->getApplication()->setBaseLanguageDirectory(dirname(__DIR__)."/inc.lang");
$appLanguage = new AppLanguage(
    $appConfig->getApplication(),
    $entityAdmin->getLanguageId(),
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
