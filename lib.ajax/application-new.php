<?php

use AppBuilder\AppArchitecture;
use AppBuilder\Entity\EntityAdminWorkspace;
use AppBuilder\Util\Composer\ComposerUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$appId = PicoDatabaseUtil::uuid();
$workspaceId = null;
$workspaceName = null;
$author = null;

if(isset($entityAdmin))
{
    $author = $entityAdmin->getname();
    $workspaceId = $entityAdmin->getWorkspaceId();
    $workspaceName = $entityAdmin->issetWorkspace() ? $entityAdmin->getWorkspace()->getName() : "";
}

$appBaseDir = dirname(dirname(__DIR__)) . "/$appId";
$appBaseDir = str_replace("/", DIRECTORY_SEPARATOR, $appBaseDir);
$appBaseDir = str_replace("\\", DIRECTORY_SEPARATOR, $appBaseDir);

try
{
    $cachePath = $activeWorkspace->getDirectory()."/magic-app-version.json";
    if(!file_exists($cachePath) || filemtime($cachePath) < strtotime('-6 hours'))
    {
        $magicAppList = ComposerUtil::getMagicAppVersionList();
        file_put_contents($cachePath, json_encode($magicAppList));
    }
    else
    {
        $magicAppList = json_decode(file_get_contents($cachePath));
        if (json_last_error() !== JSON_ERROR_NONE) {
            $magicAppList = ComposerUtil::getMagicAppVersionList();
            file_put_contents($cachePath, json_encode($magicAppList));
        }
    }
}
catch(Exception $e)
{
    $magicAppList = [];
}

$magicAppList = array_slice($magicAppList, 0, 20);


$workspaceFinder = new EntityAdminWorkspace(null, $databaseBuilder);
$workspaceList = array();
try
{
    $adminId = isset($entityAdmin) && $entityAdmin->issetAdminId() ? $entityAdmin->getAdminId() : null;
    $currentWorkspaceId = isset($entityAdmin) && $entityAdmin->issetWorkspaceId() ? $entityAdmin->getWorkspaceId() : null;
    $pageData = $workspaceFinder->findDescByAdminIdAndActive($adminId, true);
    if($pageData->getTotalResult() > 0)
    {
        foreach($pageData->getResult() as $row)
        {
            if($row->issetWorkspace())
            {
                $workspace = $row->getWorkspace();
                $workspaceList[] = array(
                    "label"=>$workspace->getName(),
                    "value"=>$workspace->getWorkspaceId(),
                    "selected"=>$currentWorkspaceId == $workspace->getWorkspaceId()
                );
            }
        }
    }
}
catch(Exception $e)
{
    
}

$data = [
    'application_name' => 'ApplicationName',
    'application_id' => $appId,
    'application_directory' => $appBaseDir,
    'application_workspace' => $workspaceList,
    'application_namespace' => 'ApplicationName',
    'application_author' => $author,
    'application_architecture' => AppArchitecture::MONOLITH,
    'application_description' => 'Description',
    'magic_app_versions' => $magicAppList
];

ResponseUtil::sendJSON($data, false, true);
