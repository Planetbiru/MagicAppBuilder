<?php

use AppBuilder\ScriptGenerator;
use AppBuilder\Util\Composer\ComposerUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;

// Start measuring execution time
$timeStart = microtime(true);

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

$entityInfo = $appConfig->getEntityInfo();
$entityApvInfo = $appConfig->getEntityApvInfo();

$composerOnline = ComposerUtil::checkInternetConnection();

header("Content-type: application/json");

$inputGet = new InputGet();
if (isset($_POST) && !empty($_POST)) {
    // Initialize InputPost with raw data processing enabled
    $request = new InputPost(true, false);
    
    // Build target path if it's not empty
    $target = trim($request->getTarget(), "/\\");
    if (!empty($target)) {
        $target = "/" . $target;
    }

    // Update path using sprintf for target inclusion
    $path = sprintf(
        "%s/applications/%s/module%s/%s.json",
        $activeWorkspace->getDirectory(),
        $activeApplication->getApplicationId(),
        $target,
        basename($request->getModuleFile(), ".php")
    );
    
    // Ensure the directory exists
    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }

    // Save the request data to the JSON file
    file_put_contents($path, $request);
    $fileGenerated = 0;

    if ($request->issetFields()) {
        $scriptGenerator = new ScriptGenerator();
        $fileGenerated = $scriptGenerator->generate($database, $request, $builderConfig, $appConfig, $entityInfo, $entityApvInfo, $composerOnline);
    }
}

$timeEnd = microtime(true);
$time = $timeEnd - $timeStart;

$response = array(
    "success" => true,
    "processing_time" => $time,
    "title" => "Success",
    "message" => sprintf("Generated %d script(s) in %.3f seconds", $fileGenerated, $time)
);

ResponseUtil::sendJSON($response);