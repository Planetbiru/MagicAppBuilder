<?php

use AppBuilder\AppImporter;
use AppBuilder\EntityInstaller\EntityWorkspace;
use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

if(isset($entityAdmin) && $entityAdmin->issetAdminId())
{
    $inputGet = new InputGet();
    $workspaceId = $inputGet->getWorkspaceId();
    $workspace = new EntityWorkspace(null, $databaseBuilder);
    try
    {
        $workspace->find($workspaceId);
        $adminId = $entityAdmin->getAdminId();
        $author = $entityAdmin->getName();
        $workspaceDirectory = FileDirUtil::normalizePath($workspace->getDirectory()."/applications");
        $dirs = FileDirUtil::scanDirectory($workspaceDirectory);
        $now = date("Y-m-d H:i:s");
        if(!empty($dirs))
        {
            foreach($dirs as $dir)
            {
                $yml = FileDirUtil::normalizePath($dir."/default.yml");
                if(file_exists($yml))
                {
                    $applicationImporter = new AppImporter($databaseBuilder);
                    $applicationImporter->importApplication($yml, $dir, $workspaceId, $author, $adminId);
                }
            }
        }
    }
    catch(Exception $e)
    {
        // Do nothing
    }
}
ResponseUtil::sendJSON(new stdClass);