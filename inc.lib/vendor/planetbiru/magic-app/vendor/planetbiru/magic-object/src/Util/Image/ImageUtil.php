<?php

namespace MagicObject\Util\Image;

use ByJG\ImageUtil\ImageColorAlpha;
use GdImage;
use MagicObject\Exceptions\FileNotFoundException;
use MagicObject\Exceptions\InvalidParameterException;
use MagicObject\Util\File\FileUtil;

/**
 * A Wrapper for GD library in PHP. GD must be installed in your system for this to work.
 * Example: $img = new Image('wheel.png');
 *          $img->flip(1)->resize(120, 0)->save('wheel.jpg');
 */
class ImageUtil
{

    const FLIP_HORIZONTAL = 1;
    const FLIP_VERTICAL = 2;
    const FLIP_BOTH = 3;

    const STAMP_POSITION_TOPRIGHT = 1;
    const STAMP_POSITION_TOPLEFT = 2;
    const STAMP_POSITION_BOTTOMRIGHT = 3;
    const STAMP_POSITION_BOTTOMLEFT = 4;
    const STAMP_POSITION_CENTER = 5;
    const STAMP_POSITION_TOP = 6;
    const STAMP_POSITION_BOTTOM = 7;
    const STAMP_POSITION_LEFT = 8;
    const STAMP_POSITION_RIGHT = 9;
    const STAMP_POSITION_RANDOM = 999;

    const TEXT_ALIGN_LEFT = 1;
    const TEXT_ALIGN_RIGHT = 2;
    const TEXT_ALIGN_CENTER = 3;

    private $fileName;
    private $info;

    /**
     * Image
     *
     * @var GdImage
     */
    private $image;

    /**
     * Image
     *
     * @var GdImage
     */
    private $orgImage;

    /**
     * Image width
     *
     * @var integer
     */
    protected $width;

    /**
     * Image height
     *
     * @var integer
     */
    protected $height;

    /**
     * Empty image
     *
     * @param int $width
     * @param int $height
     * @param ImageColor|null $color
     * @return self
     */
    public static function empty($width, $height, $color = null)
    {
        $image = imagecreatetruecolor($width, $height);
        if (!empty($color)) {
            $fill = $color->allocate($image);
            imagefill($image, 0, 0, $fill);
        }

        return new ImageUtil($image);
    }

    /**
     * Construct an Image Handler based on an image resource or file name
     *
     * @param string|resource $imageFile The path or URL to image or the image resource.
     * @throws ImageUtilException
     * @throws NotFoundException
     */
    public function __construct($imageFile)
    {
        if (!function_exists('imagecreatefrompng')) {
            throw new ImageUtilException("GD module is not installed");
        }

        if (!is_string($imageFile)) {
            $info = $this->createFromResource($imageFile);
        } else {
            $info = $this->createFromFilename($imageFile);
        }

        if (is_null($this->image)) {
            throw new ImageUtilException("'$imageFile' is not a valid image file");
        }

        $this->info = $info;
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);

