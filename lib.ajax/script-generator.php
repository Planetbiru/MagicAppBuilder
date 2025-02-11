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


$entityInfo = $appConfig->getEntityInfo();
$entityApvInfo = $appConfig->getEntityApvInfo();

$composerOnline = ComposerUtil::checkInternetConnection();

header("Content-type: application/json");

$inputGet = new InputGet();
if (isset($_POST) && !empty($_POST)) {
    // Initialize InputPost with raw data processing enabled
    $request = new InputPost(true);

    // Build the JSON file path
    $path = $activeWorkspace->getDirectory()."/applications/" . $activeApplication->getApplicationId() . "/module/" . basename($request->getModuleFile(), ".php") . ".json";
    $target = trim($request->getTarget(), "/\\");
    if (!empty($target)) {
        $target = "/" . $target;
    }
    $path = $activeWorkspace->getDirectory()."/applications/" . $activeApplication->getApplicationId() . "/module$target/" . basename($request->getModuleFile(), ".php") . ".json";
    
    // Ensure the directory exists
    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }

    // Save the request data to the JSON file
    file_put_contents($path, $request);
    $fileGenerated = 0;

    if ($request->issetFields()) {
        require_once dirname(__DIR__) . "/inc.app/database.php";
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