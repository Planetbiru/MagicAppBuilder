<?php

use AppBuilder\Entity\EntityAdmin;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoInputFieldOption;
use MagicApp\AppDto\MocroServices\PicoInputFieldUpdate;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormInputInsert;

require_once __DIR__ . "/database.php";

$entity = new EntityAdmin(null, $database);

$data = new PicoUserFormInputInsert();

$data->addInput(
	new PicoInputFieldUpdate(
		new PicoInputField("gender", "Gender"), 
		"select[multiple]", "string[]", 
		"map", 
		[new PicoInputFieldOption("M", "Man"), new PicoInputFieldOption("W", "Woman")]
	)
);

$data->addInput(
	new PicoInputFieldUpdate(
		new PicoInputField("songId", "Song ID"), 
		"select", "string", 
		"url", 
		"any.php"
	)
);
$data->addInput(
	new PicoInputFieldUpdate(
		new PicoInputField("admin", "Admin"), 
		"text", "string"
	)
);


echo PicoResponseBody::getInstance()
    ->setData($data)
    ->setEntity($entity)
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;