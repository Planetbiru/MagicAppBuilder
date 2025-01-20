<?php

use MagicAdmin\Entity\Data\ActiveApplicationHistory;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

try
{
    $inputPost = new InputPost();
    $now = date('Y-m-d H:i:s');
    if(isset($entityAdmin) && $entityAdmin->issetAdminId())
    {
        $appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
        $entityAdmin->setApplicationId($appId)->update();
        $activeApplicationHistory = new ActiveApplicationHistory(null, $databaseBuilder);
        try
        {
            $activeApplicationHistory->findOneByAdminIdAndWorkspaceId($entityAdmin->getAdminId(), $entityAdmin->getWorkspaceId());
            // Update
            $activeApplicationHistory
                ->setApplicationId($entityAdmin->getApplicationId())
                ->setTimeEdit($now)
                ->update()
                ;
        }
        catch(Exception $e)
        {
            // Insert
            $activeApplicationHistory
                ->setAdminId($entityAdmin->getAdminId())
                ->setWorkspaceId($entityAdmin->getWorkspaceId())
                ->setApplicationId($entityAdmin->getApplicationId())
                ->setTimeCreate($now)
                ->setTimeEdit($now)
                ->setActive(true)
                ->insert()
                ;
        }
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
