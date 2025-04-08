<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

if(!isset($applicationId) || empty($applicationId))
{
    $applicationId = $activeApplication->getApplicationId();
}

$status = new stdClass;
$status->applicationStatus = "created";

if($applicationId != null)
{
    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        $application->findOneByApplicationId($applicationId);
        $status->applicationStatus = $application->getApplicationStatus();
        ResponseUtil::sendJSON($status, false);
    }
    catch(Exception $e)
    {
        ResponseUtil::sendJSON($status, false);
    }
}
else
{
    ResponseUtil::sendJSON($status, false);
}