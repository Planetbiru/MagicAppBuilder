<?php

namespace MagicObject\Geometry;

/**
 * Class Circle
 *
 * Represents a circle defined by a center point and a radius.
 * This class provides methods to calculate the circle's circumference 
 * and area, based on its radius. The circle is defined by its center 
 * coordinates (x, y) and its radius (r).
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class Circle
{
    /**
     * Center point of the circle.
     *
     * @var Point
     */
    public $center;

    /**
     * x coordinate of the circle's center.
     *
     * @var float
     */
    public $x = 0.0;

    /**
     * y coordinate of the circle's center.
     *
     * @var float
     */
    public $y = 0.0;

    /**
     * Radius of the circle.
     *
     * @var float
     */
    public $r = 0.0;

    /**
     * Constructor to initialize the Circle with x, y, and r.
     *
     * @param float $x x coordinate of the center
     * @param float $y y coordinate of the center
     * @param float $r Radius of the circle
     */
    public function __construct($x, $y, $r)
    {
        $this->x = $x;
        $this->y = $y;
        $this->r = $r;
        $this->center = new Point($x, $y);
    }

    /**
     * Get the circumference of the circle.
     *
     * @return float Circumference of the circle
     */
    public function getCircumference()
    {
        return 2 * pi() * $this->r;
    }

    /**
     * Get the area of the circle.
     *
     * @return float Area of the circle
     */
    public function getArea()
    {
        return pi() * $this->r * $this->r;
    }
}
