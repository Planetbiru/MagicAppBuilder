<?php

use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputGet = new InputGet();

try
{
	$baseModuleDirectory = $appConfig->getApplication()->getBaseApplicationDirectory();
    $module = $inputGet->getModule().".php";
    $path = trim($baseModuleDirectory."/".$module);
    if(file_exists($path))
    {
        echo file_get_contents($path);
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}