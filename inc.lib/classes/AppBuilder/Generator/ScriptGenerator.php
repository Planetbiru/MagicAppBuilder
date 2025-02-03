<?php

namespace AppBuilder\Generator;

use AppBuilder\AppArchitecture;
use AppBuilder\AppBuilder;
use AppBuilder\AppBuilderApproval;
use AppBuilder\AppFeatures;
use AppBuilder\AppField;
use AppBuilder\AppSecretObject;
use AppBuilder\AppSection;
use AppBuilder\Base\AppBuilderBase;
use AppBuilder\EntityApvInfo;
use AppBuilder\EntityInfo;
use MagicObject\Database\PicoDatabase;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\MagicObject;
use MagicObject\Request\InputPost;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;
use stdClass;

class ScriptGenerator //NOSONAR
{
    const COMPOSER_PHAR = "composer.phar";
    
    /**
     * Create delete section without approval.
     *
     * Generates a delete section for an entity, optionally using a trash entity.
     *
     * @param AppBuilder $appBuilder Instance of AppBuilder.
     * @param MagicObject $entityMain Main entity object.
     * @param boolean $trashRequired Indicates if trash functionality is required.
     * @param MagicObject $entityTrash Trash entity object.
     * @param callable $callbackUpdateStatusSuccess Callback for successful status update.
     * @param callable $callbackUpdateStatusException Callback for failed status update.
     * @return string Generated delete section as a string.
     */
    public function createDeleteWithoutApproval($appBuilder, $entityMain, $trashRequired, $entityTrash, $callbackUpdateStatusSuccess, $callbackUpdateStatusException)
    {
        if($trashRequired)
        {
            return $appBuilder->createDeleteSection($entityMain, true, $entityTrash, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);
        }
        else
        {
            return $appBuilder->createDeleteSection($entityMain, false, null, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);
        }
    }

    /**
     * Get the authentication file path.
     *
     * Retrieves the path to the authentication file based on the application's configuration.
     *
     * @param AppSecretObject $appConf Application configuration object.
     * @return string Path to the authentication file.
     */
    public function getAuthFile($appConf)
    {
        $includeDir = trim($appConf->getBaseIncludeDirectory(), "/\\");
        if(!empty($includeDir)) 
        {
            $includeDir = '"/'.$includeDir.'/auth.php"';
        }
        else 
        {
            $includeDir = '"/auth.php"';
        }
        return $includeDir;
    }

    /**
     * Check if a field has reference data.
     *
     * Determines if the provided field has valid reference data.
     *
     * @param AppField $value Field object to check.
     * @param boolean $includeMap Indicates if map reference data should also be included in the check.
     * @return boolean True if reference data exists; otherwise, false.
     */
    public function hasReferenceData($value, $includeMap = false)
    {
        if($includeMap)
        {
            return ($value->getReferenceData() != null 
            && $value->getElementType() == 'select' 
            && $value->getReferenceData()->getType() == 'entity' 
            && $value->getReferenceData()->getEntity() != null 
            && $value->getReferenceData()->getEntity()->getEntityName() != null)
            || ($value->getReferenceData() != null 
            && $value->getReferenceData()->getType() == 'map' 
            && $value->getReferenceData()->getMap() != null 
            );
        }
        else
        {
            return $value->getReferenceData() != null 
            && $value->getElementType() == 'select' 
            && $value->getReferenceData()->getType() == 'entity' 
            && $value->getReferenceData()->getEntity() != null 
            && $value->getReferenceData()->getEntity()->getEntityName() != null;
        }
    }

    /**
     * Check if a field has a reference filter.
     *
     * Determines if the provided field has a valid reference filter.
     *
     * @param AppField $value Field object to check.
     * @return boolean True if reference filter exists; otherwise, false.
     */
    public function hasReferenceFilter($value)
    {
        return $value->getReferenceFilter() != null 
        && $value->getReferenceFilter()->getType() == 'entity' 
        && $value->getReferenceFilter()->getEntity() != null 
        && $value->getReferenceFilter()->getEntity()->getEntityName() != null;
    }

    /**
     * Add use statement from approval entity.
     *
     * Adds a use statement for the approval entity if approval is required.
     *
     * @param string[] $uses Array of existing use statements.
     * @param AppSecretObject $appConf Application configuration object.
     * @param boolean $approvalRequired Indicates if approval is required.
     * @param MagicObject $entity Entity object.
     * @return string[] Updated array of use statements.
     */
    public function addUseFromApproval($uses, $appConf, $approvalRequired, $entity)
    {
        if($approvalRequired) 
        {
            $entityApproval = $entity->getApprovalEntity();
            $entityApprovalName = $entityApproval->getEntityName();
            $uses[] = "use ".$appConf->getBaseEntityDataNamespace()."\\$entityApprovalName;";
        }
        return $uses;
    }

