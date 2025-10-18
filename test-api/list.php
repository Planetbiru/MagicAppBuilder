<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoOutputDataItem;
use MagicApp\AppDto\MocroServices\PicoDataHeader;
use MagicApp\AppDto\MocroServices\PicoEntityInfo;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputList;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoSortable;
use MagicObject\Language\PicoEntityLanguage;
use MagicObject\SecretObject;

require_once __DIR__ . "/database.php";

$entity = new EntityApplication(null, $database);
$entityLanguage = new PicoEntityLanguage($entity);

$pageable = new PicoPageable(new PicoPage(1, 3), new PicoSortable('name', 'asc'));

$pageData = $entity->findAll(null, $pageable);

$picoEntityInfo = new PicoEntityInfo(["active"=>"active"]);
$picoModule = new PicoModuleInfo("application", "Application", "list");
$primaryKeys = array_keys($entity->tableInfo()->getPrimaryKeys());

$picoModule
	->addAllowedAction(new PicoAllowedAction("delete", "Delete"))
	->addAllowedAction(new PicoAllowedAction("approve", "Approve"))
;

$data = new PicoUserFormOutputList();

// Set column header
$fields = [
  "applicationId", "name", "description", "workspace",
  "architecture", "projectDirectory", "baseApplicationDirectory", "author"
];
foreach ($fields as $field) {
    $data->addHeader(new PicoDataHeader($field, $entityLanguage->get($field)));
}

$data->setSortHeader("applicationId", "asc");

foreach($pageData->getResult() as $row)
{
	// What data will be shown
	$data->addDataItem(
		new PicoOutputDataItem(
			[
				"applicationId"=>$row->retrieve("applicationId"), 
				"name"=>$row->retrieve("name"),
				"description"=>$row->retrieve("description"),
				"workspace"=>$row->retrieve("workspace", "name"),
				"architecture"=>$row->retrieve("architecture"),
				"projectDirectory"=>$row->retrieve("projectDirectory"),
				"baseApplicationDirectory"=>$row->retrieve("baseApplicationDirectory"),
				"author"=>$row->retrieve("author")
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
	->setCurrentAction("list")
	->setPageable($pageable)
    ->setData($data)
    ->setEntity($entity)
	->setting($setting)
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;

