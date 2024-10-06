<?php

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
    $magicAppList = ComposerUtil::getMagicAppVersionList();
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
    'application_type' => 'fullstack',
    'application_description' => 'Description',
    'magic_app_versions' => $magicAppList
);

ResponseUtil::sendJSON($data);