<?php

namespace MagicObject\Geometry;

/**
 * Class representing a Point with x and y coordinates.
 */
class Point
{
    /**
     * X coordinate.
     *
     * @var float
     */
    public $x = 0.0;

    /**
     * Y coordinate.
     *
     * @var float
     */
    public $y = 0.0;

    /**
     * Constructor to initialize the Point with x and y coordinates.
     *
     * @param float $x X coordinate.
     * @param float $y Y coordinate.
     */
    public function __construct($x, $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Calculate the distance between this Point and another Point.
     *
     * @param Point $p Another Point.
     * @return float The distance between the two Points.
     */
    public function distanceFrom($p)
    {
        // Ensure $p is an instance of Point
        if (!$p instanceof Point) {
            throw new \InvalidArgumentException('Argument must be of type Point.');
        }

        $dx = $this->x - $p->x;
        $dy = $this->y - $p->y;
        return sqrt($dx * $dx + $dy * $dy);
    }

    /**
     * Calculate the distance between this Point and another Point.
     *
     * @param Point $p Another Point.
     * @return float The distance between the two Points.
     */
    public function distanceTo($p)
    {
        return $this->distanceFrom($p);
    }
}
