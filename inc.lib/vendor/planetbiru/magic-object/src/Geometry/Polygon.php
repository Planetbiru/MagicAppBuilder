<?php

namespace MagicObject\Geometry;

use MagicObject\Exceptions\InvalidPolygonException;

/**
 * Class representing a polygon defined by a series of points.
 *
 * This class allows for the creation and manipulation of polygons, including
 * adding points, clearing the polygon, and calculating its area and circumference.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class Polygon
{
    /**
     * Points that make up the polygon.
     *
     * @var Point[]
     */
    private $points = [];

    /**
     * Constructor to initialize the Polygon with an array of Points.
     *
     * @param Point[] $points Initial points for the polygon.
     */
    public function __construct(array $points = [])
    {
        $this->points = $points;
    }

    /**
     * Add a point to the polygon.
     *
     * @param Point $point Point to add.
     * @return self
     */
    public function addPoint(Point $point): self
    {
        $this->points[] = $point;
        return $this;
    }

    /**
     * Clear all points from the polygon.
     *
     * This method removes all points currently defined for the polygon.
     *
     * @return self
     */
    public function clearPolygon(): self
    {
        $this->points = [];
        return $this;
    }

    /**
     * Calculate the area of the polygon using the Shoelace formula.
     *
     * @return float The area of the polygon.
     * @throws InvalidPolygonException If the polygon has fewer than 3 points.
     */
    public function getArea(): float
    {
        $cnt = count($this->points);
        if ($cnt < 3) {
            throw new InvalidPolygonException("Invalid polygon. A polygon must have at least 3 points. $cnt given.");
        }

        $sum = 0;
        for ($i = 0; $i < $cnt; $i++) {
            $p1 = $this->points[$i];
            $p2 = $this->points[($i + 1) % $cnt]; // Wrap around to the first point
            $sum += ($p1->x * $p2->y) - ($p2->x * $p1->y);
        }

        return abs($sum) / 2;
    }

    /**
     * Calculate the circumference of the polygon.
     *
     * This method computes the total length of the polygon's edges.
     *
     * @return float The circumference of the polygon.
     * @throws InvalidPolygonException If the polygon has fewer than 2 points.
     */
    public function getCircumference(): float
    {
        $cnt = count($this->points);
        if ($cnt < 2) {
            throw new InvalidPolygonException("Invalid polygon. A polygon must have at least 2 points. $cnt given.");
        }

        $sum = 0;
        for ($i = 0; $i < $cnt; $i++) {
            $p1 = $this->points[$i];
            $p2 = $this->points[($i + 1) % $cnt]; // Wrap around to the first point
            $l = new Line($p1, $p2);
            $sum += $l->getLength();
        }

        return $sum;
    }

    /**
     * Get the points of the polygon.
     *
     * @return Point[] An array of Point objects that define the polygon.
     */
    public function getPoints(): array
    {
        return $this->points;
    }
}
