<?php

namespace MagicObject\Geometry;

use MagicObject\Exceptions\InvalidPolygonException;

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
     * @return float
     * @throws InvalidPolygonException
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
     * @return float
     * @throws InvalidPolygonException
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
     * @return Point[]
     */
    public function getPoints(): array
    {
        return $this->points;
    }
}
