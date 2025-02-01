<?php

use MagicAdmin\Entity\Data\AdminLevelMin;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoInputFieldFilter;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppDto\MocroServices\PicoUserFormFilterList;
use MagicApp\AppFormBuilder;
use MagicApp\AppModule;
use MagicApp\AppUserPermission;
use MagicApp\Field;
use MagicApp\PicoModule;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputGet;

require_once __DIR__ . "/database.php";

$inputGet = new InputGet();




$data = new PicoUserFormFilterList();


$entity = new AdminLevelMin(null, $database);
$options = AppFormBuilder::getInstance()->createJsonData($entity, 
PicoSpecification::getInstance()
    ->addAnd(new PicoPredicate(Field::of()->active, true))
    ->addAnd(new PicoPredicate(Field::of()->draft, false)), 
PicoSortable::getInstance()
    ->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
    ->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
Field::of()->adminLevelId, Field::of()->name, $inputGet->getAdminLevelId())->getOptions();

$data->addFilter(
    new PicoInputFieldFilter(
        new PicoInputField("adminId", "Admin"), 
        "select", 
        "string", 
        "map", 
        $options, 
        null, 
        new PicoInputField("superuser", "Superuser")
    )
);

$data->addFilter(
    new PicoInputFieldFilter(
        new PicoInputField("adminId", "Admin"), 
        "select", 
        "string", 
        "url", 
        "?user_action=show-option&field=adminId", 
        null, 
        new PicoInputField("admin", "Administrator")
    )
);

$data->addFilter(
    new PicoInputFieldFilter(
        new PicoInputField("name", "Name"), 
        "text", 
        "string", 
        null, 
        new PicoInputField("Administrator", "Administrator")
    )
);

$data->addFilter(
    new PicoInputFieldFilter(
        new PicoInputField("active", "Aktif"), 
        "checkbox", 
        "boolean", 
        null, 
        new PicoInputField(true, "Ya")
    )
);


$appModule = new AppModule();
$appModule->setModuleId("123");
$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "umk", "Umk");
$userPermission = new AppUserPermission(null, null, null, null, null);

$section = "filter-list";
echo PicoResponseBody::getInstance()
    ->setModule($currentModule, $section)
    ->setPermission($userPermission)
    ->setData($data)
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ->prettify(true)
    ;