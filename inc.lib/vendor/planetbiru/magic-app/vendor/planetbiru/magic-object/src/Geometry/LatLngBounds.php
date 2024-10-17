<?php

namespace MagicObject\Geometry;

/**
 * Class LatLngBounds
 *
 * Represents a bounding box defined by southwest and northeast LatLng objects.
 *
 * @package MagicObject\Geometry
 */
class LatLngBounds
{
    /**
     * @var LatBounds The latitude bounds of the bounding box.
     */
    protected $_LatBounds; // NOSONAR

    /**
     * @var LngBounds The longitude bounds of the bounding box.
     */
    protected $_LngBounds; // NOSONAR

    /**
     * LatLngBounds constructor.
     *
     * @param LatLng|null $LatLngSw The southwestern LatLng object.
     * @param LatLng|null $LatLngNe The northeastern LatLng object.
     *
     * @throws E_USER_ERROR If the provided LatLng objects are invalid.
     */
    public function __construct($LatLngSw = null, $LatLngNe = null) 
    {   
        if ((!is_null($LatLngSw) && !($LatLngSw instanceof LatLng))
            || (!is_null($LatLngNe) && !($LatLngNe instanceof LatLng)))
        {
            trigger_error('LatLngBounds class -> Invalid LatLng object.', E_USER_ERROR);
        }

        if ($LatLngSw && !$LatLngNe) 
        {
            $LatLngNe = $LatLngSw;
        }

        if ($LatLngSw)
        {
            $sw = SphericalGeometry::clampLatitude($LatLngSw->getLat());
            $ne = SphericalGeometry::clampLatitude($LatLngNe->getLat());
            $this->_LatBounds = new LatBounds($sw, $ne);

            $sw = $LatLngSw->getLng();
            $ne = $LatLngNe->getLng();

            if ($ne - $sw >= 360) 
            {
                $this->_LngBounds = new LngBounds(-180, 180);
            }
            else 
            {
                $sw = SphericalGeometry::wrapLongitude($LatLngSw->getLng());
                $ne = SphericalGeometry::wrapLongitude($LatLngNe->getLng());
                $this->_LngBounds = new LngBounds($sw, $ne);
            }
        } 
        else 
        {
            $this->_LatBounds = new LatBounds(1, -1);
            $this->_LngBounds = new LngBounds(180, -180);
        }
    }

    /**
     * Get the latitude bounds.
     *
     * @return LatBounds The latitude bounds of the bounding box.
     */
    public function getLatBounds()
    {
        return $this->_LatBounds;
    }

    /**
     * Get the longitude bounds.
     *
     * @return LngBounds The longitude bounds of the bounding box.
     */
    public function getLngBounds()
    {
        return $this->_LngBounds;
    }

    /**
     * Get the center point of the bounding box.
     *
     * @return LatLng The center point as a LatLng object.
     */
    public function getCenter()
    {
        return new LatLng($this->_LatBounds->getMidpoint(), $this->_LngBounds->getMidpoint());
    }

    /**
     * Check if the bounding box is empty.
     *
     * @return bool True if the bounding box is empty, false otherwise.
     */
    public function isEmpty()
    {
        return $this->_LatBounds->isEmpty() || $this->_LngBounds->isEmpty();
    }

    /**
     * Get the southwestern corner of the bounding box.
     *
     * @return LatLng The southwestern corner as a LatLng object.
     */
    public function getSouthWest()
    {
        return new LatLng($this->_LatBounds->getSw(), $this->_LngBounds->getSw(), true);
    }

    /**
     * Get the northeastern corner of the bounding box.
     *
     * @return LatLng The northeastern corner as a LatLng object.
     */
    public function getNorthEast()
    {
        return new LatLng($this->_LatBounds->getNe(), $this->_LngBounds->getNe(), true);
    }

    /**
     * Get the span of the bounding box as a LatLng object.
     *
     * @return LatLng The span defined by the latitude and longitude differences.
     */
    public function toSpan()
    {
        if ($this->_LatBounds->isEmpty()) {
            $lat = 0;
        } else {
            $lat = $this->_LatBounds->getNe() - $this->_LatBounds->getSw();
        }

        if ($this->_LngBounds->isEmpty()) {
            $lng = 0;
        } else {
            if ($this->_LngBounds->getSw() > $this->_LngBounds->getNe()) {
                $lng = 360 - ($this->_LngBounds->getSw() - $this->_LngBounds->getNe());
            } else {
                $lng = $this->_LngBounds->getNe() - $this->_LngBounds->getSw();
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
     * @param LatLngBounds $LatLngBounds The LatLngBounds object to compare.
     * @return bool True if they are equal, false otherwise.
     */
    public function equals($LatLngBounds)
    {
        return !$LatLngBounds 
            ? false 
            : $this->_LatBounds->equals($LatLngBounds->getLatBounds()) 
                && $this->_LngBounds->equals($LatLngBounds->getLngBounds());
    }

    /**
     * Check if this LatLngBounds intersects with another LatLngBounds.
     *
     * @param LatLngBounds $LatLngBounds The LatLngBounds to check for intersection.
     * @return bool True if they intersect, false otherwise.
     */
    public function intersects($LatLngBounds)
    {
        return $this->_LatBounds->intersects($LatLngBounds->getLatBounds()) 
            && $this->_LngBounds->intersects($LatLngBounds->getLngBounds());
    }

    /**
     * Extend this bounding box to include another LatLngBounds.
     *
     * @param LatLngBounds $LatLngBounds The LatLngBounds to extend with.
     * @return $this The current instance for method chaining.
     */
    public function union($LatLngBounds)
    {
        $this->extend($LatLngBounds->getSouthWest());
        $this->extend($LatLngBounds->getNorthEast());
        return $this;
    }

    /**
     * Check if this LatLngBounds contains a specific LatLng point.
     *
     * @param LatLng $LatLng The LatLng point to check for containment.
     * @return bool True if the point is contained, false otherwise.
     */
    public function contains($LatLng)
    {
        return $this->_LatBounds->contains($LatLng->getLat()) 
            && $this->_LngBounds->contains($LatLng->getLng());
    }

    /**
     * Extend the bounding box to include a new LatLng point.
     *
     * @param LatLng $LatLng The LatLng point to extend with.
     * @return $this The current instance for method chaining.
     */
    public function extend($LatLng)
    {
        $this->_LatBounds->extend($LatLng->getLat());
        $this->_LngBounds->extend($LatLng->getLng());
        return $this;    
    }
}
