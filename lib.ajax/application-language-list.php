<?php

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

header("Content-type: application/json");

$languages = $appConfig->getLanguages();
if(!isset($languages) || !is_array($languages))
{
    $languages = array();
}
echo json_encode($languages);