<?php

namespace MagicApp\Menu;

use MagicObject\MagicObject;

/**
 * Class MainMenu
 *
 * Extends the BasicMenu class to provide functionalities specific to managing
 * a hierarchical menu structure. It organizes menu items into groups based
 * on specified column names, allowing for structured retrieval of menu data.
 */
class MainMenu extends BasicMenu
{
    /**
     * Menu structure
     *
     * @var array
     */
    private $menu = array();

    /**
     * Column name for menu group
     *
     * @var string
     */
    private $columnName = "";

    /**
     * Join column name for menu group
     *
     * @var string
     */
    private $joinColumnName = "";
    
    /**
     * Construct menu
     *
     * Initializes the MainMenu with a list of menu items, organizing them
     * into groups based on the specified column names.
     *
     * @param MagicObject[] $menu An array of menu items (MagicObject instances).
     * @param string $columnName The column name used to group the menu items.
     * @param string $joinColumnName The column name used for joining menu groups.
     */
    public function __construct($menu, $columnName, $joinColumnName)
    {
        $this->columnName = $columnName;
        $this->joinColumnName = $joinColumnName;
        $this->menu = array();
        
        foreach ($menu as $menuItem) {
            $menuGroupId = $menuItem->get($columnName);
            if (!isset($this->menu[$menuGroupId])) {
                $this->menu[$menuGroupId] = array();
                $this->menu[$menuGroupId]['menuGroup'] = $menuItem->get($joinColumnName);
                $this->menu[$menuGroupId]['menuItem'] = array();
            }
            $this->menu[$menuGroupId]['menuItem'][] = $menuItem;
        }
    }

    /**
     * Get menu
     *
     * Returns the organized menu structure.
     *
     * @return array The organized menu structure grouped by specified column.
     */ 
    public function getMenu()
    {
        return $this->menu;
    }
}
