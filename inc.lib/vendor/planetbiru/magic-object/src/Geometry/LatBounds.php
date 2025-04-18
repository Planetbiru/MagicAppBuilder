<?php

namespace MagicObject\Geometry;

/**
 * Class LatBounds
 *
 * Represents a latitude bounding box defined by southwestern and northeastern latitude values.
 * This class provides functionality to manage and calculate properties of latitude bounds,
 * including checking for containment, intersections, and equality with other latitude bounds.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class LatBounds
{
    /**
     * @var float The southwestern latitude value.
     */
    protected $_swLat; // NOSONAR

    /**
     * @var float The northeastern latitude value.
     */
    protected $_neLat; // NOSONAR

    /**
     * LatBounds constructor.
     *
     * @param float $swLat The southwestern latitude.
     * @param float $neLat The northeastern latitude.
     */
    public function __construct($swLat, $neLat) 
    {
        $this->_swLat = $swLat;
        $this->_neLat = $neLat;
    }

    /**
     * Get the southwestern latitude.
     *
     * @return float The southwestern latitude.
     */
    public function getSw()
    {
        return $this->_swLat;
    }

    /**
     * Get the northeastern latitude.
     *
     * @return float The northeastern latitude.
     */
    public function getNe()
    {
        return $this->_neLat;
    }

    /**
     * Calculate the midpoint latitude between the southwestern and northeastern latitudes.
     *
     * @return float The midpoint latitude.
     */
    public function getMidpoint()
    {
        return ($this->_swLat + $this->_neLat) / 2;
    }

    /**
     * Check if the latitude bounds are empty (i.e., invalid).
     *
     * @return bool true if the bounds are empty, false otherwise.
     */
    public function isEmpty()
    {
        return $this->_swLat > $this->_neLat;
    }

    /**
     * Determine if this LatBounds intersects with another LatBounds.
     *
     * @param LatBounds $latBounds The other LatBounds to check for intersection.
     * @return bool true if there is an intersection, false otherwise.
     */
    public function intersects($latBounds)
    {
        return $this->_swLat <= $latBounds->getSw() 
            ? $latBounds->getSw() <= $this->_neLat && $latBounds->getSw() <= $latBounds->getNe() 
            : $this->_swLat <= $latBounds->getNe() && $this->_swLat <= $this->_neLat;
    }

    /**
     * Check if this LatBounds is equal to another LatBounds within a certain margin of error.
     *
     * @param LatBounds $latBounds The other LatBounds to compare.
     * @return bool true if they are equal, false otherwise.
     */
    public function equals($latBounds)
    {
        return $this->isEmpty() 
            ? $latBounds->isEmpty() 
            : abs($latBounds->getSw() - $this->_swLat) 
                + abs($this->_neLat - $latBounds->getNe()) 
                <= SphericalGeometry::EQUALS_MARGIN_ERROR;
    }

    /**
     * Check if a given latitude is contained within the bounds.
     *
     * @param float $lat The latitude to check.
     * @return bool true if the latitude is contained, false otherwise.
     */
    public function contains($lat)
    {
        return $lat >= $this->_swLat && $lat <= $this->_neLat;
    }

    /**
     * Extend the bounds to include a new latitude.
     *
     * If the bounds are empty, the latitude becomes both the southwestern and northeastern bounds.
     * If the latitude is less than the southwestern bound, it updates the southwestern bound.
     * If the latitude is greater than the northeastern bound, it updates the northeastern bound.
     *
     * @param float $lat The latitude to extend the bounds with.
     */
    public function extend($lat)
    {
        if ($this->isEmpty()) 
        {
            $this->_neLat = $this->_swLat = $lat;
        }
        else if ($lat < $this->_swLat) 
        { 
            $this->_swLat = $lat;
        }
        else if ($lat > $this->_neLat) 
        {
            $this->_neLat = $lat;
        }
    }
}
