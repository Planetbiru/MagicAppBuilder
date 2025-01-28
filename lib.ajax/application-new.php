<?php

use AppBuilder\AppArchitecture;
use AppBuilder\Entity\EntityAdminWorkspace;
use AppBuilder\Util\Composer\ComposerUtil;
use AppBuilder\Util\ResponseUtil;
use MagicAdmin\Entity\Data\GeneralCache;
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

$bundeledMagicAppVersion = "2.15.18";



if (ComposerUtil::checkInternetConnection()) {
    // Online
    try
    {
        $cache = new GeneralCache(null, $databaseBuilder);
        $needUpdate = false;
        
        try
        {
            $cache->findOneByGeneralCacheId("magic-app-version");
            $timestamp = strtotime($cache->getExpire());
            if($timestamp < time())
            {
                // Expire
                $magicAppList = ComposerUtil::getMagicAppVersionList();
                $needUpdate = true;
            }
            else
            {
                // Fresh from the oven
                $magicAppList = json_decode($cache->getContent());
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $magicAppList = ComposerUtil::getMagicAppVersionList();
                    $needUpdate = true;
                }
            }
        }
        catch(Exception $e)
        {
            $magicAppList = ComposerUtil::getMagicAppVersionList();
            $needUpdate = true;
        }
        if($needUpdate)
        {
            $cache->setGeneralCacheId("magic-app-version");
            $cache->setContent(json_encode($magicAppList));
            $cache->setExpire(date("Y-m-d H:i:s", strtotime("6 hours")));
            $cache->save();
        }
    }
    catch(Exception $e)
    {
        $magicAppList = array();
    }
    $composerOnline = true;
}
else
{
    $magicAppList = array(
        array("key"=>$bundeledMagicAppVersion, "value"=>"$bundeledMagicAppVersion (Offline)", "latest"=>false)
    );
    $composerOnline = false;
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
    // Do nothing
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
    'magic_app_versions' => $magicAppList,
    'composer_online' => $composerOnline
];

ResponseUtil::sendJSON($data, false, true);
