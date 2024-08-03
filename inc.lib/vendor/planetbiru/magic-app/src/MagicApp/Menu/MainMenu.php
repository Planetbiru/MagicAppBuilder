<?php

namespace MagicApp\Menu;

use MagicObject\MagicObject;

class MainMenu extends BasicMenu
{
    /**
     * Menu
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
     * @param MagicObject[] $menu
     * @param string $columnName
     * @param string $joinColumnName
     */
    public function __construct($menu, $columnName, $joinColumnName)
    {
        $this->columnName = $columnName;
        $this->joinColumnName = $joinColumnName;
        $this->menu = array();
        foreach($menu as $menuItem)
        {
            $menuGroupId = $menuItem->get($columnName);
            if(!isset($this->menu[$menuGroupId]))
            {
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
     * @return array
     */ 
    public function getMenu()
    {
        return $this->menu;
    }
}