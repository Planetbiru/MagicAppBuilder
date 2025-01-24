<?php

use AppBuilder\Entity\EntityAdmin;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoFieldWaitingFor;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoOutputFieldDetail;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputDetail;
use MagicObject\Response\PicoResponse;

require_once __DIR__ . "/database.php";

$entity = new EntityAdmin(null, $database);
$entity->findOneByUsername("administrator");
$data = new PicoUserFormOutputDetail();

$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("adminId", $entity->label("adminId")), "string", new PicoInputField($entity->get("adminId"), $entity->get("adminId"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("name", $entity->label("name")), "string", new PicoInputField($entity->get("name"), $entity->get("name"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("adminLevelId", $entity->label("adminLevelId")), "string", new PicoInputField($entity->get("adminLevelId"), $entity->get("adminLevel") != null ? $entity->get("adminLevel")->get("name") : "")));


$data->setWaitingfor(new PicoFieldWaitingFor(1, "new", "new"));

$picoModule = new PicoModuleInfo("any", "Any", "detail");

$picoModule
->addAllowedAction(new PicoAllowedAction("delete", "Delete"))
->addAllowedAction(new PicoAllowedAction("approve", "Approve"))
;

$body = PicoResponseBody::getInstance()
  	->setModule($picoModule)
    ->setData($data)
    ->setEntity($entity, true)
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
	->switchCaseTo("snake_case")
    ;
	
PicoResponse::sendResponse($body);