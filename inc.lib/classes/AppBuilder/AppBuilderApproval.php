<?php

namespace AppBuilder;

use AppBuilder\Base\AppBuilderBase;
use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

class AppBuilderApproval extends AppBuilderBase
{
    /**
     * Create INSERT section without approval and trash
     *
     * @param MagicObject $mainEntity
     * @param AppField[] $appFields
     * @param boolean $approvalRequired
     * @param MagicObject $approvalEntity
     * @return string
     */
    public function createInsertApprovalSection($mainEntity, $appFields, $approvalRequired, $approvalEntity)
    {
        $entityName = $mainEntity->getEntityName();
        $objectName = lcfirst($entityName);
        
        $entityApprovalName = $approvalEntity->getEntityName();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($mainEntity->getPrimaryKey());
        $upperApprovalPkName = PicoStringUtil::upperCamelize($approvalEntity->getPrimaryKey());
        $objectApprovalName = lcfirst($entityApprovalName);
        $upperWaitingFor = PicoStringUtil::upperCamelize($this->entityInfo->getWaitingFor());
        $upperDraft = PicoStringUtil::upperCamelize($this->entityInfo->getDraft());

        $approvalId = PicoStringUtil::upperCamelize($this->entityInfo->getApprovalId());

        $lines = array();
        
        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == UserAction::CREATE)";
        $lines[] = "{";
        $lines[] = parent::TAB1.$this->createConstructor($objectName, $entityName);
        foreach($appFields as $field)
        {
            $line = $this->createSetter($objectName, $field->getFieldName(), $field->getInputFilter());
            if($line != null)
            {
                $lines[] = $line;
            }
        }
        
        // set draft
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperDraft."(true);";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperWaitingFor."(WaitingFor::CREATE);";

        $upperAdminCreate = PicoStringUtil::upperCamelize($this->entityInfo->getAdminCreate());
        $upperTimeCreate = PicoStringUtil::upperCamelize($this->entityInfo->getTimeCreate());
        $upperIpCreate = PicoStringUtil::upperCamelize($this->entityInfo->getIpCreate());

