<?php

namespace MagicObject\Util\Image;

namespace ByJG\ImageUtil;

use MagicObject\Util\Image\ImageColor;

class ImageColorAlpha extends ImageColor
{
    protected $alpha;

    public function __construct($red, $green, $blue, $alpha = 127)
    {
        $this->alpha = $alpha;
        parent::__construct($red, $green, $blue);
    }

    public function allocate($image)
    {
        return imagecolorallocatealpha($image, $this->red, $this->green, $this->blue, $this->alpha);
    }

    public function getAlpha()
    {
        return $this->alpha;
    }

    public function getRgba()
    {
        return sprintf("rgba(%d,%d,%d,%f)", $this->red, $this->green, $this->blue, $this->alpha);
    }
}