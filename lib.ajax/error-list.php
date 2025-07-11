<?php

use MagicAdmin\Entity\Data\ErrorCache;
use MagicApp\Field;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputGet;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$count = 0;
$count_validator = 0;
$count_entity = 0;
try {
    $inputGet = new InputGet();
    $applicationId = $inputGet->getApplicationId();
    if(!isset($applicationId) || empty($applicationId))
    {
        $applicationId = $appConfig->getApplication()->getId();
    }
    $specification = PicoSpecification::getInstance()
        ->addAnd(PicoPredicate::getInstance()->equals(Field::of()->applicationId, $applicationId))
        ->addAnd(PicoPredicate::getInstance()->greaterThanOrEquals(Field::of()->lineNumber, 0))
    ;
    $errorCache = new ErrorCache(null, $databaseBuilder);
    $pageData = $errorCache->findAll($specification);
    foreach($pageData->getResult() as $error)
    {
        if(strpos($error->getFilePath(), "/Validator/") !== false && strpos($error->getFilePath(), "/Entity/") === false)
        {
            $count_validator++;
        }
        else if(strpos($error->getFilePath(), "/Validator/") === false && strpos($error->getFilePath(), "/Entity/") !== false)
        {
            $count_entity++;
        }
        $count++;
    }
}
catch(Exception $e)
{
    // do nothin\
}

$response = array(
    'success' => true,
    'error_validator' => $count_validator,
    'error_count_entity' => $count_entity,
    'error_count' => $count
);

PicoResponse::sendJSON($response);