        $upperAdminEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminEdit());
        $upperTimeEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeEdit());
        $upperIpEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpEdit());

        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperAdminCreate."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperTimeCreate."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperIpCreate."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";

        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperAdminEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperTimeEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperIpEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";

        $lines[] = "";

        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_INSERT_END;

        /*
        No approval record required
        $lines[] = "";
        $lines[] = parent::TAB1.$this->createConstructor($objectApprovalName, $entityApprovalName, $objectName);
        $lines[] = parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_INSERT_END;
        $lines[] = parent::TAB1.$this->createConstructor($objectName."Update", $entityName);
        $lines[] = parent::TAB1.parent::VAR.$objectName."Update".parent::CALL_SET.$upperPrimaryKeyName."(".parent::VAR
        .$objectName.parent::CALL_GET.$upperPrimaryKeyName."())".parent::CALL_SET.$approvalId."(".parent::VAR.$objectApprovalName.parent::CALL_GET.$upperApprovalPkName."())".parent::CALL_UPDATE_END;
        
        */

        $lines[] = parent::TAB1.parent::VAR.'newId = '.parent::VAR.$objectName.parent::CALL_GET.$upperPrimaryKeyName."();";
        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectTo(UserAction::DETAIL, '.$this->getStringOf($mainEntity->getPrimaryKey()).', $newId);';

        $lines[] = "}";

        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create UPDATE section without approval and trash
     *
     * @param MagicObject $mainEntity
     * @param AppField[] $appFields
     * @param boolean $approvalRequired
     * @param MagicObject $approvalEntity
     * @return string
     */
    public function createUpdateApprovalSection($mainEntity, $appFields, $approvalRequired, $approvalEntity)    
    {
        $entityName = $mainEntity->getEntityName();
        $primaryKeyName = $mainEntity->getPrimaryKey();
        $objectName = lcfirst($entityName);
        $entityApprovalName = $approvalEntity->getEntityName();
        $pkeyApprovalName = $approvalEntity->getPrimaryKey();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);
        $approvalId = PicoStringUtil::upperCamelize($this->entityInfo->getApprovalId());

        $objectApprovalName = lcfirst($entityApprovalName);
        $upperWaitingFor = PicoStringUtil::upperCamelize($this->entityInfo->getWaitingFor());
        $lines = array();
        
        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == UserAction::UPDATE)";
        $lines[] = "{";
        $lines[] = parent::TAB1.$this->createConstructor($objectApprovalName, $entityApprovalName);
        foreach($appFields as $field)
        {
            $line = $this->createSetter($objectApprovalName, $field->getFieldName(), $field->getInputFilter(), $primaryKeyName, true);
            if($line != null)
            {
                $lines[] = $line;
            }
        }

        $upperAdminEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminEdit());
        $upperTimeEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeEdit());
        $upperIpEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpEdit());


        $lines[] = parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_SET.$upperAdminEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_SET.$upperTimeEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_SET.$upperIpEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";

        $lines[] = "";
        $lines[] = parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_INSERT_END;

        $lines[] = "";

        $upperAdminAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminAskEdit());
        $upperTimeAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeAskEdit());
        $upperIpAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpAskEdit());
        $upperPkeyApprovalName = PicoStringUtil::upperCamelize($pkeyApprovalName);

        $lines[] = parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperAdminAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperTimeAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperIpAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";
        $lines[] = parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperPrimaryKeyName."(".parent::VAR."inputPost".parent::CALL_GET.$upperPrimaryKeyName."())".parent::CALL_SET.$approvalId."(".parent::VAR.$objectApprovalName.parent::CALL_GET.$upperPkeyApprovalName."())".parent::CALL_SET.$upperWaitingFor."(WaitingFor::UPDATE)->update();";
        
        $lines[] = parent::TAB1.parent::VAR.'newId = '.parent::VAR.$objectName.parent::CALL_GET.$upperPrimaryKeyName."();";
        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectTo(UserAction::DETAIL, '.$this->getStringOf($mainEntity->getPrimaryKey()).', $newId);';
        
        $lines[] = "}";
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create UPDATE section without approval and trash
     *
     * @param AppField[] $appFields
     * @param string $entityName
     * @param string $pkName
     * @param mixed $pkValue
     * @return string
     */
    public function createDeleteApprovalSectionBase($entityName, $pkName, $userAction, $waitingForKey, $waitingForFalue)
    {
        $objectName = lcfirst($entityName);
        $lines = array();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($pkName);
        $upperWaitingFor = PicoStringUtil::upperCamelize($waitingForKey);
         
        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == $userAction)";
        $lines[] = "{";
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->countableCheckedRowId())";
        $lines[] = parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1."foreach(".parent::VAR."inputPost".parent::CALL_GET."CheckedRowId() as ".parent::VAR."rowId".")";    
        $lines[] = parent::TAB1.parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperPrimaryKeyName."(".parent::VAR."rowId".")".parent::CALL_SET.$upperWaitingFor."(".parent::VAR.$waitingForFalue.");";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_UPDATE_END;
        $lines[] = parent::TAB1.parent::TAB1."}";
        $lines[] = parent::TAB1."}";
        $lines[] = "}";
        
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create delete approval section
     *
     * @param MagicObject $entityName
     * @return string
     */
    public function createDeleteApprovalSection($mainEntity)
    {
        $entityName = $mainEntity->getEntityName();
        $pkName =  $mainEntity->getPrimaryKey();
        $userAction = 'UserAction::DELETE';
        $waitingForFalue = WaitingFor::DELETE;
        return $this->createWaitingForSectionBase($entityName, $pkName, $userAction, $waitingForFalue);
    }
    
    /**
     * Create ACTIVATION section without approval and trash
     *
     * @param AppField[] $appFields
     * @param string $entityName
     * @param string $pkName
     * @param string $userAction
     * @param boolean $waitingForValue
     * @return string
     */
    public function createWaitingForSectionBase($entityName, $pkName, $userAction, $waitingForValue)
    {
        $objectName = lcfirst($entityName);
        $lines = array();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($pkName);
        $fieldWaitingFor = $this->getentityInfo()->getWaitingFor();
        $camelWaitingFor = PicoStringUtil::camelize($fieldWaitingFor);
        $upperWaitingFor = PicoStringUtil::upperCamelize($this->getentityInfo()->getWaitingFor());
        $waitingFor = str_replace('UserAction', 'WaitingFor', $userAction);

        $upperAdminAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminAskEdit());
        $upperTimeAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeAskEdit());
        $upperIpAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpAskEdit());

        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == $userAction)";
        $lines[] = "{";
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->countableCheckedRowId())";
        $lines[] = parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1."foreach(".parent::VAR."inputPost->getCheckedRowId() as ".parent::VAR."rowId)";    
        $lines[] = parent::TAB1.parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."try";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName."->where(PicoSpecification::getInstance()";

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()".parent::CALL_SET.$upperPrimaryKeyName."(".parent::VAR."rowId))";

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."PicoSpecification::getInstance()";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addOr(PicoPredicate::getInstance()->equals(".$this->getStringOf(PicoStringUtil::camelize($this->entityInfo->getWaitingFor())).", WaitingFor::NOTHING))";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addOr(PicoPredicate::getInstance()->equals(".$this->getStringOf(PicoStringUtil::camelize($this->entityInfo->getWaitingFor())).", null))";

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.")";

        if($waitingForValue == WaitingFor::ACTIVATE)
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->notEquals(".$this->getStringOf(PicoStringUtil::camelize($this->entityInfo->getActive())).", true))";
        }
        else if($waitingForValue == WaitingFor::DEACTIVATE)
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->notEquals(".$this->getStringOf(PicoStringUtil::camelize($this->entityInfo->getActive())).", false))";
        }

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.")";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperAdminAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperTimeAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperIpAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperWaitingFor."(".$waitingFor.")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_UPDATE_END;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."}";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."catch(Exception ".parent::VAR."e)";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Do something here when record is not found";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."}";
        $lines[] = parent::TAB1.parent::TAB1."}";
        $lines[] = parent::TAB1."}";


        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectToItself();';

        $lines[] = "}";
        
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create ACTIVATION section without approval and trash
     *
     * @param AppField[] $appFields
     * @param MagicObject $mainEntity
     * @param string $pkName
     * @param string $activationKey
     * @param boolean $activationValue
     * @return string
     */
    public function createActivationApprovalSection($mainEntity)
    {
        $entityName = $mainEntity->getEntityName();
        $pkName =  $mainEntity->getPrimaryKey();
        $waitingForFalue = WaitingFor::ACTIVATE;
        $userAction = 'UserAction::ACTIVATE';
        return $this->createWaitingForSectionBase($entityName, $pkName, $userAction, $waitingForFalue);
    }
    
    /**
     * Create DEACTIVATION section without approval and trash
     *
     * @param AppField[] $appFields
     * @param MagicObject $mainEntity
     * @param string $activationKey
     * @param boolean $activationValue
     * @return string
     */
    public function createDeactivationApprovalSection($mainEntity)
    {
        $entityName = $mainEntity->getEntityName();
        $pkName =  $mainEntity->getPrimaryKey();
        $waitingForFalue = WaitingFor::DEACTIVATE;
        $userAction = 'UserAction::DEACTIVATE';
        return $this->createWaitingForSectionBase($entityName, $pkName, $userAction, $waitingForFalue);
    }

    /**
     * Undocumented function
     *
     * @param MagicObject $mainEntity
     * @param AppField[] $editFields
     * @param boolean $approvalRequired
     * @param MagicObject $approvalEntity
     * @param boolean $trashRequired
     * @param MagicObject $trashEntity
     * @return string
     */
    public function createApprovalSection($mainEntity, $editFields, $approvalRequired, $approvalEntity, $trashRequired, $trashEntity)
    {
        $currentUser = $this->getCurrentAction()->getUserFunction();
        $currentTime = $this->getCurrentAction()->getTimeFunction();
        $currentIp = $this->getCurrentAction()->getIpFunction();

        $entityName = $mainEntity->getEntityName();
        $pkName =  $mainEntity->getPrimaryKey();
        
        $entityApprovalName = $approvalEntity->getEntityName();
        $entityTrashName = $trashEntity->getEntityName();
        
        $camelPkName = PicoStringUtil::camelize($pkName);
        $toBeCopied = array();
        foreach(array_keys($editFields) as $val)
        {
            $prop = PicoStringUtil::camelize($val);
            if(!in_array($val, $this->skipedAutoSetter) && $prop != $camelPkName)
            {
                $toBeCopied[] = $this->getStringOf($prop);
            }
        }
        $entityInfoName = "entityInfo";
        $entityApvInfoName = "entityApvInfo";
        $userAction = 'UserAction::APPROVE';
        $objectName = lcfirst($entityName);
        $lines = array();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($pkName);
        $variableName = PicoStringUtil::camelize($pkName);

        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == $userAction)";
        $lines[] = "{";
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->isset".$upperPrimaryKeyName."())";
        $lines[] = parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$variableName." = ".parent::VAR."inputPost->get".$upperPrimaryKeyName."();";
    
        $lines[] = parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName."->findOneBy".$upperPrimaryKeyName."(".parent::VAR.$variableName.");";
        $lines[] = parent::TAB1.parent::TAB1."if(".parent::VAR.$objectName."->isset".$upperPrimaryKeyName."())";
        $lines[] = parent::TAB1.parent::TAB1."{";

        $lines[] = $this->constructApproval($objectName, $entityInfoName, $entityApvInfoName, $trashRequired, $trashEntity);
        $lines[] = "";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback = new SetterGetter();";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterInsert(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback on new data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."return true;".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."BeforeUpdate(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback before update data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterUpdate(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback after update data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR     
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterActivate(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback after activate data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR     
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterDeactivate(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback after deactivate data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR     
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."BeforeDelete(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback before delete data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR     
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterDelete(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback after delete data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterApprove(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback after approve data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."// List of properties to be copied from $entityApprovalName to $entityName when when the user approves data modification. You can add or remove them.".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."columToBeCopied = array(".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.implode(', '.parent::NEW_LINE.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1, $toBeCopied).parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.");";
        $lines[] = "";

        if($entityTrashName == null)
        {
            $trash = "null";
        }
        else
        {
            $trash = "new $entityTrashName()";
        }
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approval->approve("
        .parent::VAR."columToBeCopied, new $entityApprovalName(), $trash, ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentUser.",  ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentTime.",  ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentIp.", ".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback);";                                               


        $lines[] = parent::TAB1.parent::TAB1."}";

        $lines[] = parent::TAB1."}";


        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectToItselfWithRequireApproval();';

        $lines[] = "}";
        return implode(parent::NEW_LINE, $lines);
        
    }

    
    /**
     * Undocumented function
     *
     * @param MagicObject $mainEntity
     * @param boolean $approvalRequired
     * @param MagicObject $approvalEntity
     * @return string
     */
    public function createRejectionSection($mainEntity, $approvalRequired, $approvalEntity)
    {
        $currentUser = $this->getCurrentAction()->getUserFunction();
        $currentTime = $this->getCurrentAction()->getTimeFunction();
        $currentIp = $this->getCurrentAction()->getIpFunction();

        $entityName = $mainEntity->getEntityName();
        $entityApprovalName = $approvalEntity->getEntityName();
        $pkName =  $mainEntity->getPrimaryKey();
        $entityInfoName = "entityInfo";
        $entityApvInfoName = "entityApvInfo";
        $userAction = 'UserAction::REJECT';
        $objectName = lcfirst($entityName);
        $lines = array();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($pkName);
        $variableName = PicoStringUtil::camelize($pkName);

        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == $userAction)";
        $lines[] = "{";
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->isset".$upperPrimaryKeyName."())";
        $lines[] = parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$variableName." = ".parent::VAR."inputPost->get".$upperPrimaryKeyName."();";
    
        $lines[] = parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName."->findOneBy".$upperPrimaryKeyName."(".parent::VAR.$variableName.");";
        $lines[] = parent::TAB1.parent::TAB1."if(".parent::VAR.$objectName."->isset".$upperPrimaryKeyName."())";
        $lines[] = parent::TAB1.parent::TAB1."{";

        $lines[] = $this->constructApproval($objectName, $entityInfoName, $entityApvInfoName).parent::NEW_LINE;

        $lines[] = "";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback = new SetterGetter();";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."BeforeReject(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback before reject data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterReject(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// callback after reject data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."}); ".parent::NEW_LINE; //NOSONAR

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approval->reject(new $entityApprovalName(),".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentUser.",  ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentTime.",  ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentIp.", ".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.");";

        $lines[] = parent::TAB1.parent::TAB1."}";

        $lines[] = parent::TAB1."}";

        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectToItselfWithRequireApproval();';

        $lines[] = "}";
        return implode(parent::NEW_LINE, $lines);
    }

    protected function constructApproval($objectName, $entityInfoName, $entityApvInfoName, $trashRequired = false, $trashEntity = null)
    {
        $result = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approval = new PicoApproval(".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName.", ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$entityInfoName.", ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$entityApvInfoName.", ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1."function(".parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null, ".parent::VAR."userId = null) {".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// approval validation here".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// if the return is incorrect, approval cannot take place".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// e.g. return ".parent::VAR."param1->notEquals".PicoStringUtil::upperCamelize($this->entityInfo->getAdminAskEdit())."(".parent::VAR."userId);".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."return true;".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1."}";

        if($trashRequired)
        {
            $entityTrashName = $trashEntity->getEntityName();
            
            $result .= ", ".parent::NEW_LINE.parent::TAB1.parent::TAB1.parent::TAB1."true, ".parent::NEW_LINE 
            .parent::TAB1.parent::TAB1.parent::TAB1."new $entityTrashName() ".parent::NEW_LINE;
            
        }

        $result .= parent::TAB1.parent::TAB1.parent::TAB1.");"; 
        return $result;
    }
}