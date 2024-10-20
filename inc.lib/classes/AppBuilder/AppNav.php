<?php

namespace AppBuilder;

/**
 * Class AppNav
 *
 * Represents a navigation item in the application.
 * This class provides properties for a unique identifier, 
 * display caption, and active state of the navigation item.
 */
class AppNav
{
    /**
     * Unique identifier for the navigation item.
     *
     * @var string
     */
    private $key = "";

    /**
     * Display caption for the navigation item.
     *
     * @var string
     */
    private $caption = "";

    /**
     * Indicates whether the navigation item is active.
     *
     * @var boolean
     */
    private $active = false;

    /**
     * Constructor for the AppNav class.
     *
     * Initializes the navigation item with a key, caption, and active state.
     *
     * @param string $key The unique identifier for the navigation item.
     * @param string $caption The display text for the navigation item.
     * @param boolean $active (optional) Indicates if the navigation item is active. Default is false.
     */
    public function __construct($key, $caption, $active = false)
    {
        $this->key = $key;
        $this->caption = $caption;
        $this->active = $active;
    }

    /**
     * Get the value of the key.
     *
     * @return string The unique identifier for the navigation item.
     */ 
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the value of the key.
     *
     * @param string $key The new unique identifier for the navigation item.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the value of the caption.
     *
     * @return string The display text for the navigation item.
     */ 
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set the value of the caption.
     *
     * @param string $caption The new display text for the navigation item.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get the value of the active state.
     *
     * @return boolean True if the navigation item is active, false otherwise.
     */ 
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of the active state.
     *
     * @param boolean $active Indicates if the navigation item should be active.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}
