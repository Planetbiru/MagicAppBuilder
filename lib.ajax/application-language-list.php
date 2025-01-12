<?php

use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$languages = $appConfig->getLanguages();
if(!isset($languages) || !is_array($languages))
{
    $languages = array();
}
ResponseUtil::sendJSON($languages);