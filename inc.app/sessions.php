<?php

use MagicObject\Session\PicoSession;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$sessionConfig = $builderConfig->getSessions();

$sessions = new PicoSession($sessionConfig);
