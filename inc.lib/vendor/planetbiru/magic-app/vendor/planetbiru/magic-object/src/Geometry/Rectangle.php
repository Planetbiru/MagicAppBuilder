<?php

namespace MagicObject\Geometry;

use MagicObject\Exceptions\InvalidPolygonException;

/**
 * Class representing a Line with two Point objects
 */
class Rectangle {

    /**
     * Point a
     *
     * @var Point
     */
    public $a;

    /**
     * Point b
     *
     * @var Point
     */
    public $b;

    /**
     * Polygon
     *
     * @var Polygon
     */
    public $polygon;

    /**
     * Constructor to initialize the Rectangle with two Point objects
     *
     * @param Point $a Point a
     * @param Point $b Point b
     */
    public function __construct(Point $a, Point $b) {

        if(!isset($this->polygon))
        {
            $this->polygon = new Polygon();
        }

        $this->a = $a;
        $this->b = $b;

        $point1 = new Point($this->a->x, $this->a->y);
        $point2 = new Point($this->b->x, $this->a->y);
        $point3 = new Point($this->b->x, $this->b->y);
        $point4 = new Point($this->a->x, $this->b->y);

        $this->polygon
            ->addPoint($point1)
            ->addPoint($point2)
            ->addPoint($point3)
            ->addPoint($point4)
            ;
    }

    /**
     * Function to calculate the area of a polygon using the Shoelace formula
     *
     * @return double
     * @throws InvalidPolygonException
     */
    public function getArea()
    {
        return $this->polygon->getArea();
    }

    /**
     * Get circumference
     *
     * @return double
     * @throws InvalidPolygonException
     */
    public function getCircumference()
    {
        return $this->polygon->getCircumference();
    }
}