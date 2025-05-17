<?php

namespace MagicAppTemplate;

use DOMDocument;
use Exception;
use MagicApp\Field;
use MagicAppTemplate\Entity\App\AppAdminLevelImpl;
use MagicAppTemplate\Entity\App\AppAdminRoleImpl;
use MagicAppTemplate\Entity\App\AppMenuCacheImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Exceptions\NoRecordFoundException;
use MagicObject\MagicObject;

/**
 * Class ApplicationMenu
 *
 * This class is responsible for generating the sidebar menu for the application.
 * It retrieves the menu structure from the database or JSON file, depending on the environment.
 * The menu is built using DOMDocument to create a structured HTML representation.
 */
class ApplicationMenu
{
    /**
     * Database connection.
     *
     * @var PicoDatabase
     */
    private $database;
    
    /**
     * Application configuration object.
     *
     * @var SecretObject
     */
    private $appConfig;
    
    /**
     * Current user object representing the logged-in user.
     *
     * @var AppAdminImpl
     */
    private $currentUser;
    
    /**
     * JSON data representing the menu structure.
     *
     * @var array
     */
    private $jsonData;
    
    /**
     * Current active page's href to highlight in the sidebar.
     *
     * @var string
     */
    private $currentHref;
    
    /**
     * Current application language object.
     *
     * @var AppLanguage
     */
    private $appLanguage;
    
