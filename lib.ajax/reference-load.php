<?php

use MagicObject\Request\InputGet;
use AppBuilder\Util\ResponseUtil;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputGet = new InputGet();
if ($inputGet->getFieldName() != null && $inputGet->getKey() != null) {
    $path = sprintf(
        "%s/applications/%s/reference/%s-%s.json",
        $activeWorkspace->getDirectory(),
        $activeApplication->getApplicationId(),
        $inputGet->getFieldName(),
        $inputGet->getKey()
    );
    if (!file_exists(dirname($path))) {
        mkdir(dirname($path), 0755, true);
    }
    if (file_exists($path)) {
        ResponseUtil::sendJSON(file_get_contents($path));
    } else {
        $entityConstant = new SecretObject($appConfig->getEntityInfo());
        if (empty($entityConstant->valueArray())) {
            $entityConstant = new SecretObject($builderConfig->getEntityInfo());
        }
        $fieldName = trim($inputGet->getFieldName());
        if (PicoStringUtil::endsWith($fieldName, "_id", true)) {
            $objectName = substr($fieldName, 0, strlen($fieldName) - 3);
        } else {
            $objectName = $fieldName;
        }
        $fieldNameEnt = $fieldName;
        if (PicoStringUtil::endsWith($fieldNameEnt, "_id", true)) {
            $fieldNameEnt = substr($fieldNameEnt, 0, strlen($fieldNameEnt) - 3);
        }
        $tableName = $fieldNameEnt;
        $entityName = PicoStringUtil::upperCamelize($fieldNameEnt);
        $name = $entityConstant->getName();
        $active = $entityConstant->getActive();
        $draft = $entityConstant->getDraft();
        $sortOrder = $entityConstant->getSortOrder();
        $draft = $entityConstant->getDraft();
        $data = array(
            "type" => "entity",
            "entity" => array(
                "entityName" => $entityName,
                "tableName" => $tableName,
                "primaryKey" => $fieldName,
                "value" => $name,
                "objectName" => $objectName,
                "propertyName" => $name,
                "specification" => array(
                    array(
                        "column" => PicoStringUtil::camelize($active),
                        "value" => true,
                        "comparison"=> "equals"
                    ),
                    array(
                        "column" => PicoStringUtil::camelize($draft),
                        "value" => false,
                        "comparison"=> "equals"
                    )
                    ),
                "sortable" => array(
                    array(
                        "sortBy" => PicoStringUtil::camelize($sortOrder),
                        "sortType" => "PicoSort::ORDER_TYPE_ASC"
                    ),
                    array(
                        "sortBy" => PicoStringUtil::camelize($name),
                        "sortType" => "PicoSort::ORDER_TYPE_ASC"
                    )
                    ),
                "additionalOutput" => array()
                ),
            "map" => array(),
            "yesno" => null,
            "truefalse" => null,
            "onezero" => null
            );
        ResponseUtil::sendJSON($data);
        exit();
    }
}
else
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}