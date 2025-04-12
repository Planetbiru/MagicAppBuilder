<?php

use MagicObject\Session\PicoSession;

require_once __DIR__ . "/inc.app/app.php";
$sessions = new PicoSession();
$sessions->startSession();
$sessions->destroy();
header("Location: ./");