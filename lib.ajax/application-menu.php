<?php

use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

if(!isset($applicationId) || empty($applicationId))
{
    $applicationId = $builderConfig->getCurrentApplication()->getId();
}

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

$menus = new SecretObject();

$menus->loadYamlFile($menuPath, false, true, true);
echo "<ul class=\"sortable-menu\">\r\n";

{
    foreach($menus as $menu)
    {
        echo "<li class=\"sortable-menu-item\">\r\n";
        echo '<span class="sortable-move-icon move-icon-up" onclick="moveUp(this)"></span>'."\r\n";
        echo '<span class="sortable-move-icon move-icon-down" onclick="moveDown(this)"></span>'."\r\n";
        echo "<a href=\"#\">".$menu->getLabel()."</a>\r\n";
        echo '<span class="sortable-toggle-icon"></span>'."\r\n";
        echo "<ul class=\"sortable-submenu\">\r\n";
        $submenus = $menu->getSubmenus();
        if(is_array($submenus))
        {
            foreach($submenus as $menu)
            {
                echo "<li class=\"sortable-submenu-item\">\r\n";
                echo '<span class="sortable-move-icon move-icon-up" onclick="moveUp(this)"></span>'."\r\n";
                echo '<span class="sortable-move-icon move-icon-down" onclick="moveDown(this)"></span>'."\r\n";
                echo "<a href=\"".$menu->getLink()."\">".$menu->getLabel()."</a>\r\n";
                echo "</li>\r\n";
            }
        }
        echo "</ul>\r\n";

        echo "</li>\r\n";
    }
}
echo "</ul>\r\n";