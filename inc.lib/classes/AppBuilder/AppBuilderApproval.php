<?php

namespace AppBuilder;

use MagicObject\MagicObject;
use MagicObject\Util\PicoStringUtil;

class AppBuilderApproval extends AppBuilderBase
{
    /**
     * Creates the INSERT section with approval and trash functionality.
     *
     * This method generates code for inserting a new entity, including setting fields and handling approval. 
     * It sets the entity status to draft and specifies the approval state. 
     * If approval is required, it manages the associated approval entity details.
     * In case of a successful operation, a success callback can be triggered; otherwise, a default redirection is applied.
     * In case of failure, a failure callback can be used or a default redirection is applied.
     *
     * @param MagicObject $mainEntity The main entity to be inserted.
     * @param AppField[] $appFields The fields to set for the entity.
     * @param bool $approvalRequired Indicates if approval is required for the insert operation.
     * @param MagicObject $approvalEntity The entity related to the approval process.
     * @param callable|null $callbackSuccess Optional callback function to be executed on successful insert.
     * @param callable|null $callbackFailed Optional callback function to be executed on failed insert.
     * @return string The generated code for the insert approval section.
     */
    public function createInsertApprovalSection($mainEntity, $appFields, $approvalRequired, $approvalEntity, $callbackSuccess = null, $callbackFailed = null)
    {
        $entityName = $mainEntity->getEntityName();
        $objectName = lcfirst($entityName);
        
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($mainEntity->getPrimaryKey());
        $upperWaitingFor = PicoStringUtil::upperCamelize($this->entityInfo->getWaitingFor());
        $upperDraft = PicoStringUtil::upperCamelize($this->entityInfo->getDraft());

        $lines = array();
        
        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == UserAction::CREATE)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1.$this->createConstructor($objectName, $entityName);
        
