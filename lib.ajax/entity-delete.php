<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
header("Content-type: text/plain");

$response = new stdClass;
$response->success = false;

try
{
	$baseDirectory = $appConfig->getApplication()->getBaseEntityDirectory();
    $baseEntity = $appConfig->getApplication()->getBaseEntityNamespace();
    $baseEntity = str_replace("\\\\", "\\", $baseEntity);
    $baseDir = rtrim($baseDirectory, "\\/")."/".str_replace("\\", "/", trim($baseEntity, "\\/"));
    $lines = array();
    $inputEntity = $inputPost->getEntity();
    $entityName = trim($inputEntity);
    $path = $baseDir."/".$entityName.".php";

    if(file_exists($path))
    {                
        unlink($path);
        $response->success = true;
    }


}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}

ResponseUtil::sendJSON($response);