<?php

use AppBuilder\Entity\EntityApplication;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoFieldWaitingFor;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoOutputFieldDetail;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputDetail;
use MagicObject\Response\PicoResponse;

require_once __DIR__ . "/database.php";

$entity = new EntityApplication(null, $database);
$entity->findOneByApplicationId("tukang");
$data = new PicoUserFormOutputDetail();

$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("applicationId", $entity->label("applicationId")), "string", new PicoInputField($entity->get("applicationId"), $entity->get("applicationId"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("name", $entity->label("name")), "string", new PicoInputField($entity->get("name"), $entity->get("name"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("description", $entity->label("description")), "string", new PicoInputField($entity->get("description"), $entity->get("description"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("workspace", $entity->label("workspace")), "string", new PicoInputField($entity->get("workspaceId"), $entity->hasValue("workspace") ? $entity->get("workspace")->get("name") : null)));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("architecture", $entity->label("architecture")), "string", new PicoInputField($entity->get("architecture"), $entity->get("architecture"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("projectDirectory", $entity->label("projectDirectory")), "string", new PicoInputField($entity->get("projectDirectory"), $entity->get("projectDirectory"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("baseApplicationDirectory", $entity->label("baseApplicationDirectory")), "string", new PicoInputField($entity->get("baseApplicationDirectory"), $entity->get("baseApplicationDirectory"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("author", $entity->label("author")), "string", new PicoInputField($entity->get("author"), $entity->get("author"))));

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
    ->setResponseCode("000")
    ->setResponseText("Success")
	->switchCaseTo("snake_case")
    ->prettify(true)
    ;
	
PicoResponse::sendResponse($body);