        $inputFile = 0;
        foreach($appFields as $field)
        {
            if($this->isInputFile($field->getDataType()))
            {
                $inputFile++;
            }
            else
            {
                $line = $this->createSetter($objectName, $field->getFieldName(), $field->getInputFilter()).";";
                if($line != null)
                {
                    $lines[] = $line;
                }
            }
        }
        if($inputFile > 0)
        {
            $linesUpload = $this->createFileUploader($appFields, $objectName);
            $lines = array_merge($lines, $linesUpload);
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

        $lines[] = parent::TAB1."try";
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;

        // TODO: Add validation here

        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_INSERT_END;

        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'newId = '.parent::VAR.$objectName.parent::CALL_GET.$upperPrimaryKeyName."();";
        if($this->isCallable($callbackSuccess))
        {
            
            $lines[] = call_user_func($callbackSuccess, $objectName, AppBuilderBase::getStringOf($mainEntity->getPrimaryKey()));
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'currentModule->redirectTo(UserAction::DETAIL, '.AppBuilderBase::getStringOf($mainEntity->getPrimaryKey()).', $newId);'; //NOSONAR
        }

        $lines[] = parent::TAB1.parent::CURLY_BRACKET_CLOSE;

        // TODO: Add catch(InvalidValueException $e)

        $lines[] = parent::TAB1."catch(Exception \$e)";
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;
        if($this->isCallable($callbackFailed))
        {
            $lines[] = call_user_func($callbackFailed, $objectName, AppBuilderBase::getStringOf($mainEntity->getPrimaryKey()), '$e');
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
     * Creates the UPDATE section with approval and trash functionality.
     *
     * This method generates code for updating an existing entity, including setting fields and handling approval. 
     * It manages the status of the entity being updated and records the necessary admin details, timestamps, and IP address. 
     * If approval is required, it includes logic for the associated approval entity.
     * In case of a successful operation, a success callback can be triggered; otherwise, a default redirection is applied.
     * In case of failure, a failure callback can be used or a default redirection is applied.
     *
     * @param MagicObject $mainEntity The main entity to be updated.
     * @param AppField[] $appFields The fields to set for the entity.
     * @param bool $approvalRequired Indicates if approval is required for the update operation.
     * @param MagicObject $approvalEntity The entity related to the approval process.
     * @param callable $callbackSuccess Callback function to be executed on successful update.
     * @param callable $callbackFailed Callback function to be executed on failed update.
     * @return string The generated code for the update approval section.
     */
    public function createUpdateApprovalSection($mainEntity, $appFields, $approvalRequired, $approvalEntity, $callbackSuccess, $callbackFailed)    // NOSONAR
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
        $lines[] = parent::CURLY_BRACKET_OPEN;

        $lines[] = parent::TAB1."try";
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;
        
        $lines[] = parent::TAB1.parent::TAB1.'$specification = PicoSpecification::getInstanceOf('.self::getStringOf(PicoStringUtil::camelize($primaryKeyName)).', $inputPost->get'.$upperPrimaryKeyName."(PicoFilterConstant::".$this->getInputFilter($primaryKeyName)."));"; // NOSONAR
        $lines[] = parent::TAB1.parent::TAB1.'$specification->addAnd($dataFilter);';
        
        $lines[] = "";
        
        $lines[] = parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.'->findOne($specification);';
        $lines[] = "";
        
        $lines[] = parent::TAB1. parent::TAB1.$this->createConstructor($objectApprovalName, $entityApprovalName, $objectName);

        $inputFile = 0;
        foreach($appFields as $field)
        {
            if($this->isInputFile($field->getDataType()))
            {
                $inputFile++;
            }
            else
            {
                $line = parent::TAB1.$this->createSetter($objectApprovalName, $field->getFieldName(), $field->getInputFilter(), $primaryKeyName, true).";";
                if($line != null)
                {
                    $lines[] = $line;
                }
            }
        }
        if($inputFile > 0)
        {
            $linesUpload = $this->createFileUploader($appFields, $objectApprovalName, 1);
            $lines = array_merge($lines, $linesUpload);
        }
        $upperAdminEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminEdit());
        $upperTimeEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeEdit());
        $upperIpEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpEdit());


        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_SET.$upperAdminEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_SET.$upperTimeEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_SET.$upperIpEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_SET.$upperPrimaryKeyName."(".parent::VAR.$objectName.parent::CALL_GET.$upperPrimaryKeyName.parent::BRACKETS.");";

        // TODO: Add validation

        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectApprovalName.parent::CALL_INSERT_END;

        $lines[] = "";

        $upperAdminAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminAskEdit());
        $upperTimeAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeAskEdit());
        $upperIpAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpAskEdit());
        $upperPkeyApprovalName = PicoStringUtil::upperCamelize($pkeyApprovalName);

        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperAdminAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).");";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperTimeAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).");";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperIpAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).");";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$approvalId."(".parent::VAR.$objectApprovalName.parent::CALL_GET.$upperPkeyApprovalName."())".parent::CALL_SET.$upperWaitingFor."(WaitingFor::UPDATE)->update();";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'newId = '.parent::VAR.$objectName.parent::CALL_GET.$upperPrimaryKeyName."();";
        if($this->isCallable($callbackSuccess))
        {
            
            $lines[] = call_user_func($callbackSuccess, $objectName, AppBuilderBase::getStringOf($mainEntity->getPrimaryKey()));
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'currentModule->redirectTo(UserAction::DETAIL, '.AppBuilderBase::getStringOf($mainEntity->getPrimaryKey()).', $newId);';
        }
        
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_CLOSE;

        // TODO: Add catch(InvalidValueException $e)

        $lines[] = parent::TAB1."catch(Exception \$e)";
        $lines[] = parent::TAB1.parent::CURLY_BRACKET_OPEN;
        if($this->isCallable($callbackFailed))
        {
            $lines[] = call_user_func($callbackFailed, $objectName, AppBuilderBase::getStringOf($mainEntity->getPrimaryKey()), '$e');
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
     * Create UPDATE section with approval and trash.
     *
     * @param string $entityName The name of the entity to be deleted.
     * @param string $primaryKeyName The primary key name of the entity.
     * @param mixed $userAction The user action type (e.g., delete).
     * @param string $waitingForKey The key for the waiting state.
     * @param mixed $waitingForValue The value for the waiting state.
     * @return string The generated code for the delete approval section.
     */
    public function createDeleteApprovalSectionBase($entityName, $primaryKeyName, $userAction, $waitingForKey, $waitingForFalue)
    {
        $objectName = lcfirst($entityName);
        $lines = array();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);
        $upperWaitingFor = PicoStringUtil::upperCamelize($waitingForKey);
         
        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == $userAction)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->countableCheckedRowId())";
        $lines[] = parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1."foreach(".parent::VAR."inputPost".parent::CALL_GET."CheckedRowId() as ".parent::VAR."rowId".")";    
        $lines[] = parent::TAB1.parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_SET.$upperPrimaryKeyName."(".parent::VAR."rowId".")".parent::CALL_SET.$upperWaitingFor."(".parent::VAR.$waitingForFalue.");";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_UPDATE_END;
        $lines[] = parent::TAB1.parent::TAB1."}";
        $lines[] = parent::TAB1."}";
        $lines[] = parent::CURLY_BRACKET_CLOSE;
        
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Creates a delete approval section for the specified entity.
     *
     * This method generates code for handling the approval of delete actions on a specified entity.
     * It utilizes the `createWaitingForSectionBase` method to manage the waiting state and 
     * includes optional callbacks for further processing upon completion or exception.
     *
     * @param MagicObject $mainEntity The main entity for which the delete approval section is created.
     * @param callable|null $callbackFinish Optional callback function to execute upon successful processing.
     * @param callable|null $callbackException Optional callback function to execute in case of an exception.
     * @return string The generated delete approval section code.
     */
    public function createDeleteApprovalSection($mainEntity, $callbackFinish, $callbackException)
    {
        $entityName = $mainEntity->getEntityName();
        $primaryKeyName =  $mainEntity->getPrimaryKey();
        $userAction = 'UserAction::DELETE';
        $waitingForFalue = WaitingFor::DELETE;
        return $this->createWaitingForSectionBase($entityName, $primaryKeyName, $userAction, $waitingForFalue, $callbackFinish, $callbackException);
    }
    
    /**
     * Creates a waiting for section based on the specified parameters.
     *
     * This method generates code to handle the waiting state of an entity based on user actions.
     * It checks if there are checked rows, constructs a query to update the waiting status of 
     * the specified entities, and includes error handling. Optional callbacks are provided 
     * for additional processing upon completion or exceptions.
     *
     * @param string $entityName Name of the entity to be processed.
     * @param string $primaryKeyName Primary key name of the entity.
     * @param string $userAction User action type (e.g., DELETE, UPDATE).
     * @param mixed $waitingForValue Value indicating the waiting state (e.g., WaitingFor::ACTIVATE).
     * @param callable|null $callbackFinish Optional callback function to execute upon completion.
     * @param callable|null $callbackException Optional callback function to execute on exception.
     * @return string The generated code for the waiting for section.
     */
    public function createWaitingForSectionBase($entityName, $primaryKeyName, $userAction, $waitingForValue, $callbackFinish = null, $callbackException = null)
    {
        $objectName = lcfirst($entityName);
        $lines = array();
        $camelPrimaryKeyName = PicoStringUtil::camelize($primaryKeyName);
        $upperWaitingFor = PicoStringUtil::upperCamelize($this->getentityInfo()->getWaitingFor());
        $waitingFor = str_replace('UserAction', 'WaitingFor', $userAction);

        $upperAdminAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getAdminAskEdit());
        $upperTimeAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getTimeAskEdit());
        $upperIpAskEdit = PicoStringUtil::upperCamelize($this->entityInfo->getIpAskEdit());

        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == $userAction)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->countableCheckedRowId())";
        $lines[] = parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1."foreach(".parent::VAR."inputPost->getCheckedRowId(PicoFilterConstant::".$this->getInputFilter($primaryKeyName).") as ".parent::VAR."rowId)";    
        $lines[] = parent::TAB1.parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."try";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName."->where(PicoSpecification::getInstance()";

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->equals(".AppBuilderBase::getStringOf($camelPrimaryKeyName).", ".parent::VAR."rowId))";

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."PicoSpecification::getInstance()";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addOr(PicoPredicate::getInstance()->equals(".AppBuilderBase::getStringOf(PicoStringUtil::camelize($this->entityInfo->getWaitingFor())).", WaitingFor::NOTHING))";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addOr(PicoPredicate::getInstance()->equals(".AppBuilderBase::getStringOf(PicoStringUtil::camelize($this->entityInfo->getWaitingFor())).", null))";

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.")";
        
        if($waitingForValue == WaitingFor::ACTIVATE)
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->notEquals(".AppBuilderBase::getStringOf(PicoStringUtil::camelize($this->entityInfo->getActive())).", true))";
        }
        else if($waitingForValue == WaitingFor::DEACTIVATE)
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(PicoPredicate::getInstance()->notEquals(".AppBuilderBase::getStringOf(PicoStringUtil::camelize($this->entityInfo->getActive())).", false))";
        }
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."->addAnd(\$dataFilter)";

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.")";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperAdminAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getUserFunction()).")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperTimeAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getTimeFunction()).")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperIpAskEdit."(".$this->fixVariableInput($this->getCurrentAction()->getIpFunction()).")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_SET.$upperWaitingFor."(".$waitingFor.")";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1.parent::CALL_UPDATE_END;
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."}";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."catch(Exception ".parent::VAR."e)";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."{";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Perform an action here when the record is not found.";
        
        if(isset($callbackException) && is_callable($callbackException))
        {
            // $objectName, $userAction, $primaryKeyName, $exceptionObject
            $lines[] = call_user_func($callbackException, $objectName, $userAction, $primaryKeyName, '$e');
        }
        else
        {
            $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."error_log(\$e);";
        }
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."}";
        $lines[] = parent::TAB1.parent::TAB1."}";
        $lines[] = parent::TAB1."}";

        if(isset($callbackFinish) && is_callable($callbackFinish))
        {
            $lines[] = call_user_func($callbackFinish, $objectName, $userAction, $primaryKeyName);
        }
        else
        {
            $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectToItself();';
        }

        $lines[] = parent::CURLY_BRACKET_CLOSE;
        
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Creates an activation approval section for the specified entity.
     *
     * This method generates code for handling the approval of activation actions on a specified entity.
     * It utilizes the `createWaitingForSectionBase` method to manage the waiting state and 
     * includes optional callbacks for further processing upon completion or exception.
     *
     * @param MagicObject $mainEntity The main entity for activation.
     * @param callable|null $callbackFinish Optional callback function to execute upon successful processing.
     * @param callable|null $callbackException Optional callback function to execute in case of an exception.
     * @return string The generated code for the activation approval section.
     */
    public function createActivationApprovalSection($mainEntity, $callbackFinish, $callbackException)
    {
        $entityName = $mainEntity->getEntityName();
        $primaryKeyName =  $mainEntity->getPrimaryKey();
        $waitingForFalue = WaitingFor::ACTIVATE;
        $userAction = 'UserAction::ACTIVATE';
        return $this->createWaitingForSectionBase($entityName, $primaryKeyName, $userAction, $waitingForFalue, $callbackFinish, $callbackException);
    }
    
    /**
     * Create a deactivation approval section.
     *
     * This method generates the code required for a deactivation approval section
     * based on the provided main entity and associated callback functions for
     * handling the completion and exceptions.
     *
     * @param MagicObject $mainEntity The main entity for deactivation.
     * @param callable $callbackFinish The callback to execute upon successful deactivation.
     * @param callable $callbackException The callback to execute if an error occurs.
     * @return string The generated code for the deactivation approval section.
     */
    public function createDeactivationApprovalSection($mainEntity, $callbackFinish, $callbackException)
    {
        $entityName = $mainEntity->getEntityName();
        $primaryKeyName =  $mainEntity->getPrimaryKey();
        $waitingForFalue = WaitingFor::DEACTIVATE;
        $userAction = 'UserAction::DEACTIVATE';
        return $this->createWaitingForSectionBase($entityName, $primaryKeyName, $userAction, $waitingForFalue, $callbackFinish, $callbackException);
    }

    /**
     * Create an approval section for the specified entity.
     *
     * @param MagicObject $mainEntity The main entity for approval.
     * @param AppField[] $editFields The fields to edit.
     * @param boolean $approvalRequired Whether approval is required.
     * @param MagicObject $approvalEntity The entity that holds approval information.
     * @param boolean $trashRequired Whether trash functionality is required.
     * @param MagicObject $trashEntity The entity used for trash operations.
     * @return string The generated approval section code.
     */
    public function createApprovalSection($mainEntity, $editFields, $approvalRequired, $approvalEntity, $trashRequired, $trashEntity)
    {
        $currentUser = $this->fixVariableInput($this->getCurrentAction()->getUserFunction());
        $currentTime = $this->fixVariableInput($this->getCurrentAction()->getTimeFunction());
        $currentIp = $this->fixVariableInput($this->getCurrentAction()->getIpFunction());

        $entityName = $mainEntity->getEntityName();
        $primaryKeyName =  $mainEntity->getPrimaryKey();
        
        $entityApprovalName = $approvalEntity->getEntityName();
        $entityTrashName = $trashEntity->getEntityName();
        
        $camelPkName = PicoStringUtil::camelize($primaryKeyName);
        $toBeCopied = array();
        foreach(array_keys($editFields) as $val)
        {
            $prop = PicoStringUtil::camelize($val);
            if(!in_array($val, $this->skipedAutoSetter) && $prop != $camelPkName)
            {
                $toBeCopied[] = AppBuilderBase::getStringOf($prop);
            }
        }
        $entityInfoName = "entityInfo";
        $entityApvInfoName = "entityApvInfo";
        $userAction = 'UserAction::APPROVE';
        $objectName = lcfirst($entityName);
        $lines = array();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);

        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == $userAction)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->isset".$upperPrimaryKeyName."())";
        $lines[] = parent::TAB1."{";
    

        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'specification = PicoSpecification::getInstanceOf('.self::getStringOf(PicoStringUtil::camelize($primaryKeyName)).', $inputPost->get'.$upperPrimaryKeyName."(PicoFilterConstant::".$this->getInputFilter($primaryKeyName)."));";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'specification->addAnd($dataFilter);';
        $lines[] = "";

        $lines[] = parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_FIND_ONE."(".parent::VAR."specification);";
        $lines[] = "";
        $lines[] = parent::TAB1.parent::TAB1."if(".parent::VAR.$objectName."->isset".$upperPrimaryKeyName."())";
        $lines[] = parent::TAB1.parent::TAB1."{";

        $lines[] = $this->constructApproval($objectName, $entityInfoName, $entityApvInfoName, $trashRequired, $trashEntity);
        $lines[] = "";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback = new SetterGetter();";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterInsert(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute after new data is inserted".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."return true;".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."BeforeUpdate(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute before updating data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterUpdate(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute after updating data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR     
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterActivate(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute after activating data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR     
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterDeactivate(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute after deactivating data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR     
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."BeforeDelete(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute before deleting data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR     
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterDelete(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute after deleting data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterApprove(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute after approval".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;


        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."// List of properties to be copied from $entityApprovalName to $entityName when the user approves data modification.";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1."// You can modify this list by adding or removing fields as needed.".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."columnToBeCopied = array(".parent::NEW_LINE
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
        .parent::VAR."columnToBeCopied, new $entityApprovalName(), $trash, ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentUser.",  ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentTime.",  ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentIp.", ".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback);";                                               


        $lines[] = parent::TAB1.parent::TAB1."}";

        $lines[] = parent::TAB1."}";


        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectToItselfWithRequireApproval();';

        $lines[] = parent::CURLY_BRACKET_CLOSE;
        return implode(parent::NEW_LINE, $lines);
    }
    
    /**
     * Create a rejection section for the specified entity.
     *
     * @param MagicObject $mainEntity The main entity for rejection.
     * @param boolean $approvalRequired Whether approval is required.
     * @param MagicObject $approvalEntity The entity that holds approval information.
     * @return string The generated rejection section code.
     */
    public function createRejectionSection($mainEntity, $approvalRequired, $approvalEntity)
    {
        $currentUser = $this->fixVariableInput($this->getCurrentAction()->getUserFunction());
        $currentTime = $this->fixVariableInput($this->getCurrentAction()->getTimeFunction());
        $currentIp = $this->fixVariableInput($this->getCurrentAction()->getIpFunction());

        $entityName = $mainEntity->getEntityName();
        $entityApprovalName = $approvalEntity->getEntityName();
        $primaryKeyName =  $mainEntity->getPrimaryKey();
        $entityInfoName = "entityInfo";
        $entityApvInfoName = "entityApvInfo";
        $userAction = 'UserAction::REJECT';
        $objectName = lcfirst($entityName);
        $lines = array();
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);

        $lines[] = "if(".parent::VAR."inputPost".parent::CALL_GET."UserAction() == $userAction)";
        $lines[] = parent::CURLY_BRACKET_OPEN;
        $lines[] = parent::TAB1."if(".parent::VAR."inputPost->isset".$upperPrimaryKeyName."())";
        $lines[] = parent::TAB1."{";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'specification = PicoSpecification::getInstanceOf('.self::getStringOf(PicoStringUtil::camelize($primaryKeyName)).', $inputPost->get'.$upperPrimaryKeyName."(PicoFilterConstant::".$this->getInputFilter($primaryKeyName)."));";
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.'specification->addAnd($dataFilter);';
        $lines[] = "";

        $lines[] = parent::TAB1.parent::TAB1.$this->createConstructor($objectName, $entityName);
        $lines[] = parent::TAB1.parent::TAB1.parent::VAR.$objectName.parent::CALL_FIND_ONE."(".parent::VAR."specification);";
        $lines[] = "";

        $lines[] = parent::TAB1.parent::TAB1."if(".parent::VAR.$objectName."->isset".$upperPrimaryKeyName."())";
        $lines[] = parent::TAB1.parent::TAB1."{";

        $lines[] = $this->constructApproval($objectName, $entityInfoName, $entityApvInfoName).parent::NEW_LINE;

        $lines[] = "";
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback = new SetterGetter();";
        
        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."BeforeReject(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute before reject data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::CALL_SET."AfterReject(function("
        .parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Logic to execute after reject data".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Your code goes here".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE //NOSONAR
        .parent::TAB1.parent::TAB1.parent::TAB1."});".parent::NEW_LINE;

        $lines[] = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approval->reject(new $entityApprovalName(),".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentUser.",  ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentTime.",  ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.$currentIp.", ".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approvalCallback".parent::NEW_LINE
        .parent::TAB1.parent::TAB1.parent::TAB1.");";

        $lines[] = parent::TAB1.parent::TAB1."}";

        $lines[] = parent::TAB1."}";

        $lines[] = parent::TAB1.parent::VAR.'currentModule->redirectToItselfWithRequireApproval();';

        $lines[] = parent::CURLY_BRACKET_CLOSE;
        return implode(parent::NEW_LINE, $lines);
    }

    /**
     * Construct the approval logic for an entity.
     *
     * @param string $objectName The name of the object to be approved.
     * @param string $entityInfoName The name of the entity info.
     * @param string $entityApvInfoName The name of the approval entity.
     * @param boolean $trashRequired Indicates if trash handling is required.
     * @param MagicObject|null $trashEntity The trash entity, if applicable.
     * @return string The generated approval construction code.
     */
    protected function constructApproval($objectName, $entityInfoName, $entityApvInfoName, $trashRequired = false, $trashEntity = null)
    {
        $result = parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR."approval = new PicoApproval(".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$objectName.", ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$entityInfoName.", ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::VAR.$entityApvInfoName.", ".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1."function(".parent::VAR."param1 = null, ".parent::VAR."param2 = null, ".parent::VAR."param3 = null) use (".parent::VAR."dataControlConfig, ".parent::VAR."database, ".parent::VAR."currentAction) {".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Approval validation logic".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// If the return is false, approval will not proceed".parent::NEW_LINE 
        .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."".parent::NEW_LINE;
         
        if($this->appFeatures->getApprovalByAnotherUser())
        {
            $result .= parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."return ".parent::VAR."dataControlConfig->isTwoUserApproval() && ".parent::VAR."param1->notEquals".PicoStringUtil::upperCamelize($this->entityInfo->getAdminAskEdit())."(".parent::VAR."currentAction->getUserId());".parent::NEW_LINE; 
        }
        else
        {
            $result .= parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."// Example: return ".parent::VAR."param1->notEquals".PicoStringUtil::upperCamelize($this->entityInfo->getAdminAskEdit())."(".parent::VAR."currentAction->getUserId());".parent::NEW_LINE 
            .parent::TAB1.parent::TAB1.parent::TAB1.parent::TAB1."return true;".parent::NEW_LINE;
        }
        
        $result .= parent::TAB1.parent::TAB1.parent::TAB1."}";

        if($trashRequired)
        {
            $entityTrashName = $trashEntity->getEntityName();
            
            $result .= ", ".parent::NEW_LINE.parent::TAB1.parent::TAB1.parent::TAB1."true, ".parent::NEW_LINE 
            .parent::TAB1.parent::TAB1.parent::TAB1."new $entityTrashName() ".parent::NEW_LINE;
            $result .= parent::TAB1.parent::TAB1.parent::TAB1.");";
        }
        else
        {
            $result .= ");"; 
        }
        
        return $result;
    }
}