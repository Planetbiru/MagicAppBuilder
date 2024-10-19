<?php

namespace MagicObject\Geometry;

/**
 * Class representing a Map containing multiple Areas.
 *
 * This class manages a collection of Area objects, allowing for the addition and retrieval of areas.
 * It can be initialized with an optional array of Area objects.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class Map
{
    /**
     * Areas in the map.
     *
     * @var Area[]
     */
    private $areas = [];

    /**
     * Constructor to initialize the Map with optional areas.
     *
     * @param Area[]|null $areas An array of Area objects to initialize the map with.
     */
    public function __construct(array $areas = null)
    {
        if (isset($areas) && is_array($areas)) {
            $this->areas = $areas;
        }
    }

    /**
     * Add an area to the map.
     *
     * This method appends a new Area object to the map's collection of areas.
     *
     * @param Area $area Area to add
     * @return self
     */
    public function addArea(Area $area)
    {
        $this->areas[] = $area;
        return $this;
    }

    /**
     * Get all areas in the map.
     *
     * This method returns an array of Area objects contained in the map.
     *
     * @return Area[] An array of Area objects
     */
    public function getAreas()
    {
        return $this->areas;
    }
}
