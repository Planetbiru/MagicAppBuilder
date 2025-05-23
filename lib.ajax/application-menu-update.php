<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Util\PicoYamlUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$constShowActive = ' show active';
$constSelected = ' selected';
$inputPost = new InputPost();
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

if($applicationId != null)
{
    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        $application->findOneByApplicationId($applicationId);
        $menuPath = $application->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
        if(!file_exists($menuPath))
        {
            if(!file_exists(basename($menuPath)))
            {
                mkdir(dirname($menuPath), 0755, true);
            }
            file_put_contents($menuPath, "");
        }
        $data = array('menu'=>json_decode($inputPost->getData(), true));
        $yaml = PicoYamlUtil::dump($data, 0, 2, 0);
        file_put_contents($menuPath, $yaml);
        ResponseUtil::sendJSON(new stdClass);
    }
    catch(Exception $e)
    {
        ResponseUtil::sendJSON(new stdClass);
    }
}
