<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

try
{
	$baseDirectory = $activeApplication->getBaseApplicationDirectory();
    $module = $inputPost->getModule();
    if(isset($module) && !empty($module))
    {
        $content = $inputPost->getContent();
        $moduleName = trim($module);
        $path = $baseDirectory . "/" . $moduleName.".php";       
        file_put_contents($path, $content);
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
ResponseUtil::sendJSON(new stdClass);