<?php

namespace MagicObject\Util\Image;

/**
 * Class ImageExif
 *
 * Provides functionality to extract and process EXIF data from images, particularly GPS coordinates.
 * This class reads the EXIF metadata from an image file and retrieves the latitude and longitude, 
 * converting them from degrees, minutes, seconds (DMS) format to decimal format.
 */
class ImageExif
{
    /**
     * Retrieves latitude and longitude from the EXIF data of an image.
     *
     * This method checks if the image file exists, reads its EXIF data, and extracts the GPS coordinates 
     * (latitude and longitude) if available. The coordinates are returned in decimal format.
     *
     * @param string $imagePath The path to the image file.
     * @return array|null An array containing the latitude and longitude in decimal format, or null if GPS data is not available.
     */
    public function getLatLongFromImage($imagePath) {
        // Check if the file exists and is readable
        if (!file_exists($imagePath)) {
            return null;
        }
    
        // Get EXIF data from the image
        $exif = exif_read_data($imagePath);
    
        // Check if GPS data is available
        if (isset($exif['GPSLatitude']) && isset($exif['GPSLongitude'])) {
            // Get latitude and longitude values
            $lat = $exif['GPSLatitude'];
            $latRef = $exif['GPSLatitudeRef'];
            $long = $exif['GPSLongitude'];
            $longRef = $exif['GPSLongitudeRef'];
    
            // Convert to decimal format
            $latDecimal = $lat[0] + ($lat[1] / 60) + ($lat[2] / 3600);
            $longDecimal = $long[0] + ($long[1] / 60) + ($long[2] / 3600);
    
            // Adjust sign based on the reference (N/S for latitude and E/W for longitude)
            if ($latRef === 'S') {
                $latDecimal = -$latDecimal;
            }
            if ($longRef === 'W') {
                $longDecimal = -$longDecimal;
            }
    
            return array($latDecimal, $longDecimal);
        }
    
        return null;
    }
}
