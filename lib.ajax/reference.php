<?php

use AppBuilder\AppSecretObject;
use MagicObject\Request\InputGet;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

try
{
    $inputGet = new InputGet();
    $referenceConfig = new AppSecretObject();
    if($curApp != null && $curApp->getId() != null)
    {
        $referenceConfigPath = $workspaceDirectory."/applications/".$curApp->getId()."/reference.yml";
        if(file_exists($referenceConfigPath))
        {
            $referenceConfig->loadYamlFile($referenceConfigPath, false, true, true);
        }
    }
    $reference = $referenceConfig->getReferenceData();
    $fieldName = $inputGet->getFieldName();
    $camelFieldName = PicoStringUtil::camelize($fieldName);
    if(PicoStringUtil::endsWith($fieldName, "_id"))
    {
        $entityName = PicoStringUtil::upperCamelize(substr($fieldName, 0, strlen($fieldName)-3));
        $primaryKey = $camelFieldName;
    }
    else
    {
        $entityName = PicoStringUtil::upperCamelize($fieldName);
        $primaryKey = PicoStringUtil::camelize($camelFieldName."_id");
    }
    $fieldReference = $reference->get($fieldName);
    if($fieldReference == null)
    {
        $fieldReference = new AppSecretObject($fieldName);
        $fieldReference->setType("entity");
        $entity = new AppSecretObject();
        $entity->setEntityName($entityName);
        $entity->setPrimaryKey($camelFieldName);
        $entity->setValue(PicoStringUtil::camelize($entityInfo->getName()));
        $specification = array(
            (new AppSecretObject())->setColumn(PicoStringUtil::camelize($entityInfo->getActive()))->setValue(true),
            (new AppSecretObject())->setColumn(PicoStringUtil::camelize($entityInfo->getDraft()))->setValue(false)
        );
        $sortable = array(
            (new AppSecretObject())->setOrderBy(PicoStringUtil::camelize($entityInfo->getSortOrder()))->setOrderType('PicoSort::SORT_ASC'),
            (new AppSecretObject())->setOrderBy(PicoStringUtil::camelize($primaryKey))->setOrderType('PicoSort::SORT_ASC')
        );
        $entity->setSpecification($specification);
        $entity->setSortable($sortable);

        $fieldReference->setEntity($entity);    
        $reference->set($fieldName, $fieldReference);

        $referenceConfig->setReferenceData($reference);
        file_put_contents($referenceConfigPath, $referenceConfig->dumpYaml());
    }
    ResponseUtil::sendJSON($fieldReference);
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}