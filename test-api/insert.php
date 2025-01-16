<?php

use AppBuilder\Generator\MocroServices\InputFieldInsert;
use AppBuilder\Generator\MocroServices\InputFieldOption;
use AppBuilder\Generator\MocroServices\ResponseBody;
use AppBuilder\Generator\MocroServices\UserFormInputInsert;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$data = new UserFormInputInsert();

$data->addInput(new InputFieldInsert("gender", "Gender", "select[multiple]", "string[]", "map", [new InputFieldOption("M", "Man"), new InputFieldOption("W", "Woman")]));
$data->addInput(new InputFieldInsert("admin", "Admin", "text", "string"));


echo ResponseBody::instanceOf($data)->switchCaseTo("camelCase");