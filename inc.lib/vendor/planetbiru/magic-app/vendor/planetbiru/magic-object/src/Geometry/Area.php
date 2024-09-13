<?php

namespace MagicObject\Geometry;

class Area
{
    /**
     * Shape
     *
     * @var string
     */
    public $shape;
    /**
     * Coordinates
     *
     * @var float[]
     */
    public $coords = array();

    /**
     * Href
     *
     * @var string
     */
    public $href;

    /**
     * Zoom
     *
     * @var float
     */
    public $zoom = 1;

    /**
     * Constructor
     *
     * @param Rectangle|Triangle|Polygon|Circle $object One of Rectangle, Triangle, Polygon, or Circle
     * @param float $zoom Zoom
     * @param string $href Href
     */
    public function __construct($object, $zoom = 1, $href = null)
    {
        if(isset($href))
        {
            $this->href = $href;
        }
        $this->zoom = $zoom;
        if($object instanceof Rectangle)
        {
            $this->shape = "rect";
            $this->coords = $this->coordsFromRectangle($object);
        }
        if($object instanceof Triangle)
        {
            $this->shape = "poly";
            $this->coords = $this->coordsFromTriangle($object);
        }
        if($object instanceof Polygon)
        {
            $this->shape = "poly";
            $this->coords = $this->coordsFromPolygon($object);
        }
        if($object instanceof Circle)
        {
            $this->shape = "circle";
            $this->coords = $this->coordsFromCircle($object);
        }
    }

    /**
     * Get rectangle coordinates
     *
     * @param Rectangle $object
     * @return float[]
     */
    public function coordsFromRectangle($object)
    {
        return array(
            $object->a->x,
            $object->a->y,
            $object->b->x,
            $object->b->y
        );
    }

    /**
     * Get triangle coordinates
     *
     * @param Triangle $object
     * @return float[]
     */
    public function coordsFromTriangle($object)
    {
        return array(
            $object->a->x,
            $object->a->y,
            $object->b->x,
            $object->b->y,
            $object->c->x,
            $object->c->y
        );
    }

    /**
     * Get Polygon coordinates
     *
     * @param Polygon $object
     * @return float[]
     */
    public function coordsFromPolygon($object)
    {
        $points = $object->getPoints();
        $str = array();
        foreach($points as $coords)
        {
            $str[] = $coords->x;
            $str[] = $coords->y;
        }
        return $str;
    }

    /**
     * Get circle info
     *
     * @param Circle $object
     * @return float[]
     */
    public function coordsFromCircle($object)
    {
        return array($object->x, $object->y, $object->r);
    }

    /**
     * Get coordinates
     *
     * @param float $zoom
     * @return float[]
     */
    public function getCoords($zoom = 1)
    {
        if($zoom == 1)
        {
            return $this->coords;
        }
        if($zoom < 0)
        {
            $zoom = abs($zoom);
        }
        return array_map(function($value) use ($zoom) {
            return $value * $zoom;
        }, $this->coords);
    }

    /**
     * Get HTML
     *
     * @return string
     */
    public function getHTML()
    {
        $attrs = array();
        $attrs[] = 'shape="'.$this->shape.'"';
        $attrs[] = 'coords="'.implode(", ", $this->getCoords($this->zoom)).'"';
        if(isset($this->href))
        {
            $attrs[] = 'href="'.$this->href.'"';
        }
        return '<area '.implode(' ', $attrs).' />';
    }

    /**
     * To String
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getHTML();
    }

    /**
     * Get the value of zoom
     */
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
     * Set the value of zoom
     *
     * @return self
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;
        return $this;
    }
}