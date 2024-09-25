<?php

use AppBuilder\Util\Composer\ComposerUtil;
use MagicObject\Util\Database\PicoDatabaseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$appId = PicoDatabaseUtil::uuid();

$appBaseDir = dirname(dirname(__DIR__)) . "/$appId";
$appBaseDir = str_replace("/", DIRECTORY_SEPARATOR, $appBaseDir);
$appBaseDir = str_replace("\\", DIRECTORY_SEPARATOR, $appBaseDir);

header("Content-type: application/json");

echo json_encode(array(
    'application_name' => 'ApplicationName',
    'application_id' => $appId,
    'application_directory' => $appBaseDir,
    'application_namespace' => 'ApplicationName',
    'application_author' => 'Your Name',
    'application_type' => 'fullstack',
    'application_description' => 'Description',
    'magic_app_versions' => ComposerUtil::getMagicAppVersionList()
));
