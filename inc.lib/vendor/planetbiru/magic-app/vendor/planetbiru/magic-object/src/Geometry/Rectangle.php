<?php

namespace MagicObject\Geometry;

use MagicObject\Exceptions\InvalidPolygonException;

/**
 * Class representing a rectangle defined by two Point objects.
 *
 * This class represents a rectangle using its bottom-left and top-right corner points.
 * It provides methods to calculate the area and circumference (perimeter) of the rectangle
 * by utilizing a Polygon representation of the rectangle's corners.
 */
class Rectangle {

    /**
     * Bottom-left corner point of the rectangle.
     *
     * @var Point
     */
    public $a;

    /**
     * Top-right corner point of the rectangle.
     *
     * @var Point
     */
    public $b;

    /**
     * Polygon representation of the rectangle.
     *
     * @var Polygon
     */
    public $polygon;

    /**
     * Constructor to initialize the Rectangle with two Point objects.
     *
     * @param Point $a Bottom-left corner point.
     * @param Point $b Top-right corner point.
     */
    public function __construct($a, $b) {
        // Initialize the polygon
        $this->polygon = new Polygon();

        // Assign the points
        $this->a = $a;
        $this->b = $b;

        // Define the rectangle's corners
        $point1 = new Point($this->a->x, $this->a->y);
        $point2 = new Point($this->b->x, $this->a->y);
        $point3 = new Point($this->b->x, $this->b->y);
        $point4 = new Point($this->a->x, $this->b->y);

        // Add the points to the polygon
        $this->polygon
            ->addPoint($point1)
            ->addPoint($point2)
            ->addPoint($point3)
            ->addPoint($point4);
    }

    /**
     * Calculate the area of the rectangle.
     *
     * @return float The area of the rectangle.
     * @throws InvalidPolygonException If the polygon is invalid.
     */
    public function getArea() {
        return $this->polygon->getArea();
    }

    /**
     * Get the circumference (perimeter) of the rectangle.
     *
     * @return float The circumference of the rectangle.
     * @throws InvalidPolygonException If the polygon is invalid.
     */
    public function getCircumference() {
        return $this->polygon->getCircumference();
    }
}
