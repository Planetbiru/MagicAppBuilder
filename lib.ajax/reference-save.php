<?php

use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
if($inputPost->getFieldName() != null && $inputPost->getKey() != null && $inputPost->getValue() != null)
{
    $path = sprintf(
        "%s/applications/%s/reference/%s-%s.json",
        $activeWorkspace->getDirectory(),
        $activeApplication->getApplicationId(),
        $inputPost->getFieldName(),
        $inputPost->getKey()
    );
    
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $inputPost->getValue());
}
ResponseUtil::sendJSON(new stdClass);