    /**
     * Generates an HTML sidebar menu based on the given JSON data.
     * This method uses the DOMDocument to dynamically create the sidebar HTML structure.
     *
     * @param array $jsonData The JSON data representing the menu structure, including menu items and their submenus.
     * @param string $currentHref The current active page's href to determine the active menu and submenu items.
     * @param object $appLanguage The application language object used to fetch localized menu item titles.
     *
     * @return string The generated HTML for the sidebar.
     */
    public static function generateSidebar($jsonData, $currentHref, $appLanguage) // NOSONAR
    {
        // Create a new DOMDocument instance to build the sidebar HTML structure
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true; // To format the output for better readability

        // Create the root <ul> element for the sidebar menu
        $sidebarMenu = $dom->createElement('ul');
        $sidebarMenu->setAttribute('class', 'nav flex-column'); // Set classes for sidebar menu
        $sidebarMenu->setAttribute('id', 'sidebarMenu'); // Set the ID for the sidebar menu
        $dom->appendChild($sidebarMenu); // Append the sidebar <ul> to the DOM

        // Check if the 'menu' key exists in the provided JSON data and if it's a valid array
        if (isset($jsonData['menu']) && is_array($jsonData['menu']) && !empty($jsonData['menu'])) {
            // Loop through each main menu item in the JSON data
            foreach ($jsonData['menu'] as $item) {
                // Create the <li> element for the main menu item
                $li = $dom->createElement('li');
                $li->setAttribute('class', 'nav-item'); // Set the class for the main menu item
                $sidebarMenu->appendChild($li); // Append the <li> to the sidebar menu

                // Get the localized title of the menu item
                $item['title'] = $appLanguage->get(strtolower(str_replace(' ', '_', $item['title'])));
                if(!isset($item['href']))
                {
                    $item['href'] = '#'.strtolower(str_replace(' ', '-', $item['title']));
                }

                // Create the <a> tag for the main menu item
                $a = $dom->createElement('a', ''); // Create an empty anchor tag
                $a->setAttribute('class', 'nav-link collapsed'); // Set the class for the anchor tag
                $a->setAttribute('href', $item['href']); // Set the href for the menu item

                // Add target="_blank" if the target attribute is provided in the JSON data
                if (isset($item['target']) && $item['target']) {
                    $a->setAttribute('target', $item['target']);
                }

                // Add collapse toggle for submenu if there are submenus
                if (count($item['submenu']) > 0) {
                    $a->setAttribute('data-toggle', 'collapse');
                    $a->setAttribute('aria-expanded', 'false');
                }

                // Create and append the icon inside the <a> tag
                if(isset($item['icon']) && $item['icon'] != '') {
                    $icon = $dom->createElement('i', '');
                    $icon->setAttribute('class', $item['icon']);
                    $a->appendChild($icon);  // Append the icon element to the anchor tag
                }


                // Add a space after the icon
                $space = $dom->createTextNode(' '); // Space between icon and title
                $a->appendChild($space);

                // Append the title text of the menu item after the space
                $a->appendChild($dom->createTextNode($item['title']));

                // Append the <a> tag to the <li> element
                $li->appendChild($a);

                // Check if the menu item has submenus
                if (count($item['submenu']) > 0) {
                    $isActive = false; // Flag to track if any submenu item is active
                    
                    // Loop through each submenu item and check if it's active
                    foreach ($item['submenu'] as $subItem) {
                        if (stripos($currentHref, $subItem['href']) !== false) {
                            $isActive = true; // Set as active if currentHref matches the submenu item's href
                            break;
                        }
                    }

                    // Create the <div> for the submenu and add 'collapse' or 'collapse show' class
                    if($isActive)
                    {
                        $li->setAttribute('class', 'nav-item nav-item-children-active'); // Set active class for the main menu item
                        $collapseClass = 'collapse show'; // Set submenu to show if any submenu is active
                        $a->setAttribute('class', 'nav-link');
                    }
                    else
                    {
                        $collapseClass = 'collapse'; // Set regular class for non-active main menu item
                    }

                    $submenuDiv = $dom->createElement('div');
                    $submenuDiv->setAttribute('id', substr($item['href'], 1)); // Use item href as ID for submenu
                    $submenuDiv->setAttribute('class', $collapseClass); // Set the collapse class for submenu
                    $li->appendChild($submenuDiv); // Append the submenu div to the main <li>

                    // Create the submenu <ul> list
                    $submenuList = $dom->createElement('ul');
                    $submenuList->setAttribute('class', 'nav flex-column pl-3'); // Set classes for the submenu list
                    $submenuDiv->appendChild($submenuList); // Append the submenu list to the submenu div

                    // Loop through each submenu item and create the submenu HTML
                    foreach ($item['submenu'] as $subItem) {
                        // Get the localized title for each submenu item
                        $subItem['title'] = $appLanguage->get(strtolower(str_replace(' ', '_', $subItem['title'])));

                        // Create the <li> element for each submenu item
                        $subLi = $dom->createElement('li');
                        
                        // Mark the submenu item as active if it matches the currentHref
                        if (stripos($currentHref, $subItem['href']) !== false) {
                            $subLi->setAttribute('class', 'nav-item active'); // Set active class for the active submenu item
                        } else {
                            $subLi->setAttribute('class', 'nav-item'); // Set regular class for non-active items
                        }
                        
                        $submenuList->appendChild($subLi); // Append the submenu <li> to the submenu list

                        // Create the <a> tag for each submenu item
                        $subA = $dom->createElement('a', ''); // Create an empty anchor tag
                        $subA->setAttribute('class', 'nav-link'); // Set the class for the submenu anchor tag
                        $subA->setAttribute('href', $subItem['href']); // Set the href for the submenu item

                        // Add target="_blank" if the target attribute is provided for submenu links
                        if (isset($subItem['target']) && $subItem['target']) {
                            $subA->setAttribute('target', $subItem['target']);
                        }

                        // Create and append the icon for submenu items
                        if(isset($subItem['icon']) && $subItem['icon'] != '') {
                            // Create the icon element for submenu items
                            $subIcon = $dom->createElement('i', '');
                            $subIcon->setAttribute('class', $subItem['icon']);
                            $subA->appendChild($subIcon);  // Append the icon to the submenu anchor tag
                        }

                        // Add a space after the icon for submenu items
                        $subSpace = $dom->createTextNode(' '); // Space between icon and title
                        $subA->appendChild($subSpace);

                        // Append the title text for the submenu item
                        $subA->appendChild($dom->createTextNode($subItem['title']));

                        // Append the submenu link to the <li> element
                        $subLi->appendChild($subA);
                    }
                }
            }
        }

        // Return the generated sidebar HTML as a string
        return $dom->saveHTML();
    }
    
