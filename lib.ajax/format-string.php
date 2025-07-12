<?php

use MagicAdmin\Entity\Data\AdminProfile;
use MagicObject\Request\InputPost;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$response = new stdClass;
$response->stringFormat = '';

$adminId = $entityAdmin->getAdminId();
$applicationId = $appConfig->getApplication()->getId();
$profileName = 'stringFormat';

$inputPost = new InputPost();

if($inputPost->getStringFormat() !== null)
{
    $profile = new stdClass;
    $profile->stringFormat = $inputPost->getStringFormat();

    $adminProfileFinder = new AdminProfile(null, $databaseBuilder);
    $adminProfile = new AdminProfile(null, $databaseBuilder);
    $adminProfile->setAdminId($adminId);
    $adminProfile->setApplicationId($applicationId);
    $adminProfile->setProfileName($profileName);
    $adminProfile->setuserAgent($_SERVER['HTTP_USER_AGENT']);
    $adminProfile->setProfileValue(json_encode($profile));
    
    $now = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $adminProfile->setTimeCreate($now);
    $adminProfile->setTimeEdit($now);
    $adminProfile->setIpCreate($ip);
    $adminProfile->setIpEdit($ip);
    $adminProfile->setActive(true);
    try {
        $adminProfileFinder->findOneByAdminIdAndApplicationIdAndProfileName($adminId, $applicationId, $profileName);
        // Get the ID
        $adminProfile->setAdminProfileId($adminProfileFinder->getAdminProfileId());
        $adminProfile->update();
    }
    catch(Exception $e)
    {
        $adminProfile->insert();
    }
}
else
{
    try {

        $adminProfile = new AdminProfile(null, $databaseBuilder);
        $adminProfile->findOneByAdminIdAndApplicationIdAndProfileName($adminId, $applicationId, $profileName);
        $response = json_decode($adminProfile->getProfileValue());
    }
    catch(Exception $e)
    {
        // Do nothing
    }
}

PicoResponse::sendJSON($response);
exit();