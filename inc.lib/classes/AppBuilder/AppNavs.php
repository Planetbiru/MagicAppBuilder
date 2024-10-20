<?php

namespace AppBuilder;

/**
 * Class AppNavs
 *
 * Represents a collection of navigation items.
 * This class provides methods to add, retrieve, and manage multiple AppNav instances.
 */
class AppNavs
{
    /**
     * Collection of navigation items.
     *
     * @var AppNav[]
     */
    private $navs = array();

    /**
     * Add a navigation item to the collection.
     *
     * This method appends the given AppNav instance to the navs array.
     *
     * @param AppNav $appNav The AppNav instance to add.
     * @return self Returns the current instance for method chaining.
     */
    public function add($appNav)
    {
        $this->navs[] = $appNav;
        return $this;
    }

    /**
     * Get a navigation item by its index.
     *
     * This method retrieves the AppNav instance at the specified index.
     *
     * @param integer $index The index of the navigation item to retrieve.
     * @return AppNav|null Returns the AppNav instance at the specified index, or null if the index is out of bounds.
     */
    public function item($index)
    {
        return isset($this->navs[$index]) ? $this->navs[$index] : null;
    }

    /**
     * Get all navigation items.
     *
     * This method returns the entire collection of AppNav instances.
     *
     * @return AppNav[] An array of AppNav instances.
     */ 
    public function getNavs()
    {
        return $this->navs;
    }
}
