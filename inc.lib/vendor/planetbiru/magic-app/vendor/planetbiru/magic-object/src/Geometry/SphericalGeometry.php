<?php

namespace MagicObject\Geometry;

/*!
 * Spherical Geometry PHP Library v1.1
 * http://tubalmartin.github.com/spherical-geometry-php/
 *
 * Copyright 2012, Túbal Martín
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * This code is a port of some classes from the Google Maps Javascript API version 3.x
 */

/**
 * Static class SphericalGeometry
 *
 * Provides utility functions for computing geodesic angles, distances, areas, and other spherical geometry calculations.
 *
 * @package MagicObject\Geometry
 */
class SphericalGeometry
{
    /**
     * @var float The margin of error for equality checks in calculations.
     */
    const EQUALS_MARGIN_ERROR = 1.0E-9;

    /**
     * @var float The Earth's radius at the equator, in meters.
     */
    const EARTH_RADIUS = 6378137; 

    /**
     * Get the Earth's radius.
     *
     * @return float The Earth's radius in meters.
     */
    public static function getEarthRadius()
    {
        return self::EARTH_RADIUS;
    }

    /**
     * Computes a bounding rectangle (LatLngBounds instance) from a point and a given radius.
     *
     * Reference: http://www.movable-type.co.uk/scripts/latlong-db.html
     *
     * @param LatLng $latLng The center point as a LatLng object.
     * @param int|float $radius The radius in meters.
     * @return LatLngBounds The computed bounding rectangle.
     */
    public static function computeBounds($latLng, $radius)
    {
        $latRadiansDistance = $radius / self::EARTH_RADIUS;
        $latDegreesDistance = rad2deg($latRadiansDistance);
        $lngDegreesDistance = rad2deg($latRadiansDistance / cos(deg2rad($latLng->getLat())));

        // SW point
        $swLat = $latLng->getLat() - $latDegreesDistance;
        $swLng = $latLng->getLng() - $lngDegreesDistance;
        $sw = new LatLng($swLat, $swLng);
        
        // NE point
        $neLat = $latLng->getLat() + $latDegreesDistance;
        $neLng = $latLng->getLng() + $lngDegreesDistance;
        $ne = new LatLng($neLat, $neLng);

        return new LatLngBounds($sw, $ne);
    }

    /**
     * Computes the heading from one LatLng to another.
     *
     * @param LatLng $fromLatLng The starting LatLng.
     * @param LatLng $toLatLng The destination LatLng.
     * @return float The heading in degrees from the starting point to the destination.
     */
    public static function computeHeading($fromLatLng, $toLatLng)
    {
        $fromLat = deg2rad($fromLatLng->getLat());
        $toLat = deg2rad($toLatLng->getLat());
        $lng = deg2rad($toLatLng->getLng()) - deg2rad($fromLatLng->getLng());

        $wrap = self::wrapLongitude(rad2deg(atan2(sin($lng) * cos($toLat), cos($fromLat) 
            * sin($toLat) - sin($fromLat) * cos($toLat) * cos($lng))));
        return ($wrap + 360) % 360;
    }

    /**
     * Computes a new LatLng based on a starting point, distance, and heading.
     *
     * @param LatLng $fromLatLng The starting LatLng.
     * @param float $distance The distance to move, in meters.
     * @param float $heading The direction to move, in degrees.
     * @return LatLng The new LatLng after the offset.
     */
    public static function computeOffset($fromLatLng, $distance, $heading) 
    {
        $distance /= self::EARTH_RADIUS;
        $heading = deg2rad($heading);
        $fromLat = deg2rad($fromLatLng->getLat());
        $cosDistance = cos($distance);
        $sinDistance = sin($distance);
        $sinFromLat = sin($fromLat);
        $cosFromLat = cos($fromLat);
        $sc = $cosDistance * $sinFromLat + $sinDistance * $cosFromLat * cos($heading);
        
        $lat = rad2deg(asin($sc));
        $lng = rad2deg(deg2rad($fromLatLng->getLng()) + atan2($sinDistance * $cosFromLat 
            * sin($heading), $cosDistance - $sinFromLat * $sc));
        
        return new LatLng($lat, $lng);
    }

