<?php

use AppBuilder\Entity\EntityAdminWorkspace;
use AppBuilder\Entity\EntityWorkspace;
use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$workspace = new EntityWorkspace(null, $databaseBuilder);
try
{
    $name = $inputPost->getWorkspaceName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    $directory = FileDirUtil::normalizePath($inputPost->getWorkspaceDirectory(PicoFilterConstant::FILTER_DEFAULT));
    $description = $inputPost->getWorkspaceDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    $phpPath = $inputPost->getPhpPath(PicoFilterConstant::FILTER_DEFAULT);
    
    $workspace->setName($name);
    $workspace->setDescription($description);
    $workspace->setDirectory($directory);
    $workspace->setPhpPath($phpPath);
    
    $author = isset($entityAdmin) && $entityAdmin->getName() ? $entityAdmin->getName() : null;
    $workspace->setAuthor($author);
    
    $now = date('Y-m-d H:i:s');
    
    $adminId = isset($entityAdmin) && $entityAdmin->issetAdminId() ? $entityAdmin->getAdminId() : null;
    
    $workspace->setAdminCreate($adminId);
    $workspace->setAdminEdit($adminId);   
    $workspace->setTimeCreate($now);
    $workspace->setTimeEdit($now);
    $workspace->setIpCreate($_SERVER['REMOTE_ADDR']);
    $workspace->setIpEdit($_SERVER['REMOTE_ADDR']);
    $workspace->setActive(true);
    
    $workspace->insert();

    $workspaceId = $workspace->getWorkspaceId();

    if(isset($entityAdmin) && $entityAdmin->getAdminId() != null)
    {
        $entityAdmin->setWorkspaceId($workspaceId)->setApplicationId(null)->update();
        
        $workspaceAdmin = new EntityAdminWorkspace(null, $databaseBuilder);

        $workspaceAdmin->setAdminId($adminId);
        $workspaceAdmin->setWorkspaceId($workspaceId);
        $workspaceAdmin->setAdminCreate($adminId);
        $workspaceAdmin->setAdminEdit($adminId);   
        $workspaceAdmin->setTimeCreate($now);
        $workspaceAdmin->setTimeEdit($now);
        $workspaceAdmin->setIpCreate($_SERVER['REMOTE_ADDR']);
        $workspaceAdmin->setIpEdit($_SERVER['REMOTE_ADDR']);
        $workspaceAdmin->setActive(true);
        $workspaceAdmin->insert();

        // Scan new workspace
        $_GET['workspaceId'] = $workspaceId;
        require_once __DIR__ . "/workspace-scan.php";
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    ResponseUtil::sendJSON(new stdClass);
}
