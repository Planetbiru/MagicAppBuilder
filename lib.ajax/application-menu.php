<?php

use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$constShowActive = ' show active';
$constSelected = ' selected';
$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

if($applicationId != null)
{
    $appConfigPath = $workspaceDirectory."/applications/".$applicationId."/default.yml";
    if(file_exists($appConfigPath))
    {
        $appConfig->loadYamlFile($appConfigPath, false, true, true);
    }
}

$menus = new SecretObject($appConfig->getMenu());

echo "<ul>\r\n";
foreach($menus as $menu)
{
    echo "<li>\r\n";
    echo "<a href=\"#\">".$menu->getLabel()."</a>\r\n";
    $submenus = $menu->getSubmenu();

    echo "<ul>\r\n";
    foreach($submenus as $menu)
    {
        echo "<li>\r\n";
        echo "<a href=\"".$menu->getLink()."\">".$menu->getLabel()."</a>\r\n";
        echo "</li>\r\n";
    }
    echo "</ul>\r\n";

    echo "</li>\r\n";
}
echo "</ul>\r\n";
