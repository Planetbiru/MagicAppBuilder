<?php

namespace MagicObject\Geometry;

/**
 * Class LatLngBounds
 *
 * Represents a bounding box defined by southwest and northeast LatLng objects.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class LatLngBounds
{
    /**
     * @var LatBounds The latitude bounds of the bounding box.
     */
    protected $_latBounds; // NOSONAR

    /**
     * @var LngBounds The longitude bounds of the bounding box.
     */
    protected $_lngBounds; // NOSONAR

    /**
     * LatLngBounds constructor.
     *
     * @param LatLng|null $latLngSw The southwestern LatLng object.
     * @param LatLng|null $tatLngNe The northeastern LatLng object.
     *
     * @throws E_USER_ERROR If the provided LatLng objects are invalid.
     */
    public function __construct($latLngSw = null, $tatLngNe = null) 
    {   
        if ((!is_null($latLngSw) && !($latLngSw instanceof LatLng))
            || (!is_null($tatLngNe) && !($tatLngNe instanceof LatLng)))
        {
            trigger_error('LatLngBounds class -> Invalid LatLng object.', E_USER_ERROR);
        }

        if ($latLngSw && !$tatLngNe) 
        {
            $tatLngNe = $latLngSw;
        }

        if ($latLngSw)
        {
            $sw = SphericalGeometry::clampLatitude($latLngSw->getLat());
            $ne = SphericalGeometry::clampLatitude($tatLngNe->getLat());
            $this->_latBounds = new LatBounds($sw, $ne);

            $sw = $latLngSw->getLng();
            $ne = $tatLngNe->getLng();

            if ($ne - $sw >= 360) 
            {
                $this->_lngBounds = new LngBounds(-180, 180);
            }
            else 
            {
                $sw = SphericalGeometry::wrapLongitude($latLngSw->getLng());
                $ne = SphericalGeometry::wrapLongitude($tatLngNe->getLng());
                $this->_lngBounds = new LngBounds($sw, $ne);
            }
        } 
        else 
        {
            $this->_latBounds = new LatBounds(1, -1);
            $this->_lngBounds = new LngBounds(180, -180);
        }
    }

    /**
     * Get the latitude bounds.
     *
     * @return LatBounds The latitude bounds of the bounding box.
     */
    public function getLatBounds()
    {
        return $this->_latBounds;
    }

    /**
     * Get the longitude bounds.
     *
     * @return LngBounds The longitude bounds of the bounding box.
     */
    public function getLngBounds()
    {
        return $this->_lngBounds;
    }

    /**
     * Get the center point of the bounding box.
     *
     * @return LatLng The center point as a LatLng object.
     */
    public function getCenter()
    {
        return new LatLng($this->_latBounds->getMidpoint(), $this->_lngBounds->getMidpoint());
    }

    /**
     * Check if the bounding box is empty.
     *
     * @return bool true if the bounding box is empty, false otherwise.
     */
    public function isEmpty()
    {
        return $this->_latBounds->isEmpty() || $this->_lngBounds->isEmpty();
    }

    /**
     * Get the southwestern corner of the bounding box.
     *
     * @return LatLng The southwestern corner as a LatLng object.
     */
    public function getSouthWest()
    {
        return new LatLng($this->_latBounds->getSw(), $this->_lngBounds->getSw(), true);
    }

    /**
     * Get the northeastern corner of the bounding box.
     *
     * @return LatLng The northeastern corner as a LatLng object.
     */
    public function getNorthEast()
    {
        return new LatLng($this->_latBounds->getNe(), $this->_lngBounds->getNe(), true);
    }

    /**
     * Get the span of the bounding box as a LatLng object.
     *
     * @return LatLng The span defined by the latitude and longitude differences.
     */
    public function toSpan()
    {
        if ($this->_latBounds->isEmpty()) {
            $lat = 0;
        } else {
            $lat = $this->_latBounds->getNe() - $this->_latBounds->getSw();
        }

        if ($this->_lngBounds->isEmpty()) {
            $lng = 0;
        } else {
            if ($this->_lngBounds->getSw() > $this->_lngBounds->getNe()) {
                $lng = 360 - ($this->_lngBounds->getSw() - $this->_lngBounds->getNe());
            } else {
                $lng = $this->_lngBounds->getNe() - $this->_lngBounds->getSw();
            }
        }

        return new LatLng($lat, $lng, true);
    }


    /**
     * Convert the bounding box to a string representation.
     *
     * @return string The string representation of the bounding box.
     */
    public function toString()
    {
        return '('. $this->getSouthWest()->toString() .', '. $this->getNorthEast()->toString() .')';
    }

    /**
     * Convert the bounding box to a URL-friendly string value.
     *
     * @param int $precision The number of decimal places to round to.
     * @return string The southwest and northeast corner values as a string.
     */
    public function toUrlValue($precision = 6)
    {
        return $this->getSouthWest()->toUrlValue($precision) .','. 
            $this->getNorthEast()->toUrlValue($precision);
    }

    /**
     * Check if this LatLngBounds is equal to another LatLngBounds object.
     *
     * @param LatLngBounds $latLngBounds The LatLngBounds object to compare.
     * @return bool true if they are equal, false otherwise.
     */
    public function equals($latLngBounds)
    {
        return !$latLngBounds 
            ? false 
            : $this->_latBounds->equals($latLngBounds->getLatBounds()) 
                && $this->_lngBounds->equals($latLngBounds->getLngBounds());
    }

    /**
     * Check if this LatLngBounds intersects with another LatLngBounds.
     *
     * @param LatLngBounds $latLngBounds The LatLngBounds to check for intersection.
     * @return bool true if they intersect, false otherwise.
     */
    public function intersects($latLngBounds)
    {
        return $this->_latBounds->intersects($latLngBounds->getLatBounds()) 
            && $this->_lngBounds->intersects($latLngBounds->getLngBounds());
    }

    /**
     * Extend this bounding box to include another LatLngBounds.
     *
     * @param LatLngBounds $latLngBounds The LatLngBounds to extend with.
     * @return $this The current instance for method chaining.
     */
    public function union($latLngBounds)
    {
        $this->extend($latLngBounds->getSouthWest());
        $this->extend($latLngBounds->getNorthEast());
        return $this;
    }

    /**
     * Check if this LatLngBounds contains a specific LatLng point.
     *
     * @param LatLng $latLng The LatLng point to check for containment.
     * @return bool true if the point is contained, false otherwise.
     */
    public function contains($latLng)
    {
        return $this->_latBounds->contains($latLng->getLat()) 
            && $this->_lngBounds->contains($latLng->getLng());
    }

    /**
     * Extend the bounding box to include a new LatLng point.
     *
     * @param LatLng $latLng The LatLng point to extend with.
     * @return $this The current instance for method chaining.
     */
    public function extend($latLng)
    {
        $this->_latBounds->extend($latLng->getLat());
        $this->_lngBounds->extend($latLng->getLng());
        return $this;    
    }
}
