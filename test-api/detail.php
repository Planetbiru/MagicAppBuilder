<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoEntityInfo;
use MagicApp\AppDto\MocroServices\PicoFieldWaitingFor;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoOutputFieldDetail;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputDetail;
use MagicAdmin\AppEntityLanguageImpl;
use MagicObject\Language\PicoEntityLanguage;
use MagicObject\Response\PicoResponse;
use MagicObject\SecretObject;

require_once __DIR__ . "/database.php";

$entity = new EntityApplication(null, $database);
$entity->findOneByApplicationId("sipro");
$picoEntityInfo = new PicoEntityInfo(["active"=>"active"]);
$data = new PicoUserFormOutputDetail($entity, $picoEntityInfo);

$appConfig = new SecretObject();
$entityLanguage = new AppEntityLanguage($entity, $appConfig, 'en');

$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("applicationId", $entityLanguage->get("applicationId")), "string", new PicoInputField($entity->get("applicationId"), $entity->get("applicationId"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("name", $entityLanguage->get("name")), "string", new PicoInputField($entity->get("name"), $entity->get("name"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("description", $entityLanguage->get("description")), "string", new PicoInputField($entity->get("description"), $entity->get("description"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("workspace", $entityLanguage->get("workspace")), "string", new PicoInputField($entity->get("workspaceId"), $entity->hasValue("workspace") ? $entity->get("workspace")->get("name") : null)));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("architecture", $entityLanguage->get("architecture")), "string", new PicoInputField($entity->get("architecture"), $entity->get("architecture"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("projectDirectory", $entityLanguage->get("projectDirectory")), "string", new PicoInputField($entity->get("projectDirectory"), $entity->get("projectDirectory"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("baseApplicationDirectory", $entityLanguage->get("baseApplicationDirectory")), "string", new PicoInputField($entity->get("baseApplicationDirectory"), $entity->get("baseApplicationDirectory"))));
$data->addOutput(new PicoOutputFieldDetail(new PicoInputField("author", $entityLanguage->get("author")), "string", new PicoInputField($entity->get("author"), $entity->get("author"))));

$picoModule = new PicoModuleInfo("application", "Application", "detail");

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