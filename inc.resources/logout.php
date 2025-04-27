<?php

use MagicObject\Session\PicoSession;

require_once __DIR__ . "/inc.app/app.php";
$sessions = new PicoSession();

unset($sessions->username);
unset($sessions->userPassword);

header("Location: ./");