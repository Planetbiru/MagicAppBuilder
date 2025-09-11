<?php

use AppBuilder\Util\ResponseUtil;
use MagicAdmin\Entity\Data\ApplicationHiddenMin;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$adminId = isset($entityAdmin) && $entityAdmin->issetAdminId() ? $entityAdmin->getAdminId() : null;
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$hidden = $inputPost->getHidden(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true);

if(isset($adminId) && !empty($adminId) && $applicationId)
{
    $hiddenApplication = new ApplicationHiddenMin(null, $databaseBuilder);
    try
    {
        $hiddenApplication->findOneByAdminIdAndApplicationId($adminId, $applicationId);
        $hiddenApplication->setTimeEdit(date("Y-m-d H:i:s"));
        $hiddenApplication->setIpEdit($_SERVER['REMOTE_ADDR']);
        $hiddenApplication->setHidden($hidden);
        $hiddenApplication->update();

        if($hidden && $entityAdmin->getApplicationId() === $applicationId)
        {
            $entityAdmin->setApplicationId(null)->update();
        }
    }
    catch(Exception $e)
    {
        $hiddenApplication->setApplicationId($applicationId);
        $hiddenApplication->setAdminId($adminId);
        $hiddenApplication->setTimeCreate(date("Y-m-d H:i:s"));
        $hiddenApplication->setTimeEdit(date("Y-m-d H:i:s"));
        $hiddenApplication->setIpCreate($_SERVER['REMOTE_ADDR']);
        $hiddenApplication->setIpEdit($_SERVER['REMOTE_ADDR']);
        $hiddenApplication->setHidden($hidden);
        $hiddenApplication->insert();
    }
}
ResponseUtil::sendJSON(new stdClass);