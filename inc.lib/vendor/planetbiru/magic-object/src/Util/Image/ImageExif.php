<?php

namespace MagicObject\Util\Image;

class ImageExif
{
    public function getLatLongFromImage($imagePath) {
        // Periksa apakah file ada dan dapat dibaca
        if (!file_exists($imagePath)) {
            return null;
        }
    
        // Ambil data EXIF dari gambar
        $exif = exif_read_data($imagePath);
    
        // Periksa apakah data GPS tersedia
        if (isset($exif['GPSLatitude']) && isset($exif['GPSLongitude'])) {
            // Ambil nilai latitude dan longitude
            $lat = $exif['GPSLatitude'];
            $latRef = $exif['GPSLatitudeRef'];
            $long = $exif['GPSLongitude'];
            $longRef = $exif['GPSLongitudeRef'];
    
            // Konversi ke format desimal
            $latDecimal = $lat[0] + ($lat[1] / 60) + ($lat[2] / 3600);
            $longDecimal = $long[0] + ($long[1] / 60) + ($long[2] / 3600);
    
            // Sesuaikan tanda berdasarkan referensi
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


