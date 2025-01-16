<?php

use MagicApp\AppDto\MocroServices\InputField;
use MagicApp\AppDto\MocroServices\InputFieldUpdate;
use MagicApp\AppDto\MocroServices\InputFieldOption;
use MagicApp\AppDto\MocroServices\InputFieldValue;
use MagicApp\AppDto\MocroServices\ResponseBody;
use MagicApp\AppDto\MocroServices\UserFormInputUpdate;
use MagicAdmin\Entity\Data\Workspace;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$data = new UserFormInputUpdate();

$data->addInput(new InputFieldUpdate(
    new InputField("gender", "Gender"), // Field
    "select[multiple]",                 // Input type
    "string[]",                         // Data type
    "map",                              // Option source
    [InputFieldOption::getInstance()->setValue("M")->setLabel("Man")->setSelected(true), new InputFieldOption("W", "Woman")], // Option value
    null,                               // Pattern
    new InputFieldValue("val", "Label") // Current value
)
);

$data->addInput(new InputFieldUpdate(new InputField("gender", "Gender"), "text", "string"));


echo ResponseBody::getInstance()
    ->setData($data)
    ->setEntity(new Workspace())
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;