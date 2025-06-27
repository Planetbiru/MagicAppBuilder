<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\ResponseUtil;
use MagicApp\Field;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

// Handle POST request to update application info
if ($inputPost->getUserAction() == "update-info")
{
    // Sanitize and retrieve application ID from input
    $applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    // Fallback to active application ID if none provided
    if (!isset($applicationId) || empty($applicationId)) {
        $applicationId = $activeApplication->getApplicationId();
    }

    if ($applicationId != null) {
        $application = new EntityApplication(null, $databaseBuilder);
        try {
            // Only update applications created within the last hour
            $timeCreate = date('Y-m-d H:i:s', strtotime('-1 hour'));

            // Build filter specifications
            $specs = PicoSpecification::getInstance()
                ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->applicationId, $applicationId))
                ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->adminCreate, $entityAdmin->getAdminId()))
                ->addAnd(PicoPredicate::getInstance()->greaterThan(Field::of()->timeCreate, $timeCreate));

            // Perform update directly using the condition
            $application->where($specs)
                ->setTimeEdit(date('Y-m-d H:i:s'))
                ->setIpEdit($_SERVER['REMOTE_ADDR'])
                ->update();
        }
        catch (Exception $e) {
            // Silent catch: ignore errors during update
        }
    }

    // Always respond with empty JSON object
    ResponseUtil::sendJSON(new stdClass, false);
}
else
{
    // Handle GET request to retrieve application status
    $applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    // Fallback to active application ID if none provided
    if (!isset($applicationId) || empty($applicationId)) {
        $applicationId = $activeApplication->getApplicationId();
    }

    // Default response: status is "created"
    $status = new stdClass;
    $status->applicationStatus = "created";

    if ($applicationId != null) {
        $application = new EntityApplication(null, $databaseBuilder);
        try {
            // Fetch application by ID and return its current status
            $application->findOneByApplicationId($applicationId);
            $status->applicationStatus = $application->getApplicationStatus();
            ResponseUtil::sendJSON($status, false);
        }
        catch (Exception $e) {
            // In case of error, return default status
            ResponseUtil::sendJSON($status, false);
        }
    }
    else {
        // No application ID found, return default status
        ResponseUtil::sendJSON($status, false);
    }
}