        $this->orgImage = imagecreatetruecolor($this->getWidth(), $this->getHeight());
        imagecopy($this->orgImage, $this->image, 0, 0, 0, 0, $this->getWidth(), $this->getHeight());
    }

    /**
     * @param $resource
     * @return array
     * @throws ImageUtilException
     */
    protected function createFromResource($resource)
    {
        if (is_resource($resource) || $resource instanceof GdImage) {
            $this->image = $resource;
            $this->retainTransparency();
            $this->fileName = sys_get_temp_dir() . '/img_' . uniqid() . '.png';

            return ['mime' => 'image/png'];
        }
        throw new ImageUtilException('Is not valid resource');
    }

    /**
     * @param $imageFile
     * @return array
     * @throws NotFoundException
     * @throws ImageUtilException
     */
    protected function createFromFilename($imageFile)
    {
        $imageFile = FileUtil::fixFilePath($imageFile);
        if (!file_exists($imageFile) || !is_readable($imageFile)) {
            throw new FileNotFoundException("File is not found or not is readable. Cannot continue.");
        }

        $this->fileName = $imageFile;
        $img = getimagesize($imageFile);
        if (empty($img)) {
            throw new ImageUtilException("Invalid file: " . $imageFile);
        }
        $this->image = imagecreatefromstring(file_get_contents($imageFile));
        $this->retainTransparency();
        return $img;
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }

    /**
     * Get width
     *
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }

    /**
     * Get file name
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->fileName;
    }

    /**
     * Retain transparency
     *
     * @param GdImage $image
     * @return void
     */
    protected function retainTransparency($image = null)
    {
        if (empty($image)) {
            $image = $this->image;
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }

    /**
     * Enter description here...
     *
     * @return GdImage
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Rotates the image to any direction using the given angle.
     * Arguments: $angle - The rotation angle, in degrees.
     * Example: $img = new Image("file.png"); $img->rotate(180); $img->show(); // Turn the image upside down.
     *
     * @param float $angle
     * @param int $background
     * @return $this
     */
    public function rotate($angle, $background = 0)
    {
        if (!is_numeric($angle)) {
            throw new InvalidParameterException('You need to pass the angle');
        }

        $this->retainTransparency();
        $this->image = imagerotate($this->image, $angle, $background);

        return $this;
    }

    /**
     * Mirrors the given image in the desired way.
     * Example: $img = new Image("file.png"); $img->flip(2); $img->show();
     *
     * @param int $type Direction of mirroring. This can be 1(Horizondal Flip), 2(Vertical Flip) or 3(Both Horizondal
     *     and Vertical Flip)
     * @return ImageUtil
     */
    public function flip($type)
    {
        if ($type !== self::FLIP_HORIZONTAL && $type !== self::FLIP_VERTICAL && $type !== self::FLIP_BOTH) {
            throw new InvalidParameterException('You need to pass the flip type');
        }

        $width = $this->getWidth();
        $height = $this->getHeight();
        $imgdest = imagecreatetruecolor($width, $height);
        $this->retainTransparency($imgdest);
        $imgsrc = $this->image;

        switch ($type) {
            //Mirroring direction
            case self::FLIP_HORIZONTAL:
                for ($x = 0; $x < $width; $x++) {
                    imagecopy($imgdest, $imgsrc, $width - $x - 1, 0, $x, 0, 1, $height);
                }
                break;

            case self::FLIP_VERTICAL:
                for ($y = 0; $y < $height; $y++) {
                    imagecopy($imgdest, $imgsrc, 0, $height - $y - 1, 0, $y, $width, 1);
                }
                break;

            default:
                for ($x = 0; $x < $width; $x++) {
                    imagecopy($imgdest, $imgsrc, $width - $x - 1, 0, $x, 0, 1, $height);
                }

                $rowBuffer = imagecreatetruecolor($width, 1);
                for ($y = 0; $y < ($height / 2); $y++) {
                    imagecopy($rowBuffer, $imgdest, 0, 0, 0, $height - $y - 1, $width, 1);
                    imagecopy($imgdest, $imgdest, 0, $height - $y - 1, 0, $y, $width, 1);
                    imagecopy($imgdest, $rowBuffer, 0, $y, 0, 0, $width, 1);
                }

                imagedestroy($rowBuffer);
                break;
        }

        $this->image = $imgdest;

        return $this;
    }

    /**
     * Resize the image to an new size. Size can be specified in the arugments.
     *
     * @param int $newWidth The width of the desired image. If 0, the function will automatically calculate the width
     *     using the height ratio.
     * @param int $newHeight The width of the desired image. If 0, the function will automatically calculate the value
     *     using the width ratio.
     * @return ImageUtil
     */
    public function resize($newWidth = null, $newHeight = null)
    {
        if (!is_numeric($newHeight) && !is_numeric($newWidth)) {
            throw new InvalidParameterException('There are no valid values');
        }

        $height = $this->getHeight();
        $width = $this->getWidth();

        //If the width or height is give as 0, find the correct ratio using the other value
        if (!$newHeight && $newWidth) {
            $newHeight = $height * $newWidth / $width; //Get the new height in the correct ratio
        }

        if ($newHeight && !$newWidth) {
            $newWidth = $width * $newHeight / $height; //Get the new width in the correct ratio
        }


        //Create the image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        $this->retainTransparency($newImage);
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, intval($newWidth), intval($newHeight), $width, $height);

        $this->image = $newImage;

        return $this;
    }

    /**
     * Resize the image in a square format and maintain the aspect ratio. The space are filled the RGB color provided.
     *
     * @param int $newSize The new size of desired image (width and height are equals)
     * @param int $fillRed
     * @param int $fillGreen
     * @param int $fillBlue
     * @return ImageUtil
     * @throws ImageUtilException
     */
    public function resizeSquare($newSize, ImageColor $color = null)
    {
        return $this->resizeAspectRatio($newSize, $newSize, $color);
    }

    /**
     * Resize the image but the aspect ratio is respected. The spaces left are filled with the RGB color provided.
     *
     * @param int $newX
     * @param int $newY
     * @param int $fillRed
     * @param int $fillGreen
     * @param int $fillBlue
     * @return ImageUtil
     * @throws ImageUtilException
     */
    public function resizeAspectRatio($newX, $newY, ImageColor $color = null)
    {
        if (empty($color)) {
            $color = new ImageColorAlpha(255, 255, 255, 127);
        }

        if (!is_numeric($newX) || !is_numeric($newY)) {
            throw new ImageUtilException('There are no valid values');
        }

        $image = $this->image;

        $width = $this->getWidth();
        $height = $this->getHeight();

        $ratio = $width / $height;
        $newRatio = $newX / $newY;

        if ($newRatio > $ratio) {
            $newWidth = $newY * $ratio;
            $newHeight = $newY;
        } else {
            $newHeight = $newX / $ratio;
            $newWidth = $newX;
        }

        $newImage = imagecreatetruecolor($newX, $newY);
        $this->retainTransparency($newImage);
        imagefill($newImage, 0, 0, $color->allocate($newImage));

        imagecopyresampled(
            $newImage,
            $image,
            intval(($newX - $newWidth) / 2),
            intval(($newY - $newHeight) / 2),
            0,
            0,
            intval($newWidth),
            intval($newHeight),
            $width,
            $height
        );

        $this->image = $newImage;

        return $this;
    }

    /**
     * Stamp an image in the current image.
     *
     * @param ImageUtil|string $srcImage The image path or the image gd resource.
     * @param int $position
     * @param int $padding
     * @param int $oppacity
     * @return ImageUtil
     * @throws ImageUtilException
     * @throws NotFoundException
     */
    public function stampImage($srcImage, $position = self::STAMP_POSITION_BOTTOMRIGHT, $padding = 5, $oppacity = 100)
    {
        $dstImage = $this->image;

        if ($srcImage instanceof ImageUtil) {
            $imageUtil = $srcImage;
        } else {
            $imageUtil = new ImageUtil($srcImage);
        }

        $watermark = $imageUtil->getImage();

        $this->retainTransparency($dstImage);
        $this->retainTransparency($watermark);

        $dstWidth = imagesx($dstImage);
        $dstHeight = imagesy($dstImage);
        $srcWIdth = imagesx($watermark);
        $srcHeight = imagesy($watermark);

        if (is_array($padding)) {
            $padx = $padding[0];
            $pady = $padding[1];
        } else {
            $padx = $pady = $padding;
        }

        if ($position == self::STAMP_POSITION_RANDOM) {
            $position = rand(1, 9);
        }
        switch ($position) {
            case self::STAMP_POSITION_TOPRIGHT:
                $dstX = ($dstWidth - $srcWIdth) - $padx;
                $dstY = $pady;
                break;
            case self::STAMP_POSITION_TOPLEFT:
                $dstX = $padx;
                $dstY = $pady;
                break;
            case self::STAMP_POSITION_BOTTOMRIGHT:
                $dstX = ($dstWidth - $srcWIdth) - $padx;
                $dstY = ($dstHeight - $srcHeight) - $pady;
                break;
            case self::STAMP_POSITION_BOTTOMLEFT:
                $dstX = $padx;
                $dstY = ($dstHeight - $srcHeight) - $pady;
                break;
            case self::STAMP_POSITION_CENTER:
                $dstX = (($dstWidth / 2) - ($srcWIdth / 2));
                $dstY = (($dstHeight / 2) - ($srcHeight / 2));
                break;
            case self::STAMP_POSITION_TOP:
                $dstX = (($dstWidth / 2) - ($srcWIdth / 2));
                $dstY = $pady;
                break;
            case self::STAMP_POSITION_BOTTOM:
                $dstX = (($dstWidth / 2) - ($srcWIdth / 2));
                $dstY = ($dstHeight - $srcHeight) - $pady;
                break;
            case self::STAMP_POSITION_LEFT:
                $dstX = $padx;
                $dstY = (($dstHeight / 2) - ($srcHeight / 2));
                break;
            case self::STAMP_POSITION_RIGHT:
                $dstX = ($dstWidth - $srcWIdth) - $padx;
                $dstY = (($dstHeight / 2) - ($srcHeight / 2));
                break;
            default:
                throw new ImageUtilException('Invalid Stamp Position');
        }

        imagecopymerge($dstImage, $watermark, $dstX, $dstY, 0, 0, $srcWIdth, $srcHeight, $oppacity);
        $this->image = $dstImage;

        return $this;
    }

    /**
     * Writes a text on the image.
     *
     * @param string $text
     * @param float[] $point
     * @param float $size
     * @param float $angle
     * @param string $font
     * @param int $maxwidth
     * @param float[] $rgbAr
     * @param int $textAlignment
     * @throws ImageUtilException
     */
    public function writeText($text, $point, $size, $angle, $font, $maxwidth = 0, $rgbAr = null, $textAlignment = 1) //NOSONAR
    {
        if (!is_readable($font)) {
            throw new ImageUtilException('Error: The specified font not found');
        }

        if (!is_array($rgbAr)) {
            $rgbAr = [0, 0, 0];
        }

        $color = imagecolorallocate($this->image, $rgbAr[0], $rgbAr[1], $rgbAr[2]);

        // Determine the line break if required.
        if (($maxwidth > 0) && ($angle == 0)) {
            $words = explode(' ', $text);
            $lines = [$words[0]];
            $currentLine = 0;

            $numberOfWords = count($words);
            for ($i = 1; $i < $numberOfWords; $i++) {
                $lineSize = imagettfbbox($size, 0, $font, $lines[$currentLine] . ' ' . $words[$i]);
                if ($lineSize[2] - $lineSize[0] < $maxwidth) {
                    $lines[$currentLine] .= ' ' . $words[$i];
                } else {
                    $currentLine++;
                    $lines[$currentLine] = $words[$i];
                }
            }
        } else {
            $lines = [$text];
        }

        $curX = $point[0];
        $curY = $point[1];

        foreach ($lines as $text) {
            $bbox = imagettfbbox($size, $angle, $font, $text);

            switch ($textAlignment) {
                case self::TEXT_ALIGN_RIGHT:
                    $curX = $point[0] - abs($bbox[2] - $bbox[0]);
                    break;

                case self::TEXT_ALIGN_CENTER:
                    $curX = $point[0] - (abs($bbox[2] - $bbox[0]) / 2);
                    break;
                default:
                break;
            }

            imagettftext($this->image, $size, $angle, $curX, $curY, $color, $font, $text);

            $curY += ($size * 1.35);
        }
    }

    /**
     * Crops the given image from the ($from_x,$from_y) point to the ($to_x,$to_y) point.
     * Example: $img -> crop(250,200,400,250);
     *
     * @param float $fromX X coordinate from where the crop should start
     * @param float $fromY Y coordinate from where the crop should start
     * @param float $toX X coordinate from where the crop should end
     * @param float $toY Y coordinate from where the crop should end
     * @return ImageUtil
     */
    public function crop($fromX, $fromY, $toX, $toY)
    {
        $newWidth = $toX - $fromX;
        $newHeight = $toY - $fromY;
        //Create the image
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        $this->retainTransparency($newImage);
        imagecopy($newImage, $this->image, 0, 0, $fromX, $fromY, $newWidth, $newHeight);
        $this->image = $newImage;

        return $this;
    }

    /**
     * Discard any changes made to the image and restore the original state
     */
    public function restore()
    {
        $this->image = imagecreatetruecolor(imagesx($this->orgImage), imagesy($this->orgImage));
        imagecopy($this->image, $this->orgImage, 0, 0, 0, 0, imagesx($this->orgImage), imagesy($this->orgImage));
        $this->retainTransparency();

        return $this;
    }

    /**
     * Make transparent the image. The transparent color must be provided
     *
     * @param int $transpRed
     * @param int $transpGreen
     * @param int $transpBlue
     * @return ImageUtil|GdImage|resource The image util object
     */
    public function makeTransparent(ImageColor $color = null, $image = null)
    {
        if (empty($color)) {
            $color = new ImageColor(255, 255, 255);
        }

        $customImage = true;
        if (empty($image) && !is_resource($image) && !($image instanceof GdImage)) {
            $image = $this->image;
            $customImage = false;
        }

        // Get image dimensions
        $width = imagesx($image);
        $height = imagesy($image);

        // Define the black color
        $transparentColor = imagecolorallocate($image, $color->getRed(), $color->getGreen(), $color->getBlue());

        // Loop through each pixel and make black background transparent
        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $colorAt = imagecolorat($image, $x, $y);

                if ($colorAt === $transparentColor) {
                    imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $color->getRed(), $color->getGreen(), $color->getBlue(), 127));
                }
            }
        }

        // Enable alpha blending and save the modified image
        imagealphablending($image, true);
        imagesavealpha($image, true);

        if ($customImage) {
            return $image;
        } else {
            $this->image = $image;
        }

        return $this;
    }

    /**
     * Destroy the image to save the memory. Do this after all operations are complete.
     */
    public function __destruct()
    {
        if (!is_null($this->image)) {
            imagedestroy($this->image);
            imagedestroy($this->orgImage);
            unset($this->image);
            unset($this->orgImage);
        }
    }
}