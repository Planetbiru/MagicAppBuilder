<?php

use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/platform-check.php";
require_once dirname(__DIR__) . "/inc.app/auth-core.php";
if(!$userLoggedIn)
{
    exit();
}
require_once __DIR__ . "/inc.db/config.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$accessedFrom = "database-explorer";

require_once __DIR__ . "/backend.php";
require_once __DIR__ . "/database-explorer.php";
