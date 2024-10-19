<?php

namespace MagicObject\Geometry;

/**
 * Class representing a Line with two Point objects.
 *
 * This class defines a Line in a 2D space represented by two endpoints, Point A and Point B.
 * It provides methods to calculate the length of the line and ensure the endpoints are valid Point instances.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class Line {

    /**
     * Point A.
     *
     * @var Point
     */
    public $a;

    /**
     * Point B.
     *
     * @var Point
     */
    public $b;

    /**
     * Constructor to initialize the Line with two Point objects.
     *
     * @param Point $a Point A.
     * @param Point $b Point B.
     * @throws \InvalidArgumentException If the parameters are not instances of Point.
     */
    public function __construct($a, $b) {
        if (!$a instanceof Point || !$b instanceof Point) {
            throw new \InvalidArgumentException('Both parameters must be instances of Point.');
        }
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * Method to calculate the length of the line.
     *
     * This method calculates the Euclidean distance between Point A and Point B.
     *
     * @return float The length of the line between Point A and Point B.
     */
    public function getLength() {
        return $this->a->distanceFrom($this->b);
    }
}
