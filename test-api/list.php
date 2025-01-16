<?php

use AppBuilder\Generator\MocroServices\AllowedAction;
use AppBuilder\Generator\MocroServices\DataHeader;
use AppBuilder\Generator\MocroServices\FieldWaitingFor;
use AppBuilder\Generator\MocroServices\OutputDataItem;
use AppBuilder\Generator\MocroServices\ResponseBody;
use AppBuilder\Generator\MocroServices\UserFormOutputList;
use MagicAdmin\Entity\Data\Workspace;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$data = new UserFormOutputList();

$data->addHeader(new DataHeader("userId", "User ID", "ASC"));

$data->addDataItem(new OutputDataItem(["userId"=>"1", "adminCreate"=>"123"], new FieldWaitingFor("new", "New"), true, true));
$data->addDataItem(new OutputDataItem([], new FieldWaitingFor("new", "New"), true));

$data->addAllowedAction(new AllowedAction("delete", "Delete"));
$data->addAllowedAction(new AllowedAction("approve", "Approve"));

echo ResponseBody::getInstance()
    ->setData($data)
    ->setEntity(new Workspace())
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;