<?php

namespace MagicObject\Geometry;

/**
 * Class representing a Circle with center Point and radius
 */
class Circle {

    /**
     * Center
     *
     * @var Point
     */
    public $center;

    /**
     * x coordinate
     *
     * @var double
     */
    public $x = 0.0;

    /**
     * y coordinate
     *
     * @var double
     */
    public $y = 0.0;

    /**
     * r coordinate
     *
     * @var double
     */
    public $r = 0.0;

    /**
     * Constructor to initialize the Circle with x, y and r
     *
     * @param double $x x coordinate
     * @param double $y y Coordinate
     * @param double $r Radius
     */
    public function __construct($x, $y, $r) {
        $this->x = $x;
        $this->y = $y;
        $this->r = $r;
        $this->center = new Point($x, $y);
    }

    /**
     * Get circumference
     *
     * @return double
     */
    public function getCircumference()
    {
        return acos(-1) * 2 * $this->r;
    }

    /**
     * Get area
     *
     * @return double
     */
    public function getArea()
    {
        return acos(-1) * $this->r * $this->r;
    }
}