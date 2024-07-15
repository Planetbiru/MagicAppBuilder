<?php

use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputGet = new InputGet();

try
{
	$baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory();
    $moduleFile = $inputGet->getModuleFile();
    $path = dirname(__DIR__) . "/inc.cfg/applications/".$curApp->getId()."/module/".basename($inputGet->getModuleFile(), ".php") . ".json";
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
