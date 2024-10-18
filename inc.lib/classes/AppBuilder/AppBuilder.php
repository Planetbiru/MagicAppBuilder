<?php

namespace AppBuilder;

use AppBuilder\Base\AppBuilderBase;
use Error;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

class AppBuilder extends AppBuilderBase
{
    /**
     * Create INSERT section without approval and trash.
     *
     * This method generates code for inserting a new entity based on the provided fields and main entity.
     *
     * @param MagicObject $mainEntity The main entity to be inserted.
     * @param AppField[] $appFields The fields to be included in the insert operation.
     * @return string The generated code for the insert section.
     */
    public function createInsertSection($mainEntity, $appFields, $callbackSuccess = null, $callbackFailed = null)
    {
        $entityName = $mainEntity->getEntityName();
        $objectName = lcfirst($entityName);
        $primaryKeyName = $mainEntity->getPrimaryKey();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);
        $lines = array();
        
        $lines[] = "if(".parent::VAR."inputPost->getUserAction() == UserAction::CREATE)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.$this->createConstructor($objectName, $entityName);
        foreach($appFields as $field)
        {
            $line = $this->createSetter($objectName, $field->getFieldName(), $field->getInputFilter());
            if($line != null)
            {
                $lines[] = $line;
            }
        }

        $upperAdminCreate = PicoStringUtil::upperCamelize($this->entityInfo->getAdminCreate());
        $upperTimeCreate = PicoStringUtil::upperCamelize($this->entityInfo->getTimeCreate());
        $upperIpCreate = PicoStringUtil::upperCamelize($this->entityInfo->getIpCreate());

        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperAdminCreate."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperTimeCreate."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperIpCreate."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";

