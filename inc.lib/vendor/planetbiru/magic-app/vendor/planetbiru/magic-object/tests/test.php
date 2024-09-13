<?php

use MagicObject\Util\PicoTestValueUtil;

require_once dirname(__DIR__) . "/vendor/autoload.php";

echo (new PicoTestValueUtil())->doReturnAttributeChecked()->whenEquals(1, 1);

