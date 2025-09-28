<?php

namespace AppBuilder;

use AppBuilder\AppArchitecture;
use AppBuilder\AppBuilder;
use AppBuilder\AppBuilderBase;
use AppBuilder\AppFeatures;
use AppBuilder\AppField;
use AppBuilder\AppSecretObject;
use AppBuilder\EntityInfo;
use AppBuilder\Util\FileDirUtil;
use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\MagicObject;
use MagicObject\Request\InputPost;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Util\PicoYamlUtil;
use stdClass;

/**
 * Class ScriptGenerator
 *
 * Kelas dasar untuk generator skrip, menyediakan fungsionalitas umum dan metode bantuan yang digunakan dalam menghasilkan modul aplikasi.
 * Kelas ini merangkum logika untuk mempersiapkan lingkungan aplikasi, mengelola dependensi Composer, memproses definisi field,
 * menghasilkan pernyataan `use`, dan memperbarui konfigurasi menu. Subkelas, seperti `ScriptGeneratorMonolith` dan
 * `ScriptGeneratorMicroservices`, memperluas kelas ini untuk mengimplementasikan logika pembuatan yang spesifik untuk arsitektur masing-masing.
 *
 * @package AppBuilder
 */
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
            && $value->getElementType() == InputType::SELECT 
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
            && $value->getElementType() == InputType::SELECT
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
     * Add use statement for upload file.
     *
     * @param array $uses Array of existing use statements.
     * @param AppField $fields Field object to check.
     * @return array Updated array of use statements.
     */
    public function addUseFromFieldType($uses, $fields)
    {
        if(AppBuilderBase::hasInputFile($fields) && !in_array("use MagicObject\\File\\PicoUploadFile;", $uses))
        {
            $uses[] = "use MagicObject\\File\\PicoUploadFile;";
            $uses[] = "use MagicObject\\File\\PicoFileRenderer;";
        }
        return $uses;
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
     * Add use statement from validator.
     *
     * Adds a use statement for the trash entity if trash functionality is required.
     *
     * @param string[] $uses Array of existing use statements.
     * @param AppSecretObject $appConf Application configuration object.
     * @param boolean $validatorRequired Indicates if trash is required.
     * @return string[] Updated array of use statements.
     */
    public function addUseFromValidator($uses, $appConf, $validatorRequired)
    {
        if($validatorRequired) 
        {
            $uses[] = "use MagicObject\\Exceptions\\InvalidValueException;";
            $uses[] = "use ".str_replace("/", "\\", dirname(dirname($appConf->getBaseEntityDataNamespace())))."\\AppValidatorMessage;";
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
    protected function getEntityApproval($entity)
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
    protected function getEntityTrash($entity)
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
    protected function getAbsoludeDir($dir, $target)
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
    protected function processField($field, &$insertFields, &$editFields, &$detailFields, &$listFields, &$exportFields, &$filterFields, &$referenceData, &$referenceEntities, &$referenceEntitiesUse) //NOSONAR
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
     * Creates and writes the final PHP script to the specified path by merging content 
     * and removing redundant 'AppFormBuilder' usage if applicable.
     *
     * @param string $path The path where the final script should be written.
     * @param string $merged The merged content to be included in the final script.
     * @return void
     */
    protected function createFinalScript($path, $merged)
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
    protected function getModulePath($request, $baseDir, $moduleFile)
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
    protected function prepareDirContainer($path)
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
     * @param array $menus An array of menu objects, each potentially containing submenu.
     * @return array An array of strings representing the existing menu items, 
     *               formatted as "submenuLink-submenuTitle".
     */
    private function getExistingMenu($menus)
    {
        $existingMenus = [];

        foreach($menus as $menu)
        {
            if(isset($menu) && is_object($menu))
            {
                $submenu = $menu->getSubmenu();
                if(is_array($submenu))
                {
                    foreach($submenu as $submenu)
                    {
                        $existingMenus[] = trim($submenu->getLink())."-".trim($submenu->getTitle());
                    }
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
     * information is then added as a submenu item under the specified menu title if it
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
    public function updateMenuOld($appConf, $request) // NOSONAR
    {
        $menuPath = $appConf->getBaseApplicationDirectory()."/inc.cfg/menu.yml";
        $this->prepareFile($menuPath);
        $menus = new SecretObject();     
        $menus->loadYamlFile($menuPath, false, true, true);

        $existingMenus = $this->getExistingMenu($menus);

        $target = trim($request->getTarget(), "/\\");
        $moduleMenu = trim($request->getModuleMenu());
        $title = trim($request->getModuleName());
        $href = ltrim(trim($target."/".$request->getModuleFile()), "/");
        $submenuHashes = [];
        $menuToCheck = $title."-".$href;
        if(!in_array($menuToCheck, $existingMenus) && $menus->getMenu() != null)
        {
            $menuArray = json_decode((string) $menus, true)['menu'];
            
            foreach($menuArray as $index=>$menu)
            {
                if(isset($menu['title']) && trim(strtolower($menu['title'])) == trim(strtolower($moduleMenu)))
                {
                    if(!isset($menuArray[$index]['submenu']))
                    {
                        $menuArray[$index]['submenu'] = [];
                    }

                    $hash = $title.$href;
                    $submenuHashes[] = $hash;
                }
            }
            
            foreach($menuArray as $index=>$menu)
            {
                if(isset($menu['title']) && trim(strtolower($menu['title'])) == trim(strtolower($moduleMenu)))
                {
                    if(!isset($menuArray[$index]['submenu']))
                    {
                        $menuArray[$index]['submenu'] = [];
                    }

                    $hash = $title.$href;
                    $skip = false;
                    foreach($menuArray[$index]['submenu'] as $sub)
                    {
                        if($sub['title'].$sub['href'] == $hash || in_array($hash, $submenuHashes))
                        {
                            $skip = true;
                            break;
                        }
                    }
                    if(!$skip)
                    {
                        $menuArray[$index]['submenu'][] = array(
                            "title" => $title, 
                            "code" => $request->getModuleCode(),
                            "href" => $href,
                            "icon" => $request->getModuleIcon(),
                        );
                    }
                }
            }
            
            $yaml = PicoYamlUtil::dump(array('menu'=>$menuArray), 0, 2, 0);
            file_put_contents($menuPath, $yaml);
        }
    }

    /**
     * Updates the menu configuration by adding a new module to the appropriate section.
     *
     * This method checks if the menu configuration file exists. If it does not, it creates
     * the file and loads the current menu structure from a YAML file. The provided module
     * information is then added as a submenu item under the specified menu title if it
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
    public function updateMenu($appConf, $request) // NOSONAR
    {
        $menuPath = $appConf->getBaseApplicationDirectory() . "/inc.cfg/menu.yml";
        $this->prepareFile($menuPath);

        $menus = new SecretObject();
        $menus->loadYamlFile($menuPath, false, true, true);

        $target = trim($request->getTarget(), "/\\");
        $moduleMenu = trim($request->getModuleMenu());
        $title = trim($request->getModuleName());
        $href = ltrim(trim($target . "/" . $request->getModuleFile()), "/");
        $menuArray = json_decode((string) $menus, true)['menu'] ?? [];

        $submenuKey = $title . $href;

        // Cek apakah submenu sudah ada di menu manapun
        foreach ($menuArray as $menu) {
            if (isset($menu['submenu']) && is_array($menu['submenu'])) {
                foreach ($menu['submenu'] as $submenu) {
                    $existingKey = ($submenu['title'] ?? '') . ($submenu['href'] ?? '');
                    if ($existingKey === $submenuKey) {
                        return; // Sudah ada, tidak perlu ditambahkan lagi
                    }
                }
            }
        }

        // Tambahkan submenu ke menu yang sesuai
        foreach ($menuArray as $index => $menu) {
            if (isset($menu['title']) && strcasecmp($menu['title'], $moduleMenu) === 0) {
                if (!isset($menuArray[$index]['submenu']) || !is_array($menuArray[$index]['submenu'])) {
                    $menuArray[$index]['submenu'] = [];
                }

                $menuArray[$index]['submenu'][] = [
                    "title" => $title,
                    "code"  => $request->getModuleCode(),
                    "href"  => $href,
                    "icon"  => $request->getModuleIcon(),
                ];
                break;
            }
        }

        $yaml = PicoYamlUtil::dump(['menu' => $menuArray], 0, 2, 0);
        file_put_contents($menuPath, $yaml);
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
    protected function initializeCallbackFunctions($appConfig)
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
     * @param AppFeatures $appFeatures
     * @return string[] Array of generated use statements.
     */
    public function createUse($appConf, $entityMainName, $approvalRequired, $sortOrder, $appFeatures)
    {
        $uses = array();
        $uses[] = "// This script is generated automatically by MagicAppBuilder";
        $uses[] = "// Visit https://github.com/Planetbiru/MagicAppBuilder";
        $uses[] = "";
        if(!$appFeatures->isBackendOnly() || $appFeatures->isExportToExcel() || $appFeatures->isExportToCsv())
        {
            $uses[] = "use MagicObject\\MagicObject;";
        }
        if($approvalRequired || $sortOrder)
        {
            $uses[] = "use MagicObject\\SetterGetter;";
        }
        if(!$appFeatures->isBackendOnly())
        {
            $uses[] = "use MagicObject\\Database\\PicoPage;";
            $uses[] = "use MagicObject\\Database\\PicoPageable;";
        }
        $uses[] = "use MagicObject\\Database\\PicoPredicate;";
        if(!$appFeatures->isBackendOnly() || $appFeatures->isExportToExcel() || $appFeatures->isExportToCsv())
        {
            $uses[] = "use MagicObject\\Database\\PicoSort;";
            $uses[] = "use MagicObject\\Database\\PicoSortable;";
        }
        $uses[] = "use MagicObject\\Database\\PicoSpecification;";
        $uses[] = "use MagicObject\\Request\\PicoFilterConstant;";
        $uses[] = "use MagicObject\\Request\\InputGet;";
        $uses[] = "use MagicObject\\Request\\InputPost;";
        if(!$appFeatures->isBackendOnly() || $appFeatures->isExportToExcel() || $appFeatures->isExportToCsv())
        {
            $uses[] = "use ".$appConf->getBaseApplicationNamespace()."\\AppEntityLanguageImpl;";
        }
        $uses[] = "use MagicApp\\AppFormBuilder;";
        $uses[] = "use MagicApp\\Field;";
        $uses[] = "use MagicApp\\PicoModule;";
        if($approvalRequired)
        {
            $uses[] = "use MagicApp\\PicoApproval;";
            $uses[] = "use MagicApp\\WaitingFor;";
        }
        $uses[] = "use MagicApp\\UserAction;";
        $uses[] = "use ".$appConf->getBaseApplicationNamespace()."\\AppIncludeImpl;";
        $uses[] = "use ".$appConf->getBaseApplicationNamespace()."\\AppUserPermissionImpl;";
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
    protected function generateEntitiesIfNotExists($database, $appConf, $entityInfo, $referenceEntities, $updateEntity = false)
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
                    $generator = new PicoEntityGenerator($database, $baseDir, $tableName, $baseNamespace, $entityName);
                    $nonupdatables = AppField::getNonupdatableColumns($entityInfo);
                    $generator->generate($nonupdatables);
                    $fileGenerated++;
                }
                $checked[] = $entityName;
            }
        }
        return $fileGenerated;
    }

    /**
     * Get the file extension in lowercase
     *
     * @param string $filename
     * @return string
     */
    function getFileExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Prepare the application directory structure and configuration.
     *
     * This function ensures that necessary directories exist, sets up Composer dependencies, and copies template files.
     * It also processes PHP files in the template directory using a callback to replace namespace placeholders.
     *
     * @param SecretObject $builderConfig The configuration object for MagicAppBuilder.
     * @param SecretObject $appConf The application configuration object.
     * @param string $baseDir The base directory for the application.
     * @param bool $onlineInstallation Determines whether Composer should be run in online mode.
     * @param string $magicObjectVersion MagicObject version for offline installation.
     * @param MagicObject $entityApplication Entity application
     * @param string $path The path to the system module
     * @return self Returns the current instance for method chaining.
     */
    public function prepareApplication($builderConfig, $appConf, $baseDir, $onlineInstallation, $magicObjectVersion, $entityApplication, $path = null)
    {
        $this->updateApplicationStatus($entityApplication, "installing-composer");
        $composer = new MagicObject($appConf->getComposer());
        $magicApp = new MagicObject($appConf->getMagicApp());
        $composer->setMagicObjectVersion($magicObjectVersion);
        $libDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory();
        if(!file_exists($baseDir))
        {
            $this->prepareDir($baseDir);
        }
        if(!file_exists($libDir)) 
        {
            // Create the base application directory if it doesn't exist
            $this->prepareDir($libDir);
            
            // Copy the composer.phar file to the base application directory
            $this->prepareComposer($builderConfig, $appConf, $composer, $magicApp, $onlineInstallation);
            
            // Copy the composer.json file to the base application directory
            $baseAppBuilder = $appConf->getBaseEntityDirectory();
            $this->prepareDir($baseAppBuilder);
        }
        
        $this->updateApplicationStatus($entityApplication, "copying-app-files");
        // Copy inc.resources/inc.app/*
        // Recursive copy, including subdirectories and files
        $sourceDir = dirname(dirname(dirname(__DIR__)))."/inc.resources/inc.app";
        $destinationDir = $appConf->getBaseApplicationDirectory()."/inc.app";
        $this->copyDirectory($sourceDir, $destinationDir, true, array('php'), function($source, $destination) use ($appConf) {
            $content = file_get_contents($source);
            $baseApplicationNamespace = $appConf->getBaseApplicationNamespace();
            $content = str_replace('/inc.resources', '', $content);
            $content = str_replace('MagicAppTemplate', $baseApplicationNamespace, $content);
            $content = str_replace('dirname(dirname(__DIR__))', 'dirname(__DIR__)', $content);
            file_put_contents($destination, $content);
        });

        $this->updateApplicationStatus($entityApplication, "copying-theme-files");
        // Copy inc.resources/lib.themes/*
        // Recursive copy, including subdirectories and files
        $sourceDir = dirname(dirname(dirname(__DIR__)))."/inc.resources/lib.themes";
        $destinationDir = $appConf->getBaseApplicationDirectory()."/lib.themes";
        $this->copyDirectory($sourceDir, $destinationDir, true, array('php'), function($source, $destination) use ($appConf) {
            $content = file_get_contents($source);
            $baseApplicationNamespace = $appConf->getBaseApplicationNamespace();
            $content = str_replace('MagicAppTemplate', $baseApplicationNamespace, $content);
            file_put_contents($destination, $content);
        });
        
        $this->updateApplicationStatus($entityApplication, "copying-entity-files");
        // Copy inc.lib/classes/MagicAppTemplate/*
        // Recursive copy, including subdirectories and files
        $sourceDir = dirname(dirname(dirname(__DIR__)))."/inc.lib/classes/MagicAppTemplate";
        $destinationDir = $appConf->getBaseApplicationDirectory()."/inc.lib/classes/".$appConf->getBaseApplicationNamespace();
        $this->copyDirectory($sourceDir, $destinationDir, true, array('php'), function($source, $destination) use ($appConf) {
            $content = file_get_contents($source);
            $baseApplicationNamespace = $appConf->getBaseApplicationNamespace();
            $content = str_replace('MagicAppTemplate', $baseApplicationNamespace, $content);
            file_put_contents($destination, $content);
        });
        
        $this->updateApplicationStatus($entityApplication, "copying-standard-module");
        // Copy inc.resources/*
        // Not recursive, only copy the files in the directory
        $sourceDir = dirname(dirname(dirname(__DIR__)))."/inc.resources";
        $destinationDir = $appConf->getBaseApplicationDirectory();
        $originalDestionationDir = $destinationDir;
        $depth = 0;
        $dirname = '__DIR__';
        if(isset($path) && !empty($path) && trim($path, "\\/") != "")
        {
            $path = rtrim($path, "\\/");
            $path = str_replace("\\", "/", $path);
            if(!PicoStringUtil::startsWith($path, "/"))
            {
                $path = "/".$path;
            }
            $depth = substr_count(trim($path, "\\/"), "/");
            $destinationDir = $appConf->getBaseApplicationDirectory() . $path;
            
            for($i = 0; $i < $depth; $i++)
            {
                $dirname = "dirname($dirname)";
            }
        }

        
        $this->copyDirectory($sourceDir, $destinationDir, false, array('php'), function($source, $destination) use ($appConf, $depth, $dirname, $originalDestionationDir) {
            $content = file_get_contents($source);
            $baseApplicationNamespace = $appConf->getBaseApplicationNamespace();
            $content = str_replace('MagicAppTemplate', $baseApplicationNamespace, $content);
            $content = str_replace('MagicAdmin', $baseApplicationNamespace, $content);
            if($depth > 0)
            {
                // Replace __DIR__
                $pattern = '/require_once\s+__DIR__\s*\.\s*[\'"]\/inc\.app\/auth\.php[\'"]\s*;/';
                $replace = 'require_once ' . $dirname . ' . "/inc.app/auth.php";';
                $content = preg_replace($pattern, $replace, $content);

                $pattern = '/require_once\s+__DIR__\s*\.\s*[\'"]\/inc\.app\/app\.php[\'"]\s*;/';
                $replace = 'require_once ' . $dirname . ' . "/inc.app/app.php";';
                $content = preg_replace($pattern, $replace, $content);

                $pattern = '/require_once\s+__DIR__\s*\.\s*[\'"]\/inc\.app\/session\.php[\'"]\s*;/';
                $replace = 'require_once ' . $dirname . ' . "/inc.app/session.php";';
                $content = preg_replace($pattern, $replace, $content);

                $pattern = '/require_once\s+__DIR__\s*\.\s*[\'"]\/inc\.app\/login\-form\.php[\'"]\s*;/';
                $replace = 'require_once ' . $dirname . ' . "/inc.app/login-form.php";';
                $content = preg_replace($pattern, $replace, $content);

            }
            file_put_contents($destination, $content);

        }, true);

        // Copy non-PHP files
        // Iterate through each file and subdirectory
        $directory = opendir($sourceDir);
        if (!$directory) {
            return false;
        }
        while (($file = readdir($directory)) !== false) {
            if ($this->dottedDirectory($file)) {
                continue; // Skip special directory entries
            }

            $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if ($fileExtension == 'php') {
                continue; // Skip non-PHP files
            }

            $sourcePath = $sourceDir . DIRECTORY_SEPARATOR . $file;
            $destinationPath = $originalDestionationDir . DIRECTORY_SEPARATOR . $file;
            copy($sourcePath, $destinationPath);
        }

        if($depth > 0)
        {
            // Create index.php in root directory
            $content = '<'.'?'.'php

require_once __DIR__ . "/inc.app/indexing.php";';
            $destination = $appConf->getBaseApplicationDirectory() . "/index.php";
            file_put_contents($destination, $content);
        }

        $this->updateApplicationStatus($entityApplication, "finish");
        return $this;
    }
    
    /**
     * Update applicatiion status.
     *
     * @param MagicObject $entity Application entity
     * @param string $status Application status
     * @return self Returns the current instance for method chaining.
     */
    private function updateApplicationStatus($entity, $status)
    {
        if(isset($entity) && $entity instanceof MagicObject)
        {
            try
            {
                $entity->setApplicationStatus($status);
                $entity->update();
            }
            catch(Exception $e)
            {
                // Do nothing
            }
        }
        return $this;
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
     * @param bool $onlineInstallation Flag indicating whether Composer should be used in online mode.
     * @param string $magicObjectVersion MagicObject version for offline installation.
     * @return self Returns the current instance for method chaining.
     */
    public function prepareComposer($builderConfig, $appConf, $composer, $magicApp, $onlineInstallation)
    {
        if($onlineInstallation)
        {
            return $this->prepareComposerOnline($builderConfig, $appConf, $composer, $magicApp);
        }
        else
        {
            return $this->prepareComposerOffline($builderConfig, $appConf, $composer);
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
        $sourcePath = dirname(dirname(__DIR__))."/".self::COMPOSER_PHAR;
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
        return $this;
    }

    /**
     * Prepare Composer in offline mode.
     *
     * Copies the composer.phar file and regenerates the autoloader in offline mode.
     *
     * @param SecretObject $builderConfig MagicAppBuilder configuration object.
     * @param SecretObject $appConf Application configuration object.
     * @param MagicObject $composer Composer configuration object.
     * @return self Returns the current instance for method chaining.
     */
    public function prepareComposerOffline($builderConfig, $appConf, $composer)
    {
        $libPath = dirname(dirname(__DIR__));
        $this->prepareDir($appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory());
        $targetDir = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."";
        $targetPath = $appConf->getBaseApplicationDirectory()."/".$composer->getBaseDirectory()."/".self::COMPOSER_PHAR;

        $source3 = $libPath."/vendor/planetbiru";
        $destination3 = $targetDir."/vendor/planetbiru";
        $this->copyDirectory($source3, $destination3, true);

        $source4 = $libPath."/vendor/autoload.php";
        $destination4 = $targetDir."/vendor/autoload.php";
        $this->copyDirectory($source4, $destination4, true);
        
        unlink($destination3."/magic-object/composer.lock");
        unlink($destination3."/magic-app/composer.lock");
        


        $composerConfig = array();

        $composerConfig['autoload'] = array();
        $composerConfig['autoload']['psr-0'] = new stdClass();
        $composerConfig['autoload']['psr-4'] = new stdClass();
        $composerConfig['autoload']['require'] = new stdClass();

        if($composer->getPsr0BaseDirecory() != null && is_array($composer->getPsr0BaseDirecory()))
        {
            foreach($composer->getPsr0BaseDirecory() as $psr)
            {
                $composerConfig['autoload']['psr-0']->{$psr->getNamespace()."\\"} = $psr->getDirectory();
            }
        }
        if($composer->getPsr4BaseDirecory() != null && is_array($composer->getPsr4BaseDirecory()))
        {
            foreach($composer->getPsr4BaseDirecory() as $psr)
            {
                $composerConfig['autoload']['psr-4']->{$psr->getNamespace()."\\"} = $psr->getDirectory();
            }
        }
        
        $magicObjectVersion = $composer->getMagicObjectVersion();

        $magicAppVersion = $appConf->getMagicApp()->getVersion();
        list($major, $minor) = explode(".", $magicAppVersion);

        
        $composerConfig['require']['planetbiru/magic-app'] = '^'.$major.'.'.$minor;
        $composerConfig['require']['planetbiru/magic-object'] = '^'.$magicObjectVersion;
        
        

        $jsonPath = $targetDir."/composer.json";
        $jsonContent = json_encode($composerConfig, JSON_PRETTY_PRINT);

        $composerConfig['repositories'] = array();
        $composerConfig['repositories'][] = array(
            'type'=>'path', 
            'url' => './vendor/planetbiru/magic-app'
        );
        $composerConfig['repositories'][] = array(
            'type'=>'path', 
            'url' => './vendor/planetbiru/magic-object'
        );
        
        file_put_contents($jsonPath, $jsonContent);

        $sourcePath = $libPath."/".self::COMPOSER_PHAR;
        $success = copy($sourcePath, $targetPath);
        
        $version = sprintf("%s.%s.%s", $major, $minor, "0");
        
        $this->addComposerVersion($destination3."/magic-object/composer.json", $magicObjectVersion);
        $this->addComposerVersion($destination3."/magic-app/composer.json", $version);

        
        if($success)
        {
        
            $phpPath = trim($builderConfig->getPhpPath());
            if(empty($phpPath))
            {
                $phpPath = "php";
            }
            
            // Command line to install Composer offline
            $cmd = sprintf(
                'cd %s && %s composer.phar install --no-dev --prefer-dist --ignore-platform-reqs',
                escapeshellarg($targetDir),
                escapeshellarg($phpPath)
            );            
            exec($cmd);
                
        }
        return $this;
    }
    
    /**
     * Add or update version
     *
     * @param string $path composer.json path
     * @param string $version version
     * @return void
     */
    private function addComposerVersion($path, $version)
    {
        // Read composer.json
        $data = json_decode(file_get_contents($path), true);
        
        // Add version
        $data['version'] = $version;
        
        // Wrire composer.json
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Copy a directory and its contents recursively.
     *
     * This function recursively copies all files and subdirectories from the source directory to the destination directory.
     * If specific file extensions are provided, files matching those extensions will be processed using a callback function.
     *
     * @param string $source The source directory path.
     * @param string $destination The destination directory path.
     * @param bool $recursive Whether to copy subdirectories recursively.
     * @param array|null $extensions An optional array of file extensions to process with the callback.
     * @param callable|null $callback An optional callback function to process files with the specified extensions.
     * @param bool $skipNotMatch Whether to skip files that do not match the specified extensions.
     * @return bool Returns true if the operation is successful, false otherwise.
     */
    public function copyDirectory($source, $destination, $recursive = false, $extensions = null, $callback = null, $skipNotMatch = false) // NOSONAR
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

        // Iterate through each file and subdirectory
        while (($file = readdir($directory)) !== false) {
            if ($this->dottedDirectory($file)) {
                continue; // Skip special directory entries
            }

            $sourcePath = $source . DIRECTORY_SEPARATOR . $file;
            $destinationPath = $destination . DIRECTORY_SEPARATOR . $file;

            // Get the file extension
            $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
            if($skipNotMatch && isset($extensions) && is_array($extensions) && !empty($fileExtension) && !in_array($fileExtension, $extensions))
            {
                continue;
            }

            if (is_dir($sourcePath)) {
                // Recursively copy subdirectories
                if($recursive) {
                    $this->copyDirectory($sourcePath, $destinationPath, true, $extensions, $callback);
                }
            } else {
                

                // Check if the file extension matches the specified extensions and a callback is provided
                if ($this->needCallUserFfunction($extensions, $fileExtension, $callback)) {
                    // Process file using the callback function
                    call_user_func($callback, $sourcePath, $destinationPath);
                } else {
                    // Copy the file normally
                    copy($sourcePath, $destinationPath);
                }
            }
        }

        // Close the directory handle
        closedir($directory);
        return true;
    }
    
    /**
     * Check if the directory is a special entry ('.' or '..').
     *
     * @param string $directory Directory name to check.
     * @return bool Returns true if the directory is '.' or '..', false otherwise.
     */
    private function dottedDirectory($directory)
    {
        return $directory === '.' || $directory === '..';
    }
    
    /**
     * Check if the callback function should be called based on file extensions.
     * 
     * This function checks if the provided file extension is in the list of allowed extensions
     *
     * @param array|null $extensions File extensions to check against.
     * @param string|null $fileExtension File extension to check.
     * @param callable|null $callback Callback function to execute if conditions are met.
     * @return bool Returns true if the callback should be called, false otherwise.
     */
    private function needCallUserFfunction($extensions, $fileExtension, $callback)
    {
        return isset($extensions) 
            && is_array($extensions) 
            && in_array($fileExtension, $extensions) 
            && isset($callback) 
            && is_callable($callback);
    }
    
    /**
     * Prepare a directory by creating it if it does not exist.
     *
     * @param string $baseDir Directory path to prepare.
     * @return self Returns the current instance for method chaining.
     */
    public function prepareDir($baseDir)
    {
        if(!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        return $this;
    }
    
    /**
     * Update the composer configuration file.
     *
     * Modifies the composer.json file to include new autoload settings.
     *
     * @param SecretObject $builderConfig MagicAppBuilder configuration object.
     * @param SecretObject $appConf Application configuration object.
     * @param SecretObject $composer Composer configuration object.
     * @return self Returns the current instance for method chaining.
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
        $cmd = "cd $targetDir"."&&"."$phpPath composer.phar update --ignore-platform-reqs";
        exec($cmd);
        return $this;
    }
    
    /**
     * Update PSR-0 autoloading settings in the composer.json file.
     *
     * @param MagicObject $appConf Application configuration object.
     * @param MagicObject $composer Composer configuration object.
     * @param stdClass $composerJson Decoded composer.json object.
     * @return self Returns the current instance for method chaining.
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
                }
            }
        }
        
        return $this;
    }
    
    /**
     * Update PSR-4 autoloading settings in the composer.json file.
     *
     * @param MagicObject $appConf Application configuration object.
     * @param MagicObject $composer Composer configuration object.
     * @param stdClass $composerJson Decoded composer.json object.
     * @return self Returns the current instance for method chaining.
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
        return $this;
    }
    
    /**
     * Fix the namespace in the specified file.
     *
     * Replaces the namespace in a file with the specified entity namespace.
     *
     * @param string $path Path to the file to update.
     * @param string $entityNamespace The new namespace to set in the file.
     * @return self Returns the current instance for method chaining.
     */
    public function fixNamespace($path, $entityNamespace)
    {
        $str = file_get_contents($path);
        $str = str_replace("namespace MagicAppTemplate\Entity\App\\", "namespace $entityNamespace\\", $str);
        file_put_contents($path, $str);
        return $this;
    }


}