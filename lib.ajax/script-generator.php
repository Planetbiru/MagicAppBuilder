<?php

use AppBuilder\Generator\ScriptGenerator;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;

$time_start = microtime(true);

require_once dirname(__DIR__) . "/inc.app/app.php";
header("Content-type: application/json");

$inputGet = new InputGet();
if (isset($_POST) && !empty($_POST)) {
    $request = new InputPost(true);

    $path = $workspaceDirectory."/applications/" . $curApp->getId() . "/module/" . basename($request->getModuleFile(), ".php") . ".json";
    $target = trim($request->getTarget(), "/\\");
    if (!empty($target)) {
        $target = "/" . $target;
    }
    $path = $workspaceDirectory."/applications/" . $curApp->getId() . "/module$target/" . basename($request->getModuleFile(), ".php") . ".json";
    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $request);

    if ($request->issetFields()) {
        require_once dirname(__DIR__) . "/inc.app/database.php";
        $scriptGenerator = new ScriptGenerator();
        $scriptGenerator->generate($database, $request, $builderConfig, $appConfig, $entityInfo, $entityApvInfo);
        
    }
}

$time_end = microtime(true);
$time = $time_end - $time_start;

$response = array(
    "success" => true,
    "processing_time" => $time,
    "title" => "Success",
    "message" => sprintf("All scripts are generated in %.3f seconds", $time)
);

ResponseUtil::sendJSON($response);