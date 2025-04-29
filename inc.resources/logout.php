<?php

require_once __DIR__ . "/inc.app/session.php";

unset($sessions->username);
unset($sessions->userPassword);

header("Location: ./");