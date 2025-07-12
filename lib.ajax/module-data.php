<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();

try
{
	$baseDirectory = $activeApplication->getBaseApplicationDirectory();
    $moduleFile = $inputGet->getModuleFile();
    $target = trim($inputGet->getTarget(), "/\\");
    if(!empty($target))
    {
        $target = "/".$target;
    }
    $path = sprintf(
        "%s/applications/%s/module%s/%s.json",
        $activeWorkspace->getDirectory(),
        $activeApplication->getApplicationId(),
        $target,
        basename($inputGet->getModuleFile(), ".php")
    );
    
    if(isset($moduleFile) && !empty($moduleFile) && file_exists($path))
    {
        ResponseUtil::sendJSON(file_get_contents($path));
    }
    else
    {
        ResponseUtil::sendJSON(new stdClass());
    }
    exit();
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
    ResponseUtil::sendJSON(new stdClass);
    exit();
}
