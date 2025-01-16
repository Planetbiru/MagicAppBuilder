<?php

use AppBuilder\Generator\MocroServices\AllowedAction;
use AppBuilder\Generator\MocroServices\FieldWaitingFor;
use AppBuilder\Generator\MocroServices\InputFieldValue;
use AppBuilder\Generator\MocroServices\OutputFieldApproval;
use AppBuilder\Generator\MocroServices\OutputFieldDetail;
use AppBuilder\Generator\MocroServices\UserFormOutputApproval;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$data = new UserFormOutputApproval();

$data->addOutput(new OutputFieldApproval("userId", "User ID", "string", new InputFieldValue(1, "Wowo"), new InputFieldValue(1, "Sisi")));
$data->addOutput(new OutputFieldApproval("admin", "Admin", "string", new InputFieldValue(2, "Didi"), new InputFieldValue(3, "Dede")));


$data->addAllowedAction(new AllowedAction("delete", "Delete"));
$data->addAllowedAction(new AllowedAction("approve", "Approve"));
$data->setWaitingfor(new FieldWaitingFor("new", "new"));

echo $data;