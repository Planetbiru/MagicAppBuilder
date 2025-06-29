<?php

require_once __DIR__ . "/inc.app/auth.php";

if($appConfig->issetApplication() && $appConfig->getApplication()->isMultiLevelMenu())
{
    require_once __DIR__ . "/module-multi-level.php";
}
else
{
    require_once __DIR__ . "/module-two-level.php";
}