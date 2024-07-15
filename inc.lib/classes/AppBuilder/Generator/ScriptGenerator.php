<?php

namespace AppBuilder\Generator;

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
use stdClass;

class ScriptGenerator
{
    /**
     * Create delete section without approval
     *
     * @param AppBuilder $appBuilder
     * @param MagicObject $entityMain
     * @param boolean $trashRequired
     * @param MagicObject $entityTrash
     * @return string
     */
    public function createDeleteWithoutApproval($appBuilder, $entityMain, $trashRequired, $entityTrash)
    {
        if($trashRequired)
        {
            return $appBuilder->createDeleteSection($entityMain, true, $entityTrash);
        }
        else
        {
            return $appBuilder->createDeleteSection($entityMain);
        }
    }

    /**
     * Get auth file
     *
     * @param AppSecretObject $appConf
     * @return string
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
     * Chek if field has reference data
     *
     * @param AppField $value
     * @return boolean
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
     * Chek if field has reference filter
     *
     * @param AppField $value
     * @return boolean
     */
    public function hasReferenceFilter($value)
    {
        return $value->getReferenceFilter() != null 
        && $value->getReferenceFilter()->getType() == 'entity' 
        && $value->getReferenceFilter()->getEntity() != null 
        && $value->getReferenceFilter()->getEntity()->getEntityName() != null;
    }

