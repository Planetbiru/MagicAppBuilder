<?php

namespace MagicObject\Geometry;

use MagicObject\Exceptions\InvalidPolygonException;

class Polygon
{
    /**
     * Points
     *
     * @var Point[]
     */
    private $points = array();

    /**
     * Constructor
     *
     * @param Point[] $poins
     */
    public function __construct($poins = null)
    {
        if(isset($poins) && is_array($poins))
        {
            $this->points = $poins;
        }
    }

    /**
     * Add point
     *
     * @param Point $point
     * @return self
     */
    public function addPoint($point)
    {
        $this->points[] = $point;
        return $this;
    }

    /**
     * Clear polygon
     *
     * @return self
     */
    public function clearPolygon()
    {
        $this->points = array();
        return $this;
    }

    /**
     * Function to calculate the area of a polygon using the Shoelace formula
     *
     * @return double
     * @throws InvalidPolygonException
     */
    public function getArea() {
        $cnt = count($this->points);
        if($cnt < 3)
        {
            throw new InvalidPolygonException("Invalid polygon. Polygon at least has 3 points. $cnt given.");
        }
        // Initialize variables for the sum and the origin point
        $sum = 0;
        $o = $this->points[0];

        // Loop through the points to calculate the area of the polygon
        for ($i = 1; $i < count($this->points) - 1; $i++) {
            $p1 = $this->points[$i];
            $p2 = $this->points[$i+1];

            // Create a Triangle object using the origin and two consecutive points
            $t = new Triangle($o, $p1, $p2);

            // Add the area of the triangle to the total sum
            $sum += $t->getArea();
        }
        return $sum;
    }

    /**
     * Get circumference
     *
     * @return double
     * @throws InvalidPolygonException
     */
    public function getCircumference()
    {
        $cnt = count($this->points);
        if($cnt < 2)
        {
            throw new InvalidPolygonException("Invalid polygon. Polygon at least has 3 points. $cnt given.");
        }
        // Initialize variables for the sum and the origin point
        $sum = 0;

        // Loop through the points to calculate the area of the polygon
        for ($i = 0; $i < count($this->points) - 1; $i++) {
            $p1 = $this->points[$i];
            $p2 = $this->points[$i+1];

            // Create a Triangle object using the origin and two consecutive points
            $l = new Line($p1, $p2);

            // Add the area of the triangle to the total sum
            $sum += $l->getLength();
        }
        return $sum;
    }

    /**
     * Get points
     *
     * @return Point[]
     */
    public function getPoints()
    {
        return $this->points;
    }
}