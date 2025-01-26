<?php

use AppBuilder\Util\ResponseUtil;
use MagicAdmin\Entity\Data\ActiveApplicationHistory;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
if($inputPost->getWorkspaceId())
{
    $workspaceId = $inputPost->getWorkspaceId();
    if(isset($entityAdmin))
    {
        $entityAdmin->setWorkspaceId($workspaceId);
        $activeApplicationHistory = new ActiveApplicationHistory(null, $databaseBuilder);
        try 
        {
            $activeApplicationHistory->findOneByAdminIdAndWorkspaceId($entityAdmin->getAdminId(), $workspaceId);
            $entityAdmin->setApplicationId($activeApplicationHistory->getApplicationId());
        }
        catch(Exception $e)
        {
            // No selected application
            $entityAdmin->setApplicationId(null);
        }
        $entityAdmin->update();
    }
}
ResponseUtil::sendJSON(new stdClass);