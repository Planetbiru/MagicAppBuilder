<?php

use AppBuilder\ScriptGeneratorMicroservices;
use AppBuilder\ScriptGeneratorMonolith;
use AppBuilder\Util\ChartDataUtil;
use AppBuilder\Util\Composer\ComposerUtil;
use AppBuilder\Util\ModuleUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicAdmin\Entity\Data\Module;
use MagicAdmin\Entity\Data\ModuleCreated;

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

$fileGenerated = 0;
$inputGet = new InputGet();
if ((isset($_POST) && !empty($_POST)) || (isset($_SERVER["CONTENT_TYPE"]) && strtolower($_SERVER["CONTENT_TYPE"]) == 'application/json')) {
    // Initialize InputPost with raw data processing enabled
    if(isset($_POST['data']))
    {
        $data = json_decode($_POST['data'], true);
        unset($_POST['data']);
    }
    else
    {
        $data = json_decode(file_get_contents("php://input"), true);
    }
    foreach($data as $k=>$v)
    {
        $_POST[$k] = $v;
    }

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

    
    
    $fileGenerated = 0;

    if ($appConfig->getApplication() != null && $request->issetFields()) {
        $applicationConf = $appConfig->getApplication();
        
        if($applicationConf->getArchitecture() == 'microservices')
        {
            $scriptGenerator = new ScriptGeneratorMicroservices();
            $fileGenerated = $scriptGenerator->generate($database, $request, $builderConfig, $appConfig, $entityInfo, $entityApvInfo, $composerOnline);
            $scriptGenerator->updateMenu($appConfig->getApplication(), $request, true);
        } 
        else
        {
            $scriptGenerator = new ScriptGeneratorMonolith();
            $fileGenerated = $scriptGenerator->generate($database, $request, $builderConfig, $appConfig, $entityInfo, $entityApvInfo, $composerOnline);    
            $scriptGenerator->updateMenu($appConfig->getApplication(), $request, true);
        }
    }

    ModuleUtil::saveModule($activeApplication->getApplicationId(), new Module(null, $databaseBuilder), $request, $currentAction->getTime(), $entityAdmin->getAdminId(), $currentAction->getIp());
    ChartDataUtil::updateChartData(new Module(null, $databaseBuilder), new ModuleCreated(null, $databaseBuilder), date('Ym'));

    foreach($request->getFields() as $field)
    {
        $dataFormat = $field->getDataFormat();
        $referenceData = $field->getReferenceData();
        $referenceFilter = $field->getReferenceFilter();
        
        // Ensure dataFormat is an object instead of an empty array
        if(empty($dataFormat))
        {
            $field->setDataFormat(new stdClass);
        }
        // Ensure referenceData is an object instead of an empty array
        if(empty($referenceData))
        {
            $field->setReferenceData(new stdClass);
        }
        // Ensure referenceFilter is an object instead of an empty array
        if(empty($referenceFilter))
        {
            $field->setReferenceFilter(new stdClass);
        }
    }
    // Save the request data to the JSON file
    $options = $builderConfig->getData()->getPrettifyModuleData() ? JSON_PRETTY_PRINT : 0;
    file_put_contents($path, json_encode(json_decode((string) $request), $options));
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
exit();