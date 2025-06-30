<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicAppTemplate\ApplicationMenu;
use MagicAppTemplate\AppMultiLevelMenuTool;
use MagicAppTemplate\Entity\App\AppModuleGroupMinImpl;
use MagicAppTemplate\Entity\App\AppModuleMinImpl;
use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\SetterGetter;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$inputGet = new InputGet();
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

$now = date("Y-m-d H:i:s");
$superuser = 'superuser';
$adminLevelId = 'superuser';
$ip = $_SERVER['REMOTE_ADDR'];

if($applicationId != null)
{
    $menuAppConfig = new SecretObject();
    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        $application->findOneByApplicationId($applicationId);
        $menuPath = $application->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
        
        $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
        if(file_exists($appConfigPath))
        {
            $menuAppConfig->loadYamlFile($appConfigPath, false, true, true);
        }
        if(!file_exists($menuPath))
        {
            if(!file_exists(basename($menuPath)))
            {
                mkdir(dirname($menuPath), 0755, true);
            }
            file_put_contents($menuPath, "");
        }
        
        // Database connection for the application
        $database = new PicoDatabase(new SecretObject($menuAppConfig->getDatabase()));
        try
        {
            $database->connect();
        

            $menus = new SecretObject();
            $menus->loadYamlFile($menuPath, false, true, true);
            if($menus == null || $menus->getMenu() == null || !is_array($menus->getMenu()))
            {
                $menus->setMenu(array());
            }
            
            $moduleGroupFinder = new AppModuleGroupMinImpl(null, $database);
            $moduleFinder = new AppModuleMinImpl(null, $database);       
            
            foreach ($menus->getMenu() as $menuIndex => $menu) {
                
                if(isset($menu) && $menu instanceof SecretObject)
                {
                    
                    $isMenuExists = $moduleGroupFinder->existsByName($menu->getTitle());
                    if($isMenuExists)
                    {
                        // Do nothing
                    }
                    else
                    {
                        $menuCreator = new AppModuleGroupMinImpl(null, $database);
                        $menuCreator->setName($menu->getTitle());
                        $menuCreator->setIcon($menu->getIcon());
                        $menuCreator->setUrl($menu->getHref());
                        $menuCreator->setSortOrder($menuIndex + 1);
                        $menuCreator->setTimeCreate($now);
                        $menuCreator->setTimeEdit($now);
                        $menuCreator->setAdminCreate($superuser);
                        $menuCreator->setAdminEdit($superuser);
                        $menuCreator->setIpCreate($ip);
                        $menuCreator->setIpEdit($ip);
                        $menuCreator->setActive(true);
                        $menuCreator->insert();
                        
                    }
                }
            }
            foreach ($menus->getMenu() as $menuIndex => $menu) {
                
                if(isset($menu) && $menu instanceof SecretObject)
                {
                    // Check if there are submenus and add them
                    $submenus = $menu->getSubmenu();
                    if (is_array($submenus)) {
                        foreach ($submenus as $submenuIndex => $submenuItem) {
                            if(!isset($submenuItem) || !($submenuItem instanceof SecretObject)) {
                                continue; // Skip if not a valid SecretObject
                            }                        
                            $isSubmenuExists = $moduleFinder->existsByNameAndUrl($submenuItem->getTitle(), $submenuItem->getHref());
                            if($isSubmenuExists)
                            {
                                // Do nothing
                            }
                            else
                            {
                                $moduleGroupId = '';
                                try
                                {
                                    $moduleGroupFinder->findOneByName($menu->getTitle());
                                    $moduleGroupId = $moduleGroupFinder->getModuleGroupId();
                                }
                                catch(Exception $e)
                                {
                                    // Do nothing
                                }
                                
                                $moduleCode = $submenuItem->getCode();
                                if($moduleCode == null || $moduleCode == "")
                                {
                                    $moduleCode = basename($submenuItem->getHref(), ".php");
                                }
                                
                                $submenuCreator = new AppModuleMinImpl(null, $database);
                                $submenuCreator->setName($submenuItem->getTitle());
                                $submenuCreator->setMenu(true);
                                $submenuCreator->setModuleGroupId($moduleGroupId);
                                $submenuCreator->setSpecialAccess($submenuItem->getSpecialAccess());
                                $submenuCreator->setIcon($submenuItem->getIcon());
                                $submenuCreator->setModuleCode($moduleCode);
                                $submenuCreator->setUrl($submenuItem->getHref());
                                $submenuCreator->setSortOrder($submenuIndex + 1);
                                $submenuCreator->setTimeCreate($now);
                                $submenuCreator->setTimeEdit($now);
                                $submenuCreator->setAdminCreate($superuser);
                                $submenuCreator->setAdminEdit($superuser);
                                $submenuCreator->setIpCreate($ip);
                                $submenuCreator->setIpEdit($ip);
                                $submenuCreator->setActive(true);
                                $submenuCreator->insert();
                            }
                        }
                    }
                }
            }
            
            $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
            if(file_exists($appConfigPath))
            {
                $appConfig->loadYamlFile($appConfigPath, false, true, true);
                // Create parent module
                if($appConfig->issetApplication() && $appConfig->getApplication()->getMultiLevelMenu())
                {
                    $appMultiLevelMenuTool = new AppMultiLevelMenuTool($database);
                    $appMultiLevelMenuTool->createParentModule($currentAction);
                    $appMultiLevelMenuTool->updateRolesByAdminLevelId($adminLevelId, $currentAction);
                    
                    // Update the application menu cache
                    $applicationMenu = new ApplicationMenu($database, null, null, null, null, null);
                    // Delete the menu cache for the specified admin level ID
                    $applicationMenu->deleteMenuCache($adminLevelId);
                }
            }
        }
        catch(Exception $e)
        {
            error_log($e->getMessage());
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
}
if(!isset($applicationId) || empty($applicationId))
{
    $applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
}

if($applicationId != null)
{
    $menuAppConfig = new SecretObject();
    $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
    if(file_exists($appConfigPath))
    {
        $menuAppConfig->loadYamlFile($appConfigPath, false, true, true);
    }
    
    // Database connection for the application
    $database = new PicoDatabase(new SecretObject($menuAppConfig->getDatabase()));
    try
    {
        $database->connect();
    }
    catch(Exception $e)
    {
        error_log($e->getMessage());
    }

    $application = new EntityApplication(null, $databaseBuilder);
    try
    {
        $application->findOneByApplicationId($applicationId);
        
        $moduleGroupFinder = new AppModuleGroupMinImpl(null, $database);
        $moduleFinder = new AppModuleMinImpl(null, $database);

        $menuPath = $application->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
        $path = basename($application->getBaseApplicationDirectory());
        if(file_exists($menuPath))
        {
            $menu = new SecretObject();
            $menu->loadYamlFile($menuPath, false, true, true);
            
            // Render the menu
            $doc = new DOMDocument('1.0', 'UTF-8');
            $doc->formatOutput = true;

            // Buat elemen <ul class="nav flex-column">
            $ul = $doc->createElement('ul');
            $ul->setAttribute('class', 'nav flex-column');
            
            if ($menu->getMenu() != null) {
                foreach ($menu->getMenu() as $item) {
                    $isMenuExists = $moduleGroupFinder->existsByName($item->getTitle());
                    // <li class="nav-item">
                    $li = $doc->createElement('li');
                    $li->setAttribute('class', 'nav-item');
                    if ($isMenuExists) {
                        $li->setAttribute('class', 'nav-item menu-exists');
                    }

                    // <a class="nav-link" href="#"><i class="..."></i> Title</a>
                    $a = $doc->createElement('a');
                    $a->setAttribute('class', 'nav-link');
                    $a->setAttribute('href', '#');

                    // <i class="..."></i>
                    $icon = $doc->createElement('i');
                    $icon->setAttribute('class', $item->getIcon());
                    $a->appendChild($icon);

                    // Spasi dan judul
                    $a->appendChild($doc->createTextNode(' ' . htmlspecialchars($item->getTitle())));

                    $li->appendChild($a);

                    // Jika ada submenu
                    if ($item->issetSubmenu() && is_array($item->getSubmenu())) {
                        $subUl = $doc->createElement('ul');
                        $subUl->setAttribute('class', 'nav flex-column');

                        foreach ($item->getSubmenu() as $subItem) {
                            $isSubmenuExists = $moduleFinder->existsByNameAndUrl($subItem->getTitle(), $subItem->getHref());
                            $subLi = $doc->createElement('li');
                            $subLi->setAttribute('class', 'nav-item');
                            if ($isSubmenuExists) {
                                $subLi->setAttribute('class', 'nav-item menu-exists');
                            }

                            $subA = $doc->createElement('a');
                            $subA->setAttribute('class', 'nav-link');
                            $subA->setAttribute('href', '../' . $path . '/' . htmlspecialchars($subItem->getHref()));
                            $subA->setAttribute('target', '_blank');

                            $subIcon = $doc->createElement('i');
                            $subIcon->setAttribute('class', htmlspecialchars($subItem->getIcon()));
                            $subA->appendChild($subIcon);

                            $subA->appendChild($doc->createTextNode(' ' . htmlspecialchars($subItem->getTitle())));
                            $subLi->appendChild($subA);
                            $subUl->appendChild($subLi);
                        }

                        $li->appendChild($subUl);
                    }

                    $ul->appendChild($li);
                }           

                // Tambahkan <ul> ke DOM dan tampilkan
                $doc->appendChild($ul);
                echo $doc->saveHTML();
            }
            else
            {
                echo "<div class='alert alert-danger'>Menu file is empty</div>";
            }
        }
        else
        {
            echo "<div class='alert alert-danger'>Menu file not found</div>";
        }
    }
    catch(Exception $e)
    {
        // Do nothing
        echo "<div class='alert alert-danger'>".$e->getMessage()."</div>";
    }
}