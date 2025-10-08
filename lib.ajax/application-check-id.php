<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\Constants\PicoHttpStatus;
use MagicObject\Constants\PicoMime;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$appId = trim($appId);

if (empty($appId)) {
    ResponseUtil::sendResponse(
        json_encode(['error' => 'Application ID is required']),
        PicoMime::APPLICATION_JSON,
        null,
        PicoHttpStatus::HTTP_OK
    );
    exit;
}

$applicationToUpdate = new EntityApplication(null, $databaseBuilder);

try
{
    $applicationToUpdate->find($appId);
    // If find() succeeds, it means the application ID exists.
    if($applicationToUpdate->getApplicationId() == $appId)
    {
        ResponseUtil::sendResponse(
            json_encode([
                'success' => false,
                'message' => "Application ID '$appId' already exists."
            ]),
            PicoMime::APPLICATION_JSON,
            null,
            PicoHttpStatus::HTTP_OK
        );
        exit;
    }
    else
    {
        ResponseUtil::sendResponse(
            json_encode([
                'success' => false,
                'message' => "Application ID '$appId' is not match."
            ]),
            PicoMime::APPLICATION_JSON,
            null,
            PicoHttpStatus::HTTP_OK
        );
        exit;
    }
}
catch(Exception $e)
{
    // find() throws an exception if the record is not found, which is what we want.
    ResponseUtil::sendResponse(
        json_encode([
          'success' => true, 
          'message' => "Application ID '$appId' is available."
        ]),
        PicoMime::APPLICATION_JSON,
        null,
        PicoHttpStatus::HTTP_OK
    );
    exit;
}