    /**
     * Constructor for the AppMenu class.
     *
     * @param PicoDatabase $database Database connection object.
     * @param SecretObject $appConfig Application configuration object.
     * @param AppAdminImpl $currentUser Current logged-in user object.
     * @param array $jsonData Menu data in JSON format.
     * @param string $currentHref The current active page's href.
     * @param AppLanguage $appLanguage Application language object.
     */
    public function __construct($database, $appConfig, $currentUser, $jsonData, $currentHref, $appLanguage) // NOSONAR
    {
        $this->database = $database;
        $this->appConfig = $appConfig;
        $this->currentUser = $currentUser;
        $this->jsonData = $jsonData;
        $this->currentHref = $currentHref;
        $this->appLanguage = $appLanguage;
    }
    
    /**
     * Fetches the menu structure from the database.
     *
     * @return array The menu list fetched from the database.
     */
	public function getMenuFromDatabase()
	{
        $menuData = array();
        try
        {
            $cache = new AppMenuCacheImpl(null, $this->database);
            // Find the menu cache by admin level ID
            $cache->findOneByAdminLevelId($this->currentUser->getAdminLevelId());
            
            $menuData = json_decode($cache->getData(), true);
            if(empty($menuData))
            {
                throw new NoRecordFoundException('Menu data not found in cache.');
            }
        }
        catch(Exception $e)
        {
            $menuData = $this->updateMenuCache($this->currentUser->getAdminLevelId());
        }
		return $menuData;
	}
    
    /**
     * Updates the menu cache for a specific admin level ID.
     *
     * @param string|null $adminLevelId The admin level ID to update the cache for. If null, updates all admin levels.
     * @return array The updated menu data.
     */
    public function updateMenuCache($adminLevelId = null)
    {
        if(isset($adminLevelId) && !empty($adminLevelId))
        {
            return $this->updateMenuCacheByAdminLevelId($adminLevelId);
        }
        else
        {
            // Update all menu caches for all admin levels
            $adminLevelFinder = new AppAdminRoleImpl(null, $this->database);
            $pageData = $adminLevelFinder->findAll();
            $menuData = array();
            foreach($pageData->getResult() as $adminLevel)
            {
                $menuData = $this->updateMenuCacheByAdminLevelId($adminLevel->getAdminLevelId());
            }
            // Applocation will not use this menuData when update all menu cache
            // but this is for future use if needed
            return $menuData;
        }
    }
    
    /**
     * Updates the menu cache for a specific admin level ID by admin level ID.
     *
     * @param string $adminLevelId The admin level ID to update the cache for.
     * @return array The updated menu data.
     */
    public function updateMenuCacheByAdminLevelId($adminLevelId)
    {
        $menuData = $this->getMenuByAdminLevelId($adminLevelId);
        $dataToStore = json_encode($menuData);
        $now = date('Y-m-d H:i:s');
        $cache = new AppMenuCacheImpl(null, $this->database);
        try
        {
            $cache->findOneByAdminLevelId($adminLevelId);
            $cache->setData($dataToStore);
            $cache->setTimeEdit($now);
            $cache->update(); // Update the menu data in the cache
        }
        catch(Exception $e)
        {
            $cache = new AppMenuCacheImpl(null, $this->database);
            $cache->setAdminLevelId($adminLevelId);
            $cache->setData($dataToStore);
            $cache->setTimeCreate($now);
            $cache->setTimeEdit($now);
            $cache->insert(); // Store the menu data in the cache
        } 
        return $menuData;
    }
    
    /**
     * Deletes the menu cache for a specific admin level ID.
     *
     * @param string $adminLevelId The admin level ID to delete the cache for.
     * @return self The current instance of the class.
     */
    public function deleteMenuCache($adminLevelId)
    {
        $cache = new AppMenuCacheImpl(null, $this->database);
        try
        {
            $cache->where(PicoSpecification::getInstance()->addAnd([Field::of()->adminLevelId, $adminLevelId]))->delete();   
        }
        catch(Exception $e)
        {
            // Handle exception if needed
        } 
        return $this;
    }
    
