<?php

require_once __DIR__ . "/inc.app/app.php";
require_once __DIR__ . "/inc.app/database-builder.php";
require_once __DIR__ . "/inc.app/sessions.php";

$sessions->destroy();
header("Location: ./");
    
