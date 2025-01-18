<?php

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
    $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
    
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

// Create a new DOMDocument instance
$dom = new DOMDocument();
$dom->formatOutput = true; // Enable formatted output

// Create the root <ul> element
$sortableMenu = $dom->createElement('ul');
$sortableMenu->setAttribute('class', 'sortable-menu');

// Add menu items to the <ul>
foreach ($menus as $menu) {
    // Create a <li> for each menu item
    $menuItem = $dom->createElement('li');
    $menuItem->setAttribute('class', 'sortable-menu-item');

    // Add icons for move up, move down, and edit
    $icons = [
        ['class' => 'icon-move-up', 'onclick' => 'moveUp(this)'],
        ['class' => 'icon-move-down', 'onclick' => 'moveDown(this)'],
        ['class' => 'icon-edit', 'onclick' => 'editMenu(this)'],
    ];

    // Append each icon to the menu item
    foreach ($icons as $icon) {
        $span = $dom->createElement('span');
        $span->setAttribute('class', 'sortable-icon ' . $icon['class']);
        $span->setAttribute('onclick', $icon['onclick']);
        $menuItem->appendChild($span);
        $menuItem->appendChild($dom->createTextNode(' '));
    }

    // Create the menu link
    $link = $dom->createElement('a', htmlspecialchars($menu->getLabel()));
    $link->setAttribute('class', 'app-menu app-menu-text');
    $link->setAttribute('href', '#');
    $menuItem->appendChild($link);
    $menuItem->appendChild($dom->createTextNode(' '));

    // Add a toggle icon
    $toggleIcon = $dom->createElement('span', '');
    $toggleIcon->setAttribute('class', 'sortable-toggle-icon');
    $menuItem->appendChild($toggleIcon);
    $menuItem->appendChild($dom->createTextNode(' '));

    // Create <ul> for submenus
    $submenu = $dom->createElement('ul');
    $submenu->setAttribute('class', 'sortable-submenu');

    // Check if there are submenus and add them
    $submenus = $menu->getSubmenus();
    if (is_array($submenus)) {
        foreach ($submenus as $submenuItem) {
            // Create <li> for each submenu item
            $submenuLi = $dom->createElement('li');
            $submenuLi->setAttribute('class', 'sortable-submenu-item');
            $submenuLi->appendChild($dom->createTextNode(' '));

            // Append icons for the submenu item
            foreach ($icons as $icon) {
                $span = $dom->createElement('span');
                $span->setAttribute('class', 'sortable-icon ' . $icon['class']);
                $span->setAttribute('onclick', $icon['onclick']);
                $submenuLi->appendChild($span);
                $submenuLi->appendChild($dom->createTextNode(' '));
            }

            // Create the submenu link
            $submenuLink = $dom->createElement('a', htmlspecialchars($submenuItem->getLabel()));
            $submenuLink->setAttribute('class', 'app-submenu app-menu-text');
            $submenuLink->setAttribute('href', htmlspecialchars($submenuItem->getLink()));
            $submenuLi->appendChild($submenuLink);
            $submenuLi->appendChild($dom->createTextNode(' '));

            // Add the submenu <li> to the <ul>
            $submenu->appendChild($submenuLi);
            $submenu->appendChild($dom->createTextNode(' '));
        }
    }

    // Add the submenu to the menu item
    $menuItem->appendChild($submenu);

    // Append the <li> menu item to the <ul>
    $sortableMenu->appendChild($menuItem);
}

// Add the menu to the DOM document
$dom->appendChild($sortableMenu);

// Output the HTML
echo $dom->saveHTML();
