<?php

use AppBuilder\AppArchitecture;
use AppBuilder\EntityInstaller\EntityAdminWorkspace;
use AppBuilder\Util\Composer\ComposerUtil;
use AppBuilder\Util\ResponseUtil;
use MagicAdmin\Entity\Data\GeneralCache;
use MagicObject\MagicObject;
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

$appBaseUrl = "http://".$_SERVER['SERVER_NAME']."/$appId";

$composerOnline = false;

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
        $dataToSave = json_encode($magicAppList);
        if($needUpdate && strlen($dataToSave) > 8)
        {
            $cache->setGeneralCacheId("magic-app-version");
            $cache->setContent($dataToSave);
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

if(!$composerOnline)
{
    $composerJson = dirname(__DIR__) . "/inc.lib/composer.json";
    $composerObject = (new MagicObject())->loadJsonFile($composerJson, false, true, true);
    $bundeledMagicAppVersion = $composerObject->getRequire()->get("planetbiru/magic-app");
    $bundeledMagicAppVersion = str_replace("^", "", $bundeledMagicAppVersion);

    $magicAppList = array(
        array(
            "key" => $bundeledMagicAppVersion, 
            "value" => "$bundeledMagicAppVersion (Offline)", 
            "latest" => true
        )
    );
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

$installationMethod = array();
if($composerOnline)
{
    $installationMethod = array(
        array(
            'value'=>'Online', 
            'label'=>'Online',
            'selected'=>true,
            'disabled'=>false
        ),
        array(
            'value'=>'Offline', 
            'label'=>'Offline',
            'selected'=>false,
            'disabled'=>false
        )
    )
    ;
}
else
{
    $installationMethod = array(
        array(
            'value'=>'Online', 
            'label'=>'Online',
            'selected'=>false,
            'disabled'=>true
        ),
        array(
            'value'=>'Offline', 
            'label'=>'Offline',
            'selected'=>true,
            'disabled'=>false
        )
    )
    ;
}
$data = array(
    'application_name' => 'ApplicationName',
    'application_id' => $appId,
    'application_directory' => $appBaseDir,
    'application_url' => $appBaseUrl,
    'application_workspace' => $workspaceList,
    'application_namespace' => 'ApplicationName',
    'application_author' => $author,
    'application_architecture' => AppArchitecture::MONOLITH,
    'application_description' => 'Description',
    'magic_app_versions' => $magicAppList,
    'installation_method' => $installationMethod
);

ResponseUtil::sendJSON($data, false, true);
