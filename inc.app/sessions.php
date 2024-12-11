<?php

use MagicObject\Session\PicoSession;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";
if($builderConfig != null && $builderConfig->getSessions() != null)
{
    $sessionConfig = $builderConfig->getSessions();
    $sessions = new PicoSession($sessionConfig);
}
else
{
    $sessions = new PicoSession();
}

$sessions->startSession();