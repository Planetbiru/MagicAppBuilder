<?php

namespace MagicObject\Geometry;

/**
 * Class representing a Triangle with three Point objects
 */
class Triangle {

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
     * Point c
     *
     * @var Point
     */
    public $c;

    /**
     * Point sa
     *
     * @var double
     */
    public $sa;

    /**
     * Point sb
     *
     * @var double
     */
    public $sb;

    /**
     * Point sc
     *
     * @var double
     */
    public $sc;

    /**
     * Constructor to initialize the Triangle with three Point objects
     *
     * @param Point $a Point a
     * @param Point $b Point b
     * @param Point $c Point c
     */
    public function __construct(Point $a, Point $b, Point $c) {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;

        // Calculate the lengths of the sides of the triangle
        $this->sa = $b->distanceFrom($c);
        $this->sb = $c->distanceFrom($a);
        $this->sc = $a->distanceFrom($b);
    }

    /**
     * Method to calculate the area of the triangle using Heron's formula
     *
     * @return double
     */
    public function getArea() {
        $z = ($this->sa + $this->sb + $this->sc) / 2;
        return sqrt($z * ($z - $this->sa) * ($z - $this->sb) * ($z - $this->sc));
    }
}