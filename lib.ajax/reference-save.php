<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
// fieldName, key, value
if($inputPost->getFieldName() != null && $inputPost->getKey() != null && $inputPost->getValue() != null)
{
    $path = $activeWorkspace->getDirectory()."/applications/".$activeApplication->getApplicationId()."/reference/".$inputPost->getFieldName() . "-" . $inputPost->getKey() . ".json";
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $inputPost->getValue());
}
ResponseUtil::sendJSON(new stdClass);