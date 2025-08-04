<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicAdmin\AppEntityLanguageImpl;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoEntityInfo;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoInputFieldOption;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoInputFieldApproval;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoResponseStatus;
use MagicApp\AppDto\MocroServices\PicoStatusCode;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputDetail;
use MagicObject\Response\PicoResponse;
use MagicObject\SecretObject;

require_once __DIR__ . "/database.php";

$entity = new EntityApplication(null, $database);

$picoModule = new PicoModuleInfo("application", "Application", "detail");

$picoModule
	->addAllowedAction(new PicoAllowedAction("delete", "Delete"))
	->addAllowedAction(new PicoAllowedAction("approve", "Approve"))
;

try
{
	$entity->findOneByApplicationId("lagu");
	$picoEntityInfo = new PicoEntityInfo(["active"=>"active"]);
	$data = new PicoUserFormOutputDetail($entity, $picoEntityInfo);

	$appConfig = new SecretObject();
	$entityLanguage = new AppEntityLanguageImpl($entity, $appConfig, 'en');

	$map1 = array(
		"monolith" => array("value" => "monolith", "label" => "Monolith", "selected" => false),
		"microservices" => array("value" => "microservices", "label" => "Microservices", "selected" => false)
	);

	$data->addOutput(new PicoInputFieldApproval(
		new PicoInputField("applicationId", $entityLanguage->get("applicationId")), 
		"text", 
		"string", 
		null, 
		null, 
		new PicoInputField($entity->get("applicationId"), $entity->get("applicationId")),
		new PicoInputField($entity->get("applicationId"), $entity->get("applicationId"))
	));

	$data->addOutput(new PicoInputFieldApproval(
		new PicoInputField("name", $entityLanguage->get("name")), 
		"text", 
		"string", 
		null, 
		null, 
		new PicoInputField($entity->get("name"), $entity->retrieve("name")),
		new PicoInputField($entity->get("name"), $entity->retrieve("name"))
	));

	$data->addOutput(new PicoInputFieldApproval(
		new PicoInputField("description", $entityLanguage->get("description")), 
		"text", 
		"string", 
		null, 
		null, 
		new PicoInputField($entity->get("description"), $entity->get("description")),
		new PicoInputField($entity->get("description"), $entity->get("description"))
	));

	$data->addOutput(new PicoInputFieldApproval(
		new PicoInputField("workspace", $entityLanguage->get("workspace")), 
		"text", 
		"string", 
		null, 
		null, 
		new PicoInputField($entity->get("workspaceId"), $entity->retrieve("workspace", "name")),
		new PicoInputField($entity->get("workspaceId"), $entity->retrieve("workspace", "name"))
	));

	$data->addOutput(new PicoInputFieldApproval(
		new PicoInputField("architecture", $entityLanguage->get("architecture")), 
		"select", 
		"string", 
		"map", 
		PicoInputFieldOption::fromArray($map1), 
		new PicoInputField($entity->get("architecture"), PicoInputFieldOption::getLabelFromValue($map1, $entity->get("architecture"))),
		new PicoInputField($entity->get("architecture"), PicoInputFieldOption::getLabelFromValue($map1, $entity->get("architecture")))
	));

	$data->addOutput(new PicoInputFieldApproval(
		new PicoInputField("projectDirectory", $entityLanguage->get("projectDirectory")), 
		"text", 
		"string", 
		null, 
		null, 
		new PicoInputField($entity->get("projectDirectory"), $entity->retrieve("projectDirectory")),
		new PicoInputField($entity->get("projectDirectory"), $entity->retrieve("projectDirectory"))
	));

	$data->addOutput(new PicoInputFieldApproval(
		new PicoInputField("baseApplicationDirectory", $entityLanguage->get("baseApplicationDirectory")), 
		"text", 
		"string", 
		null, 
		null, 
		new PicoInputField($entity->get("baseApplicationDirectory"), $entity->retrieve("baseApplicationDirectory")),
		new PicoInputField($entity->get("baseApplicationDirectory"), $entity->retrieve("baseApplicationDirectory"))
	));

	$data->addOutput(new PicoInputFieldApproval(
		new PicoInputField("author", $entityLanguage->get("author")), 
		"text", 
		"string", 
		null, 
		null, 
		new PicoInputField($entity->get("author"), $entity->retrieve("author")),
		new PicoInputField($entity->get("author"), $entity->retrieve("author"))
	));



	$body = PicoResponseBody::getInstance()
		->setModule($picoModule)
		->setData($data)
		->setEntity($entity, true)
		->setResponseStatus(new PicoResponseStatus("000"))
		->switchCaseTo("camel")
		->prettify(true)
		;
	
	PicoResponse::sendResponse($body);
}
catch(Exception $e)
{
	$body = PicoResponseBody::getInstance()
  	->setModule($picoModule)
    ->setEntity($entity, true)
    ->setResponseStatus(new PicoResponseStatus(PicoStatusCode::DATA_NOT_FOUND))
	->switchCaseTo("camel")
    ->prettify(true)
    ;
	PicoResponse::sendResponse($body);
}