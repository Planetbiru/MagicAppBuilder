<?php

use AppBuilder\Entity\EntityAdmin;
use MagicApp\AppDto\MocroServices\PicoAllowedAction;
use MagicApp\AppDto\MocroServices\PicoOutputDataItem;
use MagicApp\AppDto\MocroServices\PicoDataHeader;
use MagicApp\AppDto\MocroServices\PicoEntityInfo;
use MagicApp\AppDto\MocroServices\PicoModuleInfo;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormOutputList;
use MagicApp\AppUserPermission;
use MagicApp\PicoModule;

require_once __DIR__ . "/database.php";

$entity = new EntityAdmin(null, $database);

$pageData = $entity->findAll();

$picoEntityInfo = new PicoEntityInfo("active");
$picoModule = new PicoModuleInfo("any", "Any", "detail");
$primaryKeys = array_keys($entity->tableInfo()->getPrimaryKeys());

$picoModule
->addAllowedAction(new PicoAllowedAction("delete", "Delete"))
->addAllowedAction(new PicoAllowedAction("approve", "Approve"))
;

$data = new PicoUserFormOutputList();

$data->addHeader(new PicoDataHeader("adminId", $entity->label("adminId"), "ASC"));
$data->addHeader(new PicoDataHeader("name", $entity->label("name")));
$data->addHeader(new PicoDataHeader("username", $entity->label("username")));

foreach($pageData->getResult() as $row)
{
	$data->addDataItem(
		new PicoOutputDataItem(
			[
				"adminId"=>$row->get("adminId"), 
				"name"=>$row->get("name"),
				"username"=>$row->get("username")
			],
			$row,
			$primaryKeys,
			$picoEntityInfo
		)
	);
}


$currentModule = new PicoModule($appConfig, $database, $entity, "/", "umk", "Umk");
$userPermission = new AppUserPermission(null, null, null, null, null);



echo PicoResponseBody::getInstance()
	->setModule($picoModule)
    ->setData($data)
    ->setEntity($entity)
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ;