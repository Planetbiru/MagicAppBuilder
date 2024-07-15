<?php

use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputPost = new InputPost();

try
{
	$baseDirectory = $appConfig->getApplication()->getBaseApplicationDirectory();
    $module = $inputPost->getModule();
    if(isset($module) && !empty($module))
    {
        $content = $inputPost->getContent();
        $moduleName = trim($module);
        $path = $baseDirectory."/".$moduleName.".php";       
        file_put_contents($path, $content);
    }

}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