    /**
     * Add use from approval
     *
     * @param string[] $uses
     * @param AppSecretObject $appConf
     * @param boolean $approvalRequired
     * @param MagicObject $entity
     * @return string[]
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
     * Add use from approval
     *
     * @param string[] $uses
     * @param AppSecretObject $appConf
     * @param boolean $trashRequired
     * @param MagicObject $entity
     * @return string[]
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
     * Add use from approval
     *
     * @param string[] $uses
     * @param array $referenceEntity
     * @param AppSecretObject $appConf
     * @return string[]
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
     * Get entity approval
     *
     * @param MagicObject $entity
     * @return MagicObject
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
     * Get entity trash
     *
     * @param MagicObject $entity
     * @return MagicObject
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
     * Generate
     *
     * @param PicoDatabase $database
     * @param MagicObject|InputPost $request
     * @param AppSecretObject $builderConfig
     * @param AppSecretObject $appConfig
     * @param EntityInfo $entityInfo
     * @param EntityApvInfo $entityApvInfo
     * @return void
     */
    public function generate($database, $request, $builderConfig, $appConfig, $entityInfo, $entityApvInfo) // NOSONAR
    {
        $insertFields = array();
        $editFields = array();
        $detailFields = array();
        $listFields = array();
        $filterFields = array();
        $referenceData = array();
        $referenceEntities = array();
        $allField = array();
        foreach($request->getFields() as $value) {
            $field = new AppField($value);
            $allField[] = $field;
            if($field->getIncludeInsert()) {
                $insertFields[$field->getFieldName()] = $field;
            }
            if($field->getIncludeEdit()) {
                $editFields[$field->getFieldName()] = $field;
            }
            if($field->getIncludeDetail()) {
                $detailFields[$field->getFieldName()] = $field;
            }
            if($field->getIncludeList()) {
                $listFields[$field->getFieldName()] = $field;
            }
            if($field->getFilterElementType() != "") {
                $filterFields[$field->getFieldName()] = $field;
            }
            if($this->hasReferenceData($field, true)){
                $referenceData[$field->getFieldName()] = $field->getReferenceData();
            }
            if($this->hasReferenceData($field)){
                $referenceEntities[] = $field->getReferenceData()->getEntity();
            }
            if($this->hasReferenceFilter($field)){
                $referenceEntities[] = $field->getReferenceFilter()->getEntity();
            }
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
        
        $uses = array();
        $uses[] = "// This script is generated automatically by AppBuilder";
        $uses[] = "// Visit https://github.com/Planetbiru/AppBuilder";
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
        $uses[] = "use MagicApp\\AppInclude;";
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
        $uses[] = "use ".$appConf->getBaseEntityDataNamespace()."\\$entityMainName;";
        $uses = $this->addUseFromApproval($uses, $appConf, $approvalRequired, $entity);
        $uses = $this->addUseFromTrash($uses, $appConf, $trashRequired, $entity);
        $uses = $this->addUseFromReference($uses, $appConf, $referenceEntities);      
        
        $uses[] = "";
        
        $includes = array();
        
        $includeDir = $this->getAuthFile($appConf);
        
        $includes[] = "require_once __DIR__ . $includeDir;";
        $includes[] = "";
        
        $usesSection = implode("\r\n", $uses);
        $includeSection = implode("\r\n", $includes);
        
        $declaration = array();
        $declaration[] = AppBuilderBase::VAR."inputGet = new InputGet();";
        $declaration[] = AppBuilderBase::VAR."inputPost = new InputPost();";
        $declaration[] = '';
        $declaration[] = '$currentModule = new PicoModule($appConfig, $database, $appModule, "'.$request->getModuleCode().'", "'.$request->getModuleName().'");';
        $declaration[] = '$userPermission = new AppUserPermission($appConfig, $database, $appUserRole, $currentModule, $currentUser);';
        $declaration[] = '';

        $declaration[] = 'if(!$userPermission->allowedAccess($inputGet, $inputPost))'."\r\n".
        '{'."\r\n".
        "\t".'require_once AppInclude::appForbiddenPage(__DIR__, $appConfig);'."\r\n".
        "\t".'exit();'."\r\n".
        '}';
        $declaration[] = '';


        $declarationSection = implode("\r\n", $declaration);
        
        $appBuilder = null;

        $specification = $request->getSpecification();
        $sortable = $request->getSortable();

        $ajaxSupport = $request->getFeatures()->getAjaxSupport();

        // prepare CRUD section begin
        if($approvalRequired)
        {
            $appBuilder = new AppBuilderApproval($builderConfig, $appConfig, $appFeatures, $entityInfo, $entityApvInfo, $allField, $ajaxSupport);

            // CRUD
            $createSection = $appBuilder->createInsertApprovalSection($entityMain, $insertFields, $approvalRequired, $entityApproval);
            $updateSection = $appBuilder->createUpdateApprovalSection($entityMain, $editFields, $approvalRequired, $entityApproval);
            $activationSection = $appBuilder->createActivationApprovalSection($entityMain);
            $deactivationSection = $appBuilder->createDeactivationApprovalSection($entityMain);     
            $deleteSection = $appBuilder->createDeleteApprovalSection($entityMain);
            $approvalSection = $appBuilder->createApprovalSection($entityMain, $editFields, $approvalRequired, $entityApproval, $trashRequired, $entityTrash);
            $rejectionSection = $appBuilder->createRejectionSection($entityMain, $approvalRequired, $entityApproval);  

            // GUI
            $guiInsert = $appBuilder->createGuiInsert($entityMain, $insertFields, $approvalRequired, $entityApproval); 
            $guiUpdate = $appBuilder->createGuiUpdate($entityMain, $editFields, $approvalRequired, $entityApproval); 
            $guiDetail = $appBuilder->createGuiDetail($entityMain, $detailFields, $referenceData, $approvalRequired, $entityApproval); 
            $guiList = $appBuilder->createGuiList($entityMain, $listFields, $referenceData, $filterFields, $sortOrder, $approvalRequired, $specification, $sortable); 
        }
        else
        {
            $appBuilder = new AppBuilder($builderConfig, $appConfig, $appFeatures, $entityInfo, $entityApvInfo, $allField, $ajaxSupport);

            // CRUD
            $createSection = $appBuilder->createInsertSection($entityMain, $insertFields);
            $updateSection = $appBuilder->createUpdateSection($entityMain, $editFields);
            $activationSection = $appBuilder->createActivationSection($entityMain, $activationKey);
            $deactivationSection = $appBuilder->createDeactivationSection($entityMain, $activationKey);           
            $deleteSection = $this->createDeleteWithoutApproval($appBuilder, $entityMain, $trashRequired, $entityTrash);

            $approvalSection = "";
            $rejectionSection = "";
            $guiInsert = $appBuilder->createGuiInsert($entityMain, $insertFields); 
            $guiUpdate = $appBuilder->createGuiUpdate($entityMain, $editFields); 
            $guiDetail = $appBuilder->createGuiDetail($entityMain, $detailFields, $referenceData); 
            $guiList = $appBuilder->createGuiList($entityMain, $listFields, $referenceData, $filterFields, $sortOrder, $approvalRequired, $specification, $sortable); 
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

        if($appFeatures->isSortOrder())
        {
            $primaryKey = $entityMain->getPrimaryKey();
            $entityName = $entityMain->getEntityName();
            $objectName = lcfirst($entityMainName);
            $sortOrderSection = $appBuilder->createSortOrderSection($objectName, $entityName, $primaryKey);  
            $crudSection->add($sortOrderSection);
        }
            
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

        $moduleFile = $request->getModuleFile();

        $baseDir = $appConf->getBaseApplicationDirectory();
        $this->prepareApplication($appConf, $baseDir);

        $path = $baseDir."/".$moduleFile;

        
        $finalScript = "<"."?php\r\n\r\n".$merged."\r\n\r\n";
        if(substr_count($finalScript, 'AppFormBuilder') == 1)
        {
            $finalScript = str_replace("use MagicApp\AppFormBuilder;\r\n", '', $finalScript);
        }
        file_put_contents($path, $finalScript);
        
        $appBuilder->generateMainEntity($database, $builderConfig, $appConf, $entityMain, $entityInfo, $referenceData);
        if($approvalRequired)
        {
            $appBuilder->generateApprovalEntity($database, $builderConfig, $appConf, $entityMain, $entityInfo, $entityApproval, $referenceData);
        }
        if($trashRequired)
        {
            $appBuilder->generateTrashEntity($database, $builderConfig, $appConf, $entityMain, $entityInfo, $entityTrash, $referenceData);
        }
        $this->generateEntitiesIfNotExists($database, $appConf, $entityInfo, $referenceEntities);
    }

    /**
     * Generate entity if not exists
     *
     * @param PicoDatabase $database
     * @param SecretObject $appConf
     * @param EntityInfo $entityInfo
     * @param MagicObject[] $referenceEntities
     * @return void
     */
    private function generateEntitiesIfNotExists($database, $appConf, $entityInfo, $referenceEntities)
    {
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
                if(!file_exists($path))
                {
                    $gen = new PicoEntityGenerator($database, $baseDir, $tableName, $baseNamespace, $entityName);
                    $nonupdatables = AppField::getNonupdatetableColumns($entityInfo);
                    $gen->generate($nonupdatables);
                }
                $checked[] = $entityName;
            }
        }
    }

