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
use MagicAppTemplate\Entity\App\AppModuleMultiLevelImpl;

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
     * Loads active and menu-enabled modules from the database, ordered by module group and then by module sort order.
     * This function specifically retrieves modules that are designed for multi-level menus.
     *
     * @return \MagicObject\MagicObject[] An array of `AppModuleMultiLevelImpl` objects (or `MagicObject` instances)
     * representing the loaded modules. Returns an empty array if an error occurs
     * during database retrieval.
     */
    public function loadModuleMultiLevel()
	{
        $modules = array();
		$module = new AppModuleMultiLevelImpl(null, $this->database);
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
    public function buildMenuHierarchy($modules, $parentId = null)
    {
        $branch = [];
        $modulesUsedInThisLevel = []; // Keep track of modules used as direct children in this call

        foreach ($modules as $module) {
            // Check if the current module's parent_id matches the requested parentId
            // And ensure it's marked as a 'menu' item
            if ($module->getParentModuleId() == $parentId && $module->isMenu()) {
                // Recursively find children for the current module
                $children = $this->buildMenuHierarchy($modules, $module->getModuleId());
                
                if (!empty($children)) {
                    $module->setChildren($children); // Add children to the module object
                }
                
                $branch[] = $module; // Add the module (with its children) to the current branch
                $modulesUsedInThisLevel[] = $module->getModuleId(); // Mark this module as used
            }
        }
        
        // At the end of the top-level call ($parentId === null),
        // filter out any modules that became children of other modules.
        // This prevents them from appearing at the root level if they were only supposed to be children.
        if ($this->isBlank($parentId)) {
            $filteredModules = [];
            $allChildModuleIds = $this->collectAllChildModuleIds($branch);
            
            foreach ($modules as $module) {
                // Only add to filteredModules if it's not a child of any other module,
                // AND its parent_id matches the current parentId (which is null for root).
                if (!in_array($module->getModuleId(), $allChildModuleIds) && $this->isBlank($module->getParentModuleId()) && $module->isMenu()) {
                    $filteredModules[] = $module;
                }
            }
            return $filteredModules;
        }

        return $branch;
    }

    /**
     * Checks if a given string is considered "blank".
     * A string is considered blank if it is:
     * - Not set (null).
     * - Empty (e.g., "").
     * - Contains only whitespace characters.
     *
     * @param string|null $string The string to check. Can be null.
     * @return bool True if the string is blank, false otherwise.
     */
    public function isBlank($string)
    {
        return !isset($string) || empty($string) || trim($string) === '';
    }

    /**
     * Helper function to collect all module IDs that are children of other modules in a hierarchy.
     *
     * @param array $menuItems A hierarchical array of menu items.
     * @return array A flat array of all child module IDs.
     */
    private function collectAllChildModuleIds($menuItems) {
        $childIds = [];
        foreach ($menuItems as $item) {
            if ($item->issetChildren() && !empty($item->getChildren())) {
                foreach ($item->getChildren() as $child) {
                    $childIds[] = $child->getModuleId();
                }
                $childIds = array_merge($childIds, $this->collectAllChildModuleIds($item->getChildren()));
            }
        }
        return $childIds;
    }
    
    /**
     * Renders a hierarchical menu structure as an unordered list (UL) using DOMDocument.
     * This function recursively processes menu items, applying Bootstrap-compatible classes
     * for collapsible menus (e.g., 'collapse', 'show') and highlighting active menu paths
     * with 'open' and 'selected' classes based on the provided active URL.
     *
     * @param MagicObject[] $menuItems The hierarchical array of menu items to render. Each item is expected
     * to be a MagicObject with methods like `getUrl()`, `getTarget()`, `getIcon()`,
     * `getName()`, and `issetChildren()`.
     * @param string $activeUrl The URL of the currently active or selected page/module. This is used
     * to determine which menu items should receive 'open' and 'selected' classes.
     * @param int $level The current nesting level of the menu items (defaults to 0 for the top level).
     * This is used for applying level-specific CSS classes (e.g., 'menu-level-0').
     * @param DOMDocument|null $dom The DOMDocument instance used for creating new elements. If null,
     * a new DOMDocument will be created for the initial call.
     * @return DOMElement|null The generated UL DOMElement representing the menu hierarchy, or null if the
     * `$menuItems` array is empty.
     */
    public function renderMenuHierarchy($menuItems, $activeUrl, $level = 0, $dom = null) // NOSONAR
    {
        if (empty($menuItems)) {
            return null;
        }

        // Initialize DOMDocument for the top-level call
        if ($dom === null) {
            $dom = new DOMDocument('1.0', 'UTF-8');
            // This is crucial: we need to pass the initial DOM object through recursive calls
            // For simplicity, we'll return the root UL and let the caller append it
            // to a complete document if needed.
        }

        $this->activeUrl = $activeUrl; // Set the active URL for this rendering cycle

        $ul = $dom->createElement('ul');
        // Add 'menu-root' class if level is 0
        $ulClass = ($level === 0) ? 'menu-level-' . $level . ' menu-root' : 'menu-level-' . $level;
        $ul->setAttribute('class', $ulClass);

        if ($level === 0) {
            $ul->setAttribute('id', 'sidebarMenu'); // Only apply ID to the main root UL
        }

        foreach ($menuItems as $item) {
            $li = $dom->createElement('li');
            $a = $dom->createElement('a');
            $a->setAttribute('href', htmlspecialchars($item->getUrl()));
            $a->setAttribute('target', htmlspecialchars($item->getTarget() ?: '_self'));

            if ($item->getIcon()) {
                $i = $dom->createElement('i');
                $i->setAttribute('class', 'fa ' . htmlspecialchars($item->getIcon()));
                $a->appendChild($i);
                $a->appendChild($dom->createTextNode(' ')); // Add a space after icon
            }

            $a->appendChild($dom->createTextNode(htmlspecialchars($item->getName())));

            $hasChildren = $item->issetChildren();
            $liClasses = array(); // Array to build up classes for the LI
            $isParentActive = false; // Flag to determine if this LI (parent) should be open/expanded

            if ($hasChildren) {
                $liClasses[] = 'has-submenu';
            }

            // Determine if the current item itself is selected
            $isItemSelected = (strcasecmp($item->getUrl(), $this->activeUrl) === 0);

            if ($isItemSelected) {
                $liClasses[] = 'selected'; // Mark the item itself as selected
            }

            // Recursively render children if they exist
            $childUl = null;
            $submenuId = null;
            if ($hasChildren) {
                $submenuId = 'submenu-' . uniqid();
                $a->setAttribute('data-toggle', 'collapse');
                $a->setAttribute('href', '#' . $submenuId);
                $a->setAttribute('aria-controls', $submenuId);
                
                $childUl = $this->renderMenuHierarchy($item->getChildren(), $this->activeUrl, $level + 1, $dom);
                
                if ($childUl && ($this->hasOpenChild($childUl) || $isItemSelected)) { 
                    // If this item is selected OR any of its children (or grandchildren) are open/selected
                    $liClasses[] = 'open'; // Mark parent LI as open
                    $isParentActive = true;                   
                }
            }
            
            // Set aria-expanded for the parent link based on $isParentActive
            // If it's a parent of an active item, it should be expanded. Otherwise, collapsed.
            $a->setAttribute('aria-expanded', $isParentActive ? 'true' : 'false');

            // Crucial: Set the 'collapsed' class on the <a> tag
            // If it's a parent of an active item, it should NOT be 'collapsed'.
            // If it's not a parent of an active item (and has children), it should be 'collapsed'.
            if ($hasChildren && !$isParentActive) {
                $a->setAttribute('class', $a->getAttribute('class') . ' collapsed'); // Add 'collapsed' if it's not active
            }


            // Append the anchor tag to the li
            $li->appendChild($a);

            // Add Bootstrap collapse structure for submenus
            if ($childUl) {
                $submenuWrapper = $dom->createElement('div');
                $submenuClasses = 'collapse';
                if ($isParentActive) { // If this parent is marked 'open', the submenu should be 'show'
                    $submenuClasses .= ' show';
                }
                $submenuWrapper->setAttribute('class', $submenuClasses);
                $submenuWrapper->setAttribute('id', $submenuId);
                
                $submenuWrapper->appendChild($childUl);
                $li->appendChild($submenuWrapper);
            }

            // Add collected classes to the LI
            if (!empty($liClasses)) {
                $li->setAttribute('class', implode(' ', array_unique($liClasses)));
            }

            $ul->appendChild($li);
        }

        return $ul;
    }

    /**
     * Renders the complete menu hierarchy as an HTML string.
     * This function retrieves menu data, builds the hierarchy,
     * renders it into a DOMDocument, marks active parents, and then
     * returns the final HTML as a string.
     *
     * @return string The HTML string representation of the menu.
     */
    public function renderHtmlMenu($currentHref = null)
    {
        $this->currentHref = $currentHref;

        $modules = $this->loadModuleMultiLevel(); // Retrieve modules (this might need to be adjusted based on your menu data structure)
        $menuHierarchy = $this->buildMenuHierarchy($modules); // Build the menu hierarchy
                
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true; // For cleaner output

        // Render the main menu hierarchy
        $renderedUl = $this->renderMenuHierarchy($menuHierarchy, $this->currentHref, 0, $dom);

        if ($renderedUl) {
            // Important: Append the rendered UL element to the DOMDocument
            $dom->appendChild($renderedUl);
            // Call markParentsOpen after all menu elements are in the DOM
            // This function needs to handle Bootstrap classes as well
            $this->markParentsOpen($dom);
        }
        
        return $dom->saveHTML();
    }

    /**
     * Helper to check if any child LI element within a UL has the 'open' class.
     * This is used to determine if a parent should also be 'open'.
     *
     * @param DOMElement $ulElement The UL element to check.
     * @return bool
     */
    private function hasOpenChild($ulElement)
    {
        $lis = $ulElement->getElementsByTagName('li');
        foreach ($lis as $li) {
            if ($li->hasAttribute('class') && $this->stringContains($li->getAttribute('class'), 'open')) {
                return true;
            }
        }
        return false;
    }

    /**
     * Traverses the menu DOM to mark all parent LIs of the 'selected' item with the 'open' class.
     * It also ensures the corresponding Bootstrap collapse attributes are set correctly.
     * The traversal stops when it reaches a <ul> element that has the 'menu-root' class,
     * as these are top-level menu containers and their containing LIs are not collapsible parents in the same manner.
     *
     * @param DOMDocument $dom The main DOMDocument object.
     * @return void
     */
    public function markParentsOpen($dom) // NOSONAR
    {
        $xpath = new DOMXPath($dom);
        // Find all <li> elements that have the 'selected' class
        $selectedItems = $xpath->query("//li[contains(concat(' ', normalize-space(@class), ' '), ' selected ')]");

        foreach ($selectedItems as $selectedLi) {
            $currentLi = $selectedLi; // Start traversal from the selected <li>

            // Continue moving upwards as long as there's a current <li>
            while ($currentLi !== null) {
                // Find the <ul> element that directly contains the current <li>
                $parentUl = $xpath->query("parent::ul[1]", $currentLi)->item(0);

                // If no parent UL is found, or if we've reached the top of the document structure, break.
                if (!($parentUl instanceof DOMElement)) {
                    break;
                }

                // If the parent UL has the 'menu-root' class, we've reached the top of the menu hierarchy.
                if ($parentUl->hasAttribute('class') && $this->stringContains($parentUl->getAttribute('class'), 'menu-root')) {
                    break;
                }

                // For any other <ul> (i.e., a submenu <ul>), find its parent <li>,
                // which is the actual menu item that needs to be marked 'open' to reveal this submenu.
                $parentLiToMark = $xpath->query("parent::div/parent::li[1]", $parentUl)->item(0);

                if ($parentLiToMark instanceof DOMElement) {
                    $classAttr = $parentLiToMark->getAttribute('class');
                    $classes = preg_split('/\s+/', $classAttr, -1, PREG_SPLIT_NO_EMPTY); // NOSONAR

                    // Add 'open' class if not already present
                    if (!in_array('open', $classes)) {
                        $classes[] = 'open';
                        $parentLiToMark->setAttribute('class', implode(' ', array_unique($classes)));
                    }

                    // Also ensure the corresponding <a> tag is expanded and not 'collapsed'
                    $parentAnchor = $xpath->query("a", $parentLiToMark)->item(0);
                    if ($parentAnchor instanceof DOMElement) {
                        $anchorClasses = preg_split('/\s+/', $parentAnchor->getAttribute('class'), -1, PREG_SPLIT_NO_EMPTY);
                        
                        // Remove the 'collapsed' class if present
                        $anchorClasses = array_diff($anchorClasses, ['collapsed']);
                        $parentAnchor->setAttribute('class', implode(' ', array_unique($anchorClasses)));
                        $parentAnchor->setAttribute('aria-expanded', 'true'); // Explicitly set to true
                    }
                    
                    // Also ensure the immediate submenu <div>.collapse is 'show'
                    $submenuDiv = $xpath->query("div[@class[contains(., 'collapse')]]", $parentLiToMark)->item(0);
                    if ($submenuDiv instanceof DOMElement) {
                        $submenuDivClasses = preg_split('/\s+/', $submenuDiv->getAttribute('class'), -1, PREG_SPLIT_NO_EMPTY);
                        if (!in_array('show', $submenuDivClasses)) {
                            $submenuDivClasses[] = 'show';
                            $submenuDiv->setAttribute('class', implode(' ', array_unique($submenuDivClasses)));
                        }
                    }

                    // Move up to this newly marked parent <li> for the next iteration
                    $currentLi = $parentLiToMark;
                } else {
                    break;
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
     * Checks if a string contains a specific substring.
     * This function provides similar functionality to PHP 8's `str_contains()`
     * for compatibility with earlier PHP versions (e.g., PHP 5).
     *
     * @param string $haystack The string to search within.
     * @param string $needle The substring to search for.
     * @return bool True if the $needle is found within the $haystack, false otherwise.
     */
    public function stringContains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
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