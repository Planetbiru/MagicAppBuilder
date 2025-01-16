<?php

use AppBuilder\Generator\MocroServices\AllowedAction;
use AppBuilder\Generator\MocroServices\DataHeader;
use AppBuilder\Generator\MocroServices\FieldWaitingFor;
use AppBuilder\Generator\MocroServices\OutputDataItem;
use AppBuilder\Generator\MocroServices\UserFormOutputList;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$data = new UserFormOutputList();

$data->addHeader(new DataHeader("userId", "User ID"));

$data->addDataItem(new OutputDataItem(["userId"=>"1", "adminCreate"=>"123"], new FieldWaitingFor("new", "New"), true, true));
$data->addDataItem(new OutputDataItem([], new FieldWaitingFor("new", "New"), true));

$data->addAllowedAction(new AllowedAction("delete", "Delete"));
$data->addAllowedAction(new AllowedAction("approve", "Approve"));

echo $data;