<?php

namespace MagicObject\Geometry;

/**
 * Class Area
 *
 * Represents a geometric area defined by various shapes such as 
 * rectangles, triangles, polygons, and circles. This class encapsulates 
 * properties related to the shape, its coordinates, and any associated 
 * attributes or hyperlinks. It also provides methods to calculate 
 * coordinates based on the shape type, apply zoom factors, and generate 
 * an HTML representation for use in maps or similar applications.
 * 
 * @author Kamshory
 * @package MagicObject\Geometry
 * @link https://github.com/Planetbiru/MagicObject
 */
class Area
{
    /**
     * Shape type of the area.
     *
     * @var string
     */
    public $shape;

    /**
     * Coordinates defining the shape.
     *
     * @var float[]
     */
    public $coords = array();

    /**
     * Hyperlink reference associated with the area.
     *
     * @var string|null
     */
    public $href;

    /**
     * Additional attributes for the area.
     *
     * @var string[]
     */
    public $attributes;

    /**
     * Zoom factor for the area.
     *
     * @var float
     */
    public $zoom = 1;

    /**
     * Constructor for the Area class.
     *
     * @param mixed $object One of Rectangle, Triangle, Polygon, or Circle
     * @param float $zoom Zoom factor (default: 1)
     * @param string|null $href Hyperlink reference (optional)
     * @param string[]|null $attributes Additional attributes (optional)
     */
    public function __construct($object, $zoom = 1, $href = null, $attributes = null)
    {
        $this->href = $href;
        $this->attributes = is_array($attributes) ? $attributes : [];
        $this->zoom = $zoom;

        if ($object instanceof Rectangle) {
            $this->shape = "rect";
            $this->coords = $this->coordsFromRectangle($object);
        } elseif ($object instanceof Triangle) {
            $this->shape = "poly";
            $this->coords = $this->coordsFromTriangle($object);
        } elseif ($object instanceof Polygon) {
            $this->shape = "poly";
            $this->coords = $this->coordsFromPolygon($object);
        } elseif ($object instanceof Circle) {
            $this->shape = "circle";
            $this->coords = $this->coordsFromCircle($object);
        }
    }

    /**
     * Get coordinates from a rectangle.
     *
     * @param Rectangle $object Rectangle object
     * @return float[] Coordinates of the rectangle
     */
    public function coordsFromRectangle($object)
    {
        return [
            $object->a->x,
            $object->a->y,
            $object->b->x,
            $object->b->y
        ];
    }

    /**
     * Get coordinates from a triangle.
     *
     * @param Triangle $object Triangle object
     * @return float[] Coordinates of the triangle
     */
    public function coordsFromTriangle($object)
    {
        return [
            $object->a->x,
            $object->a->y,
            $object->b->x,
            $object->b->y,
            $object->c->x,
            $object->c->y
        ];
    }

    /**
     * Get coordinates from a polygon.
     *
     * @param Polygon $object Polygon object
     * @return float[] Coordinates of the polygon
     */
    public function coordsFromPolygon($object)
    {
        $points = $object->getPoints();
        $coords = array();
        foreach ($points as $point) {
            $coords[] = $point->x;
            $coords[] = $point->y;
        }
        return $coords;
    }

    /**
     * Get coordinates from a circle.
     *
     * @param Circle $object Circle object
     * @return float[] Coordinates of the circle
     */
    public function coordsFromCircle($object)
    {
        return [$object->x, $object->y, $object->r];
    }

    /**
     * Get coordinates with optional zoom factor.
     *
     * @param float $zoom Zoom factor (default: 1)
     * @return float[] Adjusted coordinates
     */
    public function getCoords($zoom = 1)
    {
        if ($zoom <= 0) {
            $zoom = abs($zoom);
        }
        return array_map(function($value) use ($zoom) {
            return $value * $zoom;
        }, $this->coords);
    }

    /**
     * Generate HTML representation of the area.
     *
     * @return string HTML string for the area
     */
    public function getHTML()
    {
        $attrs = array();
        $attrs[] = 'shape="' . $this->shape . '"';
        $attrs[] = 'coords="' . implode(", ", $this->getCoords($this->zoom)) . '"';

        if (isset($this->href)) {
            $attrs[] = 'href="' . $this->href . '"';
        }

        if (isset($this->attributes) && is_array($this->attributes)) {
            foreach ($this->attributes as $key => $value) {
                $attrs[] = $key . '="' . $value . '"';
            }
        }

        return '<area ' . implode(' ', $attrs) . ' />';
    }

    /**
     * Convert the area object to a string.
     *
     * @return string HTML representation of the area
     */
    public function __toString()
    {
        return $this->getHTML();
    }

    /**
     * Get the current zoom factor.
     *
     * @return float Zoom factor
     */
    public function getZoom()
    {
        return $this->zoom;
    }

    /**
     * Set the zoom factor.
     *
     * @param float $zoom Zoom factor
     * @return self Returns the current instance for method chaining.
     */
    public function setZoom($zoom)
    {
        $this->zoom = $zoom;
        return $this;
    }
}
