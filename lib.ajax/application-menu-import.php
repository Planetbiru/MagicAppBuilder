<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicAppTemplate\Entity\App\AppModuleGroupMinImpl;
use MagicAppTemplate\Entity\App\AppModuleMinImpl;
use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputGet();
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);


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
        
        // Database connection for the application
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
                    $menuCreator->setTimeCreate(date('Y-m-d H:i:s'));
                    $menuCreator->setTimeEdit(date('Y-m-d H:i:s'));
                    $menuCreator->setAdminCreate('superuser');
                    $menuCreator->setAdminEdit('superuser');
                    $menuCreator->setIpCreate($_SERVER['REMOTE_ADDR']);
                    $menuCreator->setIpEdit($_SERVER['REMOTE_ADDR']);
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
                            
                            $submenuCreator = new AppModuleMinImpl(null, $database);
                            $submenuCreator->setName($submenuItem->getTitle());
                            $submenuCreator->setModuleGroupId($moduleGroupId);
                            $submenuCreator->setIcon($submenuItem->getIcon());
                            $submenuCreator->setUrl($submenuItem->getHref());
                            $submenuCreator->setSortOrder($submenuIndex + 1);
                            $submenuCreator->setTimeCreate(date('Y-m-d H:i:s'));
                            $submenuCreator->setTimeEdit(date('Y-m-d H:i:s'));
                            $submenuCreator->setAdminCreate('superuser');
                            $submenuCreator->setAdminEdit('superuser');
                            $submenuCreator->setIpCreate($_SERVER['REMOTE_ADDR']);
                            $submenuCreator->setIpEdit($_SERVER['REMOTE_ADDR']);
                            $submenuCreator->setActive(true);
                            $submenuCreator->insert();
                        }
                    }
                }
            }
        }
    }
    catch(Exception $e)
    {
        // Do noting
    }
}
