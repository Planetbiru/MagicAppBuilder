<?php

use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

try
{
	$inputPost = new InputPost();
	
    if(isset($entityAdmin) && $entityAdmin->issetAdminId())
    {
        $appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
        $entityAdmin->setApplicationId($appId)->update();
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}