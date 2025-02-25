<?php

use AppBuilder\EntityInstaller\EntityModule;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoFieldWaitingFor;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoOutputFieldApproval;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputApproval;
use MagicObject\Response\PicoResponse;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";


$entity = new EntityModule();
$entity->setUserId("anyId");
$data = new PicoUserFormOutputApproval();

$data->addOutput(
	new PicoOutputFieldApproval(
		new PicoInputField("userId", $entity->label("userId")), 
		"string", 
		new PicoInputField($entity->get("userId"), $entity->get("userId")),
		new PicoInputField($entity->get("userId"), $entity->get("userId"))
	)
);

$data->setWaitingFor(new PicoFieldWaitingFor(1, "new", "new"));

$picoModule = new PicoModuleInfo("any", "Any", "detail", "code", "namespace");

$picoModule
	->addAllowedAction(new PicoAllowedAction("delete", "Delete"))
	->addAllowedAction(new PicoAllowedAction("approve", "Approve"))
	;

$setting = (new SecretObject())
	->setNamingStrategy("snake_case")
	->setPrettify(true)
	->setFormatOutput("xml")
	;

$appModule = new EntityModule();
$appModule->setModuleId("123");
$body = PicoResponseBody::getInstance()
	->setting($setting)
  	->setModule($picoModule)
    ->setData($data)
    ->setEntity($appModule, true)
    ->switchCaseTo("camel")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;
	
PicoResponse::sendResponse($body);
