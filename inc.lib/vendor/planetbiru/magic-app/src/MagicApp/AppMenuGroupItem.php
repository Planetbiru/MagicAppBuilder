<?php

namespace MagicApp;

class AppMenuGroupItem
{
    /**
     * Label
     *
     * @var string
     */
    private $label;
    
    /**
     * Class name
     *
     * @var string
     */
    private $class;
    /**
     * Menu item
     *
     * @var AppMenuItem[]
     */
    private $menuItem;

    /**
     * Get label
     *
     * @return string
     */ 
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string  $label  Label
     *
     * @return self
     */ 
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get class name
     *
     * @return string
     */ 
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set class name
     *
     * @param string  $class  Class name
     *
     * @return self
     */ 
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get menu item
     *
     * @return AppMenuItem[]
     */ 
    public function getMenuItem()
    {
        return $this->menuItem;
    }

    /**
     * Set menu item
     *
     * @param AppMenuItem[]  $menuItem  Menu item
     *
     * @return self
     */ 
    public function setMenuItem($menuItem)
    {
        $this->menuItem = $menuItem;

        return $this;
    }
}