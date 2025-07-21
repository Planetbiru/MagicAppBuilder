<?php

use AppBuilder\Util\ResponseUtil;
use MagicAdmin\Entity\Data\StarWorkspace;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$adminId = isset($entityAdmin) && $entityAdmin->issetAdminId() ? $entityAdmin->getAdminId() : null;
$workspaceId = $inputPost->getWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$star = $inputPost->getStar(PicoFilterConstant::FILTER_SANITIZE_BOOL, false, false, true);

if(isset($adminId) && !empty($adminId) && $workspaceId)
{
    $starWorkspace = new StarWorkspace(null, $databaseBuilder);
    try
    {
        $starWorkspace->findOneByAdminIdAndWorkspaceId($adminId, $workspaceId);
        $starWorkspace->setStar($star);
        $starWorkspace->update();
    }
    catch(Exception $e)
    {
        $starWorkspace->setWorkspaceId($workspaceId);
        $starWorkspace->setAdminId($adminId);
        $starWorkspace->setStar($star);
        $starWorkspace->insert();
    }
}
ResponseUtil::sendJSON(new stdClass);