    /**
     * Retrieves the menu structure for a specific admin level ID.
     *
     * @param string $adminLevelId The admin level ID to filter the menu.
     * @return array The menu list for the specified admin level ID.
     */
    public function getMenuByAdminLevelId($adminLevelId)
    {
        $moduleGroups = $this->getModuleGrouped($adminLevelId);

        $menuList = array();
        $menuList['menu'] = array();
        foreach($moduleGroups as $moduleGroup)
        {
            $menu = array(
                'title' => $moduleGroup->getName(),
                'icon' => $moduleGroup->getIcon(),
                'href' => $moduleGroup->getUrl(),
                'target' => $moduleGroup->getTarget(),
                'submenu' => array()
            );
            $submenus = array();
            if($moduleGroup->getModules() != null)
            {
                foreach($moduleGroup->getModules() as $module)
                {
                    $submenus[] = array(
                        'title' => $module->getName(),
                        'icon' => $module->getIcon(),
                        'href' => $module->getUrl(),
                        'target' => $module->getTarget()
                    );
                }
            }
            $menu['submenu'] = $submenus;
            $menuList['menu'][] = $menu;
        }
        return $menuList;
    }
    
    /**
     * Retrieves the modules grouped by module group.
     *
     * @param string $adminLevelId The admin level ID to filter the modules.
     * @return MagicObject[] Array of grouped modules.
     */
    public function getModuleGrouped($adminLevelId) // NOSONAR
    {
        $specialAcess = false;
        try
        {
            $adminLevel = new AppAdminLevelImpl(null, $this->database);
            $adminLevel->findOneByAdminLevelId($adminLevelId);
            $specialAcess = $adminLevel->getSpecialAccess();
        }
        catch(Exception $e)
        {
            // Handle exception if needed
        }
        
        $adminRoles = $this->loadAminRole($adminLevelId);
        $modules = $this->loadModule();
        $modulesWithGroup = array();
        
        // Step 1 - for module with valid group module
        foreach($modules as $module)
        {
            $moduleGroup = $module->getModuleGroup();
            if($moduleGroup == null || $moduleGroup->getModuleGroupId() == null)
            {
                $moduleGroup = new MagicObject();
            }
            $moduleGroupId = $module->getModuleGroupId();
            if(isset($moduleGroup) && $moduleGroup->getModuleGroupId() != null)
            {
                if(!isset($modulesWithGroup[$moduleGroupId]))
                {
                    $modulesWithGroup[$moduleGroupId] = new MagicObject();
                    $modulesWithGroup[$moduleGroupId]->setModuleGroupId($moduleGroupId);
                    $modulesWithGroup[$moduleGroupId]->setName($moduleGroup->getName());
                    $modulesWithGroup[$moduleGroupId]->setHref('#');
                    $modulesWithGroup[$moduleGroupId]->setIcon($moduleGroup->getIcon());
                    $modulesWithGroup[$moduleGroupId]->setModuleGroup($moduleGroup);
                }
                if((isset($this->appConfig) && $this->appConfig->getBypassRole()) 
                || ($specialAcess && $module->isSpecialAccess()) 
                || $this->isAllowedAccess($module, $adminRoles))
                {
                    $modulesWithGroup[$moduleGroupId]->appendModules($module);
                }   
            }
        }
        // Step 2 - for module without valid group module
        foreach($modules as $module)
        {
            $moduleGroup = $module->getModuleGroup();
            if($moduleGroup == null || $moduleGroup->getModuleGroupId() == null)
            {
                $moduleGroup = new MagicObject();
            }
            $moduleGroupId = $module->getModuleGroupId();
            if(!isset($moduleGroup) || $moduleGroup->getModuleGroupId() == null)
            {
                if(!isset($modulesWithGroup[$moduleGroupId]))
                {
                    $modulesWithGroup[$moduleGroupId] = new MagicObject();
                    $modulesWithGroup[$moduleGroupId]->setModuleGroupId($moduleGroupId);
                    $modulesWithGroup[$moduleGroupId]->setName($moduleGroup->getName());
                    $modulesWithGroup[$moduleGroupId]->setHref('#');
                    $modulesWithGroup[$moduleGroupId]->setIcon($moduleGroup->getIcon());
                    $modulesWithGroup[$moduleGroupId]->setModuleGroup($moduleGroup);
                }
                if((isset($this->appConfig) && $this->appConfig->getBypassRole()) 
                || ($specialAcess && $module->isSpecialAccess()) 
                || $this->isAllowedAccess($module, $adminRoles))
                {
                    $modulesWithGroup[$moduleGroupId]->appendModules($module);
                }   
            }
        }
                
        // Clean up empty group
        foreach($modulesWithGroup as $index=>$group)
        {
            if(!$group->issetModules())
            {
                unset($modulesWithGroup[$index]);
            }
        }
        return $modulesWithGroup;
    }
    
