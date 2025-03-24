<?php

use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();

if($inputGet->getLanguageId())
{
    $currentUser->setLanguageId($inputGet->getLanguageId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS));
    $currentUser->update();
}

if(isset($_SERVER['HTTP_REFERER']))
{
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}
header("Location: ./");
