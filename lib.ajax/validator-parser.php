<?php

use AppBuilder\Util\ValidatorUtil;
use MagicObject\Request\InputGet;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";
$inputGet = new InputGet();

if ($inputGet->getValidator() != '')
{
    $applicationId = $appConfig->getApplication()->getId();
    $validatorClassName = $inputGet->getValidator(); // Rename variable for clearer representation of the class name
    
    if (isset($validatorClassName) && !empty($validatorClassName)) {
        $path = ValidatorUtil::getPath($appConfig, $inputGet);
        PicoResponse::sendJSON(ValidatorUtil::parseValidatorClass(file_get_contents($path)));
    }
}
