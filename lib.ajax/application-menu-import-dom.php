<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicAppTemplate\Entity\App\AppModuleGroupMinImpl;
use MagicAppTemplate\Entity\App\AppModuleMinImpl;
use MagicObject\Database\PicoDatabase;
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
    $appConfig = new SecretObject();
    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        $application->findOneByApplicationId($applicationId);
        $menuPath = $application->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
        
        $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
        if(file_exists($appConfigPath))
        {
            $appConfig->loadYamlFile($appConfigPath, false, true, true);
        }
        if(!file_exists($menuPath))
        {
            if(!file_exists(basename($menuPath)))
            {
                mkdir(dirname($menuPath), 0755, true);
            }
            file_put_contents($menuPath, "");
        }
        
        $database = new PicoDatabase(new SecretObject($appConfig->getDatabase()));
        try
        {
            $database->connect();
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
            exit();
        }

        $menus = new SecretObject();
        $menus->loadYamlFile($menuPath, false, true, true);
        if($menus == null || $menus->getMenu() == null || !is_array($menus->getMenu()))
        {
            $menus->setMenu(array());
        }
        
        $moduleGroupFinder = new AppModuleGroupMinImpl(null, $database);
        $moduleFinder = new AppModuleMinImpl(null, $database);

        // Create a new DOMDocument instance
        $dom = new DOMDocument();
        $dom->formatOutput = true; // Enable formatted output

        // Create the root <ul> element
        $sortableMenu = $dom->createElement('ul');
        
        
        // Add menu items to the <ul>
        foreach ($menus->getMenu() as $menu) {
            
            if(isset($menu) && $menu instanceof SecretObject)
            {
                // Create a <li> for each menu item
                $menuItem = $dom->createElement('li');
                
                $isMenuExists = $moduleGroupFinder->existsByName($menu->getTitle());
                if($isMenuExists)
                {
                    $menuItem->setAttribute('class', 'app-menu app-menu-text exists');
                }
                else
                {
                    $menuItem->setAttribute('class', 'app-menu app-menu-text');
                }
                
                // Create the menu link
                $link = $dom->createElement('a', htmlspecialchars($menu->getTitle()));
                $link->setAttribute('class', 'app-menu app-menu-text');
                $link->setAttribute('href', '#');
                $link->setAttribute('data-icon', $menu->getIcon());
                $menuItem->appendChild($link);
                $menuItem->appendChild($dom->createTextNode(' '));


                // Create <ul> for submenus
                $submenu = $dom->createElement('ul');
                

                // Check if there are submenus and add them
                $submenus = $menu->getSubmenu();
                if (is_array($submenus)) {
                    foreach ($submenus as $submenuItem) {
                        if(!isset($submenuItem) || !($submenuItem instanceof SecretObject)) {
                            continue; // Skip if not a valid SecretObject
                        }
                        // Create <li> for each submenu item
                        $submenuLi = $dom->createElement('li');
                        
                        $isSubmenuExists = $moduleFinder->existsByNameAndUrl($submenuItem->getTitle(), $submenuItem->getHref());
                        if($isSubmenuExists)
                        {
                            $submenuLi->setAttribute('class', 'app-menu app-menu-text exists');
                        }
                        else
                        {
                            $submenuLi->setAttribute('class', 'app-menu app-menu-text');
                        }
                        
                        $submenuLi->appendChild($dom->createTextNode(' '));

                        // Create the submenu link
                        $submenuLink = $dom->createElement('a', htmlspecialchars($submenuItem->getTitle()));
                        
                        $submenuLink->setAttribute('href', htmlspecialchars($submenuItem->getHref()));
                        
                        
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
        }

        // Add the menu to the DOM document
        $dom->appendChild($sortableMenu);

        // Output the HTML
        echo $dom->saveHTML();
    }
    catch(Exception $e)
    {
        // Do noting
    }
}
