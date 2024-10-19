<?php

namespace MagicObject\Geometry;

/**
 * Class representing a triangle defined by three Point objects.
 *
 * This class represents a triangle in a 2D space using three vertices.
 * It provides methods to calculate the lengths of the sides and the area using Heron's formula.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class Triangle {

    /**
     * Vertex A of the triangle.
     *
     * @var Point
     */
    public $a;

    /**
     * Vertex B of the triangle.
     *
     * @var Point
     */
    public $b;

    /**
     * Vertex C of the triangle.
     *
     * @var Point
     */
    public $c;

    /**
     * Length of side opposite to vertex A.
     *
     * @var float
     */
    public $sa;

    /**
     * Length of side opposite to vertex B.
     *
     * @var float
     */
    public $sb;

    /**
     * Length of side opposite to vertex C.
     *
     * @var float
     */
    public $sc;

    /**
     * Constructor to initialize the Triangle with three Point objects.
     *
     * @param Point $a The first vertex of the triangle.
     * @param Point $b The second vertex of the triangle.
     * @param Point $c The third vertex of the triangle.
     */
    public function __construct($a, $b, $c) {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;

        // Calculate the lengths of the sides of the triangle
        $this->sa = $b->distanceFrom($c);
        $this->sb = $c->distanceFrom($a);
        $this->sc = $a->distanceFrom($b);
    }

    /**
     * Calculate the area of the triangle using Heron's formula.
     *
     * @return float The area of the triangle.
     */
    public function getArea() {
        $z = ($this->sa + $this->sb + $this->sc) / 2;
        return sqrt($z * ($z - $this->sa) * ($z - $this->sb) * ($z - $this->sc));
    }
}
