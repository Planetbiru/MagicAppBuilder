<?php

use AppBuilder\AppArchitecture;
use AppBuilder\Util\Composer\ComposerUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$appId = PicoDatabaseUtil::uuid();

$appBaseDir = dirname(dirname(__DIR__)) . "/$appId";
$appBaseDir = str_replace("/", DIRECTORY_SEPARATOR, $appBaseDir);
$appBaseDir = str_replace("\\", DIRECTORY_SEPARATOR, $appBaseDir);

try
{
    $cachePath = $workspaceDirectory."/magic-app-version.json";
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
    $magicAppList = array();
}
$data = array(
    'application_name' => 'ApplicationName',
    'application_id' => $appId,
    'application_directory' => $appBaseDir,
    'application_namespace' => 'ApplicationName',
    'application_author' => 'Your Name',
    'application_architecture' => AppArchitecture::MONOLITH,
    'application_description' => 'Description',
    'magic_app_versions' => $magicAppList
);

ResponseUtil::sendJSON($data, false, true);
