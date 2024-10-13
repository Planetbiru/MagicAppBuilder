<?php

namespace MagicObject\Util\Image;

use GdImage;

class ImageColor
{
    /**
     * Red component
     *
     * @var integer
     */
    protected $red;

    /**
     * Green component
     *
     * @var integer
     */
    protected $green;

    /**
     * Blue component
     *
     * @var integer
     */
    protected $blue;

    /**
     * Constructor
     *
     * @param int $red Red component
     * @param int $green Green component
     * @param int $blue Blue component
     */
    public function __construct($red, $green, $blue)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
    }


    /**
     * Allocate image
     *
     * @param GdImage $image
     * @return int
     */
    public function allocate($image)
    {
        return imagecolorallocate($image, $this->red, $this->green, $this->blue);
    }

    /**
     * Get color in hexadecimal format
     *
     * @return string
     */
    public function getHex()
    {
        return sprintf("#%02x%02x%02x", $this->red, $this->green, $this->blue);
    }

    /**
     * Get color in RGB format
     *
     * @return string
     */
    public function getRgb()
    {
        return sprintf("rgb(%d,%d,%d)", $this->red, $this->green, $this->blue);
    }

    /**
     * Get red component
     *
     * @return int
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * Set red component
     *
     * @param int  $red  Red component
     *
     * @return self
     */
    public function setRed($red)
    {
        $this->red = $red;

        return $this;
    }

    /**
     * Get green component
     *
     * @return int
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * Set green component
     *
     * @param int  $green  Green component
     *
     * @return self
     */
    public function setGreen($green)
    {
        $this->green = $green;

        return $this;
    }

    /**
     * Get blue component
     *
     * @return int
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * Set blue component
     *
     * @param int  $blue  Blue component
     *
     * @return self
     */
    public function setBlue($blue)
    {
        $this->blue = $blue;

        return $this;
    }
}