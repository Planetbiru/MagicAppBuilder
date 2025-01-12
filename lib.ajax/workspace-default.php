<?php

use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
if($inputPost->getWorkspaceId())
{
    $workspaceId = $inputPost->getWorkspaceId();
    if(isset($entityAdmin))
    {
        $entityAdmin->setWorkspaceId($workspaceId)->update();
    }
}