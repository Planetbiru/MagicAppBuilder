<?php

use MagicApp\AppDto\MocroServices\AllowedAction;
use MagicApp\AppDto\MocroServices\FieldWaitingFor;
use MagicApp\AppDto\MocroServices\InputField;
use MagicApp\AppDto\MocroServices\InputFieldValue;
use MagicApp\AppDto\MocroServices\OutputFieldDetail;
use MagicApp\AppDto\MocroServices\ResponseBody;
use MagicApp\AppDto\MocroServices\UserFormOutputDetail;
use MagicAdmin\Entity\Data\AdminProfile;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$data = new UserFormOutputDetail();

$data->addOutput(new OutputFieldDetail(new InputField("userId", "User ID"), "string", new InputFieldValue(1, "1")));
$data->addOutput(new OutputFieldDetail(new InputField("admin", "Admin"), "string", new InputFieldValue(2, "Didi")));


$data->addAllowedAction(new AllowedAction("delete", "Delete"));
$data->addAllowedAction(new AllowedAction("approve", "Approve"));
$data->setWaitingfor(new FieldWaitingFor("new", "new"));

echo ResponseBody::getInstance()
    ->setData($data)
    ->setEntity(new AdminProfile())
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;