    /**
     * Add use statement from trash entity.
     *
     * Adds a use statement for the trash entity if trash functionality is required.
     *
     * @param string[] $uses Array of existing use statements.
     * @param AppSecretObject $appConf Application configuration object.
     * @param boolean $trashRequired Indicates if trash is required.
     * @param MagicObject $entity Entity object.
     * @return string[] Updated array of use statements.
     */
    public function addUseFromTrash($uses, $appConf, $trashRequired, $entity)
    {
        if($trashRequired) 
        {
            $entityTrash = $entity->getTrashEntity();
            $entityTrashName = $entityTrash->getEntityName();
            $uses[] = "use ".$appConf->getBaseEntityDataNamespace()."\\$entityTrashName;";
        }
        return $uses;
    }

    /**
     * Add use statements from reference entities.
     *
     * Adds use statements for all unique reference entities provided.
     *
     * @param string[] $uses Array of existing use statements.
     * @param AppSecretObject $appConf Application configuration object.
     * @param array $referenceEntity Array of reference entity objects.
     * @return string[] Updated array of use statements.
     */
    public function addUseFromReference($uses, $appConf, $referenceEntity)
    {
        $entityName = array();
        foreach($referenceEntity as $entity) 
        {
            $entityName[] = $entity->getEntityName();
        }
        $entityName = array_unique($entityName);

        foreach($entityName as $ref) 
        {
            $uses[] = "use ".$appConf->getBaseEntityDataNamespace()."\\$ref;";
        }
        return $uses;
    }
    
    /**
     * Get the approval entity.
     *
     * Retrieves the approval entity associated with the provided entity.
     *
     * @param MagicObject $entity Entity object to check.
     * @return MagicObject Approval entity object.
     */
    private function getEntityApproval($entity)
    {
        if($entity->getApprovalEntity() != null)
        {
            $entityApproval = $entity->getApprovalEntity();
        }
        else
        {
            $entityApproval = new MagicObject();
        }
        return $entityApproval;
    }
    
    /**
     * Get the trash entity.
     *
     * Retrieves the trash entity associated with the provided entity.
     *
     * @param MagicObject $entity Entity object to check.
     * @return MagicObject Trash entity object.
     */
    private function getEntityTrash($entity)
    {
        if($entity->getTrashEntity() != null)
        {
            $entityTrash = $entity->getTrashEntity();
        }
        else
        {
            $entityTrash = new MagicObject();
        }
        return $entityTrash;
    }

    /**
     * Get the absolute directory path.
     *
     * Computes the absolute path based on the provided directory and target.
     *
     * @param string $dir Base directory path.
     * @param string $target Target path to process.
     * @return string Absolute directory path.
     */
    private function getAbsoludeDir($dir, $target)
    {
        $tg = trim($target, "\\/");
        $tg = str_replace("\\", "/", $tg);
        if(empty($tg))
        {
            return $dir;
        }
        $count = count(explode("/", $tg));
        for($i = 0; $i < $count; $i++)
        {
            $dir = "dirname($dir)";
        }
        return $dir;
    }
    
    /**
     * Processes a single field and updates the provided arrays accordingly.
     *
     * @param AppField $field The field object to be processed.
     * @param array &$insertFields The array to hold fields to be inserted.
     * @param array &$editFields The array to hold fields to be edited.
     * @param array &$detailFields The array to hold fields to be displayed in detail view.
     * @param array &$listFields The array to hold fields to be listed.
     * @param array &$exportFields The array to hold fields for export.
     * @param array &$filterFields The array to hold fields used for filtering.
     * @param array &$referenceData The array to hold reference data for fields.
     * @param array &$referenceEntities The array to hold reference entities.
     * @param array &$referenceEntitiesUse The array to hold entities that need to be used in both create and update operations.
     * @return void
     */
    private function processField(AppField $field, &$insertFields, &$editFields, &$detailFields, &$listFields, &$exportFields, &$filterFields, &$referenceData, &$referenceEntities, &$referenceEntitiesUse) //NOSONAR
    {
        $create = false;
        $update = false;

        if ($field->getIncludeInsert()) {
            $insertFields[$field->getFieldName()] = $field;
            $create = true;
        }
        if ($field->getIncludeEdit()) {
            $editFields[$field->getFieldName()] = $field;
            $update = true;
        }
        if ($field->getIncludeDetail()) {
            $detailFields[$field->getFieldName()] = $field;
        }
        if ($field->getIncludeList()) {
            $listFields[$field->getFieldName()] = $field;
        }
        if ($field->getIncludeExport()) {
            $exportFields[$field->getFieldName()] = $field;
        }
        if ($field->getFilterElementType() != "") {
            $filterFields[$field->getFieldName()] = $field;
        }
        if ($this->hasReferenceData($field, true)) {
            $referenceData[$field->getFieldName()] = $field->getReferenceData();
        }
        if ($this->hasReferenceData($field)) {
            $ent = $field->getReferenceData()->getEntity();
            $referenceEntities[] = $ent;
            if ($create && $update) {
                $referenceEntitiesUse[] = $ent;
            }
        }
        if ($this->hasReferenceFilter($field)) {
            $ent = $field->getReferenceFilter()->getEntity();
            $referenceEntities[] = $ent;
            $referenceEntitiesUse[] = $ent;
        }
    }

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
     * @param bool $composerOnline Flag indicating whether Composer should be used in online mode
     *
     * @return int The number of entity files that were generated. It generates and writes the application module script
     * to the specified location, as defined in the request.
     */
    public function generate($database, $request, $builderConfig, $appConfig, $entityInfo, $entityApvInfo, $composerOnline) // NOSONAR
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
        
