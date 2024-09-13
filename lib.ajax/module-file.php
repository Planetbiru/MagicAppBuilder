<?php

use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

$inputPost = new InputPost();

try
{
	$baseModuleDirectory = $appConfig->getApplication()->getBaseApplicationDirectory();
    $module = $inputPost->getModule().".php";
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
