<?php

use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$languages = $appConfig->getLanguages();
if(!isset($languages) || !is_array($languages))
{
    $languages = array();
}
ResponseUtil::sendJSON($languages);