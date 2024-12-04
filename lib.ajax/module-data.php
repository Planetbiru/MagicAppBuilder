<?php

use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputGet = new InputGet();

try
{
	$baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory();
    $moduleFile = $inputGet->getModuleFile();
    $target = trim($inputGet->getTarget(), "/\\");
    if(!empty($target))
    {
        $target = "/".$target;
    }
    $path = $workspaceDirectory."/applications/".$curApp->getId()."/module$target/".basename($inputGet->getModuleFile(), ".php") . ".json";
    if(isset($moduleFile) && !empty($moduleFile) && file_exists($path))
    {
        header("Content-type: application/json");
        header("Content-lenght: ".filesize($path));
        echo file_get_contents($path);
    }
    else
    {
        header("Content-type: application/json");
        header("Content-lenght: 2");
        echo "{}"; 
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