    /**
     * Determines whether a point is contained within a polygon.
     *
     * @param object $point A point object with x() and y() methods for longitude and latitude.
     * @param object $polygon A polygon object with an exteriorRing() method.
     * @return bool True if the point is inside the polygon, false otherwise.
     */
    public static function containsLocation($point, $polygon) {
        $vertices_x = array();    // x-coordinates of the vertices of the polygon
        $vertices_y = array();   // y-coordinates of the vertices of the polygon
        $longitude_x = $point->x();  // x-coordinate of the point to test
        $latitude_y = $point->y();    // y-coordinate of the point to test

        $polygon_array = $polygon->exteriorRing()->getComponents();
        foreach ($polygon_array as $value) {
            array_push($vertices_x, $value->x());
            array_push($vertices_y, $value->y());
        }
        // number vertices - zero-based array
        $points_polygon = count($vertices_x) - 1;
        $i = $j = $c = 0;
        for ($i = 0, $j = $points_polygon ; $i < $points_polygon; $j = $i++) {
            if ((($vertices_y[$i] > $latitude_y) != ($vertices_y[$j] > $latitude_y)) &&
                ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i])) {
                $c = !$c;
            }
        }
        return $c;
    }

    /**
     * Interpolates between two LatLng points based on a fraction.
     *
     * @param LatLng $fromLatLng The starting LatLng.
     * @param LatLng $toLatLng The ending LatLng.
     * @param float $fraction A fraction between 0 and 1.
     * @return LatLng The interpolated LatLng.
     */
    public static function interpolate($fromLatLng, $toLatLng, $fraction)
    {
        $radFromLat = deg2rad($fromLatLng->getLat());
        $radFromLng = deg2rad($fromLatLng->getLng());
        $radToLat = deg2rad($toLatLng->getLat());
        $radToLng = deg2rad($toLatLng->getLng());
        $cosFromLat = cos($radFromLat);
        $cosToLat = cos($radToLat);
        $radDist = self::_computeDistanceInRadiansBetween($fromLatLng, $toLatLng);
        $sinRadDist = sin($radDist);
        
        if ($sinRadDist < 1.0E-6) {
            return new LatLng($fromLatLng->getLat(), $fromLatLng->getLng());
        }
        
        $a = sin((1 - $fraction) * $radDist) / $sinRadDist;
        $b = sin($fraction * $radDist) / $sinRadDist;
        $c = $a * $cosFromLat * cos($radFromLng) + $b * $cosToLat * cos($radToLng);
        $d = $a * $cosFromLat * sin($radFromLng) + $b * $cosToLat * sin($radToLng);
        
        $lat = rad2deg(atan2($a * sin($radFromLat) + $b * sin($radToLat), sqrt(pow($c,2) + pow($d,2))));
        $lng = rad2deg(atan2($d, $c));
        
        return new LatLng($lat, $lng);
    }

    /**
     * Computes the distance between two LatLng points.
     *
     * @param LatLng $latLng1 The first LatLng point.
     * @param LatLng $latLng2 The second LatLng point.
     * @return float The distance in yards.
     */
    public static function computeDistanceBetween($latLng1, $latLng2)
    {
        return self::_computeDistanceInRadiansBetween($latLng1, $latLng2) * self::EARTH_RADIUS * 1.09361;
    }

    /**
     * Computes the total length of a series of LatLng points.
     *
     * @param LatLng[] $latLngsArray An array of LatLng points.
     * @return float The total length in yards.
     */
    public static function computeLength($latLngsArray) 
    {
        $length = 0;
        
        for ($i = 0, $l = count($latLngsArray) - 1; $i < $l; ++$i) {
            $length += self::computeDistanceBetween($latLngsArray[$i], $latLngsArray[$i + 1]);
        }    
        
        return $length;
    }

    /**
     * Computes the area of a polygon defined by a series of LatLng points.
     *
     * @param LatLng[] $latLngsArray An array of LatLng points defining the polygon.
     * @return float The area in square meters.
     */
    public static function computeArea($latLngsArray)
    {
        return abs(self::computeSignedArea($latLngsArray, false));
    }

    /**
     * Computes the signed area of a polygon defined by a series of LatLng points.
     *
     * @param LatLng[] $latLngsArray An array of LatLng points defining the polygon.
     * @param bool $signed Whether to return a signed area.
     * @return float The signed area in square meters.
     */
    public static function computeSignedArea($latLngsArray, $signed = true)
    {
        if (empty($latLngsArray) || count($latLngsArray) < 3) return 0;
        
        $e = 0;
        $r2 = pow(self::EARTH_RADIUS, 2);
        
        for ($i = 1, $l = count($latLngsArray) - 1; $i < $l; ++$i) {
            $e += self::_computeSphericalExcess($latLngsArray[0], $latLngsArray[$i], $latLngsArray[$i + 1], $signed);
        }
           
        return $e * $r2;
    }

    /**
     * Clamps a latitude value to be within valid bounds.
     *
     * @param float $lat The latitude to clamp.
     * @return float The clamped latitude value.
     */
    public static function clampLatitude($lat)
    {
        return min(max($lat, -90), 90); 
    }

    /**
     * Wraps a longitude value to be within valid bounds.
     *
     * @param float $lng The longitude to wrap.
     * @return float The wrapped longitude value.
     */
    public static function wrapLongitude($lng)
    {
        return fmod((fmod(($lng - -180), 360) + 360), 360) + -180;
    }

    /**
     * Computes the great circle distance (in radians) between two points.
     * Uses the Haversine formula.
     *
     * @param LatLng $latLng1 The first LatLng point.
     * @param LatLng $latLng2 The second LatLng point.
     * @return float The distance in radians.
     */
    protected static function _computeDistanceInRadiansBetween($latLng1, $latLng2)
    {
        $p1RadLat = deg2rad($latLng1->getLat());
        $p1RadLng = deg2rad($latLng1->getLng());
        $p2RadLat = deg2rad($latLng2->getLat());
        $p2RadLng = deg2rad($latLng2->getLng());
        return 2 * asin(sqrt(pow(sin(($p1RadLat - $p2RadLat) / 2), 2) + cos($p1RadLat) 
            * cos($p2RadLat) * pow(sin(($p1RadLng - $p2RadLng) / 2), 2)));
    }

    /**
     * Computes the spherical excess using L'Huilier's Theorem.
     *
     * @param LatLng $latLng1 The first vertex of the triangle.
     * @param LatLng $latLng2 The second vertex of the triangle.
     * @param LatLng $latLng3 The third vertex of the triangle.
     * @param bool $signed Whether to return a signed value.
     * @return float The spherical excess.
     */
    protected static function _computeSphericalExcess($latLng1, $latLng2, $latLng3, $signed)
    {
        $latLngsArray = array($latLng1, $latLng2, $latLng3, $latLng1);
        $distances = array();
        $sumOfDistances = 0;
        
        for ($i = 0; $i < 3; ++$i) {
            $distances[$i] = self::_computeDistanceInRadiansBetween($latLngsArray[$i], $latLngsArray[$i + 1]);
            $sumOfDistances += $distances[$i];
        }
            
        $semiPerimeter = $sumOfDistances / 2;
        $tan = tan($semiPerimeter / 2);
        
        for ($i = 0; $i < 3; ++$i) { 
            $tan *= tan(($semiPerimeter - $distances[$i]) / 2);
        }
            
        $sphericalExcess = 4 * atan(sqrt(abs($tan)));
        
        if (!$signed) {
            return $sphericalExcess;
        }
        
        // Negative or positive sign?
        array_pop($latLngsArray);
        
        $v = array();
        
        for ($i = 0; $i < 3; ++$i) { 
            $latLng = $latLngsArray[$i];
            $lat = deg2rad($latLng->getLat());
            $lng = deg2rad($latLng->getLng());
            
            $v[$i] = array();
            $v[$i][0] = cos($lat) * cos($lng);
            $v[$i][1] = cos($lat) * sin($lng);
            $v[$i][2] = sin($lat);
        }
        
        $sign = ($v[0][0] * $v[1][1] * $v[2][2] 
            + $v[1][0] * $v[2][1] * $v[0][2] 
            + $v[2][0] * $v[0][1] * $v[1][2] 
            - $v[0][0] * $v[2][1] * $v[1][2] 
            - $v[1][0] * $v[0][1] * $v[2][2] 
            - $v[2][0] * $v[1][1] * $v[0][2]) > 0 ? 1 : -1;
            
        return $sphericalExcess * $sign;
    }
}
