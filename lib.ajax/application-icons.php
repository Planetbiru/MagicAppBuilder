<?php

use AppBuilder\Entity\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$applicationId = $inputPost->getApplicationId();
$application = new EntityApplication(null, $databaseBuilder);
try
{
    $application->findOneByApplicationId($applicationId);
    $uploadDir = $application->getBaseApplicationDirectory();

    // Make sure the directory exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Check if the required fields are present in the POST request
    if (isset($_POST['image']) && isset($_POST['icon_name'])) {
        // Get the base64 image data and icon name from POST data
        $base64Image = $_POST['image'];
        $iconName = $_POST['icon_name'];
        if(stripos($iconName, ".png") === false)
        {
            $iconName .= ".png";
        }

        // Remove the data URL part (prefix) and decode the base64 string
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));

        // Create the full file path
        $filePath = $uploadDir . "/" . $iconName;

        // Save the image to the server
        if (file_put_contents($filePath, $imageData)) {
            // Respond with success
            ResponseUtil::sendJSON([
                'success' => true,
                'filePath' => $filePath
            ]);
        } else {
            // Respond with error
            ResponseUtil::sendJSON([
                'success' => false,
                'error' => 'Failed to save the icon'
            ]);
        }
    } else {
        // Invalid request, missing data
        ResponseUtil::sendJSON([
            'success' => false,
            'error' => 'Missing image or icon name'
        ]);
    }
}
catch(Exception $e)
{
    ResponseUtil::sendJSON(new stdClass);
}