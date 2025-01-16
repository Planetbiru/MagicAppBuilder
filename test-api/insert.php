<?php

use MagicApp\AppDto\MocroServices\InputFieldInsert;
use MagicApp\AppDto\MocroServices\InputFieldOption;
use MagicApp\AppDto\MocroServices\ResponseBody;
use MagicApp\AppDto\MocroServices\UserFormInputInsert;
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