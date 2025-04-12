<?php

use MagicApp\PicoModule;
use MagicAdmin\AppIncludeImpl;

require_once __DIR__ . "/inc.app/auth.php";

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "index", $appLanguage->getHome());
$appInclude = new AppIncludeImpl($appConfig, $currentModule);

require_once $appInclude->mainAppHeader(__DIR__);

require_once $appInclude->mainAppFooter(__DIR__);
