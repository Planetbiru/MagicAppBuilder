<?php

use MagicAppTemplate\AppLanguageImpl;
use MagicAppTemplate\Entity\App\AppAdminImpl;
use MagicObject\Request\InputPost;
use MagicObject\Util\File\FileUtil;
use MagicObject\Util\PicoIniUtil;
use MagicObject\Util\PicoStringUtil;

require_once __DIR__ . "/inc.app/session.php";

$inputPost = new InputPost();

$currentUser = new AppAdminImpl(null, $database);

$defaultLanguage = $appConfig->getOrDefaultLanguage();
if($defaultLanguage == null || empty($defaultLanguage))
{
    $defaultLanguage = "en";
}
$appLanguage = new AppLanguageImpl(
    $appConfig->getApplication(),
    $defaultLanguage,
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

if($inputPost->getUsername() != null && $inputPost->getPassword() != null)
{
    $userLoggedIn = false;
    try
    {
        $hashPassword = sha1($inputPost->getPassword());
        $currentUser->findOneByUsernameAndPassword($inputPost->getUsername(), sha1($hashPassword));
        $userLoggedIn = true;
        $sessions->username = $inputPost->getUsername();
        $sessions->userPassword = $hashPassword;
    }
    catch(Exception $e)
    {
        $userLoggedIn = false;
    }
    if(!$userLoggedIn)
    {
        require_once __DIR__ . "/inc.app/login-form.php";
        exit();
    }
    else
    {
        header("Location: ./index.php");
    }
}
else
{
    require_once __DIR__ . "/inc.app/login-form.php";
    exit();
}