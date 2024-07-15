<?php

use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";

$inputPost = new InputPost();
// fieldName, key, value
if($inputPost->getFieldName() != null && $inputPost->getKey() != null && $inputPost->getValue() != null)
{
    $path = dirname(__DIR__) . "/inc.cfg/applications/".$curApp->getId()."/reference/".$inputPost->getFieldName() . "-" . $inputPost->getKey() . ".json";
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, $inputPost->getValue());
}
