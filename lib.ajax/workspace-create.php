<?php

use AppBuilder\Entity\EntityWorkspace;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$workspace = new EntityWorkspace(null, $databaseBuilder);
try
{
    
    $name = $inputPost->getWorkspaceName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    $directory = $inputPost->getWorkspaceDirectory(PicoFilterConstant::FILTER_DEFAULT);
    $description = $inputPost->getWorkspaceDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    $phpPath = $inputPost->getPhpPath(PicoFilterConstant::FILTER_DEFAULT);
    
    $workspace->setName($name);
    $workspace->setDescription($description);
    $workspace->setDirectory($directory);
    $workspace->setPhpPath($phpPath);
    
    $author = isset($entityAdmin) && $entityAdmin->getName() ? $entityAdmin->getName() : null;
    $workspace->setAuthor($author);
    
    $now = date('Y-m-d H:i:s');
    
    $adminId = isset($entityAdmin) && $entityAdmin->getApplicationUserId() ? $entityAdmin->getApplicationUserId() : null;
    
    $workspace->setAdminCreate($adminId);
    $workspace->setAdminEdit($adminId); 
    
    $workspace->setTimeCreate($now);
    $workspace->setTimeEdit($now);
    $workspace->setIpCreate($_SERVER['REMOTE_ADDR']);
    $workspace->setIpEdit($_SERVER['REMOTE_ADDR']);
    $workspace->setActive(true);
    
    $workspace->insert();

    if(isset($entityAdmin) && $entityAdmin->getApplicationUserId() != null)
    {
        $entityAdmin->setWorkspaceId($workspace->getWorkspaceId())->update();
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
}