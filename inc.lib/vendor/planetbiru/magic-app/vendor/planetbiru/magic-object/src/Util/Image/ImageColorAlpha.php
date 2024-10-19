<?php

namespace MagicObject\Util\Image;

namespace ByJG\ImageUtil;

use GdImage;
use MagicObject\Util\Image\ImageColor;

/**
 * Class ImageColorAlpha
 *
 * Represents a color with an alpha channel for transparency in images.
 *
 * Example:
 * ```php
 * $color = new ImageColorAlpha(255, 0, 0, 50); // Red with 50% opacity
 * ```
 *
 * @author Kamshory
 * @package MagicObject\Util\Image
 * @link https://github.com/Planetbiru/MagicObject
 */
class ImageColorAlpha extends ImageColor
{
    /**
     * The alpha value for the color (0 is fully opaque, 127 is fully transparent).
     *
     * @var int
     */
    protected $alpha;

    /**
     * ImageColorAlpha constructor.
     *
     * @param int $red The red component (0-255).
     * @param int $green The green component (0-255).
     * @param int $blue The blue component (0-255).
     * @param int $alpha The alpha component (0-127, defaults to 127).
     */
    public function __construct($red, $green, $blue, $alpha = 127)
    {
        $this->alpha = $alpha;
        parent::__construct($red, $green, $blue);
    }

    /**
     * Allocates the color in the given image resource.
     *
     * @param GdImage $image The image resource.
     * @return int The allocated color identifier.
     */
    public function allocate($image)
    {
        return imagecolorallocatealpha($image, $this->red, $this->green, $this->blue, $this->alpha);
    }

    /**
     * Gets the alpha value of the color.
     *
     * @return int The alpha value (0-127).
     */
    public function getAlpha()
    {
        return $this->alpha;
    }

    /**
     * Gets the RGBA representation of the color as a string.
     *
     * @return string The color in rgba format.
     */
    public function getRgba()
    {
        return sprintf("rgba(%d,%d,%d,%f)", $this->red, $this->green, $this->blue, $this->alpha);
    }
}