<?php

namespace MagicAppTemplate;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Exception;
use MagicApp\Field;
use MagicAppTemplate\Entity\App\AppAdminLevelMinImpl;
use MagicAppTemplate\Entity\App\AppAdminRoleMinImpl;
use MagicAppTemplate\Entity\App\AppMenuCacheImpl;
use MagicAppTemplate\Entity\App\AppModuleImpl;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicAppTemplate\Entity\App\AppMenuGroupTranslationImpl;
use MagicAppTemplate\Entity\App\AppMenuTranslationImpl;

/**
 * Class ApplicationMenu
 *
 * This class is responsible for generating the sidebar menu for the application.
 * It retrieves the menu structure from the database or JSON file, depending on the environment.
 * The menu is built using DOMDocument to create a structured HTML representation.
 */
class ApplicationMenu // NOSONAR
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
     * Active URL
     *
     * @var string
     */
    private $activeUrl;
    
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
            if($this->currentUser->getLanguageId() != '')
            {
                $cache->findOneByAdminLevelIdAndLanguageId($this->currentUser->getAdminLevelId(), $this->currentUser->getLanguageId());
            }
            else
            {
                $cache->findOneByAdminLevelId($this->currentUser->getAdminLevelId());
            }
            
            $menuData = json_decode($cache->getData(), true);
            if(empty($menuData))
            {
                // If cache is empty, update the menu cache
                if($this->currentUser->getLanguageId() != '')
                {
                    $menuData = $this->updateMenuCache($this->currentUser->getAdminLevelId(), $this->currentUser->getLanguageId());
                }
                else
                {
                    $menuData = $this->updateMenuCache($this->currentUser->getAdminLevelId());
                }
            }
        }
        catch(Exception $e)
        {
            // If cache not found, update the menu cache
            if($this->currentUser->getLanguageId() != '')
            {
                $menuData = $this->updateMenuCache($this->currentUser->getAdminLevelId(), $this->currentUser->getLanguageId());
            }
            else
            {
                $menuData = $this->updateMenuCache($this->currentUser->getAdminLevelId());
            }
        }
		return $menuData;
	}
    
    /**
     * Updates the menu cache for a specific admin level ID.
     *
     * @param string|null $adminLevelId The admin level ID to update the cache for. If null, updates all admin levels.
     * @param string|null $languageId The language ID to update the cache for. If null, updates default language.
     * @return array The menu data for the specified admin level ID.
     */
    public function updateMenuCache($adminLevelId = null, $languageId = null)
    {
        return $this->updateMenuCacheByAdminLevelId($adminLevelId, $languageId);
    }
    
    /**
     * Updates the menu cache for a specific admin level ID by admin level ID.
     *
     * @param string $adminLevelId The admin level ID to update the cache for.
     * @param string|null $languageId The language ID to update the cache for. If null, updates default language.
     * @return array The menu data for the specified admin level ID.
     */
    public function updateMenuCacheByAdminLevelId($adminLevelId = null, $languageId = null)
    {
        $menuData = array();
        $cacheFinder = new AppMenuCacheImpl(null, $this->database);
        $cacheSpecs = PicoSpecification::getInstance();
        if(isset($adminLevelId) && !empty($adminLevelId))
        {
            $cacheSpecs->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminLevelId, $adminLevelId));   
        }
        if(isset($languageId) && !empty($languageId))
        {
            $cacheSpecs->addAnd(PicoPredicate::getInstance()->equals(Field::of()->languageId, $languageId));   
        }
        
        $now = date('Y-m-d H:i:s');
        
        try
        {
            $pageData = $cacheFinder->findAll($cacheSpecs);
            if($pageData->getTotalResult() > 0)
            {
                foreach($pageData->getResult() as $cache)
                {
                    $menuData = $this->getMenuByAdminLevelId($cache->getAdminLevelId(), $languageId);
                    $dataToStore = json_encode($menuData);
                    $cache->setData($dataToStore);
                    $cache->setTimeEdit($now);
                    $cache->update(); // Update the menu data in the cache
                }
            }
            else if(isset($adminLevelId) && !empty($adminLevelId))
            {
                $menuData = $this->getMenuByAdminLevelId($adminLevelId, $languageId);
                $dataToStore = json_encode($menuData);
                
                $cache = new AppMenuCacheImpl(null, $this->database);
                $cache->setAdminLevelId($adminLevelId);
                $cache->setLanguageId($languageId);
                $cache->setData($dataToStore);
                $cache->setTimeCreate($now);
                $cache->setTimeEdit($now);
                $cache->insert(); // Insert the new menu data into the cache
            }
        }
        catch(Exception $e)
        {
            // Do nothing
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
     * Clears all existing menu cache entries from the database.
     * This method deletes all records in the `AppMenuCacheImpl` table.
     *
     * @return void
     */
    public function clearMenuCache()
    {
        $cache = new AppMenuCacheImpl(null, $this->database);
        $cache->where(PicoSpecification::alwaysTrue())->delete();
    }
    
    /**
     * Retrieves the menu structure for a specific admin level ID.
     *
     * @param string $adminLevelId The admin level ID to filter the menu.
     * @param string|null $languageId The language ID to update the cache for. If null, updates default language.
     * @return array The menu list for the specified admin level ID.
     */
    public function getMenuByAdminLevelId($adminLevelId, $languageId = null)
    {
        $moduleGroups = $this->getModuleGrouped($adminLevelId, $languageId);

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
     * @param string|null $languageId The language ID to update the cache for. If null, updates default language.
     * @return MagicObject[] Array of grouped modules.
     */
    public function getModuleGrouped($adminLevelId, $languageId = null) // NOSONAR
    {
        $specialAcess = false;
        try
        {
            $adminLevel = new AppAdminLevelMinImpl(null, $this->database);
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

        // Translate module at once
        foreach($modules as $index => $module)
        {
            $moduleName = $this->translateModule($module->getName(), $module->getModuleId(), $languageId);
            $modules[$index]->setName($moduleName);
        }
        
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
                    // Translated module group
                    $moduleGroupName = $this->translateModuleGroup($moduleGroup->getName(), $moduleGroupId, $languageId);

                    $modulesWithGroup[$moduleGroupId]->setName($moduleGroupName);
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
     * Translates a module group name based on the provided language ID.
     *
     * @param string $name The original name of the module group.
     * @param string $moduleGroupId The ID of the module group.
     * @param string|null $languageId The ID of the language for translation. If null, the original name is returned.
     * @return string The translated module group name, or the original name if no translation is found or language ID is null.
     */
    private function translateModuleGroup($name, $moduleGroupId, $languageId)
    {
        $menuGroupTranslation = new AppMenuGroupTranslationImpl(null, $this->database);
        try
        {
            $menuGroupTranslation->findOneByModuleGroupIdAndLanguageId($moduleGroupId, $languageId);
            return $menuGroupTranslation->getName();
        }
        catch(Exception $e)
        {
            return $name;
        }
    }

    /**
     * Translates a module name based on the provided language ID.
     *
     * @param string $name The original name of the module.
     * @param string $moduleId The ID of the module.
     * @param string|null $languageId The ID of the language for translation. If null, the original name is returned.
     * @return string The translated module name, or the original name if no translation is found or language ID is null.
     */
    private function translateModule($name, $moduleId, $languageId)
    {
        $menuTranslation = new AppMenuTranslationImpl(null, $this->database);
        try
        {
            $menuTranslation->findOneByModuleIdAndLanguageId($moduleId, $languageId);
            return $menuTranslation->getName();
        }
        catch(Exception $e)
        {
            return $name;
        }
    }
    
    /**
     * Checks whether the current user has permission to access the given module.
     *
     * @param AppModuleImpl $module Module to check access for.
     * @param AppAdminRoleMinImpl[] $adminRoles List of admin roles assigned to the current user.
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
        $modules = array();
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
            $modules = array();
        }
        return $modules;
	}
    
    /**
     * Loads the admin roles from the database.
     *
     * @param string $adminLevelId The admin level ID to filter the roles.
     * @return AppAdminRoleMinImpl[] Array of admin roles.
     */
    public function loadAminRole($adminLevelId)
    {
        $adminRoles = array();
		$adminRole = new AppAdminRoleMinImpl(null, $this->database);
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
            $adminRoles = array();
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
     * Builds a hierarchical menu array from a flat list of modules.
     *
     * This function recursively processes a flat list of Module objects (which are also MagicObjects),
     * organizing them into a nested structure based on their parent_id. Only modules
     * explicitly marked as menu items (`isMenu()`) are included in the hierarchy.
     *
     * @param MagicObject[] $modules A flat array of Module objects to be processed.
     * @param string|null $parentId The ID of the parent module to filter by.
     * Use `null` to start building the top-level menu.
     * @return MagicObject[] A hierarchical array of Module objects, where each parent module
     * might contain a 'children' property (or similar, set via `setChildren()`)
     * containing its sub-modules.
     */
    public function buildMenuHierarchy($modules, $parentId = null) {
        $branch = array();
        foreach ($modules as $module) {
            // Check if the current module's parent_id matches the requested parentId
            // Also ensure it's marked as a 'menu' item if your design requires it
            if ($module->getParentId() === $parentId && $module->isMenu()) {
                // Recursively find children for the current module
                $children = $this->buildMenuHierarchy($modules, $module->getModuleId());
                if (!empty($children)) {
                    $module->setChildren($children); // Add children to the module object
                }
                $branch[] = $module; // Add the module (with its children) to the current branch
            }
        }
        return $branch;
    }
    
    /**
     * Renders a nested HTML menu structure based on hierarchical menu items.
     *
     * This method constructs an unordered list (`<ul>`) with list items (`<li>`) for each menu item.
     * It supports recursive rendering for child menu items, applies appropriate Bootstrap classes
     * for collapsed/expanded states, and marks active or open items based on the current URL.
     *
     * - Root menus get `id="sidebarMenu"` and `nav flex-column` class.
     * - Child menus are rendered inside a `<div class="collapse">` for Bootstrap collapsible support.
     * - Active and open states are visually indicated via `active`, `open`, and `show` classes.
     *
     * @param array $menuItems An array of menu items implementing methods like getName(), getUrl(), hasChildren(), getChildren(), and getIcon().
     * @param string $activeUrl The current active URL used to mark menu items as active or open.
     * @param int $level The depth level of the current menu (used to assign indentation classes). Default is 0.
     * @param DOMDocument|null $dom The DOMDocument used to create elements. If null, a new instance is created.
     * @return DOMElement|null The root <ul> element representing the menu structure, or null if menuItems is empty.
     */
    public function renderMenuHierarchy($menuItems, $activeUrl, $level = 0, $dom = null) // NOSONAR
    {
        if (empty($menuItems)) {
            return null;
        }

        if ($dom === null) {
            $dom = new DOMDocument('1.0', 'UTF-8');
        }

        $this->activeUrl = $activeUrl;

        // Use 'nav flex-column' for level 0, 'nav flex-column pl-3' for deeper levels
        $ulClass = ($level === 0) ? 'nav flex-column root-menu' : 'nav flex-column pl-3';
        $ul = $dom->createElement('ul');
        $ul->setAttribute('class', $ulClass);
        if ($level === 0) {
            $ul->setAttribute('id', 'sidebarMenu'); // Only apply ID to the main root UL
        }


        foreach ($menuItems as $item) {
            $li = $dom->createElement('li');
            $li->setAttribute('class', 'nav-item'); // All nav items
            
            // Add a custom class for root-level menu items if needed for specific styling or JS targeting
            if ($level === 0) {
                $li->setAttribute('class', $li->getAttribute('class') . ' root-menu-item');
            }

            $a = $dom->createElement('a');
            // Add 'collapsed' class for items that have children and are not currently active
            $linkClasses = ['nav-link'];
            if ($item->hasChildren() && stripos($item->getUrl(), $this->activeUrl) === false && $this->findActiveChild($item->getChildren())) {
                $linkClasses[] = 'collapsed';
            }
            
            $a->setAttribute('class', implode(' ', $linkClasses));
            $a->setAttribute('href', htmlspecialchars($item->getUrl()));
            $a->setAttribute('target', htmlspecialchars($item->getTarget() ?: '_self'));

            // If it has children, add data-toggle
            if ($item->hasChildren()) {
                $collapseId = 'collapse-' . uniqid(); // Generate a unique ID for the collapse target
                $a->setAttribute('data-toggle', 'collapse');
                $a->setAttribute('href', '#' . $collapseId);
                $a->setAttribute('aria-expanded', 'false'); // Default to collapsed
            }


            if ($item->getIcon()) {
                $i = $dom->createElement('i');
                // Assume 'fas' prefix for Font Awesome 5+, adjust if using different versions
                $i->setAttribute('class', 'fa ' . htmlspecialchars($item->getIcon()));
                $a->appendChild($i);
                $a->appendChild($dom->createTextNode(' '));
            }

            $a->appendChild($dom->createTextNode(htmlspecialchars($item->getName())));
            $li->appendChild($a);

            $hasChildren = $item->hasChildren();
            $liClasses = preg_split('/\s+/', $li->getAttribute('class'), -1, PREG_SPLIT_NO_EMPTY); // NOSONAR

            if ($hasChildren) {
                // If it has children, the parent <a> should have data-toggle, and the submenu should be a div with collapse
                $childUl = $this->renderMenuHierarchy($item->getChildren(), $this->activeUrl, $level + 1, $dom);
                if ($childUl) {
                    $submenuDiv = $dom->createElement('div');
                    $submenuDiv->setAttribute('id', $collapseId); // Use the unique ID for the collapse target
                    $submenuDiv->setAttribute('class', 'collapse'); // Default to collapsed

                    // Check if any child is active, if so, add 'show' to collapse div and 'open' to parent li
                    if ($this->hasOpenChild($childUl) || strcasecmp($item->getUrl(), $this->activeUrl) === 0) {
                        $submenuDiv->setAttribute('class', 'collapse show');
                        if (!in_array('open', $liClasses)) {
                            $liClasses[] = 'open';
                        }
                        // For Bootstrap, if parent is open, its nav-link should not be 'collapsed'
                        // Update the <a> tag's class if it was marked collapsed earlier
                        $currentAClass = $a->getAttribute('class');
                        $a->setAttribute('class', str_replace(' collapsed', '', $currentAClass));
                        $a->setAttribute('aria-expanded', 'true');
                    }
                    $submenuDiv->appendChild($childUl);
                    $li->appendChild($submenuDiv);
                }
            }

            // Mark 'selected' if current URL matches precisely (or starts with if it's a parent)
            $isItemSelected = (strcasecmp($item->getUrl(), $this->activeUrl) === 0);
            if ($isItemSelected) {
                $liClasses[] = 'selected';
                if (!in_array('open', $liClasses)) { // Also mark as open if selected
                    $liClasses[] = 'open';
                }
                // For Bootstrap, if active, its nav-link should not be 'collapsed'
                $currentAClass = $a->getAttribute('class');
                $a->setAttribute('class', str_replace(' collapsed', '', $currentAClass) . ' active'); // Add 'active' class to nav-link
            }


            if (!empty($liClasses)) {
                $li->setAttribute('class', implode(' ', array_unique($liClasses)));
            }

            $ul->appendChild($li);
        }

        return $ul;
    }

    /**
     * Recursively checks whether any child menu item (or sub-child) is active based on the current URL.
     *
     * This is used to determine whether a parent item should be expanded (opened) in the menu.
     *
     * @param array $menuItems Array of child menu items.
     * @return bool True if any of the child items (recursively) matches the active URL, otherwise false.
     */
    private function findActiveChild($menuItems) // NOSONAR
    {
        if (empty($menuItems)) {
            return false;
        }
        foreach ($menuItems as $item) {
            if (strcasecmp($item->getUrl(), $this->activeUrl) === 0) {
                return true;
            }
            if ($item->hasChildren() && $this->findActiveChild($item->getChildren())) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if any child <li> element within the given <ul> has the 'open' class.
     *
     * This is used to decide whether a collapsible section should be shown (`collapse show`) by default.
     *
     * @param DOMElement $ulElement The <ul> element to search for open children.
     * @return bool True if any <li> under this <ul> has the 'open' class, otherwise false.
     */
    private function hasOpenChild($ulElement) // NOSONAR
    {
        $lis = $ulElement->getElementsByTagName('li');
        foreach ($lis as $li) {
            if ($li->hasAttribute('class') && str_contains($li->getAttribute('class'), 'open')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Traverses the DOM to mark all ancestor menu items as open for every selected <li> element.
     *
     * This function updates parent <li> elements by:
     * - Adding the 'open' class to them.
     * - Ensuring their <a> tags are expanded and not collapsed (adjusts class and aria-expanded attribute).
     * It uses XPath queries to walk up the DOM tree from selected items and apply the changes accordingly.
     * The traversal stops when it reaches a root menu `<ul>` element (identified by the `root-menu` class).
     *
     * @param DOMDocument $dom The DOM document that contains the menu structure.
     * @return void
     */
    public function markParentsOpen($dom) // NOSONAR
    {
        $xpath = new DOMXPath($dom);
        $selectedItems = $xpath->query("//li[contains(concat(' ', normalize-space(@class), ' '), ' selected ')]");

        foreach ($selectedItems as $selectedLi) {
            $currentElement = $selectedLi;

            // Start from the selected LI and traverse upwards
            while ($currentElement !== null) {
                // Find the direct parent UL
                $parentUl = $xpath->query("parent::ul[1]", $currentElement)->item(0);

                if ($parentUl instanceof DOMElement) {
                    // If the parent UL has the 'root-menu' class, we stop marking its parent LI.
                    // This UL is essentially the top-level menu.
                    if ($parentUl->hasAttribute('class') && str_contains($parentUl->getAttribute('class'), 'root-menu')) {
                        // We mark the LI that directly contains this root-menu UL's submenu if applicable.
                        // However, the 'root-menu-item' class is on the LI, not the UL, so let's target the parent LI.
                        $parentLiOfRootMenuUl = $xpath->query("parent::div/parent::li[1]", $parentUl)->item(0);
                        if ($parentLiOfRootMenuUl instanceof DOMElement) {
                             $classAttrLi = $parentLiOfRootMenuUl->getAttribute('class');
                             $classesLi = preg_split('/\s+/', $classAttrLi, -1, PREG_SPLIT_NO_EMPTY);
                             if (!in_array('open', $classesLi)) {
                                 $classesLi[] = 'open';
                                 $parentLiOfRootMenuUl->setAttribute('class', implode(' ', array_unique($classesLi)));
                             }
                             $parentAnchorLi = $xpath->query("a", $parentLiOfRootMenuUl)->item(0);
                             if ($parentAnchorLi instanceof DOMElement) {
                                 $anchorClassesLi = preg_split('/\s+/', $parentAnchorLi->getAttribute('class'), -1, PREG_SPLIT_NO_EMPTY);
                                 $anchorClassesLi = array_diff($anchorClassesLi, ['collapsed']);
                                 $parentAnchorLi->setAttribute('class', implode(' ', array_unique($anchorClassesLi)));
                                 $parentAnchorLi->setAttribute('aria-expanded', 'true');
                             }
                        }
                        break; // Stop when we hit the root menu UL
                    }

                    // If it's a regular submenu UL, find its parent LI
                    $parentLi = $xpath->query("parent::div/parent::li[1]", $parentUl)->item(0);

                    if ($parentLi instanceof DOMElement) {
                        $classAttr = $parentLi->getAttribute('class');
                        $classes = preg_split('/\s+/', $classAttr, -1, PREG_SPLIT_NO_EMPTY);

                        if (!in_array('open', $classes)) {
                            $classes[] = 'open';
                            $parentLi->setAttribute('class', implode(' ', array_unique($classes)));

                            // Also ensure the corresponding <a> tag is not 'collapsed' and has 'aria-expanded="true"'
                            $parentAnchor = $xpath->query("a", $parentLi)->item(0);
                            if ($parentAnchor instanceof DOMElement) {
                                $anchorClasses = preg_split('/\s+/', $parentAnchor->getAttribute('class'), -1, PREG_SPLIT_NO_EMPTY);
                                $anchorClasses = array_diff($anchorClasses, ['collapsed']); // Remove 'collapsed'
                                $parentAnchor->setAttribute('class', implode(' ', array_unique($anchorClasses)));
                                $parentAnchor->setAttribute('aria-expanded', 'true');
                            }
                        }
                        $currentElement = $parentLi; // Move up to the next parent LI
                    } else {
                        break; // No more parent LI elements in the expected structure
                    }
                } else {
                    break; // Reached the top of the DOM or unexpected structure
                }
            }
        }
    }
    
    /**
     * Renders a hierarchical menu as a flat list of <option> elements for a <select> dropdown.
     * Indentation is added based on the menu item's level to simulate hierarchy.
     *
     * @param MagicObject[] $menuItems The hierarchical array of menu items (from buildMenuHierarchy).
     * @param string $selectedValue The module_id of the currently selected option (optional).
     * @param int $level The current nesting level, used for indentation.
     * @param string $indentChar The character(s) to use for indentation (e.g., '&nbsp;&nbsp;', '--').
     * @return string The HTML string containing <option> tags.
     */
    public function renderMenuAsSelectOptions($menuItems, $selectedValue = null, $level = 0, $indentChar = '&nbsp;&nbsp;&nbsp;&nbsp;') {
        $html = '';
        $prefix = str_repeat($indentChar, $level); // Create indentation string

        foreach ($menuItems as $item) {
            $selectedAttribute = ($selectedValue === $item->getModuleId()) ? ' selected' : '';
            
            // Add the option tag with indentation
            $html .= '<option value="' . htmlspecialchars($item->getModuleId()) . '"' . $selectedAttribute . '>';
            $html .= $prefix . htmlspecialchars($item->getName());
            $html .= '</option>';

            // Recursively add children's options
            if ($item->issetChildren()) {
                $html .= $this->renderMenuAsSelectOptions($item->getChildren(), $selectedValue, $level + 1, $indentChar);
            }
        }
        return $html;
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