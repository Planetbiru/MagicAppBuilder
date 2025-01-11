<?php

namespace MagicApp;

/**
 * Class AppMenuGroupItem
 *
 * Represents a group of menu items, including a label, a CSS class, and the menu items themselves.
 * This class is used to define a collection of related menu items, with a label and a CSS class for styling purposes.
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
     * This method returns the label associated with the menu group. The label is used to identify the menu group
     * in the user interface.
     *
     * @return string The label of the menu group.
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label for the menu group.
     *
     * This method sets the label that will be associated with the menu group. The label is typically displayed in the
     * user interface to identify the group of menu items.
     *
     * @param string $label The label for the menu group.
     *
     * @return self The current instance of the class, allowing for method chaining.
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get the CSS class name of the menu group.
     *
     * This method returns the CSS class name associated with the menu group. The CSS class is used to style the group
     * in the user interface.
     *
     * @return string The CSS class name of the menu group.
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set the CSS class name for the menu group.
     *
     * This method sets the CSS class name that will be used to style the menu group in the user interface.
     *
     * @param string $class The CSS class name.
     *
     * @return self The current instance of the class, allowing for method chaining.
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get the menu items in this group.
     *
     * This method returns an array of menu items that belong to this group. The menu items represent the individual
     * actions or links available within the menu group.
     *
     * @return AppMenuItem[] Array of menu items in this group.
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * Set the menu items for this group.
     *
     * This method sets an array of menu items for the menu group. Each menu item corresponds to a specific action or
     * link within the group.
     *
     * @param AppMenuItem[] $menuItems Array of menu items to be associated with the group.
     *
     * @return self The current instance of the class, allowing for method chaining.
     */
    public function setMenuItems($menuItems)
    {
        $this->menuItems = $menuItems;

        return $this;
    }
}
