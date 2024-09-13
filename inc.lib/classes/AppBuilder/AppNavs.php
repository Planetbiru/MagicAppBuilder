<?php

namespace AppBuilder;

class AppNavs
{
    /**
     * Navs
     *
     * @var AppNav[]
     */
    private $navs = array();

    /**
     * Add nav
     *
     * @param AppNav $appNav
     * @return self
     */
    public function add($appNav)
    {
        $this->navs[] = $appNav;
        return $this;
    }

    /**
     * Get item
     *
     * @param integer $index
     * @return AppNav
     */
    public function item($index)
    {
        return $this->navs[$index];
    }

    /**
     * Get navs
     *
     * @return  AppNav[]
     */ 
    public function getNavs()
    {
        return $this->navs;
    }
}