<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoEntityInfo;
use MagicApp\AppDto\MocroServices\PicoFieldWaitingFor;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoInputFieldOption;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoInputFieldUpdate;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputDetail;
use MagicApp\AppEntityLanguage;
use MagicObject\Response\PicoResponse;
use MagicObject\SecretObject;

require_once __DIR__ . "/database.php";

$entity = new EntityApplication(null, $database);
$entity->findOneByApplicationId("sipro");
$picoEntityInfo = new PicoEntityInfo(["active"=>"active"]);
$data = new PicoUserFormOutputDetail($entity, $picoEntityInfo);

$appConfig = new SecretObject();
$entityLanguage = new AppEntityLanguage($entity, $appConfig, 'en');

$map1 = array(
	"monolith" => array("value" => "monolith", "label" => "Monolith", "selected" => false),
	"microservices" => array("value" => "microservices", "label" => "Microservices", "selected" => false)
);

$data->addOutput(new PicoInputFieldUpdate(new PicoInputField("applicationId", $entityLanguage->get("applicationId")), "text", "string", null, null, null, new PicoInputField($entity->get("applicationId"), $entity->get("applicationId"))));
$data->addOutput(new PicoInputFieldUpdate(new PicoInputField("name", $entityLanguage->get("name")), "text", "string", null, null, null, new PicoInputField($entity->get("name"), $entity->get("name"))));
$data->addOutput(new PicoInputFieldUpdate(new PicoInputField("description", $entityLanguage->get("description")), "text", "string", null, null, null, new PicoInputField($entity->get("description"), $entity->get("description"))));
$data->addOutput(new PicoInputFieldUpdate(new PicoInputField("workspace", $entityLanguage->get("workspace")), "text", "string", null, null, null, new PicoInputField($entity->get("workspaceId"), $entity->hasValue("workspace") ? $entity->get("workspace")->get("name") : null)));
$data->addOutput(new PicoInputFieldUpdate(new PicoInputField("architecture", $entityLanguage->get("architecture")), "select", "string", "map", PicoInputFieldOption::fromArray($map1), null, new PicoInputField($entity->get("architecture"), PicoInputFieldOption::getLabelFromValue($map1, $entity->get("architecture")))));
$data->addOutput(new PicoInputFieldUpdate(new PicoInputField("projectDirectory", $entityLanguage->get("projectDirectory")), "text", "string", null, null, null, new PicoInputField($entity->get("projectDirectory"), $entity->get("projectDirectory"))));
$data->addOutput(new PicoInputFieldUpdate(new PicoInputField("baseApplicationDirectory", $entityLanguage->get("baseApplicationDirectory")), "text", "string", null, null, null, new PicoInputField($entity->get("baseApplicationDirectory"), $entity->get("baseApplicationDirectory"))));
$data->addOutput(new PicoInputFieldUpdate(new PicoInputField("author", $entityLanguage->get("author")), "text", "string", null, null, null, new PicoInputField($entity->get("author"), $entity->get("author"))));

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