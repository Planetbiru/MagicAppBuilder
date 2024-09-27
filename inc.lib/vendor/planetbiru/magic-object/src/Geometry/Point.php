<?php

namespace MagicObject\Geometry;

/**
 * Class representing a Point with x and y coordinates
 */
class Point {
    /**
     * X coordinate
     *
     * @var double
     */
    public $x = 0.0;

    /**
     * Y coordinate
     *
     * @var double
     */
    public $y = 0.0;

    /**
     * Constructor to initialize the Point with x and y coordinates
     *
     * @param double $x x coordinate
     * @param double $y y coordinate
     */
    public function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * Method to calculate the distance between two Point objects
     *
     * @param self $p Point
     * @return double
     */
    public function distanceFrom(Point $p) {
        $dx = $this->x - $p->x;
        $dy = $this->y - $p->y;
        return sqrt($dx * $dx + $dy * $dy);
    }
}