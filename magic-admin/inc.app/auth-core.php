<?php

use MagicAdmin\AppLanguageImpl;
use MagicApp\Entity\AppModule;
use MagicApp\Entity\AppUser;
use MagicApp\Entity\AppUserRole;
use MagicObject\SecretObject;
use MagicObject\SetterGetter;
use MagicObject\Util\File\FileUtil;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

require_once dirname(dirname(__DIR__))."/inc.app/auth-core.php";

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
if($appConfig->getApplication() == null)
{
    $appConfig->setApplication(new SecretObject());
}
$appConfig->getApplication()->setBaseLanguageDirectory(dirname(__DIR__)."/inc.lang");

if($appConfig->getLanguages() == null)
{
    $appConfig->setLanguages([new SecretObject()]);
}

$langDir = dirname(__DIR__) . "/inc.lang/";

$availableLanguages = [];
if (is_dir($langDir)) {
    foreach (scandir($langDir) as $dir) {
        if ($dir === '.' || $dir === '..' || $dir === 'source') {
            continue;
        }
        if (is_dir($langDir . $dir)) {
            $availableLanguages[] = $dir;
        }
    }
}

// default from application config
$defaultLanguage = $appConfig->getOrDefaultLanguage();
if ($defaultLanguage == null || empty($defaultLanguage)) {
    $defaultLanguage = "en";
}

// get from Accept-Language header
$acceptLanguage = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
$chosenLanguage = $defaultLanguage;

if (!empty($acceptLanguage)) {
    // example: "en-US,en;q=0.9,id;q=0.8"
    $lang = strtolower(substr($acceptLanguage, 0, 2));

    if (in_array($lang, $availableLanguages)) {
        $chosenLanguage = $lang;
    }
}

$entityAdmin->setLanguageId($chosenLanguage);

$appLanguage = new AppLanguageImpl(
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

if($entityAdmin->getAdminLevelId() == 'superuser')
{
    $appConfig->setBypassRole(true);
}