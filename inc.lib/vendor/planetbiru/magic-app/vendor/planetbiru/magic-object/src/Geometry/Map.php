<?php

namespace MagicObject\Geometry;

class Map
{
    /**
     * Area
     *
     * @var Area[]
     */
    private $area = array();

    public function __construct($area = null)
    {
        if(isset($area) && is_array($area))
        {
            $this->area = $area;
        }
    }

    /**
     * Add area
     *
     * @param Area $area Area
     * @return self
     */
    public function addArea($area)
    {
        $this->area[] = $area;
        return $this;
    }

    /**
     * Get area
     *
     * @return  Area[]
     */
    public function getArea()
    {
        return $this->area;
    }
}