<?php

use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$moduleLocation = $appConfig->getApplication() != null ? $appConfig->getApplication()->getBaseModuleDirectory() : [];
ResponseUtil::sendJSON($moduleLocation);