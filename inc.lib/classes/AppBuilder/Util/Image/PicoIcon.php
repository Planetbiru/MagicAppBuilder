<?php

namespace AppBuilder\Util\Image;

use GdImage;

/**
 * PicoIcon class is used to generate and manage ICO (icon) files from base64 image data.
 *
 * This class allows you to add images (from base64 strings) to an ICO file, resize them, 
 * and save the resulting ICO file to a location on the disk.
 * The class requires PHP's GD library to function properly.
 */
class PicoIcon {
    private $images = array(); // NOSONAR
    private $hasRequirements = false; // NOSONAR

    /**
     * Constructor for PicoIcon class.
     *
     * This constructor checks if the required GD functions are available. If an image 
     * data is provided, it adds the image to the ICO generator.
     *
     * @param string[]|null $imageData Array of base64 encoded image data to add to the ICO. Default is null.
     */
    public function __construct($imageData = null) {
        $requiredFunctions = array(
            'getimagesize',
            'imagecreatefromstring',
            'imagecreatetruecolor',
            'imagecolortransparent',
            'imagecolorallocatealpha',
            'imagealphablending',
            'imagesavealpha',
            'imagesx',
            'imagesy',
            'imagecopyresampled',
        );

        // Check if all required GD functions are available
        foreach ($requiredFunctions as $function) {
            if (!function_exists($function)) {
                trigger_error("The PicoIcon class requires the $function function, which is part of the GD library.");
                return;
            }
        }

        $this->hasRequirements = true;

        // If image data is provided, add it to the ICO
        if (isset($imageData) && !empty($imageData)) {
            foreach($imageData as $data) {
                $this->addImageFromString($data);
            }
        }
    }

    /**
     * Add an image to the ICO generator from a base64 string (image data).
     *
     * This method converts the base64 string into an image resource, resizes it to 
     * the specified dimensions, and adds the resized image to the ICO data.
     *
     * @param string $imageData Base64 encoded image data.
     * @param array|null $sizes Optional. Array of size (width, height) for the generated ICO file. Default is null.
     * @return bool true on success, false on failure.
     */
    public function addImageFromString($imageData, $sizes = null) {
        if (!$this->hasRequirements) {
            return false;
        }

        $im = imagecreatefromstring($imageData);
        if ($im === false) {
            return false;
        }

        if (!isset($sizes)) {
            $size = array(imagesx($im), imagesy($im)); // Default size from image
            $sizes = array($size);
        }

        // Loop through the provided sizes and add each resized image
        foreach ($sizes as $size) {
            list($width, $height) = $size;

            $newIm = imagecreatetruecolor($width, $height);
            imagecolortransparent($newIm, imagecolorallocatealpha($newIm, 0, 0, 0, 127));
            imagealphablending($newIm, false);
            imagesavealpha($newIm, true);

            $sourceWidth = imagesx($im);
            $sourceHeight = imagesy($im);

            if (false === imagecopyresampled($newIm, $im, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight)) {
                continue;
            }

            $this->addImageData($newIm);
        }

        return true;
    }

    /**
     * Save the ICO data to a file.
     *
     * This method saves the generated ICO file to the specified location.
     *
     * @param string $file Path to save the ICO file.
     * @return bool true on success, false on failure.
     */
    public function saveIconFile($file) {
        if (!$this->hasRequirements || false === ($data = $this->getIconData()) || false === ($fh = fopen($file, 'w'))) {
            return false;
        }

        if (false === fwrite($fh, $data)) {
            fclose($fh);
            return false;
        }

        fclose($fh);

        return true;
    }

    /**
     * Get the ICO data (header + image data).
     *
     * This method generates the ICO file data in binary format, which includes the
     * header and all image data.
     *
     * @return string|false The ICO file data in binary format, or false if there are no images.
     */
    private function getIconData() {
        if (!is_array($this->images) || empty($this->images)) {
            return false;
        }

        $data = pack('vvv', 0, 1, count($this->images));
        $pixelData = '';
        $iconDirEntrySize = 16;
        $offset = 6 + ($iconDirEntrySize * count($this->images));

        // Loop through images and append their data
        foreach ($this->images as $image) {
            $data .= pack('CCCCvvVV', $image['width'], $image['height'], $image['color_palette_colors'], 0, 1, $image['bits_per_pixel'], $image['size'], $offset);
            $pixelData .= $image['data'];
            $offset += $image['size'];
        }

        $data .= $pixelData;
        unset($pixelData);

        return $data;
    }

    /**
     * Add image data (BMP format) to the ICO.
     *
     * This method processes the image and converts it into a BMP format, which is
     * then added to the ICO file data.
     *
     * @param GdImage $im The GD image resource to convert.
     * @return void
     */
    private function addImageData($im) // NOSONAR
    {
        $width = imagesx($im);
        $height = imagesy($im);

        $pixelData = array();
        $opacityData = array();
        $currentOpacityVal = 0;

        // Process each pixel's color and opacity
        for ($y = $height - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($im, $x, $y);
                $alpha = ($color & 0x7F000000) >> 24;
                $alpha = (1 - ($alpha / 127)) * 255;
                $color &= 0xFFFFFF;
                $color |= 0xFF000000 & ($alpha << 24);
                $pixelData[] = $color;

                $opacity = ($alpha <= 127) ? 1 : 0;
                $currentOpacityVal = ($currentOpacityVal << 1) | $opacity;

                if ((($x + 1) % 32) == 0) {
                    $opacityData[] = $currentOpacityVal;
                    $currentOpacityVal = 0;
                }
            }

            // Handle any remaining pixels for opacity data
            if (($x % 32) > 0) {
                while (($x++ % 32) > 0) {
                    $currentOpacityVal = $currentOpacityVal << 1;
                }
                $opacityData[] = $currentOpacityVal;
                $currentOpacityVal = 0;
            }
        }

        $imageHeaderSize = 40;
        $colorMaskSize = $width * $height * 4;
        $opacityMaskSize = (ceil($width / 32) * 4) * $height;

        $data = pack('VVVvvVVVVVV', 40, $width, ($height * 2), 1, 32, 0, 0, 0, 0, 0, 0);

        foreach ($pixelData as $color) {
            $data .= pack('V', $color);
        }

        foreach ($opacityData as $opacity) {
            $data .= pack('N', $opacity);
        }

        // Store image data
        $image = array(
            'width' => $width,
            'height' => $height,
            'color_palette_colors' => 0,
            'bits_per_pixel' => 32,
            'size' => $imageHeaderSize + $colorMaskSize + $opacityMaskSize,
            'data' => $data,
        );

        $this->images[] = $image;
    }
}
