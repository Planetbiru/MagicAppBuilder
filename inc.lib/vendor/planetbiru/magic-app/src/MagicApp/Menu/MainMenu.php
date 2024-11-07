<?php

namespace MagicApp\Menu;

use MagicObject\MagicObject;

/**
 * Class MainMenu
 *
 * Represents the main menu structure that organizes a list of menu items 
 * into groups based on specified column names. It extends the `BasicMenu` class 
 * and provides functionality for organizing menu items into logical groups, 
 * such as grouping by menu category or other criteria.
 *
 * This class also supports associating each menu group with a unique identifier 
 * and allows for easy retrieval of the grouped menu structure. It is designed 
 * to be used in scenarios where menus need to be structured dynamically and 
 * grouped based on data stored in an entity or database.
 *
 * @package MagicApp\Menu
 * @link https://github.com/Planetbiru/MagicApp
 * @author Kamshory
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