        $uses = $this->createUse($appConf, $entityMainName, $approvalRequired, $sortOrder);   
        $uses = $this->addUseFromApproval($uses, $appConf, $approvalRequired, $entity);
        $uses = $this->addUseFromTrash($uses, $appConf, $trashRequired, $entity);
        $uses = $this->addUseFromReference($uses, $appConf, $referenceEntitiesUse);
        $uses = $this->addExporterLibrary($uses, $appFeatures);      
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
        $declaration[] = '$userPermission = new AppUserPermission($appConfig, $database, $appUserRole, $currentModule, $currentUser);';
        $declaration[] = '$appInclude = new AppIncludeImpl($appConfig, $currentModule);';
        $declaration[] = '';

        $declaration[] = 'if(!$userPermission->allowedAccess($inputGet, $inputPost))'."\r\n".
        '{'."\r\n".
        "\t".'require_once $appInclude->appForbiddenPage(__DIR__);'."\r\n".
        "\t".'exit();'."\r\n".
        '}';
        
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

        // prepare CRUD section begin
        if($approvalRequired) {
            $appBuilder = new AppBuilderApproval($builderConfig, $appConfig, $appFeatures, $entityInfo, $entityApvInfo, $allField, $ajaxSupport);
            $appBuilder->setTarget($request->getTarget());

            // CRUD
            $createSection = $appBuilder->createInsertApprovalSection($entityMain, $insertFields, $approvalRequired, $entityApproval, $callbackCreateSuccess, $callbackCreateFailed);
            $updateSection = $appBuilder->createUpdateApprovalSection($entityMain, $editFields, $approvalRequired, $entityApproval, $callbackUpdateSuccess, $callbackUpdateFailed);
            
            $activationSection = $appBuilder->createActivationApprovalSection($entityMain, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);
            $deactivationSection = $appBuilder->createDeactivationApprovalSection($entityMain, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);     
            $deleteSection = $appBuilder->createDeleteApprovalSection($entityMain, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);
            $approvalSection = $appBuilder->createApprovalSection($entityMain, $editFields, $approvalRequired, $entityApproval, $trashRequired, $entityTrash);
            $rejectionSection = $appBuilder->createRejectionSection($entityMain, $approvalRequired, $entityApproval);  

            // GUI
            $guiInsert = $appBuilder->createGuiInsert($entityMain, $insertFields); 
            $guiUpdate = $appBuilder->createGuiUpdate($entityMain, $editFields, $approvalRequired); 
            $guiDetail = $appBuilder->createGuiDetail($entityMain, $detailFields, $referenceData, $approvalRequired, $entityApproval); 
            $guiList = $appBuilder->createGuiList($entityMain, $listFields, $exportFields, $referenceData, $filterFields, $sortOrder, $approvalRequired, $specification, $sortable); 
        } else {
            $appBuilder = new AppBuilder($builderConfig, $appConfig, $appFeatures, $entityInfo, $entityApvInfo, $allField, $ajaxSupport);
            $appBuilder->setTarget($request->getTarget());
            
            // CRUD        
            $createSection = $appBuilder->createInsertSection($entityMain, $insertFields, $callbackCreateSuccess, $callbackCreateFailed);
            $updateSection = $appBuilder->createUpdateSection($entityMain, $editFields, $callbackUpdateSuccess, $callbackUpdateFailed);
            
            $activationSection = $appBuilder->createActivationSection($entityMain, $activationKey, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);
            $deactivationSection = $appBuilder->createDeactivationSection($entityMain, $activationKey, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);           
            $deleteSection = $this->createDeleteWithoutApproval($appBuilder, $entityMain, $trashRequired, $entityTrash, $callbackUpdateStatusSuccess, $callbackUpdateStatusException);

            $approvalSection = "";
            $rejectionSection = "";
            $guiInsert = $appBuilder->createGuiInsert($entityMain, $insertFields); 
            $guiUpdate = $appBuilder->createGuiUpdate($entityMain, $editFields); 
            $guiDetail = $appBuilder->createGuiDetail($entityMain, $detailFields, $referenceData); 
            $guiList = $appBuilder->createGuiList($entityMain, $listFields, $exportFields, $referenceData, $filterFields, $sortOrder, $approvalRequired, $specification, $sortable); 
        }
        
        // prepare CRUD section end
        
        $crudSection = (new AppSection(AppSection::SEPARATOR_IF_ELSE))->add($createSection)->add($updateSection)->add($activationSection)->add($deactivationSection)->add($deleteSection)->add($approvalSection)->add($rejectionSection);

