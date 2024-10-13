<?php

namespace MagicObject\Geometry;

/**
 * Class representing a Map containing multiple Areas.
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
     * @param Area[]|null $areas An array of Area objects
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
     * @return Area[] An array of Area objects
     */
    public function getAreas()
    {
        return $this->areas;
    }
}
