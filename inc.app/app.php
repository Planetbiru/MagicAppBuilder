<?php

use AppBuilder\AppSecretObject;
use AppBuilder\EntityApvInfo;
use AppBuilder\EntityInfo;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$builderConfig = new AppSecretObject(null);

$cacheDir = dirname(__DIR__) . "/.cache/";
$builderConfigPath = dirname(__DIR__) . "/inc.cfg/core.yml";

if(file_exists($builderConfigPath))
{
    $builderConfig->loadYamlFile($builderConfigPath, false, true, true);
}