        if($appFeatures->isSortOrder()) {
            $primaryKey = $entityMain->getPrimaryKey();
            $entityName = $entityMain->getEntityName();
            $objectName = lcfirst($entityMainName);
            $sortOrderSection = $appBuilder->createSortOrderSection($objectName, $entityName, $primaryKey);  
            $crudSection->add($sortOrderSection);
        }
            
        $guiSection = (new AppSection(AppSection::SEPARATOR_IF_ELSE))->add($guiInsert)->add($guiUpdate)->add($guiDetail)->add($guiList);

        $merged = (new AppSection(AppSection::SEPARATOR_NEW_LINE))->add($usesSection)->add($includeSection)->add($declarationSection)->add($crudSection)->add($guiSection);

        $moduleFile = $request->getModuleFile();

        $baseDir = $appConf->getBaseApplicationDirectory();
        $this->prepareApplication($builderConfig, $appConf, $baseDir, $composerOnline);

        $path = $this->getModulePath($request, $baseDir, $moduleFile);
        error_log($path);
        
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
        $this->updateMenu($appConf, $request);
        return $fileGenerated;
    }
    
    /**
     * Creates and writes the final PHP script to the specified path by merging content 
     * and removing redundant 'AppFormBuilder' usage if applicable.
     *
     * @param string $path The path where the final script should be written.
     * @param string $merged The merged content to be included in the final script.
     * @return void
     */
    private function createFinalScript($path, $merged)
    {
        $finalScript = "<"."?php\r\n\r\n".$merged."\r\n\r\n";
        if(substr_count($finalScript, 'AppFormBuilder') == 1) {
            $finalScript = str_replace("use MagicApp\AppFormBuilder;\r\n", '', $finalScript);
        }
        file_put_contents($path, $finalScript);
    }
    
    /**
     * Constructs the full path to a module file based on the request target, base directory, and module filename.
     *
     * @param MagicObject|InputPost $request The request object, which contains the target path information.
     * @param string $baseDir The base directory where the module resides.
     * @param string $moduleFile The filename of the module to be appended to the path.
     * @return string The full file path to the module, including the target subdirectory.
     */
    private function getModulePath($request, $baseDir, $moduleFile)
    {
        $target = trim($request->getTarget(), "/\\");
        if(!empty($target)) {
            $target = "/".$target;
        }

        return $baseDir."$target/".$moduleFile;
    }
    
    /**
     * Prepares the directory container by creating the parent directories if they don't exist.
     *
     * @param string $path The path where the directory container should be prepared.
     * @return void
     */
    private function prepareDirContainer($path)
    {
        if(!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
    }
    
    /**
     * Prepares the file by ensuring its parent directory exists and creating an empty file if needed.
     *
     * @param string $path The path of the file to be prepared.
     * @return void
     */
    private function prepareFile($path)
    {
        if(!file_exists($path))
        {
            if(!file_exists(basename($path)))
            {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, "");
        } 
    }
    
    /**
     * Retrieves a list of existing menu items from the provided menu structure.
     *
     * @param array $menus An array of menu objects, each potentially containing submenus.
     * @return array An array of strings representing the existing menu items, 
     *               formatted as "submenuLink-submenuLabel".
     */
    private function getExistingMenu($menus)
    {
        $existingMenus = [];

        foreach($menus as $menu)
        {
            $submenus = $menu->getSubmenus();
            if(is_array($submenus))
            {
                foreach($submenus as $submenu)
                {
                    $existingMenus[] = trim($submenu->getLink())."-".trim($submenu->getLabel());
                }
            }
        }
        return $existingMenus;
    }

    /**
     * Updates the menu configuration by adding a new module to the appropriate section.
     *
     * This method checks if the menu configuration file exists. If it does not, it creates
     * the file and loads the current menu structure from a YAML file. The provided module
     * information is then added as a submenu item under the specified menu label if it
     * does not already exist.
     *
     * @param SecretObject $appConf Configuration object containing application settings.
     * @param InputPost $request Request object containing the target, module menu, 
     *        module name, and module file information.
     *
     * @return void
     *
     * @throws Exception If there is an error reading or writing the menu configuration file.
     */
    private function updateMenu($appConf, $request) // NOSONAR
    {
        $menuPath = $appConf->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
        $this->prepareFile($menuPath);
        $menus = new SecretObject();     
        $menus->loadYamlFile($menuPath, false, true, true);

        $existingMenus = $this->getExistingMenu($menus);

        $target = trim($request->getTarget(), "/\\");
        $moduleMenu = trim($request->getModuleMenu());
        $label = trim($request->getModuleName());
        $link = trim($target."/".$request->getModuleFile());

        $menuToCheck = $label."-".$link;
        if(!in_array($menuToCheck, $existingMenus))
        {
            $menuArray = $menus->valueArray();
            foreach($menuArray as $index=>$menu)
            {
                if(trim(strtolower($menu['label'])) == trim(strtolower($moduleMenu)))
                {
                    if(!isset($menuArray[$index]['submenus']))
                    {
                        $menuArray[$index]['submenus'] = [];
                    }

                    $hash = $label.$link;
                    $skip = false;
                    foreach($menuArray[$index]['submenus'] as $sub)
                    {
                        if($sub['label'].$sub['link'] == $hash)
                        {
                            $skip = true;
                            break;
                        }
                    }
                    if(!$skip)
                    {
                        $menuArray[$index]['submenus'][] = array("label"=>$label, "link"=>$link);
                    }
                }
            }
            $yaml = PicoYamlUtil::dump($menuArray, 0, 2, 0);
            file_put_contents($menuPath, $yaml);
        }
    }
    
    /**
     * Initializes callback functions based on the application type.
     *
     * This method sets up success and failure callback functions for create and update actions
     * if the application type is 'microservices'. If the application type is not 'microservices',
     * all callback functions will be set to null.
     *
     * The created callback functions are designed to send success or exception responses
     * using the provided API response handler.
     *
     * @param SecretObject $appConfig Configuration object containing application settings.
     *
     * @return stdClass An object containing the initialized callback functions for
     *                  create and update operations. Each function is either a callable
     *                  or null, depending on the application type.
     */
    private function initializeCallbackFunctions($appConfig)
    {
        if($appConfig->getApplication()->getArchitecture() == AppArchitecture::MICROSERVICES) {
            // Define callback functions for microservices application
            $callbackCreateSuccess = function($objectName, $primaryKeyName) {
                return "\t\t".'$apiResponse->sendSuccess(UserAction::CREATE, $'.$objectName.", $primaryKeyName);";
            };
            $callbackCreateFailed = function($objectName, $primaryKeyName, $exceptionObject) {
                return "\t\t".'$apiResponse->sendException(UserAction::CREATE, $'.$objectName.", $primaryKeyName, $exceptionObject);";
            };
            
            $callbackUpdateSuccess = function($objectName, $primaryKeyName) {
                return "\t\t".'$apiResponse->sendSuccess(UserAction::UPDATE, $'.$objectName.", $primaryKeyName);";
            };
            $callbackUpdateFailed = function($objectName, $primaryKeyName, $exceptionObject) {
                return "\t\t".'$apiResponse->sendException(UserAction::UPDATE, $'.$objectName.", $primaryKeyName, $exceptionObject);";
            };
            
            $callbackUpdateStatusSuccess = function($objectName, $userAction, $primaryKeyName) {
                return "\t\t\t".
                '$apiResponse->sendSuccess('.
                $userAction.
                ', $'.$objectName.
                ', Field::of()->'.PicoStringUtil::camelize($primaryKeyName).
                ', $inputPost->getCheckedRowId(PicoFilterConstant::".$this->getInputFilter($primaryKeyName).")'.
                ");";
            };
            $callbackUpdateStatusException = function($objectName, $userAction, $primaryKeyName, $exceptionObject) {
                return "\t\t\t\t".
                '$apiResponse->sendException('.
                $userAction.
                ', $'.$objectName.
                ', Field::of()->'.PicoStringUtil::camelize($primaryKeyName).
                ', $inputPost->getCheckedRowId(PicoFilterConstant::".$this->getInputFilter($primaryKeyName).")'.
                ', '.$exceptionObject.
                ");";
            };
        }
        else
        {
            // Set all callback functions to null for monolith application
            $callbackCreateSuccess = null;
            $callbackCreateFailed = null;
            
            $callbackUpdateSuccess = null;
            $callbackUpdateFailed = null;
            
            $callbackUpdateStatusSuccess = null;
            $callbackUpdateStatusException = null;
        }
        
        // Create an object to hold the callback functions
        $stdClass = new stdClass;
        
        $stdClass->callbackCreateSuccess = $callbackCreateSuccess;
        $stdClass->callbackCreateFailed = $callbackCreateFailed;
        
        $stdClass->callbackUpdateSuccess = $callbackUpdateSuccess;
        $stdClass->callbackUpdateFailed = $callbackUpdateFailed;
        
        $stdClass->callbackUpdateStatusSuccess = $callbackUpdateStatusSuccess;
        $stdClass->callbackUpdateStatusException = $callbackUpdateStatusException;
        
        return $stdClass;
    }
    
    /**
     * Add exporter library.
     *
     * Adds use statements for the exporter library if exporting features (Excel or CSV) are enabled.
     *
     * @param string[] $uses Array of existing use statements.
     * @param AppFeatures $appFeatures Application feature configuration.
     * @return string[] Updated array of use statements.
     */
    public function addExporterLibrary($uses, $appFeatures)
    {
        if($appFeatures->isExportToExcel() || $appFeatures->isExportToCsv())
        {
            $uses[] = "use MagicApp\\XLSX\\DocumentWriter;";
            $uses[] = "use MagicApp\\XLSX\\XLSXDataFormat;";
        }
        return $uses;
    }
    
    /**
     * Add an empty line to the use statements.
     *
     * @param string[] $uses Array of existing use statements.
     * @return string[] Updated array of use statements with an empty line added.
     */
    public function addAddEmptyLine($uses)
    {
        $uses[] = "";
        return $uses;
    }
    
    /**
     * Create use statements for the application.
     *
     * Generates an array of use statements based on application configuration and entity details.
     *
     * @param SecretObject $appConf Application configuration object.
     * @param string $entityMainName Main entity name.
     * @param boolean $approvalRequired Flag indicating if approval is required.
     * @param boolean $sortOrder Flag indicating if sorting is required.
     * @return string[] Array of generated use statements.
     */
    public function createUse($appConf, $entityMainName, $approvalRequired, $sortOrder)
    {
        $uses = array();
        $uses[] = "// This script is generated automatically by MagicAppBuilder";
        $uses[] = "// Visit https://github.com/Planetbiru/MagicAppBuilder";
        $uses[] = "";
        $uses[] = "use MagicObject\\MagicObject;";
        if($approvalRequired || $sortOrder)
        {
            $uses[] = "use MagicObject\\SetterGetter;";
        }
        $uses[] = "use MagicObject\\Database\\PicoPage;";
        $uses[] = "use MagicObject\\Database\\PicoPageable;";
        $uses[] = "use MagicObject\\Database\\PicoPredicate;";
        $uses[] = "use MagicObject\\Database\\PicoSort;";
        $uses[] = "use MagicObject\\Database\\PicoSortable;";
        $uses[] = "use MagicObject\\Database\\PicoSpecification;";
        $uses[] = "use MagicObject\\Request\\PicoFilterConstant;";
        $uses[] = "use MagicObject\\Request\\InputGet;";
        $uses[] = "use MagicObject\\Request\\InputPost;";
        $uses[] = "use MagicApp\\AppEntityLanguage;";
        $uses[] = "use MagicApp\\AppFormBuilder;";
        $uses[] = "use MagicApp\\Field;";
        $uses[] = "use MagicApp\\PicoModule;";
        if($approvalRequired)
        {
            $uses[] = "use MagicApp\\PicoApproval;";
            $uses[] = "use MagicApp\\WaitingFor;";
        }
        $uses[] = "use MagicApp\\UserAction;";
        $uses[] = "use MagicApp\\AppUserPermission;";
        $uses[] = "use ".$appConf->getBaseApplicationNamespace()."\\AppIncludeImpl;";
        $uses[] = "use ".$appConf->getBaseEntityDataNamespace()."\\$entityMainName;";
        return $uses;
    }

    /**
     * Generates entity files if they do not already exist or need to be updated.
     *
     * This method checks for the existence of entity class files for the provided reference entities.
     * If the entity class file does not exist or needs to be updated (based on the `updateEntity` flag),
     * it generates the file using the database schema and the provided configuration settings.
     * The generated file includes relevant metadata, fields, and configurations as required by the application.
     *
     * @param PicoDatabase $database Database connection object used for schema queries to retrieve table information.
     * @param SecretObject $appConf Application configuration object containing paths, namespaces, and settings for entity file generation.
     * @param EntityInfo $entityInfo Metadata and field information for the entity being processed (e.g., non-updatable fields).
     * @param MagicObject[] $referenceEntities Array of reference entity objects to validate and generate their corresponding entity files.
     * @param bool $updateEntity Flag indicating whether to regenerate or update existing entity files.
     *
     * @return int The total number of entity files that were generated or updated.
     */
    private function generateEntitiesIfNotExists($database, $appConf, $entityInfo, $referenceEntities, $updateEntity = false)
    {
        $fileGenerated = 0;
        $checked = array();
        foreach($referenceEntities as $entity)
        {
            $entityName = $entity->getEntityName();
            if(!in_array($entityName, $checked))
            {
                $tableName = $entity->getTableName();
                $baseDir = $appConf->getBaseEntityDirectory();
                $baseNamespace = $appConf->getBaseEntityDataNamespace();
                $fileName = $baseNamespace."/".$entityName;
                $path = $baseDir."/".$fileName.".php";
                $path = str_replace("\\", "/", $path);
                if($updateEntity || !file_exists($path))
                {
                    $gen = new PicoEntityGenerator($database, $baseDir, $tableName, $baseNamespace, $entityName);
                    $nonupdatables = AppField::getNonupdatableColumns($entityInfo);
                    $gen->generate($nonupdatables);
                    $fileGenerated++;
                }
                $checked[] = $entityName;
            }
        }
        return $fileGenerated;
    }

    /**
     * Prepare the application directory structure and configuration.
     *
     * Creates necessary directories and prepares the application setup based on the configuration.
     *
     * @param SecretObject $builderConfig MagicAppBuilder configuration object.
     * @param SecretObject $appConf Application configuration object.
     * @param string $baseDir Base directory for the application.
     * @param bool $composerOnline Flag indicating whether Composer should be used in online mode
     * @return void
     */
    public function prepareApplication($builderConfig, $appConf, $baseDir, $composerOnline)
    {
        $composer = new MagicObject($appConf->getComposer());
        $magicApp = new MagicObject($appConf->getMagicApp());
        $libDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory();
        if(!file_exists($baseDir))
        {
            $this->prepareDir($baseDir);
        }
        if(!file_exists($libDir)) 
        {
            $this->prepareDir($libDir);
            $this->prepareComposer($builderConfig, $appConf, $composer, $magicApp, $composerOnline);
                  
            $baseAppBuilder = $appConf->getBaseEntityDirectory()."";
            $this->prepareDir($baseAppBuilder);
            $arr = array(           
            );
            foreach($arr as $file)
            {
                copy(dirname(dirname(__DIR__))."/".$file, $baseAppBuilder."/".$file);
            }
        }
    }
    
    /**
     * Prepare the composer setup.
     *
     * Sets up the composer configuration and determines whether to use online or offline mode.
     *
     * @param SecretObject $builderConfig MagicAppBuilder configuration object.
     * @param SecretObject $appConf Application configuration object.
     * @param MagicObject $composer Composer configuration object.
     * @param MagicObject $magicApp MagicApp configuration object.
     * @param bool $composerOnline Flag indicating whether Composer should be used in online mode.
     * @return void
     */
    public function prepareComposer($builderConfig, $appConf, $composer, $magicApp, $composerOnline)
    {
        if($composerOnline)
        {
            $this->prepareComposerOnline($builderConfig, $appConf, $composer, $magicApp);
        }
        else
        {
            $this->prepareComposerOffline($builderConfig, $appConf, $composer);
        }
    }

    /**
     * Prepare Composer in online mode.
     *
     * Downloads and sets up Composer dependencies in online mode.
     *
     * @param SecretObject $builderConfig MagicAppBuilder configuration object.
     * @param SecretObject $appConf Application configuration object.
     * @param MagicObject $composer Composer configuration object.
     * @param MagicObject $magicApp MagicApp configuration object.
     * @return void
     */
    public function prepareComposerOnline($builderConfig, $appConf, $composer, $magicApp)
    {
        $version = $magicApp->getVersion();
        if(!empty($version))
        {
            $version = ":".$version;
        }
        $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory());
        $targetDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."";
        $targetPath = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/".self::COMPOSER_PHAR;
        $sourcePath = dirname(dirname(dirname(__DIR__)))."/".self::COMPOSER_PHAR;
        $success = copy($sourcePath, $targetPath);
        if($success)
        {
            $phpPath = trim($builderConfig->getPhpPath());
            if(empty($phpPath))
            {
                $phpPath = "php";
            }
            $cmd = "cd $targetDir"."&&"."$phpPath composer.phar require planetbiru/magic-app$version --ignore-platform-reqs";
            exec($cmd);     
            $this->updateComposer($builderConfig, $appConf, $composer);
        }
    }

    /**
     * Prepare Composer in offline mode.
     *
     * Copies the composer.phar file and regenerates the autoloader in offline mode.
     *
     * @param SecretObject $builderConfig MagicAppBuilder configuration object.
     * @param SecretObject $appConf Application configuration object.
     * @param MagicObject $composer Composer configuration object.
     * @return void
     */
    public function prepareComposerOffline($builderConfig, $appConf, $composer)
    {
        $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory());
        $targetDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."";
        $targetPath = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/".self::COMPOSER_PHAR;
        $sourcePath = dirname(dirname(dirname(__DIR__)))."/".self::COMPOSER_PHAR;
        $success = copy($sourcePath, $targetPath);
        if($success)
        {
            $phpPath = trim($builderConfig->getPhpPath());
            if(empty($phpPath))
            {
                $phpPath = "php";
            }
            $cmd = "cd $targetDir"."&&"."$phpPath composer.phar composer dump-autoload --ignore-platform-reqs";
            exec($cmd);     
            $this->updateComposer($builderConfig, $appConf, $composer);
        }
    }

    /**
     * Copy a directory and its contents recursively.
     *
     * Recursively copies all files and subdirectories from the source directory to the destination directory.
     *
     * @param string $source The source directory path.
     * @param string $destination The destination directory path.
     * @return bool Returns true if the operation is successful, false otherwise.
     */
    public function copyDirectory($source, $destination)
    {
        // Ensure the source directory exists
        if (!is_dir($source)) {
            return false;
        }

        // Create the destination directory if it doesn't exist
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        // Open the source directory
        $directory = opendir($source);
        if (!$directory) {
            return false;
        }

        // Copy each file and subdirectory
        while (($file = readdir($directory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue; // Skip special directories
            }

            $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
            $destinationPath = $destination . DIRECTORY_SEPARATOR . $file;

            if (is_dir($sourcePath)) {
                // Recursively copy subdirectories
                $this->copyDirectory($sourcePath, $destinationPath);
            } else {
                // Copy individual files
                copy($sourcePath, $destinationPath);
            }
        }

        closedir($directory);
        return true;
    }
    
    /**
     * Prepare a directory by creating it if it does not exist.
     *
     * @param string $baseDir Directory path to prepare.
     * @return void
     */
    public function prepareDir($baseDir)
    {
        if(!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
    }
    
    /**
     * Update the composer configuration file.
     *
     * Modifies the composer.json file to include new autoload settings.
     *
     * @param SecretObject $builderConfig MagicAppBuilder configuration object.
     * @param SecretObject $appConf Application configuration object.
     * @param SecretObject $composer Composer configuration object.
     * @return void
     */
    public function updateComposer($builderConfig, $appConf, $composer)
    {
        $composerJsonFile = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/composer.json";   
        $composerJson = json_decode(file_get_contents($composerJsonFile));
        if(!isset($composerJson->autoload))
        {
            $composerJson->autoload = new stdClass;
        }
        $psr0 = $composer->getPsr0();
        $psr4 = $composer->getPsr4();
        
        if($psr0)
        {
            $this->updatePsr0($appConf, $composer, $composerJson);
        }
        
        if($psr4)
        {
            $this->updatePsr4($appConf, $composer, $composerJson);
        }
         
        if(!isset($composerJson->autoload->{'psr-0'}))
        {
            $composerJson->autoload->{'psr-0'} = new stdClass;
        }
        
        file_put_contents($composerJsonFile, json_encode($composerJson, JSON_PRETTY_PRINT));

        $targetDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."";
        $phpPath = trim($builderConfig->getPhpPath());
        if(empty($phpPath))
        {
            $phpPath = "php";
        }
        $cmd = "cd $targetDir"."&&"."$phpPath composer.phar update";
        exec($cmd);
    }
    
    /**
     * Update PSR-0 autoloading settings in the composer.json file.
     *
     * @param MagicObject $appConf Application configuration object.
     * @param MagicObject $composer Composer configuration object.
     * @param stdClass $composerJson Decoded composer.json object.
     * @return void
     */
    private function updatePsr0($appConf, $composer, $composerJson)
    {
        if(!isset($composerJson->autoload->psr0))
        {
            $composerJson->autoload->{'psr-0'} = new stdClass;
        }
        $entityAppDir = null;
        $psr0BaseDirectory = $composer->getPsr0BaseDirecory(); 
        if(isset($psr0BaseDirectory) && is_array($psr0BaseDirectory))
        {
            foreach($psr0BaseDirectory as $dir)
            {
                $composerJson->autoload->{'psr-0'}->{$dir->getNamespace()."\\"} = $dir->getDirectory()."/";
                $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/classes/".$dir->getNamespace()); // NOSONAR
                $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/classes/".$dir->getNamespace()."/Entity/App"); // NOSONAR
                $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/classes/".$dir->getNamespace()."/Entity/Data"); // NOSONAR
                if(!isset($entityAppDir))
                {
                    $entityAppDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/classes/".$dir->getNamespace()."/Entity/App";
                    $entityNamespace = $dir->getNamespace();
                }
            }
        }
        if(isset($entityAppDir))
        {
            $sourceDir = dirname(__DIR__)."/App";
            $arr = array(
                "AppAdminImpl.php",
                "AppAdminLevelImpl.php",
                "AppAdminRoleImpl.php",
                "AppModuleGroupImpl.php",
                "AppModuleImpl.php",
                "AppNotificationImpl.php",          
            );
            foreach($arr as $file)
            {
                copy($sourceDir."/".$file, $entityAppDir."/".$file);
                $this->fixNamespace($entityAppDir."/".$file, $entityNamespace);
            }
        }
    }
    
    /**
     * Update PSR-4 autoloading settings in the composer.json file.
     *
     * @param MagicObject $appConf Application configuration object.
     * @param MagicObject $composer Composer configuration object.
     * @param stdClass $composerJson Decoded composer.json object.
     * @return void
     */
    public function updatePsr4($appConf, $composer, $composerJson)
    {
        if(!isset($composerJson->autoload->{'psr-4'}))
        {
            $composerJson->autoload->{'psr-4'} = new stdClass;
        }
        $psr4BaseDirectory = $composer->getPsr4BaseDirectory();       
        if(isset($psr4BaseDirectory) && is_array($psr4BaseDirectory))
        {
            foreach($psr4BaseDirectory as $dir)
            {
                $composerJson->autoload->{'psr-4'}->{$dir->getNamespace()."\\"} = $dir->getDirectory()."/";
                $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/classes/".$dir->getNamespace());
            }
        }
    }
    
    /**
     * Fix the namespace in the specified file.
     *
     * Replaces the namespace in a file with the specified entity namespace.
     *
     * @param string $path Path to the file to update.
     * @param string $entityNamespace The new namespace to set in the file.
     * @return void
     */
    private function fixNamespace($path, $entityNamespace)
    {
        $str = file_get_contents($path);
        $str = str_replace("namespace AppBuilder\App\\", "namespace $entityNamespace\\", $str);
        file_put_contents($path, $str);
    }
}