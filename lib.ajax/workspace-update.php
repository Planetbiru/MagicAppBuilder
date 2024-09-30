<?php

use AppBuilder\AppSecretObject;
use MagicObject\MagicObject;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";

$inputPost = new InputPost();

$workspace = trim($inputPost->getWorkspace());
$builderConfig = new AppSecretObject(null);

$builderConfigPath = dirname(__DIR__) ."/inc.cfg/core.yml";

if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}
$builderConfig->setWorkspaceDirectory($workspace);

if(PicoStringUtil::startsWith($workspace, "./"))
{
    $workspaceDirectory = dirname(__DIR__) . "/" . substr($workspace, 2);
}
else
{
    $workspaceDirectory = $workspace;
}

if(!file_exists($workspaceDirectory))
{
    mkdir($workspaceDirectory, 0755, true);
}
file_put_contents($builderConfigPath, (new MagicObject($builderConfig))->dumpYaml());
ResponseUtil::sendJSON(new stdClass);