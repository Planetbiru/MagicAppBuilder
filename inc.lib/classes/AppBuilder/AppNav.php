<?php

namespace AppBuilder;

class AppNav
{
    /**
     * Key
     *
     * @var string
     */
    private $key = "";
    /**
     * Caption
     *
     * @var string
     */
    private $caption = "";
    /**
     * ACtive
     *
     * @var boolean
     */
    private $active = false;

    /**
     * Cunstructor
     *
     * @param string $key
     * @param string $caption
     */
    public function __construct($key, $caption, $active = false)
    {
        $this->key = $key;
        $this->caption = $caption;
        $this->active = $active;
    }

    /**
     * Get the value of key
     */ 
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set the value of key
     *
     * @return  self
     */ 
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get the value of caption
     */ 
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set the value of caption
     *
     * @return  self
     */ 
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get the value of active
     */ 
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the value of active
     *
     * @return  self
     */ 
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }
}