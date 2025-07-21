<?php

use AppBuilder\Util\ResponseUtil;
use MagicAdmin\Entity\Data\StarApplication;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$adminId = isset($entityAdmin) && $entityAdmin->issetAdminId() ? $entityAdmin->getAdminId() : null;
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$star = $inputPost->getStar(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true);

if(isset($adminId) && !empty($adminId) && $applicationId)
{
    $databaseBuilder->setCallbackDebugQuery(function($sql){
       error_log($sql); 
    });
    $starApplication = new StarApplication(null, $databaseBuilder);
    try
    {
        $starApplication->findOneByAdminIdAndApplicationId($adminId, $applicationId);
        $starApplication->setStar($star);
        $starApplication->update();
    }
    catch(Exception $e)
    {
        $starApplication->setApplicationId($applicationId);
        $starApplication->setAdminId($adminId);
        $starApplication->setStar($star);
        $starApplication->insert();
    }
}
ResponseUtil::sendJSON(new stdClass);