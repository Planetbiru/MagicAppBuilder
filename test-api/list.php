<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoOutputDataItem;
use MagicApp\AppDto\MocroServices\PicoDataHeader;
use MagicApp\AppDto\MocroServices\PicoEntityInfo;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputList;
use MagicObject\SecretObject;

require_once __DIR__ . "/database.php";

$entity = new EntityApplication(null, $database);

$pageData = $entity->findAll();

$picoEntityInfo = new PicoEntityInfo(["active"=>"active"]);
$picoModule = new PicoModuleInfo("application", "Application", "list");
$primaryKeys = array_keys($entity->tableInfo()->getPrimaryKeys());

$picoModule
	->addAllowedAction(new PicoAllowedAction("delete", "Delete"))
	->addAllowedAction(new PicoAllowedAction("approve", "Approve"))
;

$data = new PicoUserFormOutputList();

$data->addHeader(new PicoDataHeader("applicationId", $entity->label("applicationId")));
$data->addHeader(new PicoDataHeader("name", $entity->label("name")));
$data->addHeader(new PicoDataHeader("description", $entity->label("description")));
$data->addHeader(new PicoDataHeader("workspace", $entity->label("workspace")));
$data->addHeader(new PicoDataHeader("architecture", $entity->label("architecture")));
$data->addHeader(new PicoDataHeader("projectDirectory", $entity->label("projectDirectory")));
$data->addHeader(new PicoDataHeader("baseApplicationDirectory", $entity->label("baseApplicationDirectory")));
$data->addHeader(new PicoDataHeader("author", $entity->label("author")));
$data->setSortHeader("applicationId", "asc");
foreach($pageData->getResult() as $row)
{
	$data->addDataItem(
		new PicoOutputDataItem(
			[
				"applicationId"=>$row->get("applicationId"), 
				"name"=>$row->get("name"),
				"description"=>$row->get("description"),
				"workspace"=>$row->hasValue("workspace") ? $row->get("workspace")->get("name") : null,
				"architecture"=>$row->get("architecture"),
				"projectDirectory"=>$row->get("projectDirectory"),
				"baseApplicationDirectory"=>$row->get("baseApplicationDirectory"),
				"author"=>$row->get("author")
			],
			$row,
			$primaryKeys,
			$picoEntityInfo
		)
	);
}

$setting = new SecretObject();
$setting->setPrettify(true);

echo PicoResponseBody::getInstance()
	->setModule($picoModule)
    ->setData($data)
    ->setEntity($entity)
	->setting($setting)
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;