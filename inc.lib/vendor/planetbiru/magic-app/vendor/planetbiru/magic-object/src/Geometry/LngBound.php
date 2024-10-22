<?php

namespace MagicObject\Geometry;

/**
 * Class LngBounds
 *
 * Represents a longitudinal bounding range defined by southwest and northeast longitude values.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class LngBounds
{
    /**
     * @var float The southwestern longitude value.
     */
    protected $_swLng; // NOSONAR

    /**
     * @var float The northeastern longitude value.
     */
    protected $_neLng; // NOSONAR

    /**
     * LngBounds constructor.
     *
     * @param float $swLng The southwestern longitude value.
     * @param float $neLng The northeastern longitude value.
     */
    public function __construct($swLng, $neLng) 
    {
        $swLng = $swLng == -180 && $neLng != 180 ? 180 : $swLng;
        $neLng = $neLng == -180 && $swLng != 180 ? 180 : $neLng;

        $this->_swLng = $swLng;
        $this->_neLng = $neLng;
    }

    /**
     * Get the southwestern longitude.
     *
     * @return float The southwestern longitude value.
     */
    public function getSw()
    {
        return $this->_swLng;
    }

    /**
     * Get the northeastern longitude.
     *
     * @return float The northeastern longitude value.
     */
    public function getNe()
    {
        return $this->_neLng;
    }

    /**
     * Get the midpoint of the longitude bounds.
     *
     * @return float The midpoint longitude value.
     */
    public function getMidpoint()
    {
        $midPoint = ($this->_swLng + $this->_neLng) / 2;

        if ($this->_swLng > $this->_neLng) 
        {
            $midPoint = SphericalGeometry::wrapLongitude($midPoint + 180);
        }

        return $midPoint;
    }

    /**
     * Check if the bounds are empty.
     *
     * @return bool True if the bounds are empty, false otherwise.
     */
    public function isEmpty()
    {
        return $this->_swLng - $this->_neLng == 360;
    }

    /**
     * Check if this LngBounds intersects with another LngBounds.
     *
     * @param LngBounds $LngBounds The LngBounds to check for intersection.
     * @return bool True if they intersect, false otherwise.
     */
    public function intersects($LngBounds) // NOSONAR
    {
        if ($this->isEmpty() || $LngBounds->isEmpty()) 
        {
            return false;
        } 
        else if ($this->_swLng > $this->_neLng) 
        {
            return $LngBounds->getSw() > $LngBounds->getNe() 
                || $LngBounds->getSw() <= $this->_neLng 
                || $LngBounds->getNe() >= $this->_swLng;
        } 
        else if ($LngBounds->getSw() > $LngBounds->getNe()) 
        {
            return $LngBounds->getSw() <= $this->_neLng || $LngBounds->getNe() >= $this->_swLng;
        } 
        else 
        {
            return $LngBounds->getSw() <= $this->_neLng && $LngBounds->getNe() >= $this->_swLng;
        }
    }

    /**
     * Check if this LngBounds is equal to another LngBounds.
     *
     * @param LngBounds $LngBounds The LngBounds object to compare.
     * @return bool True if they are equal, false otherwise.
     */
    public function equals($LngBounds)
    {
        return $this->isEmpty() 
            ? $LngBounds->isEmpty() 
            : fmod(abs($LngBounds->getSw() - $this->_swLng), 360) 
                + fmod(abs($LngBounds->getNe() - $this->_neLng), 360) 
                <= SphericalGeometry::EQUALS_MARGIN_ERROR;   
    }

    /**
     * Check if a given longitude is contained within the bounds.
     *
     * @param float $lng The longitude to check.
     * @return bool True if the longitude is contained, false otherwise.
     */
    public function contains($lng)
    {
        $lng = $lng == -180 ? 180 : $lng;

        return $this->_swLng > $this->_neLng 
            ? ($lng >= $this->_swLng || $lng <= $this->_neLng) && !$this->isEmpty()
            : $lng >= $this->_swLng && $lng <= $this->_neLng;
    }

    /**
     * Extend the bounds to include a new longitude.
     *
     * @param float $lng The longitude to extend the bounds with.
     * @return void
     */
    public function extend($lng)
    {
        if ($this->contains($lng)) 
        {
            return;
        }

        if ($this->isEmpty())
        {
            $this->_swLng = $this->_neLng = $lng;
        } 
        else 
        {
            $swLng = $this->_swLng - $lng;
            $swLng = $swLng >= 0 ? $swLng : $this->_swLng + 180 - ($lng - 180);
            $neLng = $lng - $this->_neLng;
            $neLng = $neLng >= 0 ? $neLng : $lng + 180 - ($this->_neLng - 180);

            if ($swLng < $neLng) 
            {
                $this->_swLng = $lng;
            } 
            else 
            {
                $this->_neLng = $lng;
            }
        }
    }
}
