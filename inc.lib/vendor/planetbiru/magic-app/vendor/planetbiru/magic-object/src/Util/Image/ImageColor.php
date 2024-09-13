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
     * @param integer $red Red component
     * @param integer $green Green component
     * @param integer $blue Blue component
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
     * @return integer
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
     * @return integer
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * Set red component
     *
     * @param integer  $red  Red component
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
     * @return integer
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * Set green component
     *
     * @param integer  $green  Green component
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
     * @return integer
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * Set blue component
     *
     * @param integer  $blue  Blue component
     *
     * @return self
     */
    public function setBlue($blue)
    {
        $this->blue = $blue;

        return $this;
    }
}