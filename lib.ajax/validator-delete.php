<?php

use AppBuilder\Util\ValidatorUtil;
use MagicObject\Request\InputPost;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

if ($inputPost->getValidator() != '')
{
    $applicationId = $appConfig->getApplication()->getId();
    $validatorClassName = $inputPost->getValidator(); // Rename variable for clearer representation of the class name
    if (isset($validatorClassName) && !empty($validatorClassName)) {
        
        $path = ValidatorUtil::getPath($appConfig, $inputPost);
        if(file_exists($path))
        {
            unlink($path);
        }
    }
}

$response = new stdClass;
$response->success = true;
PicoResponse::sendJSON($response);

exit(); // Ensure the script stops after sending the JSON response