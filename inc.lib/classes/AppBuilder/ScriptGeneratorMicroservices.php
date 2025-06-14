<?php

namespace AppBuilder;

use MagicObject\Generator\PicoEntityGenerator;

class ScriptGeneratorMicroservices extends ScriptGenerator
{
    /**
     * Generates the application module based on the provided configuration and request data.
     *
     * This method handles the creation, update, activation, deactivation, and deletion of entities
     * according to the specifications defined in the request. It constructs the necessary sections
     * for CRUD operations and prepares the corresponding GUI elements while considering any approval 
     * processes or trash management requirements.
     *
     * @param PicoDatabase $database The database connection object used for entity operations.
     * @param MagicObject|InputPost $request The request object containing field definitions and specifications.
     * @param AppSecretObject $builderConfig Configuration settings for building the application.
     * @param AppSecretObject $appConfig Application-specific configuration, including feature flags.
     * @param EntityInfo $entityInfo Information about the main entity being processed.
     * @param EntityApvInfo $entityApvInfo Information about the approval process for the entity.
     * @param bool $onlineInstallation Flag indicating whether Composer should be used in online mode
     *
     * @return int The number of entity files that were generated. It generates and writes the application module script
     * to the specified location, as defined in the request.
     */
    public function generate($database, $request, $builderConfig, $appConfig, $entityInfo, $entityApvInfo, $onlineInstallation) // NOSONAR
    {
        $insertFields = array();
        $editFields = array();
        $detailFields = array();
        $listFields = array();
        $exportFields = array();
        $filterFields = array();
        $referenceData = array();
        $referenceEntities = array();
        $referenceEntitiesUse = array();
        $allField = array();
        
        $clbk = $this->initializeCallbackFunctions($appConfig);
        
        $callbackCreateSuccess = $clbk->callbackCreateSuccess;
        $callbackCreateFailed = $clbk->callbackCreateFailed;
        $callbackUpdateSuccess = $clbk->callbackUpdateSuccess;
        $callbackUpdateFailed = $clbk->callbackUpdateFailed;
        $callbackUpdateStatusSuccess = $clbk->callbackUpdateStatusSuccess;
        $callbackUpdateStatusException = $clbk->callbackUpdateStatusException;
        
        foreach ($request->getFields() as $value) {
            $field = new AppField($value);
            $allField[] = $field;
    
            // Process the field by updating the arrays
            $this->processField(
                $field,
                $insertFields,
                $editFields,
                $detailFields,
                $listFields,
                $exportFields,
                $filterFields,
                $referenceData,
                $referenceEntities,
                $referenceEntitiesUse
            );
        }
        
        $entity = $request->getEntity();      
        $entityMain = $entity->getMainEntity();
        $entityApproval = $this->getEntityApproval($entity);
        $entityTrash = $this->getEntityTrash($entity); 
    
        $appFeatures = new AppFeatures($request->getFeatures());

        $entityMainName = $entityMain->getEntityName();
        $approvalRequired = $appFeatures->isApprovalRequired();
        $trashRequired = $appFeatures->isTrashRequired();
        $sortOrder = $appFeatures->isSortOrder();    
        $activationKey = $entityInfo->getActive();
        $appConf = $appConfig->getApplication();
        
        $uses = $this->createUse($appConf, $entityMainName, $approvalRequired, $sortOrder, $appFeatures);
        $uses = $this->addUseFromFieldType($uses, $request->getFields());   
        $uses = $this->addUseFromApproval($uses, $appConf, $approvalRequired, $entity);
        $uses = $this->addUseFromTrash($uses, $appConf, $trashRequired, $entity);
        if(!$appFeatures->isBackendOnly())
        {
            $uses = $this->addUseFromReference($uses, $appConf, $referenceEntitiesUse);
        }
        if(!$appFeatures->isBackendOnly() || $appFeatures->isExportToExcel() || $appFeatures->isExportToCsv())
        {
            $uses = $this->addExporterLibrary($uses, $appFeatures);      
        }
        $uses = $this->addAddEmptyLine($uses);     
        
        $uses[] = "";
        
        $includes = array();
        
        $includeDir = $this->getAuthFile($appConf);

        $dir = $this->getAbsoludeDir("__DIR__", $request->getTarget());
        
        $includes[] = "require_once $dir . $includeDir;";
        $includes[] = "";
        
        $usesSection = implode("\r\n", $uses);
        $includeSection = implode("\r\n", $includes);

        $upperModuleName = str_replace(" ", "", ucwords(strtolower($request->getModuleName())));
        
        $declaration = array();
        $declaration[] = AppBuilderBase::VAR."inputGet = new InputGet();";
        $declaration[] = AppBuilderBase::VAR."inputPost = new InputPost();";
        $declaration[] = '';
        $declaration[] = '$currentModule = new PicoModule($appConfig, $database, $appModule, "'.$request->getTarget().'", "'.$request->getModuleCode().'", $appLanguage->get'.$upperModuleName.'());';
        $declaration[] = '$userPermission = new AppUserPermissionImpl($appConfig, $database, $appUserRole, $currentModule, $currentUser);';
        $declaration[] = '$appInclude = new AppIncludeImpl($appConfig, $currentModule);';
        $declaration[] = '';

        $declaration[] = 'if(!$userPermission->allowedAccess($inputGet, $inputPost))'."\r\n".
        '{'."\r\n".
        "\t".'require_once $appInclude->appForbiddenPage(__DIR__);'."\r\n".
        "\t".'exit();'."\r\n".
        '}';
        $declaration[] = '';
        if($appFeatures->isUserActivityLogger())
        {
            $declaration[] = '$userActivityLogger->logActivity($currentUser, $currentAction);';
        }
        
        $declaration[] = '';
        
        $specification = $request->getSpecification();
        if(isset($specification))
        {
            $arr1 = array();
            $arr1[] = '$dataFilter = PicoSpecification::getInstance();';
            foreach($specification as $values)
            {
                if(is_array($values) || is_object($values))
                {
                    $column = trim(AppBuilderBase::getStringOf($values->getColumn()));
                    $value = trim($values->getValue());
                    $comparison = trim($values->getComparison());
                    if($comparison == "")
                    {
                        $comparison = "equals";
                    }

                    $value = AppBuilderBase::fixValue($value);

                    $arr1[] = '$dataFilter->addAnd(PicoPredicate::getInstance()->'.$comparison.'('.$column.', '.$value.'));';
                }
            }
            if(!empty($arr1))
            {
                $declaration[] = implode("\r\n", $arr1);
            }
            else
            {
                $declaration[] = '$dataFilter = null;';
            }
        }
        else
        {
            $declaration[] = '$dataFilter = null;';
        }
            
        $declaration[] = '';
        
        $declarationSection = implode("\r\n", $declaration);
  
        $appBuilder = null;
        $sortable = $request->getSortable();

        $ajaxSupport = $request->getFeatures()->getAjaxSupport() == 'true' || $request->getFeatures()->getAjaxSupport() == 1;
        $guiInsert = null;
        $guiUpdate = null;
        $guiDetail = null;
        $guiList = null;
        $export = null;
        $activationSection = null;
        $deactivationSection = null;

        // prepare CRUD section begin
        if($approvalRequired) {
            $appBuilder = new AppBuilderApproval($builderConfig, $appConfig, $appFeatures, $entityInfo, $entityApvInfo, $allField, $ajaxSupport);
            $appBuilder->setTarget($request->getTarget());

            // CRUD
            $createSection = $appBuilder->createInsertApprovalSection($entityMain, $insertFields, $approvalRequired, $entityApproval, $callbackCreateSuccess, $callbackCreateFailed);
            $updateSection = $appBuilder->createUpdateApprovalSection($entityMain, $editFields, $approvalRequired, $entityApproval, $callbackUpdateSuccess, $callbackUpdateFailed);
            if($appFeatures->isActivateDeactivate())
            {
                $activationSection = $appBuilder->createActivationApprovalSection($entityMain, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);
                $deactivationSection = $appBuilder->createDeactivationApprovalSection($entityMain, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);  
            }   
            $deleteSection = $appBuilder->createDeleteApprovalSection($entityMain, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);
            $approvalSection = $appBuilder->createApprovalSection($entityMain, $editFields, $approvalRequired, $entityApproval, $trashRequired, $entityTrash);
            $rejectionSection = $appBuilder->createRejectionSection($entityMain, $approvalRequired, $entityApproval);  

            // GUI
            if($appFeatures->isBackendOnly())
            {
                $export = $appBuilder->createExport($entityMain, $listFields, $exportFields, $referenceData, $filterFields, $sortable);
            }
            else
            {
                $guiInsert = $appBuilder->createGuiInsert($entityMain, $insertFields); 
                $guiUpdate = $appBuilder->createGuiUpdate($entityMain, $editFields, $approvalRequired); 
                $guiDetail = $appBuilder->createGuiDetail($entityMain, $detailFields, $referenceData, $approvalRequired, $entityApproval); 
                $guiList = $appBuilder->createGuiList($entityMain, $listFields, $exportFields, $referenceData, $filterFields, $approvalRequired, $sortable);
            }
        } 
        else 
        {
            $appBuilder = new AppBuilder($builderConfig, $appConfig, $appFeatures, $entityInfo, $entityApvInfo, $allField, $ajaxSupport);
            $appBuilder->setTarget($request->getTarget());
            
            // CRUD        
            $createSection = $appBuilder->createInsertSection($entityMain, $insertFields, $callbackCreateSuccess, $callbackCreateFailed);
            $updateSection = $appBuilder->createUpdateSection($entityMain, $editFields, $callbackUpdateSuccess, $callbackUpdateFailed);
            
            if($appFeatures->isActivateDeactivate())
            {
                $activationSection = $appBuilder->createActivationSection($entityMain, $activationKey, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);
                $deactivationSection = $appBuilder->createDeactivationSection($entityMain, $activationKey, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);     
            }      
            $deleteSection = $this->createDeleteWithoutApproval($appBuilder, $entityMain, $trashRequired, $entityTrash, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);

            $approvalSection = "";
            $rejectionSection = "";
            if($appFeatures->isBackendOnly())
            {
                $export = $appBuilder->createExport($entityMain, $listFields, $exportFields, $referenceData, $filterFields, $sortable);                 
            }
            else
            {
                $guiInsert = $appBuilder->createGuiInsert($entityMain, $insertFields); 
                $guiUpdate = $appBuilder->createGuiUpdate($entityMain, $editFields); 
                $guiDetail = $appBuilder->createGuiDetail($entityMain, $detailFields, $referenceData); 
                $guiList = $appBuilder->createGuiList($entityMain, $listFields, $exportFields, $referenceData, $filterFields, $approvalRequired, $sortable); 
            }
        }
        
        // prepare CRUD section end
        
        $crudSection = (new AppSection(AppSection::SEPARATOR_IF_ELSE))
            ->add($createSection)
            ->add($updateSection)
            ->add($activationSection)
            ->add($deactivationSection)
            ->add($deleteSection)
            ->add($approvalSection)
            ->add($rejectionSection)
            ;

        
        if($appFeatures->isSortOrder()) {
            $primaryKey = $entityMain->getPrimaryKey();
            $entityName = $entityMain->getEntityName();
            $objectName = lcfirst($entityMainName);
            $sortOrderSection = $appBuilder->createSortOrderSection($objectName, $entityName, $primaryKey);  
            $crudSection->add($sortOrderSection);
        }
        
        if($appFeatures->isBackendOnly())
        {
            $merged = (new AppSection(AppSection::SEPARATOR_NEW_LINE))
                ->add($usesSection)
                ->add($includeSection)
                ->add($declarationSection)
                ->add($crudSection)
                ->add($export)
                ;
        }
        else
        {
            $guiSection = (new AppSection(AppSection::SEPARATOR_IF_ELSE))
                ->add($guiInsert)
                ->add($guiUpdate)
                ->add($guiDetail)
                ->add($guiList)
                ;

            $merged = (new AppSection(AppSection::SEPARATOR_NEW_LINE))
                ->add($usesSection)
                ->add($includeSection)
                ->add($declarationSection)
                ->add($crudSection)
                ->add($guiSection)
                ;
        }

        $moduleFile = $request->getModuleFile();

        $baseDir = $appConf->getBaseApplicationDirectory();

        $path = $this->getModulePath($request, $baseDir, $moduleFile);
        
        $this->prepareDirContainer($path);     
        $this->createFinalScript($path, $merged);

        $updateEntity = $request->getUpdateEntity() == '1' || $request->getUpdateEntity() == 'true';
        
        $fileGenerated = 1;
        $appBuilder->setUpdateEntity($updateEntity);
        if($updateEntity)
        {
            $fileGenerated++;
        }
        $appBuilder->generateMainEntity($database, $appConf, $entityMain, $entityInfo, $referenceData);
        
        if($approvalRequired) {
            $appBuilder->generateApprovalEntity($database, $appConf, $entityMain, $entityInfo, $entityApproval, $referenceData);
            $fileGenerated++;
        }
        if($trashRequired) {
            $appBuilder->generateTrashEntity($database, $appConf, $entityMain, $entityInfo, $entityTrash, $referenceData);
            $fileGenerated++;
        }
        
        // Do not update referenced entities automatically
        $fileGenerated += $this->generateEntitiesIfNotExists($database, $appConf, $entityInfo, $referenceEntities, false);


        // Generate validator
        

        $this->updateMenu($appConf, $request);
        return $fileGenerated;
    }   
}