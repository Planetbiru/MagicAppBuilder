<?php

use MagicObject\Request\InputPost;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";

$inputPost = new InputPost();
if($inputPost->getFieldName() != null && $inputPost->getKey() != null)
{
    header("Content-type: application/json");
    $path = dirname(__DIR__) . "/inc.cfg/applications/".$curApp->getId()."/reference/".$inputPost->getFieldName() . "-" . $inputPost->getKey() . ".json";
    if(!file_exists(dirname($path)))
    {
        mkdir(dirname($path), 0755, true);
    }
    if(file_exists($path))
    {
        echo file_get_contents($path);
    }
    else
    {
        $entityConstant = new SecretObject($appConfig->getEntityInfo());
        if (empty($entityConstant->valueArray())) {
            $entityConstant = new SecretObject($builderConfig->getEntityInfo());
        }
        $fieldName = trim($inputPost->getFieldName());
        if(PicoStringUtil::endsWith($fieldName, "_id", true))
        {
            $objectName = substr($fieldName, 0, strlen($fieldName) - 3);
        }
        else
        {
            $objectName = $fieldName;
        }
        $fieldNameEnt = $fieldName;
        if(PicoStringUtil::endsWith($fieldNameEnt, "_id", true))
        {
            $fieldNameEnt = substr($fieldNameEnt, 0, strlen($fieldNameEnt) - 3);        
        }
        $tableName = $fieldNameEnt;
        $entityName = PicoStringUtil::upperCamelize($fieldNameEnt);
        $name = $entityConstant->getName();
        $active = $entityConstant->getActive();
        $draft = $entityConstant->getDraft();
        $sortOrder = $entityConstant->getSortOrder();
        $draft = $entityConstant->getDraft();
        echo json_encode(
            array(
                "type"=>"entity",
                "entity"=>array(
                    "entityName" =>$entityName,
                    "tableName"=>$tableName,
                    "primaryKey"=>$fieldName,
                    "value"=>$name,
                    "objectName"=>$objectName,
                    "propertyName"=>$name,
                    "specification"=>array(
                        array(
                            "column"=>PicoStringUtil::camelize($active),
                            "value"=>true
                        ),
                        array(
                            "column"=>PicoStringUtil::camelize($draft),
                            "value"=>true
                        )
                    ),
                    "sortable"=>array(
                        array(
                            "sortBy"=>PicoStringUtil::camelize($sortOrder),
                            "sortType"=>"PicoSort::ORDER_TYPE_ASC"
                        ),
                        array(
                            "sortBy"=>PicoStringUtil::camelize($name),
                            "sortType"=>"PicoSort::ORDER_TYPE_ASC"
                        )
                    ),
                    "additionalOutput"=>array()
                ),
                "map"=> array(),
                "yesno"=>null,
                "truefalse"=>null,
                "onezero"=>null
            )
        );
    }
}