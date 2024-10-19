<?php

namespace MagicObject\Geometry;

/**
 * Class LatLng
 *
 * Represents a geographical point defined by latitude and longitude values.
 * This class provides methods to manage and manipulate geographic coordinates,
 * including equality checks and string representations.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class LatLng
{
    /**
     * @var float The latitude value.
     */
    protected $_lat; // NOSONAR

    /**
     * @var float The longitude value.
     */
    protected $_lng; // NOSONAR

    /**
     * LatLng constructor.
     *
     * @param float $lat The latitude value.
     * @param float $lng The longitude value.
     * @param bool $noWrap Whether to wrap the longitude value.
     *
     * @throws E_USER_ERROR If the latitude or longitude is not a valid float.
     */
    public function __construct($lat, $lng, $noWrap = false)
    {
        $lat = (float) $lat;
        $lng = (float) $lng;

        if (is_nan($lat) || is_nan($lng)) {
            trigger_error('LatLng class -> Invalid float numbers: ('. $lat .', '. $lng .')', E_USER_ERROR);
        }

        if ($noWrap !== true) {
            $lat = SphericalGeometry::clampLatitude($lat);
            $lng = SphericalGeometry::wrapLongitude($lng);
        }

        $this->_lat = $lat;
        $this->_lng = $lng;
    }

    /**
     * Get the latitude value.
     *
     * @return float The latitude.
     */
    public function getLat()
    {
        return $this->_lat;
    }

    /**
     * Get the longitude value.
     *
     * @return float The longitude.
     */
    public function getLng()
    {
        return $this->_lng;
    }

    /**
     * Check if this LatLng is equal to another LatLng object within a certain margin of error.
     *
     * @param LatLng $LatLng The LatLng object to compare.
     * @return bool True if they are equal, false otherwise.
     */
    public function equals($LatLng)
    {
        if (!is_object($LatLng) || !($LatLng instanceof self)) {
            return false;
        }

        return abs($this->_lat - $LatLng->getLat()) <= SphericalGeometry::EQUALS_MARGIN_ERROR 
            && abs($this->_lng - $LatLng->getLng()) <= SphericalGeometry::EQUALS_MARGIN_ERROR;             
    }

    /**
     * Convert the LatLng object to a string representation.
     *
     * @return string The string representation of the LatLng in the format "(lat, lng)".
     */
    public function toString()
    {
        return '('. $this->_lat .', '. $this->_lng .')';
    }

    /**
     * Convert the LatLng object to a URL-friendly string value.
     *
     * @param int $precision The number of decimal places to round to (default: 6).
     * @return string The latitude and longitude values as a string.
     */
    public function toUrlValue($precision = 6)
    {
        $precision = (int) $precision;
        return round($this->_lat, $precision) .','. round($this->_lng, $precision);
    }
}
