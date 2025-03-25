<?php

namespace MagicApp;

use AppBuilder\App\AppAdminRoleImpl;
use AppBuilder\App\AppModuleImpl;
use Exception;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;

class AppMenu
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
     * Generates an HTML sidebar menu based on a JSON structure and the current active link.
     *
     * This function dynamically generates a sidebar in HTML format. It reads menu data from a provided
     * JSON object, and adds submenu items if available. If the `currentHref` matches any submenu item's href,
     * the respective submenu will be expanded by adding the "show" class to its `collapse` div.
     *
     * @param string $jsonData A JSON-encoded string representing the menu structure, including main items and submenus.
     * @param string $currentHref The href of the current page, used to determine which submenu (if any) should be expanded.
     * @param AppLanguage $appLanguage Application language object.
     * 
     * @return string The generated HTML for the sidebar, including the main menu and any expanded submenus.
     */
    public static function generateSidebar($jsonData, $currentHref, $appLanguage) // NOSONAR
    {
        
        // Start the sidebar HTML structure
        $sidebarHTML = '<ul class="nav flex-column" id="sidebarMenu">';

        if(isset($jsonData['menu']) && is_array($jsonData['menu']) && !empty($jsonData['menu']))
        {
            // Loop through each main menu item
            foreach ($jsonData['menu'] as $item) {
                $sidebarHTML .= '<li class="nav-item">';

                $item['title'] = $appLanguage->get(strtolower(str_replace(' ', '_', $item['title'])));
                
                // Link for the main menu item, add collapse toggle if there are submenus
                $sidebarHTML .= '<a class="nav-link" href="' . $item['href'] . '"';
                
                // Add target="_blank" if specified in the JSON (or set default)
                $target = isset($item['target']) ? $item['target'] : '';
                if ($target) {
                    $sidebarHTML .= ' target="' . $target . '"';
                }

                if (count($item['submenu']) > 0) {
                    $sidebarHTML .= ' data-toggle="collapse" aria-expanded="false"';
                }
                $sidebarHTML .= '><i class="' . $item['icon'] . '"></i> ' . $item['title'] . '</a>'."\r\n";
                
                // Check if there are submenus
                if (count($item['submenu']) > 0) {
                    // Check if currentHref matches any of the submenu items' href
                    $isActive = false;
                    foreach ($item['submenu'] as $subItem) {
                        if ($subItem['href'] === $currentHref) {
                            $isActive = true;
                            break;
                        }
                    }
                    
                    // Add class "show" if the currentHref matches any submenu item
                    $collapseClass = $isActive ? 'collapse show' : 'collapse';
                    $sidebarHTML .= '<div id="' . substr($item['href'], 1) . '" class="' . $collapseClass . '">'."\r\n";
                    $sidebarHTML .= '<ul class="nav flex-column pl-3">'."\r\n";
                    
                    // Loop through each submenu item
                    foreach ($item['submenu'] as $subItem) {
                        $subItem['title'] = $appLanguage->get(strtolower(str_replace(' ', '_', $subItem['title'])));
                        $sidebarHTML .= '<li class="nav-item">';
                        $sidebarHTML .= '<a class="nav-link" href="' . $subItem['href'] . '"';
                        
                        // Add target="_blank" for submenu links if specified
                        $subTarget = isset($subItem['target']) ? $subItem['target'] : '';
                        if ($subTarget) {
                            $sidebarHTML .= ' target="' . $subTarget . '"';
                        }

                        $sidebarHTML .= '><i class="' . $subItem['icon'] . '"></i> ' . $subItem['title'] . '</a>';
                        $sidebarHTML .= '</li>'."\r\n";
                    }
                    
                    $sidebarHTML .= '</ul>'."\r\n";
                    $sidebarHTML .= '</div>'."\r\n";
                }

                $sidebarHTML .= '</li>'."\r\n";
            }
        }
        // Close the sidebar HTML structure
        $sidebarHTML .= '</ul>';

        // Return the generated sidebar HTML
        return $sidebarHTML;
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
    public function __construct($database, $appConfig, $currentUser, $jsonData, $currentHref, $appLanguage)
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
		$moduleGroups = $this->getModuleGrouped();
        
/*
menu:
  - title: "Home"
    icon: "fas fa-home"
    href: "index.php"
    submenu: []
  - title: "Master"
    icon: "fas fa-folder"
    href: "#submenu1"
    submenu:
      - title: "Application"
        icon: "fas fa-microchip"
        href: "application.php"
      - title: "Application Group"
        icon: "fas fa-microchip"
        href: "application-group.php"
      - title: "Workspace"
        icon: "fas fa-building"
        href: "workspace.php"
      - title: "Administrator"
        icon: "fas fa-user"
        href: "admin.php"
*/
        $menuList = array();
        $menuList['menu'] = array();
        foreach($moduleGroups as $moduleGoup)
        {
            $menu = array(
                'title' => $moduleGoup->getName(),
                'icon' => $moduleGoup->getIcon(),
                'href' => $moduleGoup->getIcon(),
                'submenu' => array()
            );
            $submenus = array();
            if($moduleGoup->getModules() != null)
            {
                foreach($moduleGoup->getModules() as $module)
                {
                    $submenus[] = array(
                        'title' => $module->getName(),
                        'icon' => $module->getIcon(),
                        'href' => $module->getIcon(),
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
     * @return MagicObject[] Array of grouped modules.
     */
    public function getModuleGrouped()
    {
        $modules = $this->loadModule();
        $adminRoles = $this->loadAminRole();
        $modulesWithGroup = array();
        
        // Step 1 - for module with valid group module
        foreach($modules as $module)
        {
            $moduleGroup = $module->getGroupModule();
            $moduleGroupId = $module->getModuleGroupId();
            if(isset($moduleGroup) && $moduleGroup->getGroupModuleId() != null)
            {
                if(!isset($modulesWithGroup[$moduleGroupId]))
                {
                    $modulesWithGroup[$moduleGroupId] = new MagicObject();
                    $modulesWithGroup[$moduleGroupId]->setModuleGroupId();
                    $modulesWithGroup[$moduleGroupId]->setModuleGroup($moduleGroup);
                }
                if($this->isAllowedAccess($module, $adminRoles))
                {
                    $modulesWithGroup[$moduleGroupId]->appendModules();
                }   
            }
        }
        // Step 2 - for module without valid group module
        foreach($modules as $module)
        {
            $moduleGroup = $module->getGroupModule();
            $moduleGroupId = $module->getModuleGroupId();
            if(!isset($moduleGroup) || $moduleGroup->getGroupModuleId() == null)
            {
                if(!isset($modulesWithGroup[$moduleGroupId]))
                {
                    $modulesWithGroup[$moduleGroupId] = new MagicObject();
                    $modulesWithGroup[$moduleGroupId]->setModuleGroupId();
                    $modulesWithGroup[$moduleGroupId]->setModuleGroup($moduleGroup);
                }
                if($this->isAllowedAccess($module, $adminRoles))
                {
                    $modulesWithGroup[$moduleGroupId]->appendModules();
                }   
            }
        }
        
        // Clean up empty group
        foreach($modulesWithGroup as $index=>$group)
        {
            if($group->issetModules())
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
                if($adminRole->getModuleId() == $module->getModule() && 
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
     * @return AppModuleImpl[] Array of modules.
     */
	public function loadModule()
	{
        $modules = [];
		$module = new AppModuleImpl(null, $this->database);
        $specs = PicoSpecification::getInstance()
        ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->active, true))
        ;
        $sorts = PicoSortable::getInstance()
            ->addSortable(new PicoSort('moduleGroup.sortOrder', PicoSort::ORDER_TYPE_ASC))
            ->addSortable(new PicoSort('sortOrder', PicoSort::ORDER_TYPE_ASC))
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
     * @return AppAdminRoleImpl[] Array of admin roles.
     */
    function loadAminRole()
    {
        $adminRoles = [];
		$adminRole = new AppAdminRoleImpl(null, $this->database);
        $specs = PicoSpecification::getInstance()
        ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminLevelId, $this->currentUser->getAdminLevelId()))
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
     * Renders the complete menu (either from JSON or database depending on the environment).
     *
     * @return string The rendered HTML menu.
     */
    public function renderMenu()
    {
        $menuData = null;
        if($this->appConfig->getDevelopmentMode())
        {
            $menuData = $this->jsonData;
        }
        else
        {
            $menuData = $this->getMenuFromDatabase();
        }
        return self::generateSidebar($menuData, $this->currentHref, $this->appLanguage);
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