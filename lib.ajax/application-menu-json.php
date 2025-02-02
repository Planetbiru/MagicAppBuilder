<?php

use AppBuilder\Entity\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

if(!isset($applicationId) || empty($applicationId))
{
    $applicationId = $activeApplication->getApplicationId();
}

if($applicationId != null)
{
    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        $application->findOneByApplicationId($applicationId);

        $menuPath = $application->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
        if(!file_exists($menuPath))
        {
            if(!file_exists(dirname($menuPath)))
            {
                mkdir(dirname($menuPath), 0755, true);
            }
            file_put_contents($menuPath, "");
        }

        $menus = new SecretObject();
        $menus->loadYamlFile($menuPath, false, true, true);
        ResponseUtil::sendJSON($menus, false, true);
    }
    catch(Exception $e)
    {
        ResponseUtil::sendJSON(new stdClass, false, true);
    }
}
else
{
    ResponseUtil::sendJSON(new stdClass, false, true);
}