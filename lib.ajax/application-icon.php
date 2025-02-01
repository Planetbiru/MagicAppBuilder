<?php

use AppBuilder\Entity\EntityApplication;
use AppBuilder\Util\Image\PicoIcon;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

// Ensure that application ID and icon name are present in form data
$iconName = isset($_POST['icon_name']) ? $_POST['icon_name'] : 'favicon.ico';

$inputPost = new InputPost();
$applicationId = $inputPost->getApplicationId();
$application = new EntityApplication(null, $databaseBuilder);
try
{
    $application->findOneByApplicationId($applicationId);
    $uploadDir = $application->getBaseApplicationDirectory();
    
    // Directory to store temporary files
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Array to store temporary PNG image paths
    $images = [];

    // Process each uploaded PNG image
    foreach ($_POST['images'] as $key => $image) {
        $images[] = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
    }

    // If PNG images are uploaded
    if (!empty($images)) {
        
        // Path to store the .ico file
        $icoPath = $uploadDir . "/" . $iconName;

        try {
            // Create an ImageMagick object to generate the .ico file
            $icons = new PicoIcon($images);
            
            // Convert and save as .ico file
            $icons->saveIconFile($icoPath);

            $appManifest = [
                "name" => $application->getName(),
                "short_name" => str_replace(array("-", " ", "_"), "", $application->getName()),
                "icons" => [
                    [
                        "src" => "apple-icon-57x57.png",
                        "sizes" => "57x57",
                        "type" => "image/png"
                    ],
                    [
                        "src" => "apple-icon-60x60.png",
                        "sizes" => "60x60",
                        "type" => "image/png"
                    ],
                    [
                        "src" => "android-icon-192x192.png",
                        "sizes" => "192x192",
                        "type" => "image/png"
                    ]
                ],
                "start_url" => "/",
                "display" => "standalone"
            ];
            $manifestPath = $uploadDir . "/manifest.json";
            file_put_contents($manifestPath, json_encode($appManifest, JSON_PRETTY_PRINT));

            // Return success response
            ResponseUtil::sendJSON([
                'success' => true,
                'filePath' => $icoPath
            ]);
        } catch (Exception $e) {
            // Handle errors if there is an issue using ImageMagick
            ResponseUtil::sendJSON([
                'success' => false,
                'error' => 'Error creating .ico file: ' . $e->getMessage()
            ]);
        }
    } else {
        ResponseUtil::sendJSON([
            'success' => false,
            'error' => 'No images were uploaded.'
        ]);
    }
}
catch(Exception $e)
{
    ResponseUtil::sendJSON(new stdClass);
}