        $upperAdminEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminEdit());
        $upperTimeEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeEdit());
        $upperIpEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpEdit());

        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperAdminEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperTimeEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperIpEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";

        $lines[] = parent::TAB1.parent::PHP_TRY;
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_INSERT_END;
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'newId = '.parent::VAR.$objectName.parent::CALL_GET.$upperPrimaryKeyName."();";
        if(isset($callbackSuccess) && is_callable($callbackSuccess))
        {
            
            $lines[] = call_user_func($callbackSuccess, $objectName, $this->getStringOf($mainEntity->getPrimaryKey()));
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'currentModule->redirectTo(UserAction::DETAIL, '.$this->getStringOf($mainEntity->getPrimaryKey()).', $newId);'; //NOSONAR
        }
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::TAB1."catch(Exception \$e)"; //NOSONAR
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;
        if(isset($callbackFailed) && is_callable($callbackFailed))
        {
            $lines[] = call_user_func($callbackFailed, $objectName, $this->getStringOf($mainEntity->getPrimaryKey()), '$e');
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1."\$currentModule->redirectToItself();";
        }
        
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::CURLY_BRACKET_CLOSE;

        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create UPDATE section without approval and trash.
     *
     * This method generates code for updating an existing entity based on the provided fields and main entity.
     *
     * @param MagicObject $mainEntity The main entity to be updated.
     * @param AppField[] $appFields The fields to be included in the update operation.
     * @return string The generated code for the update section.
     */
    public function createUpdateSection($mainEntity, $appFields)
    {
        $entityName = $mainEntity->getEntityName();
        $primaryKeyName = $mainEntity->getPrimaryKey();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);
        $objectName = lcfirst($entityName);
        $updatePk = false;
        $lines = array();
        
        $lines[] = "if(".parent::VAR."inputPost->getUserAction() == UserAction::UPDATE)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.$this->createConstructor($objectName, $entityName);
        
        foreach($appFields as $field)
        {
            if($primaryKeyName == $field->getFieldName())
            {
                $updatePk = true;
            }
            $line = $this->createSetter($objectName, $field->getFieldName(), $field->getInputFilter());
            if($line != null)
            {
                $lines[] = $line;
            }
        }

        $upperAdminEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminEdit());
        $upperTimeEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeEdit());
        $upperIpEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpEdit());

        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperAdminEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperTimeEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperIpEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";

        if(!$updatePk)
        {
            $line = $this->createSetter($objectName, $primaryKeyName, $this->getInputFilter($primaryKeyName));
            $lines[] = $line;
        }

        $lines[] = parent::TAB1.parent::PHP_TRY;
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_UPDATE_END;
        if($updatePk)
        {
            $lines[] = ''; 
            $lines[] = parent::TAB1.parent::TAB1.'// update primary key value';      
            $lines[] = parent::TAB1.parent::TAB1.'$specification = PicoSpecification::getInstance()->addAnd(new PicoPredicate('.$this->getStringOf(PicoStringUtil::camelize($primaryKeyName)).', $inputPost->get'.$upperPrimaryKeyName.'()));';
                
            $lines[] = parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
            $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.'->where($specification)->set'.$upperPrimaryKeyName.'($inputPost->get'.PicoStringUtil::upperCamelize('app_builder_new_pk').$upperPrimaryKeyName.'())'.parent::CALL_UPDATE_END;

            $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'newId = $inputPost->get'.PicoStringUtil::upperCamelize('app_builder_new_pk').$upperPrimaryKeyName.'();';
            $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'currentModule->redirectTo(UserAction::DETAIL, '.$this->getStringOf($mainEntity->getPrimaryKey()).', $newId);';    
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'newId = '.parent::VAR.$objectName.parent::CALL_GET.$upperPrimaryKeyName."();";
            $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'currentModule->redirectTo(UserAction::DETAIL, '.$this->getStringOf($mainEntity->getPrimaryKey()).', $newId);';    
        }
        
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::TAB1."catch(Exception \$e)";
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.parent::TAB1."\$currentModule->redirectToItself();";
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::CURLY_BRACKET_CLOSE;
        
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create DELETE section without approval and trash.
     *
     * @param MagicObject $mainEntity The main entity to be deleted.
     * @param bool $withTrash Indicates if the deletion is soft (to trash).
     * @param MagicObject|null $trashEntity The trash entity if using soft delete.
     * @return string The generated code for the delete section.
     */
    public function createDeleteSection($mainEntity, $withTrash = false, $trashEntity = null)
    {
        $entityName = $mainEntity->getEntityName();
        $pkName =  $mainEntity->getPrimaryKey();

        $objectName = lcfirst($entityName);
        
        $objectNameBk = $objectName;
        $lines = array();
        $upperPkName = PicoStringUtil::upperCamelize($pkName);
        
        $lines[] = "if(".parent::VAR."inputPost->getUserAction() == UserAction::DELETE)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->countableCheckedRowId())";
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.parent::TAB1."foreach(".parent::VAR."inputPost->getCheckedRowId() as ".parent::VAR."rowId)";    
        $lines[] = parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::PHP_TRY;    
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_OPEN;
        if($withTrash)
        {
            $entityTrashName = $trashEntity->getEntityName();
            $objectTrashName = lcfirst($entityTrashName);
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.$this->createConstructor($objectNameBk, $entityName);
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectNameBk."->findOneBy".$upperPkName."(".parent::VAR."rowId);";
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."if(".parent::VAR.$objectNameBk."->isset".$upperPkName."())";
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_OPEN;       
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.$this->createConstructor($objectTrashName, $entityTrashName, $objectNameBk);
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectTrashName.parent::CALL_INSERT_END;
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectNameBk."->delete();";
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.$this->createConstructor($objectNameBk, $entityName);
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectNameBk."->where(PicoSpecification::getInstance()";
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->equals(".$this->getStringOf($pkName).", ".parent::VAR."rowId))";
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.")";
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->delete();";
        }   
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."catch(Exception \$e)";    
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Do something here to handle exception";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_CLOSE;    
        $lines[] = parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_CLOSE;

        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectToItself();';
        
        $lines[] = parent::CURLY_BRACKET_CLOSE;
        
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create ACTIVATION section without approval and trash.
     *
     * @param MagicObject $mainEntity The main entity to be activated.
     * @param string $activationKey The key used for activation.
     * @param string $userAction The user action for activation.
     * @param bool $activationValue The value indicating activation status.
     * @return string The generated code for the activation section.
     */
    public function createActivationSectionBase($mainEntity, $activationKey, $userAction, $activationValue)
    {
        $entityName = $mainEntity->getEntityName();
        $pkName =  $mainEntity->getPrimaryKey();

        $objectName = lcfirst($entityName);
        
        $upperAdminEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminEdit());
        $upperTimeEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeEdit());
        $upperIpEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpEdit());
        
        $lines = array();
        $upperActivationKey = PicoStringUtil::upperCamelize($activationKey);
        $lines[] = "if(".parent::VAR."inputPost->getUserAction() == $userAction)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->countableCheckedRowId())";
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;

        $lines[] = parent::TAB1.parent::TAB1."foreach(".parent::VAR."inputPost->getCheckedRowId() as ".parent::VAR."rowId)";    
        $lines[] = parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::PHP_TRY;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_OPEN;
            
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName."->where(PicoSpecification::getInstance()";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->equals(".$this->getStringOf(PicoStringUtil::camelize($pkName)).", ".parent::VAR."rowId))";

        if($activationValue)
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->notEquals(".$this->getStringOf(PicoStringUtil::camelize($this->entityInfo->getActive())).", true))";
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->notEquals(".$this->getStringOf(PicoStringUtil::camelize($this->entityInfo->getActive())).", false))";
        }

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.")";
        
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperAdminEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperTimeEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperIpEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).")";
        
        if($activationValue)
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperActivationKey."(true)";
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperActivationKey."(false)";
        }
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_UPDATE_END;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."catch(Exception ".parent::VAR."e)";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Do something here to handle exception";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::TAB1.parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_CLOSE;
        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectToItself();';
        $lines[] = parent::CURLY_BRACKET_CLOSE;
        
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create ACTIVATION section without approval and trash.
     *
     * @param MagicObject $mainEntity The main entity to be activated.
     * @param string $activationKey The key used for activation.
     * @return string The generated code for the activation section.
     */
    public function createActivationSection($mainEntity, $activationKey)
    {
        return $this->createActivationSectionBase($mainEntity, $activationKey, 'UserAction::ACTIVATE', true);
    }
    
    /**
     * Create DEACTIVATION section without approval and trash.
     *
     * @param MagicObject $mainEntity The main entity to be deactivated.
     * @param string $activationKey The key used for deactivation.
     * @return string The generated code for the deactivation section.
     */
    public function createDeactivationSection($mainEntity, $activationKey)
    {
        return $this->createActivationSectionBase($mainEntity, $activationKey, 'UserAction::DEACTIVATE', false);
    }
}