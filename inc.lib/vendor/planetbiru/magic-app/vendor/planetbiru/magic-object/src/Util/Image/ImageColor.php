<?php

namespace MagicObject\Util\Image;

use GdImage;

/**
 * Class ImageColor
 *
 * Represents an RGB color and provides methods for manipulating and retrieving
 * color information in various formats (hexadecimal, RGB). This class can
 * also allocate the color to a given GD image resource for rendering.
 * 
 * @author Kamshory
 * @package MagicObject\Util\Image
 * @link https://github.com/Planetbiru/MagicObject
 */
class ImageColor
{
    /**
     * Red component
     *
     * @var int
     */
    protected $red;

    /**
     * Green component
     *
     * @var int
     */
    protected $green;

    /**
     * Blue component
     *
     * @var int
     */
    protected $blue;

    /**
     * Constructor
     *
     * @param int $red   Red component
     * @param int $green Green component
     * @param int $blue  Blue component
     */
    public function __construct($red, $green, $blue)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }

    /**
     * Allocate color in the given image resource.
     *
     * @param GdImage $image The GD image resource.
     * @return int The color index of the allocated color.
     */
    public function allocate($image)
    {
        return imagecolorallocate($image, $this->red, $this->green, $this->blue);
    }

    /**
     * Get color in hexadecimal format.
     *
     * @return string The color represented as a hexadecimal string.
     */
    public function getHex()
    {
        return sprintf("#%02x%02x%02x", $this->red, $this->green, $this->blue);
    }

    /**
     * Get color in RGB format.
     *
     * @return string The color represented as an RGB string.
     */
    public function getRgb()
    {
        return sprintf("rgb(%d,%d,%d)", $this->red, $this->green, $this->blue);
    }

    /**
     * Get the red component of the color.
     *
     * @return int The red component.
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * Set the red component of the color.
     *
     * @param int $red The red component.
     * @return self Returns the current instance for method chaining.
     */
    public function setRed($red)
    {
        $this->red = $red;

        return $this;
    }

    /**
     * Get the green component of the color.
     *
     * @return int The green component.
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * Set the green component of the color.
     *
     * @param int $green The green component.
     * @return self Returns the current instance for method chaining.
     */
    public function setGreen($green)
    {
        $this->green = $green;

        return $this;
    }

    /**
     * Get the blue component of the color.
     *
     * @return int The blue component.
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * Set the blue component of the color.
     *
     * @param int $blue The blue component.
     * @return self Returns the current instance for method chaining.
     */
    public function setBlue($blue)
    {
        $this->blue = $blue;

        return $this;
    }
}
