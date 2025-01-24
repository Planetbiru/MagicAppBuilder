<?php

use AppBuilder\AppSecretObject;
use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputGet;
use AppBuilder\Util\ResponseUtil;
use MagicApp\EntityApvInfo;
use MagicApp\EntityInfo;
use MagicObject\MagicObject;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";


try
{
    $inputGet = new InputGet();
    $referenceConfig = new AppSecretObject();
    
    $yml = FileDirUtil::normalizePath($activeApplication->getProjectDirectory()."/default.yml");
        
    if(file_exists($yml))
    {
        $config = new MagicObject(null);
        $config->loadYamlFile($yml, false, true, true);
        $curApp = $config->getApplication();
    }
    else
    {
        $curApp = new SecretObject();
    }

    $entityInfo = new EntityInfo($appConfig->getEntityInfo());
    $entityApvInfo = new EntityApvInfo($appConfig->getEntityApvInfo());

    if($curApp != null && $activeApplication->getApplicationId() != null)
    {
        $referenceConfigPath = $activeWorkspace->getDirectory()."/".$activeApplication->getApplicationId()."/reference.yml";
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
    ResponseUtil::sendJSON(new stdClass);
}