<?php

namespace MagicObject\Geometry;

/**
 * Class representing a Line with two Point objects
 */
class Line {

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
     * Constructor to initialize the Line with two Point objects
     *
     * @param Point $a Point a
     * @param Point $b Point b
     */
    public function __construct(Point $a, Point $b) {
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * Method to calculate the length of the line
     *
     * @return double
     */
    public function getLength() {
        return $this->a->distanceFrom($this->a);
    }
}