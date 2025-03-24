<?php

use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();

if($inputGet->getLanguageId())
{
    $entityAdmin->setLanguageId($inputGet->getLanguageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
    $entityAdmin->update();
}

if(isset($_SERVER['HTTP_REFERER']))
{
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}
header("Location: ./");
