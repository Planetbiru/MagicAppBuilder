<?php

use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\PicoYamlUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$constShowActive = ' show active';
$constSelected = ' selected';
$inputPost = new InputPost();
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

if($applicationId != null)
{
    $appConfigPath = $workspaceDirectory."/applications/".$applicationId."/default.yml";
    
    if(file_exists($appConfigPath))
    {
        $appConfig->loadYamlFile($appConfigPath, false, true, true);
    }
}
$menuPath = $appConfig->getApplication()->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
if(!file_exists($menuPath))
{
    if(!file_exists(basename($menuPath)))
    {
        mkdir(dirname($menuPath), 0755, true);
    }
    file_put_contents($menuPath, "");
}

$data = json_decode($inputPost->getData(), true);
$yaml = PicoYamlUtil::dump($data, 0, 2, 0);
file_put_contents($menuPath, $yaml);
