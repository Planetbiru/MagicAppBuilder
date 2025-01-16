<?php

use AppBuilder\Generator\MocroServices\InputFieldInsert;
use AppBuilder\Generator\MocroServices\InputFieldOption;
use AppBuilder\Generator\MocroServices\ResponseBody;
use AppBuilder\Generator\MocroServices\UserFormInputInsert;
use MagicAdmin\Entity\Data\Workspace;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$data = new UserFormInputInsert();

$data->addInput(new InputFieldInsert("gender", "Gender", "select[multiple]", "string[]", "map", [new InputFieldOption("M", "Man"), new InputFieldOption("W", "Woman")]));
$data->addInput(new InputFieldInsert("admin", "Admin", "text", "string"));


echo ResponseBody::getInstance()
    ->setData($data)
    ->setEntity(new Workspace())
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;