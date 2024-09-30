
<?php

use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$moduleLocation = $appConfig->getApplication() != null ? $appConfig->getApplication()->getBaseModuleDirectory() : array();
PicoResponse::sendJSON($moduleLocation);