    /**
     * Checks whether the current user has permission to access the given module.
     *
     * @param AppModuleImpl $module Module to check access for.
     * @param AppAdminRoleImpl[] $adminRoles List of admin roles assigned to the current user.
     * @return bool Returns true if access is allowed, false otherwise.
     */
    public function isAllowedAccess($module, $adminRoles)
    {
        if(isset($adminRoles) && is_array($adminRoles) && !empty($adminRoles))
        {
            foreach($adminRoles as $adminRole)
            {
                if($adminRole->getModuleId() == $module->getModuleId() && 
                    (
                           $adminRole->isAllowedList()
                        || $adminRole->isAllowedDetail()
                        || $adminRole->isAllowedCreate()
                        || $adminRole->isAllowedUpdate()
                        || $adminRole->isAllowedDelete()
                        || $adminRole->isAllowedApprove()
                        || $adminRole->isAllowedSortOrder()
                        || $adminRole->isAllowedExport()
                    )
                )
                {
                    return true;
                }
            }
        }
        return false;
    }
	
    /**
     * Loads the modules from the database.
     *
     * @return MagicObject[] Array of modules.
     */
	public function loadModule()
	{
        $modules = [];
		$module = new AppModuleImpl(null, $this->database);
        $specs = PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->menu, true))
            ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->active, true))
        ;
        $sorts = PicoSortable::getInstance()
            ->addSortable(new PicoSort('moduleGroup.sortOrder', PicoSort::ORDER_TYPE_ASC))
            ->addSortable(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
        ;
        try
        {
            $pageData = $module->findAll($specs, null, $sorts);
            $modules = $pageData->getResult();
        }
        catch(Exception $e)
        {
            $modules = [];
        }
        return $modules;
	}
    
    /**
     * Loads the admin roles from the database.
     *
     * @param string $adminLevelId The admin level ID to filter the roles.
     * @return AppAdminRoleImpl[] Array of admin roles.
     */
    public function loadAminRole($adminLevelId)
    {
        $adminRoles = [];
		$adminRole = new AppAdminRoleImpl(null, $this->database);
        $specs = PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminLevelId, $adminLevelId))
            ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->active, true))
        ;

        try
        {
            $pageData = $adminRole->findAll($specs);
            $adminRoles = $pageData->getResult();
        }
        catch(Exception $e)
        {
            $adminRoles = [];
        }
        return $adminRoles;
    }

    /**
     * Get menu data
     *
     * @return array Menu data
     */
    public function getMenuData()
    {
        if($this->appConfig->getDevelopmentMode())
        {
            return $this->jsonData;
        }
        else
        {
            return $this->getMenuFromDatabase();
        }
    }
    
    /**
     * Renders the complete menu (either from Yaml file or database depending on the environment).
     *
     * @return string The rendered HTML menu.
     */
    public function renderMenu()
    {        
        return self::generateSidebar($this->getMenuData(), $this->currentHref, $this->appLanguage);
    }
    
    /**
     * Converts the AppMenu object to a string representation (renders the menu).
     *
     * @return string The rendered HTML menu.
     */
    public function __toString()
    {
        return $this->renderMenu();
    }
}