    public function prepareApplication($appConf, $baseDir)
    {
        $libDir = $appConf->getBaseApplicationDirectory()."/".$appConf->getComposer()->getBaseApplicationDirectory();
        if(!file_exists($baseDir))
        {
            $this->prepareDir($baseDir);
        }
        if(!file_exists($libDir)) 
        {    
            $this->prepareDir($libDir);
            $this->prepareComposer($appConf);
                  
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
    public function prepareComposer($appConf)
    {
        $composer = $appConf->getComposer();
        $mo = $appConf->getMagicApp();
        $version = $mo->getVersion();
        if(!empty($version))
        {
            $version = ":".$version;
        }
        $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseApplicationDirectory());
        $targetDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseApplicationDirectory()."";
        $targetPath = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseApplicationDirectory()."/composer.phar";
        $sourcePath = dirname(dirname(dirname(__DIR__)))."/composer.phar";
        $success = copy($sourcePath, $targetPath);
        error_log("copy($sourcePath, $targetPath)");
        if($success)
        {
            
            $cmd = "cd $targetDir"."&&"."php composer.phar require planetbiru/magic-app$version";
            error_log("CMD: ".$cmd."\r\n");
            exec($cmd);     
            $this->updateComposer($appConf, $composer);
        }
    }
    public function prepareDir($baseDir)
    {
        if(!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
    }
    
    public function updateComposer($appConf, $composer)
    {
        $composerJsonFile = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseApplicationDirectory()."/composer.json";   
        $composerJson = json_decode(file_get_contents($composerJsonFile));
        if(!isset($composerJson->autoload))
        {
            $composerJson->autoload = new stdClass;
        }
        $psr0 = $composer->getPsr0();
        $psr4 = $composer->getPsr4();
        
        if($psr0)
        {
            if(!isset($composerJson->autoload->psr0))
            {
                $composerJson->autoload->{'psr-0'} = new stdClass;
            }
            $psr0BaseDirectory = $composer->getPsr0BaseDirectory();       
            foreach($psr0BaseDirectory as $dir)
            {
                $composerJson->autoload->{'psr-0'}->{$dir->getNamespace()."\\"} = $dir->getDirectory()."/";
            }
        }
        
        if($psr4)
        {
            if(!isset($composerJson->autoload->{'psr-4'}))
            {
                $composerJson->autoload->{'psr-4'} = new stdClass;
            }
            $psr0BaseDirectory = $composer->getPsr0BaseDirectory();       
            foreach($psr0BaseDirectory as $dir)
            {
                $composerJson->autoload->{'psr-4'}->{$dir->getNamespace()."\\"} = $dir->getDirectory()."/";
            }
        }
        
        $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseApplicationDirectory()."/classes/AppBuilder");
         
        if(!isset($composerJson->autoload->{'psr-0'}))
        {
            $composerJson->autoload->{'psr-0'} = new stdClass;
        }
        $composerJson->autoload->{'psr-0'}->{"AppBuilder\\"} = $dir->getDirectory()."/";
        
        file_put_contents($composerJsonFile, json_encode($composerJson, JSON_PRETTY_PRINT));

        $targetDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseApplicationDirectory()."";
        $cmd = "cd $targetDir"."&&"."php composer.phar update";
        exec($cmd);
    }
}