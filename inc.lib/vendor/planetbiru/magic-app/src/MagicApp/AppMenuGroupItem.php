<?php

namespace MagicApp;

/**
 * Class AppMenuGroupItem
 *
 * Represents a group of menu items, including a label, a CSS class, and the menu items themselves.
 */
class AppMenuGroupItem
{
    /**
     * Label for the menu group.
     *
     * @var string
     */
    private $label;

    /**
     * CSS class name associated with the menu group.
     *
     * @var string
     */
    private $class;

    /**
     * Array of menu items in this group.
     *
     * @var AppMenuItem[]
     */
    private $menuItems = [];

    /**
     * Get the label of the menu group.
     *
     * @return string
     */ 
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label for the menu group.
     *
     * @param string $label The label for the menu group.
     *
     * @return self
     */ 
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the CSS class name of the menu group.
     *
     * @return string
     */ 
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the CSS class name for the menu group.
     *
     * @param string $class The CSS class name.
     *
     * @return self
     */ 
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the menu items in this group.
     *
     * @return AppMenuItem[] Array of menu items.
     */ 
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * Set the menu items for this group.
     *
     * @param AppMenuItem[] $menuItems Array of menu items.
     *
     * @return self
     */ 
    public function setMenuItems(array $menuItems)
    {
        $this->menuItems = $menuItems;

        return $this;
    }
}
