<?php

use MagicAdmin\Entity\Data\AdminLevelMin;
use MagicApp\AppDto\MocroServices\PicoFilterData;
use MagicApp\AppDto\MocroServices\PicoInputField;
use MagicApp\AppDto\MocroServices\PicoInputFieldFilter;
use MagicApp\AppDto\MocroServices\PicoResponseBody;
use MagicApp\AppFormBuilder;
use MagicApp\Field;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\InputGet;

require_once __DIR__ . "/database.php";

$inputGet = new InputGet();

$entity = new AdminLevelMin(null, $database);

$options = AppFormBuilder::getInstance()->createJsonData($entity, 
PicoSpecification::getInstance()
    ->addAnd(new PicoPredicate(Field::of()->active, true))
    ->addAnd(new PicoPredicate(Field::of()->draft, false)), 
PicoSortable::getInstance()
    ->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
    ->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
Field::of()->adminLevelId, Field::of()->name, $inputGet->getAdminLevelId())->getOptions();
$currentValue = PicoInputField::getSelectedValue($options);
$data = new PicoFilterData();
$data->setFilter(
    new PicoInputFieldFilter(
        new PicoInputField("adminId", "Admin"), 
        "select", 
        "string", 
        "map", 
        $options, 
        null, 
        $currentValue
    )
);

echo PicoResponseBody::getInstance()
    ->setData($data)
    ->setEntity($entity)
    ->switchCaseTo("camelCase")
    ->setResponseCode("000")
    ->setResponseText("Success")
    ->prettify(true)
    ;