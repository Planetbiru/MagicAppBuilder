<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";
$response = new stdClass;
$response->success = false;
$inputPost = new InputPost();

try
{
	$baseModuleDirectory = $activeApplication->getBaseApplicationDirectory();
    $module = $inputPost->getModule().".php";
    $path = trim($baseModuleDirectory."/".$module);
    if(file_exists($path))
    {
        unlink($path);
        error_log($path);
        $response->success = true;
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
ResponseUtil::sendJSON($response);