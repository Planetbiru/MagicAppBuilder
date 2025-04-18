<?php

namespace AppBuilder;

use DOMText;
use DOMElement;
use DOMDocument;
use AppBuilder\AppField;
use AppBuilder\EntityInfo;
use AppBuilder\AppFeatures;
use AppBuilder\InputType;
use AppBuilder\ElementClass;
use MagicObject\MagicObject;
use AppBuilder\EntityApvInfo;
use MagicObject\SecretObject;
use AppBuilder\AppSecretObject;
use AppBuilder\AppEntityGenerator;
use AppBuilder\DataType;
use AppBuilder\Util\DataUtil;
use MagicObject\Util\PicoStringUtil;
use MagicObject\Database\PicoDatabase;

/**
 * Base class for the AppBuilder framework.
 *
 * This class serves as the foundation for building applications using the AppBuilder framework.
 * It encapsulates configuration settings, entity information, and various helper methods
 * for application development. The class also manages different aspects of the application,
 * such as field definitions, actions, and AJAX support.
 */
class AppBuilderBase //NOSONAR
{
    const TAB1 = "\t";
    const TAB2 = "\t\t";
    const TAB3 = "\t\t\t";
    const TAB4 = "\t\t\t\t";
    const TAB5 = "\t\t\t\t\t";
    const TAB6 = "\t\t\t\t\t\t";
    
    const N_TAB2 = "\n\t\t";
    const NN_TAB2 = "\n\n\t\t";
    const N_TAB3 = "\n\t\t\t";
    const N_TAB4 = "\n\t\t\t\t";
    const N_TAB5 = "\n\t\t\t\t\t";
    const N_TAB6 = "\n\t\t\t\t\t\t";
    const N_TAB7 = "\n\t\t\t\t\t\t\t";
    
    const NEW_LINE = "\r\n";
    const NEW_LINE_R = "\r";
    const NEW_LINE_N = "\n";
    const VAR = "$";
    const CALL_INSERT_END = "->insert();";
    const CALL_UPDATE_END = "->update();";
    const CALL_DELETE_END = "->delete();";
    const CALL_SET = "->set";
    const CALL_GET = "->get";
    const CALL_ISSET = "->isset";
    const BRACKETS = "()";
    
    const PHP_OPEN_TAG = '<'.'?'.'php ';
    const PHP_CLOSE_TAG = '?'.'>';
    
    const STYLE_NATIVE = 'native';
    const STYLE_SETTER_GETTER = 'setter-getter';
    const ECHO = 'echo ';

    const WRAPPER_INSERT = "insert";
    const WRAPPER_UPDATE = "update";
    const WRAPPER_DETAIL = "detail";
    const WRAPPER_LIST = "list";
    
    const BACK_TO_LIST = 'back_to_list';

    const APP_ENTITY_LANGUAGE = 'appEntityLanguage';
    const APP_CONFIG = "appConfig";
    const CURLY_BRACKET_OPEN = "{";
    const CURLY_BRACKET_CLOSE = "}";
    const PHP_TRY = "try";
    const MAP_FOR = '$mapFor';
    const REDIRECT_TO_ITSELF = 'currentModule->getRedirectUrl()';
    const CALL_FIND_ONE = '->findOne';
    const CALL_FIND_ONE_BY = '->findOneBy';
    const CALL_FIND_ONE_WITH = '->findOneWith';
    const CALL_FIND_ONE_WITH_PRIMARY_KEY = '->findOneWithPrimaryKeyValue';

    /**
     * Set and get value style
     *
     * @var string
     */
    protected $style = self::STYLE_NATIVE;
    
    /**
     * Config base directory
     *
     * @var string
     */
    protected $configBaseDirectory = "";

    /**
     * Skip auto setter
     *
     * @var array
     */
    protected $skipedAutoSetter = array();

    /**
     * Entity info
     *
     * @var EntityInfo
     */
    protected $entityInfo;

    /**
     * Entity approval info
     *
     * @var EntityApvInfo
     */
    protected $entityApvInfo;

    /**
     * AppBuilder config
     *
     * @var SecretObject
     */
    protected $appBuilderConfig;
    
    /**
     * Application config
     *
     * @var SecretObject
     */
    protected $appConfig;

    /**
     * Current action
     *
     * @var SecretObject
     */
    protected $currentAction;
    
    /**
     * App feature
     *
     * @var AppFeatures
     */
    protected $appFeatures;

    /**
     * AJAX Support
     * @var bool
     */
    protected $ajaxSupport = false;
    
    /**
     * All field
     *
     * @var AppField[]
     */
    protected $allField;

    /**
     * Target
     * @var string
     */
    protected $target = "/";

    /**
     * Update entity
     * @var bool
     */
    protected $updateEntity = false;

    /**
     * Cache to store option values that belong to a group.
     *
     * @var string[]
     */
    private $inGroup;

    /**
     * Constructor
     *
     * Initializes the object with application and entity configurations.
     *
     * @param SecretObject $appBuilderConfig AppBuilder configuration object.
     * @param SecretObject $appConfig Application configuration object.
     * @param AppFeatures $appFeatures Application features object.
     * @param EntityInfo $entityInfo Entity information object.
     * @param EntityApvInfo $entityApvInfo Entity approval information object.
     * @param AppField[] $allField Array of all field objects.
     * @param boolean $ajaxSupport Indicates if AJAX support is enabled.
     */
    public function __construct($appBuilderConfig, $appConfig, $appFeatures, $entityInfo, $entityApvInfo, $allField, $ajaxSupport)
    {
        $this->appBuilderConfig = $appBuilderConfig;
        $this->appConfig = $appConfig;
        $this->appFeatures = $appFeatures;
        $this->allField = $allField;
        $this->ajaxSupport = $ajaxSupport;
        $this->currentAction = $appConfig->getCurrentAction();
        $this->configBaseDirectory = $appConfig->getConfigBaseDirectory();
        $this->entityInfo = $entityInfo;
        $this->entityApvInfo = $entityApvInfo;
        $this->skipedAutoSetter = array(
            $entityInfo->getDraft(),
            $entityInfo->getAdminCreate(),
            $entityInfo->getAdminEdit(),
            $entityInfo->getAdminAskEdit(),
            $entityInfo->getIpCreate(),
            $entityInfo->getIpEdit(),
            $entityInfo->getIpAskEdit(),
            $entityInfo->getTimeCreate(),
            $entityInfo->getTimeEdit(),
            $entityInfo->getTimeAskEdit(),
            $entityInfo->getWaitingFor(),
            $entityInfo->getApprovalId()
        );
    }
    
    /**
     * Check if the field is an input file type.
     * 
     * Checks if the given data type corresponds to an input file type.
     *
     * @param AppField[] $appFields Array of AppField objects representing the form fields.
     * @param string $objectName Name of the object.
     * @param int $indent Indentation level for the generated code.
     * @return array Generated lines of code for file uploaders.
     */
    public function createFileUploader($appFields, $objectName, $indent = 0)
    {
        $lines = array();
        $lines[] = '';
        $line = self::TAB1.'$inputFiles = new PicoUploadFile();';
        $lines[] = $line;
        foreach($appFields as $field)
        {
            if($this->isInputFile($field->getDataType()))
            {
                $upperFieldName = ucfirst(PicoStringUtil::camelize($field->getFieldName()));
                $line = self::TAB1.'$inputFiles->move'.$upperFieldName.'(function($fileUpload) use ($'.$objectName.', $appConfig)'." {\r\n".
                self::TAB1.self::TAB1.'$pathToSaves = [];'."\r\n".
                self::TAB1.self::TAB1.'foreach($fileUpload->getAll() as $fileItem)'."\r\n".
                self::TAB1.self::TAB1.'{'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.'if($fileItem->isExists())'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.'{'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.self::TAB1.'$targetName = $fileItem->getRandomName(40);'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.self::TAB1.'$targetPath = $appConfig->getUpload() == null ? "lib.upload/".$targetName : $appConfig->getUpload()->getAbsolutePath()."/".$targetName;'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.self::TAB1.'$pathToSave = $appConfig->getUpload() == null ? "lib.upload/".$targetName : $appConfig->getUpload()->getRelativePath()."/".$targetName;'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.self::TAB1.'$fileItem->moveTo($targetPath);'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.self::TAB1.'$pathToSaves[] = $pathToSave;'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.'}'."\r\n".
                self::TAB1.self::TAB1.'}'."\r\n".
                self::TAB1.self::TAB1.'if($fileUpload->isMultiple())'."\r\n".
                self::TAB1.self::TAB1.'{'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.'$'.$objectName.self::CALL_SET.$upperFieldName.'($pathToSaves);'."\r\n".
                self::TAB1.self::TAB1.'}'."\r\n".
                self::TAB1.self::TAB1.'else if(isset($pathToSaves[0]))'."\r\n".
                self::TAB1.self::TAB1.'{'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.'$'.$objectName.self::CALL_SET.$upperFieldName.'($pathToSaves[0]);'."\r\n".
                self::TAB1.self::TAB1.'}'."\r\n".
                self::TAB1.self::TAB1.'else '."\r\n".
                self::TAB1.self::TAB1.'{'."\r\n".
                self::TAB1.self::TAB1.self::TAB1.'// Do something when no file is uploaded'."\r\n".
                self::TAB1.self::TAB1.'}'."\r\n".
                self::TAB1.'});';
                $lines[] = $line;
            }
        }
        $lines[] = '';
        
        if($indent > 0)
        {
            $lines2 = explode("\r\n", implode("\r\n", $lines));
            foreach($lines2 as $key => $line)
            {
                if(strlen($line) > 0)
                {
                    $lines2[$key] = str_repeat(self::TAB1, $indent).$line;
                }
            }
            $lines = $lines2;
        }
        
        return $lines;
    }
    
    
    /**
     * Get input filter for a specific field.
     *
     * Retrieves the input filter associated with the given field name.
     *
     * @param string $fieldName Name of the field.
     * @return string|null Input filter string or null if not found.
     */
    public function getInputFilter($fieldName)
    {
        foreach($this->allField as $field)
        {
            if($field->getFieldName() == $fieldName)
            {
                return $field->getInputFilter();
            }
        }
        return null;
    }
    
    /**
     * Load application configuration from a YAML file.
     *
     * Loads the configuration for a specified application name.
     *
     * @param string $appName Application name.
     * @return MagicObject Loaded application configuration object.
     */
    public function loadApplicationConfig($appName)
    {
        $config = new MagicObject();
        $appConfigPath = $this->configBaseDirectory . "/" . $appName . ".yml";
        $config->loadYamlFile($appConfigPath);
        return $config;
    }

    /**
     * Get the base directory for configuration files.
     *
     * @return string Base directory path for configurations.
     */
    public function getConfigBaseDirectory()
    {
        return $this->configBaseDirectory;
    }

    /**
     * Set the base directory for configuration files.
     *
     * @param string $configBaseDirectory New base directory path for configurations.
     * @return self
     */
    public function setConfigBaseDirectory($configBaseDirectory)
    {
        $this->configBaseDirectory = $configBaseDirectory;

        return $this;
    }

    /**
     * Create constructor code for an object.
     *
     * Generates code for creating a new instance of an entity.
     *
     * @param string $objectName Name of the object.
     * @param string $entityName Name of the entity.
     * @param string|null $dataToLoad Optional data to load into the entity.
     * @return string Generated constructor code as a string.
     */
    protected function createConstructor($objectName, $entityName, $dataToLoad = null)
    {
        if ($dataToLoad == null) {
            $dataToLoad = "null";
        } else {
            $dataToLoad = self::VAR . $dataToLoad;
        }
        return self::VAR . $objectName . " = new $entityName($dataToLoad, " . self::VAR . $this->appConfig->getGlobalVariableDatabase() . ");";
    }

    /**
     * Create setter method for an object field.
     *
     * Generates code for a setter method for a specified field.
     *
     * @param string $objectName Name of the object.
     * @param string $fieldName Name of the field.
     * @param string $fieldFilter Field filter type.
     * @param string|null $primaryKeyName Optional primary key name.
     * @param boolean $updatePk Indicates if the primary key should be updated.
     * @return string|null Generated setter code as a string or null if skipped.
     */
    protected function createSetter($objectName, $fieldName, $fieldFilter, $primaryKeyName = null, $updatePk = false)
    {
        if(!isset($objectName) || empty($objectName))
        {
            $objectName = "";
        }
        else
        {
            $objectName = self::VAR . $objectName;
        }
        if (in_array($fieldName, $this->skipedAutoSetter)) {
            return null;
        }
        if ($this->style = self::STYLE_SETTER_GETTER) {
            if($primaryKeyName != null && $primaryKeyName == $fieldName && $updatePk)
            {
                $methodSource = PicoStringUtil::upperCamelize("app_builder_new_pk").PicoStringUtil::upperCamelize($fieldName);
            }
            else
            {
                $methodSource = PicoStringUtil::upperCamelize($fieldName);
            }
            $methodTarget = PicoStringUtil::upperCamelize($fieldName);
            return self::TAB1 . $objectName . self::CALL_SET . $methodTarget . "(" . self::VAR . "inputPost".self::CALL_GET.$methodSource . "(PicoFilterConstant::" . $fieldFilter . ", false, false, true))";
        } else {
            return self::TAB1 . $objectName . self::CALL_SET."('" . $fieldName . "', " . self::VAR . "inputPost".self::CALL_GET."('" . $fieldName . "', PicoFilterConstant::" . $fieldFilter . "))";
        }
    }

    /**
     * Get entity information.
     *
     * Retrieves the entity information object.
     *
     * @return EntityInfo Entity information object.
     */
    public function getentityInfo()
    {
        return $this->entityInfo;
    }

    /**
     * Set entity information.
     *
     * Updates the entity information object.
     *
     * @param EntityInfo $entityInfo New entity information object.
     * @return self
     */
    public function setentityInfo($entityInfo)
    {
        $this->entityInfo = $entityInfo;

        return $this;
    }

    /**
     * Load or create application configuration.
     *
     * Loads existing configuration or creates a new one from a template.
     *
     * @param string $appId Application ID.
     * @param string $appBaseConfigPath Base path for application configuration.
     * @param string $configTemplatePath Path to the configuration template.
     * @return AppSecretObject Loaded or created application configuration object.
     */
    public static function loadOrCreateConfig($appId, $appBaseConfigPath, $configTemplatePath)
    {
        $appConfig = new AppSecretObject();
        if ($appId != null) {
            $appConfigPath = $appBaseConfigPath . "/" . $appId . "/default.yml";
            $appConfigDir = $appBaseConfigPath . "/" . $appId;
            if (file_exists($appConfigPath)) {
                $appConfig->loadYamlFile($appConfigPath, false, true, true);
            } else {
                $appConfig->loadYamlFile($configTemplatePath, false, true, true);
                if (!file_exists($appConfigDir)) {
                    mkdir($appConfigDir, 0755, true);
                }
                copy($configTemplatePath, $appConfigPath);
            }
        }
        return $appConfig;
    }

    /**
     * Update application configuration.
     *
     * Saves the provided configuration for the specified application ID.
     *
     * @param string $appId Application ID.
     * @param string $appBaseConfigPath Base path for application configuration.
     * @param SecretObject $appConfig Application configuration object.
     */
    public static function updateConfig($appId, $appBaseConfigPath, $appConfig)
    {
        if ($appId != null) {
            $appConfigPath = $appBaseConfigPath . "/" . $appId . "/default.yml";
            $appConfigDir = $appBaseConfigPath . "/" . $appId;

            if (!file_exists($appConfigDir)) {
                mkdir($appConfigDir, 0755, true);
            }
            file_put_contents($appConfigPath, $appConfig->dumpYaml());
        }
    }

    /**
     * Get the AppBuilder configuration object.
     *
     * @return SecretObject AppBuilder configuration object.
     */
    public function getAppBuilderConfig()
    {
        return $this->appBuilderConfig;
    }

    /**
     * Get the current action from the application configuration.
     *
     * @return SecretObject Current action object.
     */
    public function getCurrentAction()
    {
        return $this->currentAction;
    }
    
    
    /**
     * Create a form element in the DOM.
     *
     * Generates a 'form' element with specified attributes.
     *
     * @param DOMDocument $dom DOM Document object.
     * @param string $name Name attribute for the form.
     * @return DOMElement Created 'form' element.
     */
    private function createElementForm($dom, $name)
    {
        $form = $dom->createElement('form');
        $form->setAttribute('name', $name);
        $form->setAttribute('id', $name);
        $form->setAttribute('action', '');
        $form->setAttribute('method', 'post');
        return $form; 
    }
    
    /**
     * Create a responsive table element in the DOM.
     *
     * Generates a 'table' element with responsive attributes.
     *
     * @param DOMDocument $dom DOM Document object.
     * @return DOMElement Created 'table' element.
     */
    private function createElementTableResponsive($dom)
    {
        $table = $dom->createElement('table');
        $table->setAttribute('class', 'responsive responsive-two-cols');
        $table->setAttribute('border', '0');
        $table->setAttribute('cellpadding', '0');
        $table->setAttribute('cellspacing', '0');
        $table->setAttribute('width', '100%');
        return $table;
    }

    /**
     * Create a responsive approval table element in the DOM.
     *
     * Generates a 'table' element with approval-specific responsive attributes.
     *
     * @param DOMDocument $dom DOM Document object.
     * @return DOMElement Created 'table' element for approval.
     */
    private function createElementTableResponsiveApproval($dom)
    {
        $table = $dom->createElement('table');
        $table->setAttribute('class', 'responsive responsive-three-cols');
        $table->setAttribute('border', '0');
        $table->setAttribute('cellpadding', '0');
        $table->setAttribute('cellspacing', '0');
        $table->setAttribute('width', '100%');
        return $table;
    }
    
    /**
     * Create a cancel button element in the DOM.
     *
     * Generates a 'button' element with specified attributes.
     *
     * @param DOMDocument $dom DOM Document object.
     * @param string $value Button text.
     * @param string|null $name Optional name attribute for the button.
     * @param string|null $id Optional id attribute for the button.
     * @param string|null $onclickUrlVariable Optional URL for the button's onclick event.
     * @return DOMElement Created 'button' element.
     */
    private function createCancelButton($dom, $value, $name = null, $id = null, $onclickUrlVariable = null)
    {
        $input = $dom->createElement('button');
        $input->setAttribute('type', 'button');
        $input->setAttribute('class', ElementClass::BUTTON_PRIMARY);
        if($name != null)
        {
            $input->setAttribute('name', $name);
        }
        if($id != null)
        {
            $input->setAttribute('id', $id);
        }
        $input->appendChild($dom->createTextNode($value));
        if($onclickUrlVariable != null)
        {
            $input->setAttribute('onclick', "window.location='".self::PHP_OPEN_TAG.self::ECHO.self::VAR.$onclickUrlVariable.";".self::PHP_CLOSE_TAG."';");
        }
        return $input;
    }
    
    /**
     * Get text for a specific language ID.
     *
     * Retrieves a localized string based on the given language ID.
     *
     * @param string $id Language ID.
     * @return string Localized text as a string.
     */
    private function getTextOfLanguage($id)
    {
        if($this->style == self::STYLE_SETTER_GETTER)
        {
            $param = PicoStringUtil::upperCamelize($id);
            return self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage->get$param".self::BRACKETS.";".self::PHP_CLOSE_TAG;
        }
        else
        {
            return self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage->get('".$id."');".self::PHP_CLOSE_TAG;
        }
    }

    /**
     * Fix table HTML tags.
     *
     * Corrects self-closing tags in table HTML.
     *
     * @param string $html HTML string to fix.
     * @return string Fixed HTML string.
     */
    private function fixTable($html)
    {
        $html = str_replace('<div/>', '<div></div>', $html);
        $html = str_replace('<td/>', '<td></td>', $html);
        $html = str_replace('<th/>', '<th></th>', $html);
        $html = str_replace('<tr/>', '<tr></tr>', $html);
        $html = str_replace('<thead/>', '<thead></thead>', $html);
        $html = str_replace('<tbody/>', '<tbody></tbody>', $html);
        $html = str_replace('<table/>', '<table></table>', $html);
        return $html;
    }

    /**
     * Fix PHP code in HTML string.
     *
     * Corrects encoded PHP code in the provided HTML string.
     *
     * @param string $html HTML string to fix.
     * @return string Fixed HTML string with valid PHP code.
     */
    private function fixPhpCode($html)
    {
        return str_replace(
            array('&lt;?php', '?&gt;', '-&gt;', '=&gt;', '&gt;', '&lt;', '&quot;', '&amp;'), 
            array('<'.'?'.'php', '?'.'>', '->', '=>', '>', '<', '"', '&'), 
            $html
        );
    }
    
    /**
     * Create GUI INSERT section without approval.
     *
     * Generates an HTML form for inserting new records, including input fields and buttons.
     *
     * @param MagicObject $mainEntity The main entity object containing metadata.
     * @param AppField[] $fields Array of AppField objects representing the form fields.
     * @return string Generated HTML and PHP code for the INSERT form.
     */
    public function createGuiInsert($mainEntity, $fields)
    {
        $entityName = $mainEntity->getEntityName();
        $primaryKeyName =  $mainEntity->getPrimaryKey();

        $objectName = lcfirst($entityName);
        $dom = new DOMDocument();
        
        $form = $this->createElementForm($dom, 'createform');
        if(AppBuilderBase::hasInputFile($fields))
        {
            $form->setAttribute('enctype', 'multipart/form-data');
        }
        
        $table1 = $this->createInsertFormTable($dom, $mainEntity, $objectName, $fields, $primaryKeyName);


        $table2 = $this->createButtonContainerTableCreate($dom, "save-create", "save-create", 'UserAction::CREATE');

        $form->appendChild($table1);
        $form->appendChild($table2);
        
        $dom->appendChild($form);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $xml = $dom->saveXML();
        $html = $this->xmlToHtml($xml);     
        $html = $this->fixTable($html);
        $html = $this->fixPhpCode($html);
        
        $html = trim($html, self::NEW_LINE);
        $html = $this->addTab($html, 2);
        $html = $this->addIndent($html, 2);
        $html = $this->addWrapper($html, self::WRAPPER_INSERT);
        
        return "if(".self::VAR."inputGet->getUserAction() == UserAction::CREATE)\r\n"
        .self::CURLY_BRACKET_OPEN.self::NEW_LINE
        .$this->constructEntityLabel($entityName).self::NEW_LINE
        .$this->getIncludeHeader().self::NEW_LINE
        .self::PHP_CLOSE_TAG.self::NEW_LINE.$html.self::NEW_LINE.self::PHP_OPEN_TAG.self::NEW_LINE
        .$this->getIncludeFooter().self::NEW_LINE
        .self::CURLY_BRACKET_CLOSE;
    }
    
    /**
     * Create GUI UPDATE section, optionally requiring approval.
     *
     * Generates an HTML form for updating existing records, with logic for handling approval.
     *
     * @param MagicObject $mainEntity The main entity object containing metadata.
     * @param AppField[] $fields Array of AppField objects representing the form fields.
     * @param boolean $approvalRequired Flag indicating if approval is needed for the update.
     * @return string Generated HTML and PHP code for the UPDATE form.
     */
    public function createGuiUpdate($mainEntity, $fields, $approvalRequired = false)
    {   
        $entityName = $mainEntity->getEntityName();
        $primaryKeyName =  $mainEntity->getPrimaryKey();
        $upperPkName = PicoStringUtil::upperCamelize($primaryKeyName);

        $objectName = lcfirst($entityName);
        $dom = new DOMDocument();
        
        $form = $this->createElementForm($dom, 'updateform');
        if(AppBuilderBase::hasInputFile($fields))
        {
            $form->setAttribute('enctype', 'multipart/form-data');
        }
        
        $table1 = $this->createUpdateFormTable($dom, $mainEntity, $objectName, $fields, $primaryKeyName);      

        $table2 = $this->createButtonContainerTableUpdate($dom, $objectName, $primaryKeyName);

        $form->appendChild($table1);
        $form->appendChild($table2);
        
        $dom->appendChild($form);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $xml = $dom->saveXML();

        $html = $this->xmlToHtml($xml);
        $html = $this->fixTable($html);
        $html = $this->fixPhpCode($html);
        
        $html = trim($html, self::NEW_LINE);
        
        $html = $this->addTab($html, 2);
        $html = $this->addIndent($html, 2);
        $html = $this->addWrapper($html, self::WRAPPER_UPDATE);
        
        $getData = array();
        $getData[] = $this->getSpecs($primaryKeyName, 1);
        $getData[] = self::TAB1.$this->createConstructor($objectName, $entityName);
        $getData[] = self::TAB1."try{";
        $getData[] = self::TAB1.self::TAB1.self::VAR.$objectName.self::CALL_FIND_ONE.'($specification);'; // NOSONAR
        $getData[] = self::TAB1.self::TAB1."if(".self::VAR.$objectName.self::CALL_ISSET.$upperPkName.self::BRACKETS.")";
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;

        $getData[] = $this->constructEntityLabel($entityName);
        $getData[] = $this->getIncludeHeader();

        if($approvalRequired)
        {
            $upperWaitingFor = PicoStringUtil::upperCamelize($this->entityInfo->getWaitingFor());
            $getData[] = self::TAB1.self::TAB1."if(!UserAction::isRequireApproval(".self::VAR.$objectName."->get".$upperWaitingFor.self::BRACKETS."))";
            $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        }


        $getData[] = self::PHP_CLOSE_TAG.self::NEW_LINE.$html.self::NEW_LINE.self::PHP_OPEN_TAG;

        if($approvalRequired)
        {
            $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
            $getData[] = self::TAB1.self::TAB1."else";
            $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
            $getData[] = self::TAB1.self::TAB1.self::TAB1.self::PHP_CLOSE_TAG;
            $getData[] = self::TAB1.self::TAB1.self::TAB1.'<div class="alert alert-warning"><?php echo $appLanguage->getMessageNoneditableDataWaitingApproval();?></div>';
            $getData[] = self::TAB1.self::TAB1.self::TAB1.'<div class="form-control-container button-area"><button type="button" class="btn btn-primary" id="back_to_list" onclick="window.location=\'<?php echo $currentModule->getRedirectUrl();?>\';"><?php echo $appLanguage->getButtonBackToList();?></button></div>';
            $getData[] = self::TAB1.self::TAB1.self::TAB1.self::PHP_OPEN_TAG;
    
            $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        }

        

        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1.self::TAB1."else";
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = self::TAB1.self::TAB1.self::TAB1."// Do somtething here when data is not found"; // NOSONAR
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::PHP_CLOSE_TAG;
        $getData[] = self::TAB1.self::TAB1.self::TAB1.'<div class="alert alert-warning"><?php echo $appLanguage->getMessageDataNotFound();?></div>'; // NOSONAR
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::PHP_OPEN_TAG;
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;

        
        $getData[] = $this->getIncludeFooter();

        

        $getData[] = self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1."catch(Exception ".self::VAR."e)"; // NOSONAR
        $getData[] = self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = $this->getIncludeHeader();
        $getData[] = self::TAB1.self::TAB1."// Do somtething here when exception"; // NOSONAR
        $getData[] = self::TAB1.self::TAB1.self::PHP_CLOSE_TAG;
        $getData[] = self::TAB1.self::TAB1.'<div class="alert alert-danger"><?php echo $e->getMessage();?></div>'; // NOSONAR
        $getData[] = self::TAB1.self::TAB1.self::PHP_OPEN_TAG;
        $getData[] = $this->getIncludeFooter();


        $getData[] = self::TAB1.self::CURLY_BRACKET_CLOSE.self::NEW_LINE;
        
        return "if(".self::VAR."inputGet->getUserAction() == UserAction::UPDATE)\r\n"
        .self::CURLY_BRACKET_OPEN.self::NEW_LINE
        .implode(self::NEW_LINE, $getData)
        .self::CURLY_BRACKET_CLOSE;
    }
    
    /**
     * Create GUI DETAIL section, handling approval if required.
     *
     * Generates a detail view for an entity, displaying its properties and handling approval logic.
     *
     * @param MagicObject $mainEntity The main entity object containing metadata.
     * @param AppField[] $appFields Array of AppField objects for displaying the entity's details.
     * @param MagicObject[] $referenceData Array of reference data for related entities.
     * @param boolean $approvalRequired Flag indicating if approval is needed.
     * @param MagicObject|null $approvalEntity Optional entity for handling approval logic.
     * @return string Generated HTML and PHP code for the DETAIL view.
     */
    public function createGuiDetail($mainEntity, $appFields, $referenceData, $approvalRequired = false, $approvalEntity = null)
    {
        if($approvalRequired)
        {
            return $this->createGuiDetailWithApproval($mainEntity, $appFields, $referenceData, $approvalEntity);
        }
        else
        {
            return $this->createGuiDetailWithoutApproval($mainEntity, $appFields, $referenceData);
        }
    }
    
    /**
     * Define a mapping for subquery references.
     *
     * Constructs an array mapping of reference data for entity relationships.
     *
     * @param MagicObject[] $referenceData Array of reference data objects.
     * @return string PHP array definition of subquery references.
     */
    public function defineSubqueryReference($referenceData)
    {
        $map = array();
        foreach($referenceData as $fieldName => $reference)
        {
            if(
            $reference->getType() == 'entity'
            && $reference->getEntity() != null
            )
            {
                $entity = $reference->getEntity();
                $entityName = $entity->getEntityName();
                $tableName = $entity->getTableName();
                $primaryKey = $entity->getPrimaryKey();
                $objectName = $entity->getObjectName();
                $propertyName = $entity->getPropertyName();
                $camel = PicoStringUtil::camelize($fieldName);
                $map[] = 
                "\r\n\"$camel\" => array(".
                "\r\n\t\"columnName\" => \"$fieldName\",".
                "\r\n\t\"entityName\" => \"$entityName\",".
                "\r\n\t\"tableName\" => \"$tableName\",".
                "\r\n\t\"primaryKey\" => \"$primaryKey\",".
                "\r\n\t\"objectName\" => \"$objectName\",".
                "\r\n\t\"propertyName\" => \"$propertyName\"".
                "\r\n)";
            }
        }
        if(!empty($map))
        {
            return 'array('.implode(", ", $map)."\r\n)";
        }
        else
        {
            return 'null';
        }
    }

    /**
     * Define a mapping for specific fields with associative data.
     *
     * Builds a mapping of values for fields that contain associative data structures.
     *
     * @param MagicObject[] $referenceData Array of reference data objects.
     * @return string PHP code defining the mapping for specified fields.
     */
    public function defineMap($referenceData)
    {
        $map = array();
        foreach($referenceData as $fieldName => $reference)
        {
            if($reference->getType() == 'map'
            && $reference->getMap() != null
            )
            {
                $values = $reference->getMap()->valueArray();
                $arr1 = array();
                foreach($values as $val1)
                {
                    $arr2 = array();
                    foreach($val1 as $key2=>$val2)
                    {
                        $val3 = $this->fixJsonValue($val2);
                        $arr2[] = '"'.trim($key2).'" => '.$val3;
                    }
                    $arr1[] = '"'.trim($val1['value']).'" => array('.implode(', ', $arr2).')';
                }
                $upperFieldName = PicoStringUtil::upperCamelize($fieldName);
                $map[] = self::MAP_FOR.$upperFieldName." = array(\r\n".self::TAB1.implode(",\r\n".self::TAB1, $arr1)."\r\n".");";
            }
        }
        return implode("\r\n", $map);
    }
    
    /**
     * Fixes the JSON value by ensuring that strings are properly quoted and escaped.
     *
     * This method checks if the provided value is a boolean or null representation
     * in string form ('true', 'false', or 'null'). If the value is not one of those,
     * it wraps the value in double quotes and escapes any existing double quotes inside
     * the string. If the value is 'true', 'false', or 'null', it returns the value as is.
     *
     * @param string $val2 The value to fix for JSON compatibility.
     * @return string The properly formatted JSON value.
     */
    private function fixJsonValue($val2)
    {
        if($val2 != 'true' && $val2 != 'false' && $val2 != 'null')
        {
            return '"'.str_replace("\"", "\\\"", $val2).'"';
        }
        return $val2;
    }
    
    /**
     * Generates the specification code based on the provided primary key name, its upper-cased version, and indentation level.
     * 
     * This method constructs a formatted string for the specification logic. The generated string 
     * includes a specification object initialization and an additional method call, both of which 
     * are indented according to the value of `$ntab`. If `$ntab` is 0, no indentation is applied.
     * 
     * @param string $primaryKeyName The name of the primary key to be used in the specification.
     * @param int $ntab The number of indentation levels (default is 0).
     * @return string The generated specification code as a formatted string.
     */
    public function getSpecs($primaryKeyName, $ntab = 0)
    {
        $pnName = PicoStringUtil::camelize($primaryKeyName);
        $upperPkName = ucfirst($pnName);
        // Add TAB1 repeated $ntab times for indentation
        $tabs = str_repeat(self::TAB1, $ntab);

        // Create the $specsVar1 with the correct indentation based on $ntab
        $specsVar1 = $tabs . '$specification = PicoSpecification::getInstanceOf(' . AppBuilderBase::getStringOf($pnName) . ', ' . self::VAR . "inputGet" . self::CALL_GET . $upperPkName . '(PicoFilterConstant::'.$this->getInputFilter($primaryKeyName).'));';
        $specsVar1 .= self::NEW_LINE . $tabs . '$specification->addAnd($dataFilter);';
        
        return $specsVar1;
    }

    /**
     * Create GUI DETAIL section with approval.
     *
     * Generates an HTML detail view for an entity, including logic for handling approval.
     *
     * @param MagicObject $mainEntity The main entity object containing metadata.
     * @param AppField[] $appFields Array of AppField objects representing the form fields.
     * @param MagicObject[] $referenceData Array of reference data for related entities.
     * @param MagicObject|null $approvalEntity Optional entity for handling approval logic.
     * @return string Generated HTML and PHP code for the DETAIL view with approval.
     */
    public function createGuiDetailWithApproval($mainEntity, $appFields, $referenceData, $approvalEntity = null)
    {
        $map = $this->addIndent($this->defineMap($referenceData), 3);
        $entityName = $mainEntity->getEntityName();
        $entityApprovalName = $approvalEntity->getEntityName();
        $objectApprovalName = PicoStringUtil::camelize($entityApprovalName);
        $primaryKeyName =  $mainEntity->getPrimaryKey();
        $upperPkName = PicoStringUtil::upperCamelize($primaryKeyName);

        $objectName = lcfirst($entityName);
        
        $htmlDetail = $this->createTableDetail($objectName, $appFields, $primaryKeyName);

        $htmlDetailCompare = $this->createTableDetailCompare($mainEntity, $objectName, $appFields, $primaryKeyName, $approvalEntity, $objectApprovalName);

        $getData = array();
        $getData[] = $this->getSpecs($primaryKeyName, 1);
        $getData[] = self::TAB1.$this->createConstructor($objectName, $entityName);
        $getData[] = self::TAB1."try{";
        
        $features = $this->appFeatures;
        if($features->getSubquery())
        {
            $referece = $this->defineSubqueryReference($referenceData);
            $subqueryVar = '$subqueryMap = '.$referece.';';
            $getData[] = $this->addIndent($subqueryVar, 2);
            $getData[] = self::TAB1.self::TAB1.self::VAR.$objectName.self::CALL_FIND_ONE.'($specification, null, $subqueryMap);';
        }
        else
        {
            $getData[] = self::TAB1.self::TAB1.self::VAR.$objectName.self::CALL_FIND_ONE.'($specification);';
        }
        
        $getData[] = self::TAB1.self::TAB1."if(".self::VAR.$objectName.self::CALL_ISSET.$upperPkName.self::BRACKETS.")";
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = self::TAB1.self::TAB1.self::TAB1.'// Define map here';
        $getData[] = $map;

        $getData[] = self::TAB1.self::TAB1.self::TAB1."if(UserAction::isRequireNextAction(\$inputGet) && ".self::VAR.$objectName."->notNullApprovalId())";
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.$this->createConstructor($objectApprovalName, $entityApprovalName);
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1."try";
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        if($features->getSubquery())
        {
            $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::VAR.$objectApprovalName.self::CALL_FIND_ONE_WITH_PRIMARY_KEY."(".self::VAR.$objectName.self::CALL_GET."ApprovalId(), ".self::VAR."subqueryMap);";
        }
        else
        {
            $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::VAR.$objectApprovalName."->find(".self::VAR.$objectName.self::CALL_GET."ApprovalId());";
        }
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1."catch(Exception ".self::VAR."e)";
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1."// do something here";
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;

        $getData[] = $this->constructEntityLabel($entityName);
        $getData[] = $this->getIncludeHeader();
        $getData[] = self::PHP_CLOSE_TAG.self::NEW_LINE.$htmlDetailCompare.self::NEW_LINE.self::PHP_OPEN_TAG;
        $getData[] = $this->getIncludeFooter();

        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1.self::TAB1.self::TAB1."else";
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;

        $getData[] = $this->constructEntityLabel($entityName);
        $getData[] = $this->getIncludeHeader();

        $getData[] = self::PHP_CLOSE_TAG.self::NEW_LINE.$htmlDetail.self::NEW_LINE.self::PHP_OPEN_TAG;
        $getData[] = $this->getIncludeFooter();

        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;

        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1.self::TAB1."else";
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = $this->getIncludeHeader();
        $getData[] = self::TAB1.self::TAB1.self::TAB1."// Do somtething here when data is not found";
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::PHP_CLOSE_TAG;
        $getData[] = self::TAB1.self::TAB1.self::TAB1.'<div class="alert alert-warning"><?php echo $appLanguage->getMessageDataNotFound();?></div>';
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::PHP_OPEN_TAG;
        $getData[] = $this->getIncludeFooter();
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1."catch(Exception ".self::VAR."e)";
        $getData[] = self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = $this->getIncludeHeader();
        $getData[] = self::TAB1.self::TAB1."// Do somtething here when exception";
        $getData[] = self::TAB1.self::TAB1.self::PHP_CLOSE_TAG;
        $getData[] = self::TAB1.self::TAB1.'<div class="alert alert-danger"><?php echo $e->getMessage();?></div>';
        $getData[] = self::TAB1.self::TAB1.self::PHP_OPEN_TAG;
        $getData[] = $this->getIncludeFooter();
        $getData[] = self::TAB1.self::CURLY_BRACKET_CLOSE.self::NEW_LINE;

        return "if(".self::VAR."inputGet->getUserAction() == UserAction::DETAIL)\r\n"
        .self::CURLY_BRACKET_OPEN.self::NEW_LINE
        .implode(self::NEW_LINE, $getData)
        .self::CURLY_BRACKET_CLOSE;
    }
    
    /**
     * Create GUI DETAIL section without approval.
     *
     * Generates an HTML detail view for an entity without requiring approval.
     *
     * @param MagicObject $mainEntity The main entity object containing metadata.
     * @param AppField[] $appFields Array of AppField objects representing the form fields.
     * @param MagicObject[] $referenceData Array of reference data for related entities.
     * @return string Generated HTML and PHP code for the DETAIL view without approval.
     */
    public function createGuiDetailWithoutApproval($mainEntity, $appFields, $referenceData)
    {
        $map = $this->addIndent($this->defineMap($referenceData), 3);
        $entityName = $mainEntity->getEntityName();
        $primaryKeyName =  $mainEntity->getPrimaryKey();
        $upperPkName = PicoStringUtil::upperCamelize($primaryKeyName);

        $objectName = lcfirst($entityName);
        
        $htmlDetail = $this->createTableDetail($objectName, $appFields, $primaryKeyName);

        $getData = array();

        $getData[] = $this->getSpecs($primaryKeyName, 1);
        $getData[] = self::TAB1.$this->createConstructor($objectName, $entityName);
        $getData[] = self::TAB1."try{";
        
        $features = $this->appFeatures;
        if($features->getSubquery())
        {
            $referece = $this->defineSubqueryReference($referenceData);
            $subqueryVar = '$subqueryMap = '.$referece.';';
            $getData[] = $this->addIndent($subqueryVar, 2);
            $getData[] = self::TAB1.self::TAB1.self::VAR.$objectName.self::CALL_FIND_ONE.'($specification, null, $subqueryMap);';
        }
        else
        {
            $getData[] = self::TAB1.self::TAB1.self::VAR.$objectName.self::CALL_FIND_ONE.'($specification);';
        }       
        
        $getData[] = self::TAB1.self::TAB1."if(".self::VAR.$objectName.self::CALL_ISSET.$upperPkName.self::BRACKETS.")";
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;

        $getData[] = $this->constructEntityLabel($entityName);
        $getData[] = $this->getIncludeHeader();
        $getData[] = self::TAB1.self::TAB1.self::TAB1.'// Define map here';
        $getData[] = $map;

        $getData[] = self::PHP_CLOSE_TAG.self::NEW_LINE.$htmlDetail.self::NEW_LINE.self::PHP_OPEN_TAG;
        $getData[] = $this->getIncludeFooter();     

        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1.self::TAB1."else";
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = self::TAB1.self::TAB1.self::TAB1."// Do somtething here when data is not found";
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::PHP_CLOSE_TAG;
        $getData[] = self::TAB1.self::TAB1.self::TAB1.'<div class="alert alert-warning"><?php echo $appLanguage->getMessageDataNotFound();?></div>';
        $getData[] = self::TAB1.self::TAB1.self::TAB1.self::PHP_OPEN_TAG;
        $getData[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1.self::CURLY_BRACKET_CLOSE;
        $getData[] = self::TAB1."catch(Exception ".self::VAR."e)";
        $getData[] = self::TAB1.self::CURLY_BRACKET_OPEN;
        $getData[] = $this->getIncludeHeader();
        $getData[] = self::TAB1.self::TAB1."// Do somtething here when exception";
        $getData[] = self::TAB1.self::TAB1.self::PHP_CLOSE_TAG;
        $getData[] = self::TAB1.self::TAB1.'<div class="alert alert-danger"><?php echo $e->getMessage();?></div>';
        $getData[] = self::TAB1.self::TAB1.self::PHP_OPEN_TAG;
        $getData[] = $this->getIncludeFooter();
        $getData[] = self::TAB1.self::CURLY_BRACKET_CLOSE.self::NEW_LINE;

        return "if(".self::VAR."inputGet->getUserAction() == UserAction::DETAIL)\r\n"
        .self::CURLY_BRACKET_OPEN.self::NEW_LINE
        .implode(self::NEW_LINE, $getData)
        .self::CURLY_BRACKET_CLOSE;
    }

    /**
     * Create a detail table for displaying entity information.
     *
     * Generates a table layout for the detail view of an entity, including form elements.
     *
     * @param string $objectName The name of the entity object.
     * @param AppField[] $appFields Array of AppField objects representing the form fields.
     * @param string $primaryKeyName The name of the primary key for the entity.
     * @return string Generated HTML code for the detail table.
     */
    public function createTableDetail($objectName, $appFields, $primaryKeyName)
    {
        $dom = new DOMDocument();
        
        $formDetail = $this->createElementForm($dom, 'detailform');
        $tableDetail1 = $this->createDetailTable($dom, $objectName, $appFields);
        $tableDetail2 = $this->createButtonContainerTableDetail($dom, $objectName, $primaryKeyName);

        

        $approval = '<?php
if(UserAction::isRequireNextAction($inputGet) && UserAction::isRequireApproval($'.$objectName.'->getWaitingFor()))
{
    ?>
    <div class="alert alert-info"><?php echo UserAction::getWaitingForMessage($appLanguage, $'.$objectName.'->getWaitingFor());?></div>
    <?php
}
?>
';
        $approval = str_replace("\r\n", "\n", $approval);

        $dom->appendChild($dom->createTextNode($approval));
        $formDetail->appendChild($tableDetail1);


        $formDetail->appendChild($tableDetail2);

        $formDetail->appendChild($tableDetail2);

        $dom->appendChild($formDetail);

        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $xml = $dom->saveXML();

        $htmlDetail = $this->xmlToHtml($xml);
        $htmlDetail = $this->fixTable($htmlDetail);
        $htmlDetail = $this->fixPhpCode($htmlDetail);
        $htmlDetail = trim($htmlDetail, self::NEW_LINE);
        
        $htmlDetail = $this->addTab($htmlDetail, 2);
        $htmlDetail = $this->addIndent($htmlDetail, 2);
        $htmlDetail = $this->addWrapper($htmlDetail, self::WRAPPER_DETAIL);

        return $htmlDetail;
    }

    /**
     * Create a comparison detail table for the entity, including approval entity.
     *
     * Generates a comparison table layout for the detail view of an entity with its approval counterpart.
     *
     * @param MagicObject $mainEntity The main entity object containing metadata.
     * @param string $objectName The name of the entity object.
     * @param AppField[] $appFields Array of AppField objects representing the form fields.
     * @param string $primaryKeyName The name of the primary key for the entity.
     * @param MagicObject $approvalEntity The approval entity for comparison.
     * @param string $objectApprovalName The name of the approval entity object.
     * @return string Generated HTML code for the comparison detail table.
     */
    public function createTableDetailCompare($mainEntity, $objectName, $appFields, $primaryKeyName, $approvalEntity, $objectApprovalName)
    {
        $dom = new DOMDocument();
        
        $formDetail = $this->createElementForm($dom, 'detailform');
                
        $div = $dom->createElement('div');
        $div->setAttribute('class', 'alert alert-info');
            
        $messagePhp = '
<?php
echo UserAction::getWaitingForMessage($appLanguage, $'.$objectName.'->getWaitingFor());
?>
';
        $messagePhp = str_replace("\r\n", "\n", $messagePhp);
        $messagePhp = $this->addIndent($messagePhp, 1);
        $messagePhp = str_replace("\r\n", "\n", $messagePhp);
        
        $message = $dom->createTextNode($messagePhp);

        $div->appendChild($message);
        
        $formDetail->appendChild($div);
        
        $tableDetail1 = $this->createDetailTableCompare($dom, $mainEntity, $objectName, $appFields, $primaryKeyName, $approvalEntity, $objectApprovalName);
        $tableDetail2 = $this->createButtonContainerTableApproval($dom, $objectName, $primaryKeyName);

        $formDetail->appendChild($tableDetail1);
        $formDetail->appendChild($tableDetail2);
        
        $dom->appendChild($formDetail);
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;

        $xml = $dom->saveXML();

        $htmlDetail = $this->xmlToHtml($xml);
        $htmlDetail = $this->fixTable($htmlDetail);
        $htmlDetail = $this->fixPhpCode($htmlDetail);
        $htmlDetail = trim($htmlDetail, self::NEW_LINE);
        
        $htmlDetail = $this->addTab($htmlDetail, 2);
        $htmlDetail = $this->addIndent($htmlDetail, 2);
        $htmlDetail = $this->addWrapper($htmlDetail, self::WRAPPER_DETAIL);

        return $htmlDetail;
    }

    /**
     * Create export value for a given field.
     *
     * @param string $objectName The name of the object.
     * @param AppField $field The field object containing information about the field.
     * @return string The formatted export value as a string.
     */
    private function createExportValue($objectName, $field) // NOSONAR
    {
        $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
        
        $yes = self::VAR."appLanguage->getYes()"; // NOSONAR
        $no = self::VAR."appLanguage->getNo()"; // NOSONAR

        $true = self::VAR."appLanguage->getTrue()";
        $false = self::VAR."appLanguage->getFalse()";

        if(($field->getElementType() == InputType::CHECKBOX) ||
            ($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null
            && $field->getReferenceData()->getType() == ReferenceType::YESNO)
        )
        {
            $val = "->option".$upperFieldName."(".$yes.", ".$no.")"; // NOSONAR
            $result = self::VAR.'row'.$val;
        }
        else if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::ENTITY
            && $field->getReferenceData()->getEntity() != null
            && $field->getReferenceData()->getEntity()->getObjectName() != null
            && $field->getReferenceData()->getEntity()->getPropertyName() != null
            )
        {
            $objName = $field->getReferenceData()->getEntity()->getObjectName();
            $propName = $field->getReferenceData()->getEntity()->getPropertyName();
            $upperObjName = PicoStringUtil::upperCamelize($objName);
            $upperPropName = PicoStringUtil::upperCamelize($propName);

            $val = self::CALL_ISSET.$upperObjName.self::BRACKETS.' ? $row'.self::CALL_GET.$upperObjName.self::BRACKETS.self::CALL_GET.$upperPropName.self::BRACKETS.' : ""'; // NOSONAR
            $result = self::VAR.'row'.$val;
        }
        else if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::MAP
            && $field->getReferenceData()->getMap() != null
            )
        {
            $v1 = 'isset('.self::MAP_FOR.$upperFieldName.')'; // NOSONAR
            $v2 = 'isset($mapFor'.$upperFieldName.'[$'.'row'.self::CALL_GET.$upperFieldName.self::BRACKETS.'])'; // NOSONAR
            $v3 = 'isset($mapFor'.$upperFieldName.'[$'.'row'.self::CALL_GET.$upperFieldName.self::BRACKETS.']["label"])'; // NOSONAR
            $v4 = self::MAP_FOR.$upperFieldName.'[$'.'row'.self::CALL_GET.$upperFieldName.self::BRACKETS.']["label"]'; // NOSONAR
            $val = "$v1 && $v2 && $v3 ? $v4 : \"\"";
            $result = $val;
        }
        else if($field->getElementType() == InputType::SELECT 
        && $field->getReferenceData() != null
            && $field->getReferenceData()->getType() == ReferenceType::TRUE_FALSE
            )
        {
            $val = "->option".$upperFieldName."(".$true.", ".$false.")";
            $result = self::VAR.'row'.$val;
        }
        else if($field->getElementType() == InputType::SELECT 
        && $field->getReferenceData() != null
            && $field->getReferenceData()->getType() == ReferenceType::ONE_ZERO
            )
        {
            $val = "->option".$upperFieldName."(\"1\", \"0\")";
            $result = self::VAR.'row'.$val;
        }
        else
        {
            $val = "".self::CALL_GET.$upperFieldName.self::BRACKETS."";
            $result = self::VAR.'row'.$val;
        }
        
        return $result;

    }

    /**
     * Generate the export script for the specified entity and fields.
     *
     * @param MagicObject $entityMain The main entity object for the export.
     * @param AppField[] $exportFields An array of AppField objects to be exported.
     * @return string The generated export script as a string.
     */
    private function createExportScript($entityMain, $exportFields)
    {
        $entityName = $entityMain->getentityName();
        $objectName = lcfirst($entityName);
        $headers = array();
        $data = array();
        $globals = array();
        $globals[] = '$appLanguage';
        foreach($exportFields as $field)
        {
            $caption = PicoStringUtil::upperCamelize($field->getFieldName());
            $caption = $this->fixFieldName($caption, $field);

            if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::MAP
            && $field->getReferenceData()->getMap() != null
            )
            {
                $globals[] = '$'.PicoStringUtil::camelize('map_for_'.$field->getFieldName());
            }

            if($field->getElementType() == InputType::TEXT)
            {
                $line1 = self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$caption.self::BRACKETS." => ".self::VAR."headerFormat".self::CALL_GET.$caption.self::BRACKETS; 
            }
            else
            {
                $line1 = self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$caption.self::BRACKETS." => ".self::VAR."headerFormat->asString()";
            }

            $headers[] = $line1;
            $line2 = $this->createExportValue($objectName, $field);          
            $data[] = $line2;

        }

        if($this->appFeatures->isExportToExcel())
        {
            $exporter = '
'."\t".'$exporter = DocumentWriter::getXLSXDocumentWriter();
'."\t".'$fileName = $currentModule->getModuleName()."-".date("Y-m-d-H-i-s").".xlsx";
'."\t".'$sheetName = "Sheet 1";
';
        }
        else
        {
            $exporter = '
'."\t".'$exporter = DocumentWriter::getCSVDocumentWriter();
'."\t".'$fileName = $currentModule->getModuleName()."-".date("Y-m-d-H-i-s").".csv";
'."\t".'$sheetName = "CSV Document";
';
        }
        $uses = "";
        if(!empty($globals))
        {
            $uses = " use (".implode(", ", $globals).") ";
        }

return 'if($inputGet->getUserAction() == UserAction::EXPORT)
{'.$exporter.'
'."\t".'$headerFormat = new XLSXDataFormat($dataLoader, 3);
'."\t".'$pageData = $dataLoader->findAll($specification, null, $sortable, true, $subqueryMap, MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA);
'."\t".'$exporter->write($pageData, $fileName, $sheetName, array(
'."\t\t".'$appLanguage->getNumero() => $headerFormat->asNumber(),
'."\t\t".implode(",\n\t\t", $headers).'
'."\t".'), 
'."\t".'function($index, $row)'.$uses.'{
'."\t\t".'return array(
'.self::TAB3.'sprintf("%d", $index + 1),
'.self::TAB3.implode(",\n\t\t\t", $data).'
'."\t\t".');
'."\t".'});
'."\t".'exit();
}';
    }
    
    /**
     * Creates an export file (Excel or CSV) based on provided entity data.
     *
     * @param object $entityMain The main entity to export.
     * @param array $listFields List of fields for the export.
     * @param array $exportFields Fields to be included in the export.
     * @param array $referenceData Reference data for the fields.
     * @param array $filterFields Filters applied to the export.
     * @param array $sortable Sortable fields for data ordering.
     *
     * @return string|null The generated export content or null if export is not enabled.
     */
    public function createExport($entityMain, $listFields, $exportFields, $referenceData, $filterFields, $sortable) // NOSONAR
    {
        if($this->appFeatures->isExportToExcel() || $this->appFeatures->isExportToCsv())
        {
            $entityName = $entityMain->getentityName();

            $getData = array();
            
            $getData[] = 'if($inputGet->getUserAction() == UserAction::EXPORT)';
            $getData[] = '{';
            
            $getData[] = "\t".$this->constructEntityLabel($entityName);

            // before script
            $beforeScript = $this->beforeListScript($entityMain, $listFields, $filterFields, $referenceData, $sortable);
            $beforeScript = str_replace('$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);'."\n", '', $beforeScript);
            $beforeScript = trim($beforeScript, "\n");
            $arr = explode("\n", $beforeScript);
            foreach($arr as $k=>$v)
            {
                $arr[$k] = "\t".$v;
            }
            $beforeScript = implode("\n", $arr);
            $beforeScript = trim($beforeScript, "\n");
            
            $getData[] = $beforeScript;
            
            $entityName = $entityMain->getentityName();
            $objectName = lcfirst($entityName);
            $headers = array();
            $data = array();
            $globals = array();
            $globals[] = '$appLanguage';
            foreach($exportFields as $field)
            {
                $caption = PicoStringUtil::upperCamelize($field->getFieldName());
                $caption = $this->fixFieldName($caption, $field);

                if($field->getElementType() == InputType::SELECT 
                && $field->getReferenceData() != null 
                && $field->getReferenceData()->getType() == ReferenceType::MAP
                && $field->getReferenceData()->getMap() != null
                )
                {
                    $globals[] = '$'.PicoStringUtil::camelize('map_for_'.$field->getFieldName());
                }

                if($field->getElementType() == InputType::TEXT)
                {
                    $line1 = self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$caption.self::BRACKETS." => ".self::VAR."headerFormat".self::CALL_GET.$caption.self::BRACKETS.""; 
                }
                else
                {
                    $line1 = self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$caption.self::BRACKETS." => ".self::VAR."headerFormat->asString()";
                }

                $headers[] = $line1;
                $line2 = $this->createExportValue($objectName, $field);          
                $data[] = $line2;

            }

            if($this->appFeatures->isExportToExcel())
            {
                $exporter = '
'."\t".'$exporter = DocumentWriter::getXLSXDocumentWriter();
'."\t".'$fileName = $currentModule->getModuleName()."-".date("Y-m-d-H-i-s").".xlsx";
'."\t".'$sheetName = "Sheet 1";
';
        }
        else
        {
            $exporter = '
'."\t".'$exporter = DocumentWriter::getCSVDocumentWriter();
'."\t".'$fileName = $currentModule->getModuleName()."-".date("Y-m-d-H-i-s").".csv";
'."\t".'$sheetName = "CSV Document";
';
        }
        $uses = "";
        if(!empty($globals))
        {
            $uses = " use (".implode(", ", $globals).") ";
        }

        $getData[] = ''.$exporter.'
'."\t".'$headerFormat = new XLSXDataFormat($dataLoader, 3);
'."\t".'$pageData = $dataLoader->findAll($specification, null, $sortable, true, $subqueryMap, MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA);
'."\t".'$exporter->write($pageData, $fileName, $sheetName, array(
'."\t\t".'$appLanguage->getNumero() => $headerFormat->asNumber(),
'."\t\t".implode(",\n\t\t", $headers).'
'."\t".'), 
'."\t".'function($index, $row)'.$uses.'{
'."\t\t".'return array(
'.self::TAB3.'sprintf("%d", $index + 1),
'.self::TAB3.implode(",\n\t\t\t", $data).'
'."\t\t".');
'."\t".'});
'."\t".'exit();
}';
            
            
            $result = 
            "else "
            .implode(self::NEW_LINE, $getData)
            ;
            
            if(stripos($result, ' multiple=""') !== false && stripos($result, ' data-multi-select=""')  !== false) // NOSONAR
            {
                $result = str_replace(array(' multiple=""', ' data-multi-select=""'), array(' multiple', ' data-multi-select'), $result);
            }
            
            return $result;
        }
        return null;
    }

    /**
     * Create the GUI list section for displaying entities.
     *
     * @param MagicObject $entityMain The main entity object for the list.
     * @param AppField[] $listFields An array of fields to be displayed in the list.
     * @param AppField[] $exportFields An array of fields available for export.
     * @param MagicObject[] $referenceData Additional reference data objects.
     * @param AppField[] $filterFields An array of fields used for filtering.
     * @param boolean $approvalRequired Indicates if approval is required.
     * @param array $sortable Sorting options for the list.
     * @return string The generated HTML output for the list section.
     */
    public function createGuiList($entityMain, $listFields, $exportFields, $referenceData, $filterFields, $approvalRequired, $sortable) //NOSONAR
    {
        $entityName = $entityMain->getentityName();
        $primaryKey = $entityMain->getPrimaryKey();
        $objectName = lcfirst($entityName);
        $dom = new DOMDocument();
        $filterSection = $dom->createElement('div');
        $filterSection->setAttribute('class', 'filter-section');
        
        $filterSection->appendChild($dom->createTextNode("\n\t"));      
        $filterSection->appendChild($this->createFilterForm($dom, $filterFields));
        $filterSection->appendChild($dom->createTextNode("\n"));
        
        $dataSection = $dom->createElement('div');
        $dataSection->setAttribute('class', 'data-section');
        if($this->ajaxSupport)
        {
            $dataSection->setAttribute('data-ajax-support', 'true');
            $dataSection->setAttribute('data-ajax-name', 'main-data');
        }
        if($this->ajaxSupport)
        {
            $dataSection->appendChild($dom->createTextNode("\n\t".self::PHP_OPEN_TAG.'} /*ajaxSupport*/ '.self::PHP_CLOSE_TAG));
        }
        $dataSection->appendChild($dom->createTextNode("\n\t".self::PHP_OPEN_TAG)); 
                
        $dataSection->appendChild($dom->createTextNode("try{\n")); 
        $dataSection->appendChild($dom->createTextNode("\t\t".$this->getFindAllScript())); 
        
        $dataSection->appendChild($dom->createTextNode("\n\t\tif(\$pageData->getTotalResult() > 0)")); 
        
        $dataSection->appendChild($dom->createTextNode(self::N_TAB2.self::CURLY_BRACKET_OPEN)); 

        $paginationVar = '
    $pageControl = $pageData->getPageControl(Field::of()->page, $currentModule->getSelf())
    ->setNavigation(
        $dataControlConfig->getPrev(), $dataControlConfig->getNext(),
        $dataControlConfig->getFirst(), $dataControlConfig->getLast()
    )
    ->setPageRange($dataControlConfig->getPageRange())
    ;';
        $paginationVar = str_replace("\r\n", "\n", $paginationVar);
        $pagination1 = $dom->createTextNode($this->createPagination("pagination-top", '$pageControl'));
        $pagination2 = $dom->createTextNode($this->createPagination("pagination-bottom", '$pageControl'));

        $paginationVar = $this->addIndent($paginationVar, 2);
        $dataSection->appendChild($dom->createTextNode($paginationVar)); 

        $dataSection->appendChild($dom->createTextNode("\n\t".self::PHP_CLOSE_TAG)); 
        $dataSection->appendChild($dom->createTextNode("\n")); 
        $dataSection->appendChild($pagination1);
        $dataSection->appendChild($dom->createTextNode("\n\t")); 
        
        $dataSection->appendChild($this->createDataList($dom, $listFields, $objectName, $primaryKey, $approvalRequired));
        
        $dataSection->appendChild($dom->createTextNode("\n")); 
        $dataSection->appendChild($pagination2);
        $dataSection->appendChild($dom->createTextNode("\n\t")); 
        $dataSection->appendChild($dom->createTextNode("\n\t".self::PHP_OPEN_TAG));    

        $dataSection->appendChild($dom->createTextNode("\n")); 
        $dataSection->appendChild($dom->createTextNode("\t}\n")); 
        
        $dataSection->appendChild($dom->createTextNode($this->scriptWhenListNotFound())); 
        
        $dataSection->appendChild($dom->createTextNode("\n")); 

        $dom->appendChild($filterSection);

        $catch = '
<?php
}
catch(Exception $e)
{
    ?>
    <div class="alert alert-danger"><?php echo $appInclude->printException($e);?></div>
    <?php
} 
?>
';
    $catch = str_replace("\r\n", "\n", $this->addIndent($catch, 1));

        $dataSection->appendChild($dom->createTextNode($catch));

        if($this->ajaxSupport)
        {
            $dataSection->appendChild($dom->createTextNode("".self::PHP_OPEN_TAG.'/*ajaxSupport*/ if(!$currentAction->isRequestViaAjax()){ '.self::PHP_CLOSE_TAG."\n"));
        }
        $dom->appendChild($dataSection);
        
        $xml = $dom->saveXML();

        $htmlList = $this->xmlToHtml($xml);
        $htmlList = $this->fixTable($htmlList);
        $htmlList = $this->fixPhpCode($htmlList);
        $htmlList = trim($htmlList, self::NEW_LINE);
        
        $htmlList = $this->addTab($htmlList, 2);
        $htmlList = $this->addIndent($htmlList, 2);
        $htmlList = $this->addWrapper($htmlList, self::WRAPPER_LIST);

        
        $getData = array();
        
        $getData[] = $this->constructEntityLabel($entityName);

        // before script
        $getData[] = $this->beforeListScript($entityMain, $listFields, $filterFields, $referenceData, $sortable);
        if($this->appFeatures->isExportToExcel() || $this->appFeatures->isExportToCsv())
        {
            $getData[] = $this->createExportScript($entityMain, $exportFields);
        }

        if($this->ajaxSupport)
        {
            $getData[] = '/*ajaxSupport*/';
            $getData[] = 'if(!$currentAction->isRequestViaAjax()){';
        }
        $getData[] = $this->getIncludeHeader();

        $getData[] = self::PHP_CLOSE_TAG.self::NEW_LINE.$htmlList.self::NEW_LINE.self::PHP_OPEN_TAG;
        $getData[] = $this->getIncludeFooter();
        if($this->ajaxSupport)
        {
            $getData[] = '}';
            $getData[] = '/*ajaxSupport*/';
        }
        
        
        $result = 
        "\r\n{\r\n"
        .implode(self::NEW_LINE, $getData)
        .self::NEW_LINE
        .self::CURLY_BRACKET_CLOSE;
        
        if(stripos($result, ' multiple=""') !== false && stripos($result, ' data-multi-select=""')  !== false)
        {
            $result = str_replace(array(' multiple=""', ' data-multi-select=""'), array(' multiple', ' data-multi-select'), $result);
        }
        
        return $result;
    }
    
    /**
     * Generate script for displaying a message when no data is found.
     *
     * @return string The script for handling no data found scenarios.
     */
    public function scriptWhenListNotFound()
    {
        if($this->appFeatures->isApprovalRequired())
        {
        $script = 
'else if($inputGet->isShowRequireApprovalOnly())
{
    ?>
    <div class="alert alert-info"><?php echo $appLanguage->getMessageNoDataRequireApproval();?></div>
    <?php
}
else
{
    ?>
    <div class="alert alert-info"><?php echo $appLanguage->getMessageDataNotFound();?></div>
    <?php
}
?>';
        }
        else
        {
            $script = 
'else
{
    ?>
    <div class="alert alert-info"><?php echo $appLanguage->getMessageDataNotFound();?></div>
    <?php
}
?>';
        }

        $script = str_replace("\r\n", "\n", $script);
        $script = $this->addIndent($script, 1);
        $script = str_replace("\r\n", "\n", $script);
        
        return $script;
    }
    
    /**
     * Create pagination controls for data display.
     *
     * @param string $className The CSS class for the pagination.
     * @param string|null $paginationVar The variable containing pagination data, if available.
     * @return string The generated HTML for pagination.
     */
    public function createPagination($className, $paginationVar = null)
    {
        if(isset($paginationVar))
        {
            $script = 
            '<div class="pagination '.$className.'">
    <div class="pagination-number">
    <?php echo '.$paginationVar.'; ?>
    </div>
</div>';
        }
        else
        {
        $script = 
'<div class="pagination '.$className.'">
    <div class="pagination-number">
    <?php
    foreach($pageData->getPagination() as $pg)
    {
        ?><span class="page-selector<?php echo $pg'."['selected'] ? ' page-selected':'';?>".'" data-page-number="<?php echo $pg'."['page'];?>".'"><a href="<?php echo PicoPagination::getPageUrl($pg'."['page']".');?>"><?php echo $pg'."['page']".';?></a></span><?php
    }
    ?>
    </div>
</div>';
        }
        $script = str_replace("\r\n", "\n", $script);
        $script = $this->addIndent($script, 1);
        $script = str_replace("\r\n", "\n", $script);
        
        return $script;
        
    }

    /**
     * Get additional filters based on the provided specifications.
     *
     * @return string The additional filter script as a string.
     */
    private function getAdditionalFilter()
    {
        $additionalFilter = '';
        

        if($this->appFeatures->isApprovalRequired())
        {
            $additionalFilter .= "\n".'if($inputGet->isShowRequireApprovalOnly()){';
            $additionalFilter .= "\n\t".'$specification->addAnd(PicoPredicate::getInstance()->notEquals(Field::of()->waitingFor, WaitingFor::NOTHING));';
            $additionalFilter .= "\n\t".'$specification->addAnd(PicoPredicate::getInstance()->notEquals(Field::of()->waitingFor, null));';
            $additionalFilter .= "\n".'}';
        }

        return $additionalFilter;
    }

    /**
     * Get sortable parameters based on the specified sortable fields.
     *
     * @param array $sortable The array of sortable field specifications.
     * @return string The generated sortable parameters as a string.
     */
    private function getSortableParams($sortable)
    {
        $sortableParam = 'null';
        if(isset($sortable))
        {
            $arr0 = array();
            foreach($sortable as $values)
            {
                if(is_array($values) || is_object($values))
                {
                    $sortBy = $values->getSortBy();
                    $sortType = $values->getSortType();
                    if(stripos($sortType, 'PicoSort::') === false)
                    {
                        $sortType = '"'.$sortType.'"';
                    }
                    $arr0[] = "array(\r\n\t\t\"sortBy\" => \"$sortBy\", \r\n\t\t\"sortType\" => $sortType\r\n\t)";
                }
            }
            if(!empty($arr0))
            {
                $sortableParam = "array(\r\n".self::TAB1.implode(",\r\n".self::TAB1, $arr0)."\r\n".")";
            }
        }
        return $sortableParam;
    }
    
    /**
     * Create the script before the GUI list section.
     *
     * @param MagicObject $mainEntity The main entity object.
     * @param AppField[] $listFields An array of fields to be displayed in the list.
     * @param AppField[] $filterFields An array of filter fields.
     * @param MagicObject[] $referenceData An array of reference data objects.
     * @param array $sortable Sortable field specifications.
     * @return string The generated script for the list section.
     */
    public function beforeListScript($entityMain, $listFields, $filterFields, $referenceData, $sortable) // NOSONAR
    {
        $map = $this->defineMap($referenceData); 
        $additionalFilter = $this->getAdditionalFilter();
        $sortableParam = $this->getSortableParams($sortable);
        
        $arrFilter = array();
        $arrSort = array();
        
        foreach($filterFields as $field)
        {
            $type = $this->getFilterType($field);
            
            $multipleFilter = $field->getMultipleFilter();
            if($multipleFilter)
            {
                $type .= "[]";
            }
            
            $arrFilter[] = '"'.PicoStringUtil::camelize($field->getFieldName())
            .'" => PicoSpecification::filter("'.PicoStringUtil::camelize($field->getFieldName()).'", "'.$type.'")';
        }

        foreach($listFields as $field)
        {
            $arrSort[] = '"'.PicoStringUtil::camelize($field->getFieldName()).'" => "'.PicoStringUtil::camelize($field->getFieldName()).'"';
        }
        $script = 
''.$map.'
$specMap = array(
'."\t".implode(",\n\t", $arrFilter).'
);
$sortOrderMap = array(
'."\t".implode(",\n\t", $arrSort).'
);

// You can define your own specifications
// Pay attention to security issues
$specification = PicoSpecification::fromUserInput($inputGet, $specMap);
$specification->addAnd($dataFilter);
'.$additionalFilter.'

// You can define your own sortable
// Pay attention to security issues
$sortable = PicoSortable::fromUserInput($inputGet, $sortOrderMap, '.$sortableParam.');

$pageable = new PicoPageable(new PicoPage($inputGet->getPage(), $dataControlConfig->getPageSize()), $sortable);
$dataLoader = new '.$entityMain->getEntityName().'(null, $database);
';
        $features = $this->appFeatures;
        if($features->getSubquery())
        {
            $referece = $this->defineSubqueryReference($referenceData);
            $subqueryVar = '
$subqueryMap = '.$referece.';
';
        }
        else
        {
            $subqueryVar = '
';
        }
        
        $script = $script.$subqueryVar;

        $script = str_replace("\r\n", "\n", $script);
        $script = str_replace("\r\n", "\n", $script);
        
        return $script;
    }

    /**
     * Generate the script to find all entities based on conditions.
     *
     * @return string The script for finding all entities.
     */
    private function getFindAllScript()
    {
        $features = $this->appFeatures;
        if($features->getSubquery())
        {
            return '$pageData = $dataLoader->findAll($specification, $pageable, $sortable, true, $subqueryMap, MagicObject::FIND_OPTION_NO_FETCH_DATA);';
        }
        else
        {
            return '$pageData = $dataLoader->findAll($specification, $pageable, $sortable, true, null, MagicObject::FIND_OPTION_NO_FETCH_DATA);';
        }        
    }

    /**
     * Get the filter type for a specific field.
     *
     * @param AppField $field The field object to determine the filter type.
     * @return string The determined filter type.
     */
    private function getFilterType($field)
    {
        $type = $field->getReferenceFilter() != null ? $field->getReferenceFilter()->getType() : '';
        $dataType = null;
        if($type == 'yesno' || $type == 'truefalse')
        {
            $dataType = 'boolean';
        }
        else if($type == 'onezero')
        {
            $dataType = 'number';
        }
        else
        {
            if(stripos($field->getInputFilter(), 'number') !== false)
            {
                $dataType = 'number';
            }
            else
            {
                $dataType = 'fulltext';
            }
        }
        return $dataType;
    }
    
    /**
     * Create the data list form with a table and buttons.
     *
     * @param DOMDocument $dom The DOM document for generating HTML.
     * @param AppField[] $listFields An array of fields to be displayed in the list.
     * @param string $objectName The name of the object being listed.
     * @param string $primaryKey The primary key for the object.
     * @param boolean $approvalRequired Indicates if approval is required.
     * @return DOMElement The generated form element containing the data list.
     */
    public function createDataList($dom, $listFields, $objectName, $primaryKey, $approvalRequired)
    {
        $form = $dom->createElement('form');
        $form->setAttribute('action', '');
        $form->setAttribute('method', 'post');
        $form->setAttribute('class', 'data-form');

        $div1 = $dom->createElement('div');
        $div1->setAttribute('class', 'data-wrapper');
        
        $table = $this->createDataTableList($dom, $listFields, $objectName, $primaryKey, $approvalRequired);
        
        $div1->appendChild($dom->createTextNode(self::N_TAB3)); 
        $div1->appendChild($table);
        $div1->appendChild($dom->createTextNode(self::N_TAB2)); 


        $div2 = $dom->createElement('div');
        $div2->setAttribute('class', 'button-wrapper');
        
        $buttonWrapper = $this->createButtonWrapper($dom, $this->appFeatures);
        
        $div2->appendChild($dom->createTextNode(self::N_TAB3)); 
        $div2->appendChild($buttonWrapper);
        $div2->appendChild($dom->createTextNode(self::N_TAB2)); 
        
        $form->appendChild($dom->createTextNode(self::N_TAB2)); 
        $form->appendChild($div1);
        $form->appendChild($dom->createTextNode(self::N_TAB2)); 
        $form->appendChild($div2);
        $form->appendChild($dom->createTextNode("\n\t")); 
        
        
        
        return $form;
    }

    /**
     * Create a wrapper for buttons in the data list.
     *
     * @param DOMDocument $dom The DOM document for generating HTML.
     * @param AppFeatures $appFeatures The application features object.
     * @return DOMElement The generated button wrapper element.
     */
    public function createButtonWrapper($dom, $appFeatures)
    {
        $activate = $appFeatures->isActivateDeactivate();

        $sortOrder = $appFeatures->isSortOrder();

        $wrapper = $dom->createElement('div');
        $wrapper->setAttribute('class', 'button-area');

        if($activate)
        {
            $wrapper->appendChild($dom->createTextNode(self::N_TAB4.self::PHP_OPEN_TAG.'if($userPermission->isAllowedUpdate()){ '.self::PHP_CLOSE_TAG)); // NOSONAR 
            $activate = $dom->createElement('button');
            $activate->setAttribute('type', 'submit');
            $activate->setAttribute('class', ElementClass::BUTTON_SUCCESS);
            $activate->setAttribute('name', 'user_action');
            $activate->setAttribute('id', 'activate_selected');
            $activate->setAttribute('value', 'activate');
            $activate->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonActivate();'.self::PHP_CLOSE_TAG));

            $deactivate = $dom->createElement('button');
            $deactivate->setAttribute('type', 'submit');
            $deactivate->setAttribute('class', ElementClass::BUTTON_WARNING); // NOSONAR
            $deactivate->setAttribute('name', 'user_action');
            $deactivate->setAttribute('id', 'deactivate_selected');
            $deactivate->setAttribute('value', 'deactivate');
            $deactivate->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonDeactivate();'.self::PHP_CLOSE_TAG));

            
            $wrapper->appendChild($dom->createTextNode(self::N_TAB4)); 
            $wrapper->appendChild($activate);
            $wrapper->appendChild($dom->createTextNode(self::N_TAB4)); 
            $wrapper->appendChild($deactivate);
            $wrapper->appendChild($dom->createTextNode(self::N_TAB4.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG));
        }

        $wrapper->appendChild($dom->createTextNode(self::N_TAB4.self::PHP_OPEN_TAG.'if($userPermission->isAllowedDelete()){ '.self::PHP_CLOSE_TAG));
        $delete = $dom->createElement('button');
        $delete->setAttribute('type', 'submit');
        $delete->setAttribute('class', 'btn btn-danger');
        $delete->setAttribute('name', 'user_action');
        $delete->setAttribute('id', 'delete_selected');
        $delete->setAttribute('value', 'delete');
        $delete->setAttribute('data-onclik-message', self::PHP_OPEN_TAG.'echo htmlspecialchars($appLanguage->getWarningDeleteConfirmation());'.self::PHP_CLOSE_TAG);
        $delete->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonDelete();'.self::PHP_CLOSE_TAG));

        $wrapper->appendChild($dom->createTextNode(self::N_TAB4)); 
        $wrapper->appendChild($delete);
        $wrapper->appendChild($dom->createTextNode(self::N_TAB4.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG));

        if($sortOrder)
        {
            $wrapper->appendChild($dom->createTextNode(self::N_TAB4.self::PHP_OPEN_TAG.'if($userPermission->isAllowedSortOrder()){ '.self::PHP_CLOSE_TAG)); // NOSONAR
            $order = $dom->createElement('button');
            $order->setAttribute('type', 'submit');
            $order->setAttribute('class', ElementClass::BUTTON_PRIMARY);
            $order->setAttribute('name', 'user_action');
            $order->setAttribute('id', 'save_current_order');
            $order->setAttribute('value', 'sort_order');
            $order->setAttribute('disabled', 'disabled');
            $order->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonSaveCurrentOrder();'.self::PHP_CLOSE_TAG));

            $wrapper->appendChild($dom->createTextNode(self::N_TAB4)); 
            $wrapper->appendChild($order);
            $wrapper->appendChild($dom->createTextNode(self::N_TAB4.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG));
        }
        
        $wrapper->appendChild($dom->createTextNode(self::N_TAB3)); 
        return $wrapper;
    }
    
    /**
     * Create a data table list with headers and rows.
     *
     * @param DOMDocument $dom The DOM document for generating HTML.
     * @param AppField[] $listFields An array of fields to be displayed in the table.
     * @param string $objectName The name of the object being listed.
     * @param string $primaryKey The primary key for the object.
     * @param boolean $approvalRequired Indicates if approval is required.
     * @return DOMElement The generated table element containing the data list.
     */
    public function createDataTableList($dom, $listFields, $objectName, $primaryKey, $approvalRequired = false)
    {
        $table = $dom->createElement('table');
        $table->setAttribute('class', 'table table-row table-sort-by-column');
        
        
        $thead = $this->createTableListHead($dom, $listFields, $primaryKey, $approvalRequired);
        $tbody = $this->createTableListBody($dom, $listFields, $objectName, $primaryKey, $approvalRequired);
        
        $table->appendChild($dom->createTextNode(self::N_TAB4)); 
        $table->appendChild($thead);
        $table->appendChild($dom->createTextNode(self::N_TAB3)); 
        
        $table->appendChild($dom->createTextNode(self::N_TAB4)); 
        $table->appendChild($tbody);
        $table->appendChild($dom->createTextNode(self::N_TAB3)); 
        
        return $table;
    }
    
    /**
     * Creates the table header for a list of items.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param AppField[] $listFields An array of fields to include in the header.
     * @param string $objectName The name of the object for which the table is being created.
     * @param string $primaryKey The primary key of the object.
     * @param boolean $approvalRequired Indicates if approval actions are required.
     * 
     * @return DOMElement The created table header element (thead).
     */
    public function createTableListHead($dom, $listFields, $primaryKey, $approvalRequired = false)
    {
        
        $thead = $dom->createElement('thead');
        
        $trh = $dom->createElement('tr');
        
        if($this->appFeatures->isSortOrder())
        {
            // sort-control begin
            $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedSortOrder()){ '.self::PHP_CLOSE_TAG)); 
            $td = $dom->createElement('td');
            $td->setAttribute('class', 'data-sort data-sort-header');
            $td->appendChild($dom->createTextNode(""));
            $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
            $trh->appendChild($td);
            $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
            // sort-control end
        }

        // checkbox begin
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedBatchAction()){ '.self::PHP_CLOSE_TAG)); 
        $td = $dom->createElement('td');
        $td->setAttribute('class', 'data-controll data-selector');
        $td->setAttribute('data-key', $primaryKey);
        $selector = '.checkbox-'.strtolower(str_replace('_', '-', $primaryKey));
        
        $chekcbox = $dom->createElement('input');
        $chekcbox->setAttribute('type', 'checkbox');
        $chekcbox->setAttribute('class', 'checkbox check-master');
        $chekcbox->setAttribute('data-selector', $selector);
        
        $td->appendChild($dom->createTextNode(self::N_TAB7)); 
        $td->appendChild($chekcbox);
        $td->appendChild($dom->createTextNode(self::N_TAB6)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        // checkbox end
        
        // edit begin
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedUpdate()){ '.self::PHP_CLOSE_TAG)); 
        $spanEdit = $dom->createElement('span');
        $spanEdit->setAttribute('class', 'fa fa-edit');
        $spanEdit->appendChild($dom->createTextNode(''));
        
        $td2 = $dom->createElement('td');
        $td2->setAttribute('class', 'data-controll data-editor');
        
        $td2->appendChild($dom->createTextNode(self::N_TAB7)); 
        $td2->appendChild($spanEdit);
        $td2->appendChild($dom->createTextNode(self::N_TAB6)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td2);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        // edit end
        
        // detail begin
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedDetail()){ '.self::PHP_CLOSE_TAG)); // NOSONAR
        $spanDetail = $dom->createElement('span');
        $spanDetail->setAttribute('class', 'fa fa-folder');
        $spanDetail->appendChild($dom->createTextNode(''));
        
        $td2 = $dom->createElement('td');
        $td2->setAttribute('class', 'data-controll data-viewer');
        
        $td2->appendChild($dom->createTextNode(self::N_TAB7)); 
        $td2->appendChild($spanDetail);
        $td2->appendChild($dom->createTextNode(self::N_TAB6)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td2);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        // detail end
        
        // approval begin
        if($approvalRequired && $this->appFeatures->getApprovalPosition() == AppFeatures::BEFORE_DATA)
        {
            $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedApprove()){ '.self::PHP_CLOSE_TAG)); // NOSONAR
            $td3 = $dom->createElement('td');
            $td3->setAttribute('class', 'data-controll data-approval'); // NOSONAR
            $td3->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getApproval();'.self::PHP_CLOSE_TAG)); 
            
            $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
            $trh->appendChild($td3);
            $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        }
        // approval end

        // no begin
        $td2 = $dom->createElement('td');
        $td2->setAttribute('class', 'data-controll data-number');
        $td2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getNumero();'.self::PHP_CLOSE_TAG)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td2);
        // no end  
        
        foreach($listFields as $field)
        {
            $td = $dom->createElement('td');
            $td->setAttribute('data-col-name', $field->getFieldName());
            $td->setAttribute('class', 'order-controll');
            $a = $dom->createElement('a');
            $a->setAttribute('href', '#');

            $caption = PicoStringUtil::upperCamelize($field->getFieldName());
            if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::ENTITY
            && $field->getReferenceData()->getEntity() != null
            && $field->getReferenceData()->getEntity()->getObjectName() != null
            && $field->getReferenceData()->getEntity()->getPropertyName() != null
            && PicoStringUtil::endsWith($field->getFieldName(), "_id")
            )
            {
                $caption = PicoStringUtil::upperCamelize(substr($field->getFieldName(), 0, strlen($field->getFieldName()) - 3));
            }

            $a->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.self::ECHO.self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$caption.self::BRACKETS.";".self::PHP_CLOSE_TAG)); 
            $td->appendChild($a);
            $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
            $trh->appendChild($td);
        }
        
        // approval begin
        if($approvalRequired && $this->appFeatures->getApprovalPosition() == AppFeatures::AFTER_DATA)
        {
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedApprove()){ '.self::PHP_CLOSE_TAG)); 
        $td3 = $dom->createElement('td');
        $td3->setAttribute('class', 'data-controll data-approval');
        $td3->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getApproval();'.self::PHP_CLOSE_TAG)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td3);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        }
        // approval end
        
        $trh->appendChild($dom->createTextNode(self::N_TAB5)); 
        
        $thead->appendChild($dom->createTextNode(self::N_TAB5)); 
        $thead->appendChild($trh);
        $thead->appendChild($dom->createTextNode(self::N_TAB4)); 
        
        return $thead;
    }

    /**
     * Creates an approve button element.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param string $objectName The name of the object for which the button is being created.
     * @param string $primaryKey The primary key of the object.
     * @param string $upperPkName The upper camel case name of the primary key.
     * 
     * @return DOMElement The created approve button element (a).
     */
    private function createButtonApprove($dom, $objectName, $primaryKey, $upperPkName)
    {
        $buttonApprove = $dom->createElement('a');
        $buttonApprove->setAttribute('class', 'btn btn-tn btn-success');

        $approvalType = $this->appFeatures->getApprovalType();
        if($approvalType == 2)
        {
            $buttonApprove->setAttribute('href', self::PHP_OPEN_TAG.'echo $currentModule->getRedirectUrl(UserAction::DETAIL, '.AppBuilderBase::getStringOf($primaryKey).', '.self::VAR.$objectName.self::CALL_GET.$upperPkName.self::BRACKETS.', array(UserAction::NEXT_ACTION => UserAction::APPROVAL));'.self::PHP_CLOSE_TAG); // NOSONAR
        }
        else
        {
            $buttonApprove->setAttribute('href', self::PHP_OPEN_TAG.'echo $currentModule->getRedirectUrl(UserAction::DETAIL, '.AppBuilderBase::getStringOf($primaryKey).', '.self::VAR.$objectName.self::CALL_GET.$upperPkName.self::BRACKETS.', array(UserAction::NEXT_ACTION => UserAction::APPROVE));'.self::PHP_CLOSE_TAG);
        }
        $buttonApprove->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonApproveTiny();'.self::PHP_CLOSE_TAG)); 
        return $buttonApprove;
    }

    /**
     * Creates a reject button element.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param string $objectName The name of the object for which the button is being created.
     * @param string $primaryKey The primary key of the object.
     * @param string $upperPkName The upper camel case name of the primary key.
     * 
     * @return DOMElement The created reject button element (a or text node).
     */
    private function createButtonReject($dom, $objectName, $primaryKey, $upperPkName)
    {
        $approvalType = $this->appFeatures->getApprovalType();
        if($approvalType == 2)
        {
            $buttonReject = $dom->createTextNode(self::PHP_OPEN_TAG.'echo UserAction::getWaitingForText($appLanguage, $'.$objectName.'->getWaitingFor());'.self::PHP_CLOSE_TAG);
        }
        else
        {
            $buttonReject = $dom->createElement('a');
            $buttonReject->setAttribute('class', 'btn btn-tn btn-warning');
            $buttonReject->setAttribute('href', self::PHP_OPEN_TAG.'echo $currentModule->getRedirectUrl(UserAction::DETAIL, '.AppBuilderBase::getStringOf($primaryKey).', '.self::VAR.$objectName.self::CALL_GET.$upperPkName.self::BRACKETS.', array(UserAction::NEXT_ACTION => UserAction::REJECT));'.self::PHP_CLOSE_TAG);
            $buttonReject->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonRejectTiny();'.self::PHP_CLOSE_TAG)); 
        }
        return $buttonReject;
    }
    
    /**
     * Creates the table body for a list of items.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param AppField[] $listFields An array of fields to include in the body.
     * @param string $objectName The name of the object for which the table is being created.
     * @param string $primaryKey The primary key of the object.
     * @param boolean $approvalRequired Indicates if approval actions are required.
     * 
     * @return DOMElement The created table body element (tbody).
     */
    public function createTableListBody($dom, $listFields, $objectName, $primaryKey, $approvalRequired = false)
    {
        
        $tbody = $dom->createElement('tbody');
        if($this->appFeatures->isSortOrder())
        {
            $tbody->setAttribute('class', 'data-table-manual-sort');
        }
        $trh = $dom->createElement('tr');
        
        if($this->appFeatures->isSortOrder())
        {
            // sort-control begin
            $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedSortOrder()){ '.self::PHP_CLOSE_TAG)); 
            $td = $dom->createElement('td');
            $td->setAttribute('class', 'data-sort data-sort-body data-sort-handler');
            $td->appendChild($dom->createTextNode(""));
            $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
            $trh->appendChild($td);
            
            $trh->setAttribute("data-primary-key", self::PHP_OPEN_TAG.'echo $'.$objectName.self::CALL_GET.PicoStringUtil::upperCamelize($primaryKey).self::BRACKETS.';'.self::PHP_CLOSE_TAG); // NOSONAR
            $trh->setAttribute("data-sort-order", self::PHP_OPEN_TAG.'echo $'.$objectName.self::CALL_GET.PicoStringUtil::upperCamelize($this->entityInfo->getSortOrder()).self::BRACKETS.';'.self::PHP_CLOSE_TAG); // NOSONAR
            $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
            // sort-control end
        }
        
        // checkbox begin
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedBatchAction()){ '.self::PHP_CLOSE_TAG)); 
        $td = $dom->createElement('td');
        $td->setAttribute('class', 'data-selector');
        $td->setAttribute('data-key', $primaryKey);
        $upperPkName = PicoStringUtil::upperCamelize($primaryKey);
        $className = 'checkbox check-slave checkbox-'.strtolower(str_replace('_', '-', $primaryKey));
        
        $chekcbox = $dom->createElement('input');
        $chekcbox->setAttribute('type', 'checkbox');
        $chekcbox->setAttribute('class', $className);
        $chekcbox->setAttribute('name', 'checked_row_id[]');
        $chekcbox->setAttribute('value', self::PHP_OPEN_TAG.self::ECHO.self::VAR.$objectName.self::CALL_GET.$upperPkName.self::BRACKETS.";".self::PHP_CLOSE_TAG);
        
        $td->appendChild($dom->createTextNode(self::N_TAB7)); 
        $td->appendChild($chekcbox);
        $td->appendChild($dom->createTextNode(self::N_TAB6)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        // checkbox end
        
        // edit begin
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedUpdate()){ '.self::PHP_CLOSE_TAG)); 
        $edit = $dom->createElement('a');
        $edit->setAttribute('class', 'edit-control');
        $href = self::PHP_OPEN_TAG.'echo $currentModule->getRedirectUrl(UserAction::UPDATE, '.AppBuilderBase::getStringOf($primaryKey).', '.self::VAR.$objectName.self::CALL_GET.$upperPkName.self::BRACKETS.');'.self::PHP_CLOSE_TAG;
        $edit->setAttribute('href', $href);
        $spanEdit = $dom->createElement('span');
        $spanEdit->setAttribute('class', 'fa fa-edit');
        $spanEdit->appendChild($dom->createTextNode(''));
        $edit->appendChild($spanEdit);
        
        $td2 = $dom->createElement('td');
        
        $td2->appendChild($dom->createTextNode(self::N_TAB7)); 
        $td2->appendChild($edit);
        $td2->appendChild($dom->createTextNode(self::N_TAB6)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td2);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        // edit end
        
        // detail begin
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedDetail()){ '.self::PHP_CLOSE_TAG)); 
        $detail = $dom->createElement('a');
        $detail->setAttribute('class', 'detail-control field-master');
        $href = self::PHP_OPEN_TAG.'echo $currentModule->getRedirectUrl(UserAction::DETAIL, '.AppBuilderBase::getStringOf($primaryKey).', '.self::VAR.$objectName.self::CALL_GET.$upperPkName.self::BRACKETS.');'.self::PHP_CLOSE_TAG;
        $detail->setAttribute('href', $href);
        $spanDetail = $dom->createElement('span');
        $spanDetail->setAttribute('class', 'fa fa-folder');
        $spanDetail->appendChild($dom->createTextNode(''));
        $detail->appendChild($spanDetail);
        
        $td2 = $dom->createElement('td');
        
        $td2->appendChild($dom->createTextNode(self::N_TAB7)); 
        $td2->appendChild($detail);
        $td2->appendChild($dom->createTextNode(self::N_TAB6)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td2);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG));
        // detail end
        
        // approval begin
        if($approvalRequired && $this->appFeatures->getApprovalPosition() == AppFeatures::BEFORE_DATA)
        {
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedApprove()){ '.self::PHP_CLOSE_TAG)); 
        $td3 = $dom->createElement('td');
        $td3->setAttribute('class', 'data-controll data-approval');
        
        $buttonApprove = $this->createButtonApprove($dom, $objectName, $primaryKey, $upperPkName);
        $buttonReject = $this->createButtonReject($dom, $objectName, $primaryKey, $upperPkName);
        
        
        $td3->appendChild($dom->createTextNode(self::N_TAB7.self::PHP_OPEN_TAG.'if(UserAction::isRequireApproval($'.$objectName.'->getWaitingFor())){ '.self::PHP_CLOSE_TAG)); 
        $td3->appendChild($dom->createTextNode(self::N_TAB7)); 
        $td3->appendChild($buttonApprove); 
        $td3->appendChild($dom->createTextNode(self::N_TAB7));  
        $td3->appendChild($buttonReject); 
        $td3->appendChild($dom->createTextNode(self::N_TAB7.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        $td3->appendChild($dom->createTextNode(self::N_TAB6));  
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td3);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        }
        // approval end

        // no begin
        $td2 = $dom->createElement('td');
        $td2->setAttribute('class', 'data-number');
        $td2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $pageData->getDataOffset() + $dataIndex;'.self::PHP_CLOSE_TAG)); 
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td2);
        // no end
        
        
        foreach($listFields as $field)
        {
            $td = $dom->createElement('td');
            $td->setAttribute('data-col-name', $field->getFieldName());
            if($this->appFeatures->isSortOrder() && PicoStringUtil::camelize($this->entityInfo->getSortOrder()) == PicoStringUtil::camelize($field->getFieldName()))
            {
                $td->setAttribute('class', 'data-sort-order-column');
            }            
            $value = $this->createDetailValue($dom, $objectName, $field);          
            $td->appendChild($value);           
            $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
            $trh->appendChild($td);
        }
        
        // approval begin
        if($approvalRequired && $this->appFeatures->getApprovalPosition() == AppFeatures::AFTER_DATA)
        {
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'if($userPermission->isAllowedApprove()){ '.self::PHP_CLOSE_TAG)); 
        $td3 = $dom->createElement('td');
        $td3->setAttribute('class', 'data-controll data-approval');
        
        $buttonApprove = $this->createButtonApprove($dom, $objectName, $primaryKey, $upperPkName);
        $buttonReject = $this->createButtonReject($dom, $objectName, $primaryKey, $upperPkName);
        
        $td3->appendChild($dom->createTextNode(self::N_TAB7.self::PHP_OPEN_TAG.'if(UserAction::isRequireApproval($'.$objectName.'->getWaitingFor())){ '.self::PHP_CLOSE_TAG)); 
        $td3->appendChild($dom->createTextNode(self::N_TAB7)); 
        $td3->appendChild($buttonApprove); 
        $td3->appendChild($dom->createTextNode(self::N_TAB7));  
        $td3->appendChild($buttonReject); 
        $td3->appendChild($dom->createTextNode(self::N_TAB7.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        $td3->appendChild($dom->createTextNode(self::N_TAB6));  
        
        $trh->appendChild($dom->createTextNode(self::N_TAB6)); 
        $trh->appendChild($td3);
        $trh->appendChild($dom->createTextNode(self::N_TAB6.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG)); 
        }
        // approval end
        
        $trh->appendChild($dom->createTextNode(self::N_TAB5)); 
        
        $tbody->appendChild($dom->createTextNode(self::N_TAB5)); 
        $tbody->appendChild($dom->createTextNode(self::PHP_OPEN_TAG));
        $tbody->appendChild($dom->createTextNode(self::N_TAB5)); 
        $tbody->appendChild($dom->createTextNode("\$dataIndex = 0;")); 
        $tbody->appendChild($dom->createTextNode(self::N_TAB5."while(\$".$objectName." = \$pageData->fetch())")); 
        $tbody->appendChild($dom->createTextNode(self::N_TAB5.self::CURLY_BRACKET_OPEN)); 
        $tbody->appendChild($dom->createTextNode(self::N_TAB6."\$dataIndex++;")); 
        $tbody->appendChild($dom->createTextNode(self::N_TAB5)); 

        $tbody->appendChild($dom->createTextNode(self::PHP_CLOSE_TAG));  
        
        $tbody->appendChild($dom->createTextNode("\n\n\t\t\t\t\t")); 

        $trh->setAttribute('data-number', self::PHP_OPEN_TAG.'echo $pageData->getDataOffset() + $dataIndex;'.self::PHP_CLOSE_TAG); 

        if($this->appFeatures->isActivateDeactivate())
        {
            $trh->setAttribute('data-active', self::PHP_OPEN_TAG.'echo $'.$objectName.'->option'.ucfirst($this->entityInfo->getActive())."('true', 'false');".self::PHP_CLOSE_TAG);
        }

        $tbody->setAttribute('data-offset', self::PHP_OPEN_TAG.'echo $pageData->getDataOffset();'.self::PHP_CLOSE_TAG); 

        
        $tbody->appendChild($trh);
        $tbody->appendChild($dom->createTextNode(self::N_TAB5)); 
        
        $tbody->appendChild($dom->createTextNode(self::PHP_OPEN_TAG));
        $tbody->appendChild($dom->createTextNode(self::N_TAB5.self::CURLY_BRACKET_CLOSE)); 
        $tbody->appendChild($dom->createTextNode(self::N_TAB5)); 
        $tbody->appendChild($dom->createTextNode(self::PHP_CLOSE_TAG));  
        $tbody->appendChild($dom->createTextNode("\n\n\t\t\t\t")); 
        
        
        return $tbody;
    }

    /**
     * Creates a filter form with various filter elements.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param AppField[] $filterFields An array of fields to be included in the filter.
     * 
     * @return DOMElement The created filter form element (form).
     */
    public function createFilterForm($dom, $filterFields)
    {
        $form = $dom->createElement('form');
        $form->setAttribute('action', '');
        $form->setAttribute('method', 'get');
        $form->setAttribute('class', 'filter-form');
        
        $form = $this->appendFilter($dom, $form, $filterFields);    
        
        $submitWrapper = $dom->createElement('span');
        $submitWrapper->setAttribute('class', 'filter-group');
        $buttonSearch = $dom->createElement('button');
        $buttonSearch->setAttribute('type', 'submit');
        $buttonSearch->setAttribute('class', ElementClass::BUTTON_SUCCESS);
        $buttonSearch->setAttribute('id', 'show_data');
        $buttonSearch->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage".self::CALL_GET."ButtonSearch();".self::PHP_CLOSE_TAG));

        $submitWrapper->appendChild($dom->createTextNode(self::N_TAB3));
        $submitWrapper->appendChild($buttonSearch);
        $submitWrapper->appendChild($dom->createTextNode(self::N_TAB2));    
        
        $addWrapper = $dom->createElement('span');
        $addWrapper->setAttribute('class', 'filter-group');    
        $buttonSearch = $dom->createElement('button');
        $buttonSearch->setAttribute('type', 'button');
        $buttonSearch->setAttribute('class', ElementClass::BUTTON_PRIMARY);
        $buttonSearch->setAttribute('id', 'add_data');
        $buttonSearch->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage".self::CALL_GET."ButtonAdd();".self::PHP_CLOSE_TAG));
        $buttonSearch->setAttribute('onclick', "window.location='".self::PHP_OPEN_TAG.self::ECHO.self::VAR."currentModule".self::CALL_GET."RedirectUrl(UserAction::CREATE);".self::PHP_CLOSE_TAG."'");       
        
        $addWrapper->appendChild($dom->createTextNode(self::N_TAB3)); 
        $addWrapper->appendChild($buttonSearch); 
        $addWrapper->appendChild($dom->createTextNode(self::N_TAB2));

        ////
        $approvalFilterWrapper = $dom->createElement('span');
        $approvalFilterWrapper->setAttribute('class', 'filter-group');    
        $buttonSearch = $dom->createElement('button');
        $buttonSearch->setAttribute('type', 'submit');
        $buttonSearch->setAttribute('name', 'show_require_approval_only');
        $buttonSearch->setAttribute('id', 'show_require_approval_data');
        $buttonSearch->setAttribute('value', 'true');
        $buttonSearch->setAttribute('class', ElementClass::BUTTON_SUCCESS);
        $buttonSearch->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage".self::CALL_GET."ButtonShowRequireApproval();".self::PHP_CLOSE_TAG));
              
        $approvalFilterWrapper->appendChild($dom->createTextNode(self::N_TAB3));  
        $approvalFilterWrapper->appendChild($buttonSearch);
        $approvalFilterWrapper->appendChild($dom->createTextNode(self::N_TAB2));
        ////
        
        ////
        $exportFilterWrapper = $dom->createElement('span');
        $exportFilterWrapper->setAttribute('class', 'filter-group');    
        $buttonExport = $dom->createElement('button');
        $buttonExport->setAttribute('type', 'submit');
        $buttonExport->setAttribute('name', 'user_action');
        $buttonExport->setAttribute('id', 'export_data');
        $buttonExport->setAttribute('value', 'export');
        $buttonExport->setAttribute('class', ElementClass::BUTTON_SUCCESS);
        $buttonExport->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage".self::CALL_GET."ButtonExport();".self::PHP_CLOSE_TAG));
              
        $exportFilterWrapper->appendChild($dom->createTextNode(self::N_TAB3));  
        $exportFilterWrapper->appendChild($buttonExport);
        $exportFilterWrapper->appendChild($dom->createTextNode(self::N_TAB2));
        ////
        
        $form->appendChild($dom->createTextNode(self::N_TAB2));
        
        $form->appendChild($submitWrapper);

        if($this->appFeatures->isApprovalRequired())
        {
            $form->appendChild($dom->createTextNode(self::N_TAB2.self::PHP_OPEN_TAG.'if($userPermission->isAllowedApprove()){ '.self::PHP_CLOSE_TAG));
            $form->appendChild($dom->createTextNode(self::NN_TAB2));
            $form->appendChild($approvalFilterWrapper);
            $form->appendChild($dom->createTextNode(self::N_TAB2.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG));
        }

        if($this->appFeatures->isExportToExcel() || $this->appFeatures->isExportToCsv())
        {
            $form->appendChild($dom->createTextNode(self::N_TAB2.self::PHP_OPEN_TAG.'if($userPermission->isAllowedExport()){ '.self::PHP_CLOSE_TAG));
            $form->appendChild($dom->createTextNode(self::NN_TAB2));
            $form->appendChild($exportFilterWrapper);
            $form->appendChild($dom->createTextNode(self::N_TAB2.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG));
        }

        $form->appendChild($dom->createTextNode(self::N_TAB2.self::PHP_OPEN_TAG.'if($userPermission->isAllowedCreate()){ '.self::PHP_CLOSE_TAG));
        $form->appendChild($dom->createTextNode(self::NN_TAB2));
        $form->appendChild($addWrapper);
        $form->appendChild($dom->createTextNode(self::N_TAB2.self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG));

        $form->appendChild($dom->createTextNode("\n\t"));

        return $form;
    }

    /**
     * Fixes the field name by converting it to upper camel case if certain conditions are met.
     *
     * This method checks if the provided field is a 'select' type with reference data of type 'entity'.
     * It also ensures that the entity, its object name, and property name are not null. If the field name
     * ends with "_id", it removes the "_id" suffix and converts the remaining portion of the field name to 
     * upper camel case. If the conditions are not met, it returns the original upper field name.
     *
     * @param string $upperFieldName The original upper camel case field name.
     * @param object $field The field object containing the relevant data for processing.
     * @return string The modified field name in upper camel case if conditions are met, otherwise the original field name.
     */
    protected function fixFieldName($upperFieldName, $field)
    {
        if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::ENTITY
            && $field->getReferenceData()->getEntity() != null
            && $field->getReferenceData()->getEntity()->getObjectName() != null
            && $field->getReferenceData()->getEntity()->getPropertyName() != null
            && PicoStringUtil::endsWith($field->getFieldName(), "_id")
            )
        {
            return PicoStringUtil::upperCamelize(substr($field->getFieldName(), 0, strlen($field->getFieldName()) - 3));
        }
        return $upperFieldName;
    }
    
    /**
     * Appends filter elements to the given form.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param DOMElement $form The form element to which filters will be appended.
     * @param AppField[] $filterFields An array of fields for filtering.
     * 
     * @return DOMElement The updated form element with appended filters.
     */
    public function appendFilter($dom, $form, $filterFields) // NOSONAR
    {
        
        foreach($filterFields as $field)
        {
            $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
            $upperFieldName = $this->fixFieldName($upperFieldName, $field);
            $multipleFilter = $field->getMultipleFilter();

            $labelStr = self::PHP_OPEN_TAG.self::ECHO.self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$upperFieldName.self::BRACKETS.";".self::PHP_CLOSE_TAG;
            $label = $dom->createTextNode($labelStr);
            
            $labelWrapper = $dom->createElement('span');
            $labelWrapper->setAttribute('class', 'filter-label');
            $labelWrapper->appendChild($label);
                
            if($field->getFilterElementType() == InputType::TEXT)
            {
                $form->appendChild($dom->createTextNode(self::N_TAB2));
                
                $filterGroup = $dom->createElement('span');
                $filterGroup->setAttribute('class', 'filter-group');

                $input = $dom->createElement('input');
                $this->setInputTypeAttribute($input, $field->getDataType(), true);

                if($multipleFilter)
                {
                    $input->setAttribute('name', $field->getFieldName().'[]');
                }
                else
                {
                    $input->setAttribute('name', $field->getFieldName());
                }
                
                
                $fieldName = PicoStringUtil::upperCamelize($field->getFieldName());
                $dataType = $field->getDataType();
                if($multipleFilter)
                {
                    if($dataType == 'password' || self::isInputFile($dataType))
                    {
                        // do nothing
                        $input->setAttribute('multiple', 'multiple');
                    }
                    else
                    {
                        $input->setAttribute('placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getTypeHere();'.self::PHP_CLOSE_TAG); // NOSONAR
                        $input->setAttribute('data-initial-value', self::PHP_OPEN_TAG.AppBuilderBase::ECHO.'htmlspecialchars(json_encode('.AppBuilderBase::VAR."inputGet".AppBuilderBase::CALL_GET.$fieldName.self::BRACKETS."));".AppBuilderBase::PHP_CLOSE_TAG);
                    }
                }
                else
                {
                    if($dataType == 'password' || self::isInputFile($dataType))
                    {
                        // do nothing
                    }
                    else
                    {
                        $input->setAttribute('value', AppBuilderBase::PHP_OPEN_TAG.AppBuilderBase::ECHO.AppBuilderBase::VAR."inputGet".AppBuilderBase::CALL_GET.$fieldName.self::BRACKETS.";".AppBuilderBase::PHP_CLOSE_TAG);
                    }
                }
                if(!self::isInputFile($dataType))
                {
                    $input->setAttribute('autocomplete', 'off'); 
                }
                if($multipleFilter &&  !($dataType == 'password' || self::isInputFile($dataType)))
                {
                    $input->setAttribute('data-multi-input', 'true');
                }
                $filterGroup->appendChild($dom->createTextNode(self::N_TAB3));
                
                $filterGroup->appendChild($labelWrapper);
                
                $filterGroup->appendChild($dom->createTextNode(self::N_TAB3));
                
                $inputWrapper = $dom->createElement('span');
                $inputWrapper->setAttribute('class', 'filter-control');
                $inputWrapper->appendChild($dom->createTextNode(self::N_TAB4));
                $inputWrapper->appendChild($input);
                $inputWrapper->appendChild($dom->createTextNode(self::N_TAB3));
                
                $filterGroup->appendChild($inputWrapper);
                
                
                $filterGroup->appendChild($dom->createTextNode(self::N_TAB2));
                
                $form->appendChild($filterGroup);
                
                $form->appendChild($dom->createTextNode(self::N_TAB2));
                
                
            }
            else if($field->getFilterElementType() == InputType::SELECT)
            {
                $referenceFilter = $field->getReferenceFilter();

                
                $form->appendChild($dom->createTextNode(self::N_TAB2));
                
                $filterGroup = $dom->createElement('span');
                $filterGroup->setAttribute('class', 'filter-group');

                
                $select = $dom->createElement('select');
                $select->setAttribute('class', 'form-control');
                
                if($multipleFilter)
                {
                    $select->setAttribute('name', $field->getFieldName().'[]');
                    
                    $select->setAttribute('data-placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getSelectItems();'.self::PHP_CLOSE_TAG); // NOSONAR
                    $select->setAttribute('data-search-placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getPlaceholderSearch();'.self::PHP_CLOSE_TAG); // NOSONAR
                    $select->setAttribute('data-label-selected', self::PHP_OPEN_TAG.'echo $appLanguage->getLabelSelected();'.self::PHP_CLOSE_TAG); // NOSONAR
                    $select->setAttribute('data-label-select-all', self::PHP_OPEN_TAG.'echo $appLanguage->getLabelSelectAll();'.self::PHP_CLOSE_TAG); // NOSONAR
                    $select->setAttributeNode($dom->createAttribute('multiple'));
                    $select->setAttributeNode($dom->createAttribute('data-multi-select'));
                }
                else
                {
                    $select->setAttribute('name', $field->getFieldName());
                }
                // class="form-control" data-placeholder="$appLanguage->getSelectItems()" multiple multi-select
                                
                $inputGetName = PicoStringUtil::upperCamelize($field->getFieldName());

                

                $value = $dom->createElement('option');
                
                $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage->getLabelOptionSelectOne();".self::PHP_CLOSE_TAG; // NOSONAR
                $textLabel = $dom->createTextNode($caption);
                $value->appendChild($textLabel);

                $value->setAttribute('value', '');
                $select->appendChild($dom->createTextNode(self::N_TAB6));
                if(!$multipleFilter)
                {
                    $select->appendChild($value);
                }

                $select = $this->appendOption($dom, $select, $referenceFilter, self::VAR."inputGet".self::CALL_GET.$inputGetName.self::BRACKETS."");

                $filterGroup->appendChild($dom->createTextNode(self::N_TAB3));               
                $filterGroup->appendChild($labelWrapper);               
                $filterGroup->appendChild($dom->createTextNode(self::N_TAB3));

                $inputWrapper = $dom->createElement('span');
                $inputWrapper->setAttribute('class', 'filter-control');
                $inputWrapper->appendChild($dom->createTextNode(self::N_TAB5));
                $inputWrapper->appendChild($select);
                $inputWrapper->appendChild($dom->createTextNode(self::N_TAB3));
                
                $filterGroup->appendChild($inputWrapper);
                $filterGroup->appendChild($dom->createTextNode(self::N_TAB2)); 

                $form->appendChild($filterGroup);
                
                $form->appendChild($dom->createTextNode(self::N_TAB2));
            }  
        }
        return $form;
    }
    
    /**
     * Retrieves the object name derived from the field name.
     *
     * @param string $fieldName The field name to process.
     * 
     * @return string The corresponding object name.
     */
        public function getObjectNameFromFieldName($fieldName)
    {
        if(PicoStringUtil::endsWith($fieldName, "_id"))
        {
            return PicoStringUtil::camelize(substr($fieldName, 0, strlen($fieldName) - 3));
        }
        else
        {
            return PicoStringUtil::camelize($fieldName);
        }
    }

    /**
     * Creates a responsive insert form table for the specified fields.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param MagicObject $mainEntity The main entity object for the form.
     * @param string $objectName The name of the object being inserted.
     * @param AppField[] $fields An array of fields to include in the form.
     * @param string $primaryKeyName The name of the primary key for the object.
     * 
     * @return DOMElement The created insert form table element (table).
     */
    private function createInsertFormTable($dom, $mainEntity, $objectName, $fields, $primaryKeyName)
    {
        $table = $this->createElementTableResponsive($dom);
        $tbody = $dom->createElement('tbody');
        foreach($fields as $field)
        {
            if($field->getIncludeInsert())
            {
                $tr = $this->createInsertRow($dom, $mainEntity, $objectName, $field, $primaryKeyName);
                $tbody->appendChild($tr);
            }
        }
        $table->appendChild($tbody);
        return $table;
    }
    
    /**
     * Creates a responsive update form table for the specified fields.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param MagicObject $mainEntity The main entity object for the form.
     * @param string $objectName The name of the object being updated.
     * @param AppField[] $fields An array of fields to include in the form.
     * @param string $primaryKeyName The name of the primary key for the object.
     * 
     * @return DOMElement The created update form table element (table).
     */
    private function createUpdateFormTable($dom, $mainEntity, $objectName, $fields, $primaryKeyName)
    {
        $table = $this->createElementTableResponsive($dom);
        $tbody = $dom->createElement('tbody');
        foreach($fields as $field)
        {
            if($field->getIncludeEdit())
            {
                $tr = $this->createUpdateRow($dom, $mainEntity, $objectName, $field, $primaryKeyName);
                $tbody->appendChild($tr);
            }
        }
        $table->appendChild($tbody);
        return $table;
    }
    
    /**
     * Creates a responsive detail table for the specified fields.
     *
     * @param DOMDocument $dom The DOM document to create elements in.
     * @param string $objectName The name of the object being detailed.
     * @param AppField[] $fields An array of fields to include in the detail view.
     * 
     * @return DOMElement The created detail table element (table).
     */
    private function createDetailTable($dom, $objectName, $fields)
    {
        $table = $this->createElementTableResponsive($dom);
        $tbody = $dom->createElement('tbody');
        foreach($fields as $field)
        {
            if($field->getIncludeDetail())
            {
                $tr = $this->createDetailRow($dom, $objectName, $field);
                $tbody->appendChild($tr);
            }
        }
        $table->appendChild($tbody);
        return $table;
    }

    /**
     * Create a detailed comparison table for two entities.
     *
     * @param DOMDocument $dom             The DOM document to append the table to.
     * @param string $objectName           The name of the main object.
     * @param AppField[] $fields           The fields to include in the comparison.
     * @param string $primaryKeyName       The name of the primary key field.
     * @param MagicObject $approvalEntity   The approval entity for comparison.
     * @param string $objectApprovalName    The name of the approval object.
     * @return DOMElement                  The generated comparison table element.
     */
    private function createDetailTableCompare($dom, $mainEntity, $objectName, $fields, $primaryKeyName, $approvalEntity, $objectApprovalName) // NOSONAR
    {
        $table = $this->createElementTableResponsiveApproval($dom);
        $thead = $dom->createElement('thead');
        $tbody = $dom->createElement('tbody');

        $trh = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');
        $td3 = $dom->createElement('td');

        $td1->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getColumnName();'.self::PHP_CLOSE_TAG));
        $td2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getValueBefore();'.self::PHP_CLOSE_TAG));
        $td3->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getValueAfter();'.self::PHP_CLOSE_TAG));

        $trh->appendChild($td1);
        $trh->appendChild($td2);
        $trh->appendChild($td3);

        $thead->appendChild($trh);

        foreach($fields as $field)
        {
            if($field->getIncludeDetail())
            {
                $tr = $this->createDetailCompareRow($dom, $objectName, $field, $objectApprovalName);
                $tbody->appendChild($tr);
            }
        }
        $table->appendChild($thead);
        $table->appendChild($tbody);
        return $table;
    }

    /**
     * Create a row for inserting a new entity in a form.
     *
     * @param DOMDocument $dom             The DOM document to append the row to.
     * @param MagicObject $mainEntity      The main entity being modified.
     * @param string $objectName           The name of the object.
     * @param AppField $field              The field being inserted.
     * @param string $primaryKeyName       The name of the primary key field.
     * @return DOMElement                  The generated row element.
     */
    private function createInsertRow($dom, $mainEntity, $objectName, $field, $primaryKeyName) // NOSONAR
    {
        $tr = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');

        $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
        $upperFieldName = $this->fixFieldName($upperFieldName, $field);

        $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$upperFieldName.self::BRACKETS.";".self::PHP_CLOSE_TAG;
        $label = $dom->createTextNode($caption);

        $td1->appendChild($label);

        $input = $this->createInsertControl($dom, $field, $field->getFieldName());

        if($input != null)
        {
            $td2->appendChild($input);
        }

        $tr->appendChild($td1);
        $tr->appendChild($td2);

        return $tr;
    }
    
    /**
     * Create a row for updating an existing entity in a form.
     *
     * @param DOMDocument $dom             The DOM document to append the row to.
     * @param MagicObject $mainEntity      The main entity being modified.
     * @param string $objectName           The name of the object.
     * @param AppField $field              The field being updated.
     * @param string $primaryKeyName       The name of the primary key field.
     * @return DOMElement                  The generated row element.
     */
    private function createUpdateRow($dom, $mainEntity, $objectName, $field, $primaryKeyName) // NOSONAR
    {
        $tr = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');

        $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
        $upperFieldName = $this->fixFieldName($upperFieldName, $field);

        $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$upperFieldName.self::BRACKETS.";".self::PHP_CLOSE_TAG;
        $label = $dom->createTextNode($caption);

        $td1->appendChild($label);

        $input = $this->createUpdateControl($dom, $objectName, $field, $primaryKeyName, $field->getFieldName());

        if($input != null)
        {
            $td2->appendChild($input);
        }

        $tr->appendChild($td1);
        $tr->appendChild($td2);

        return $tr;
    }
    
    /**
     * Create a detail row for displaying an entity's field.
     *
     * @param DOMDocument $dom             The DOM document to append the row to.
     * @param string $objectName           The name of the object.
     * @param AppField $field              The field to display.
     * @return DOMElement                  The generated detail row element.
     */
    private function createDetailRow($dom, $objectName, $field)
    {
        $tr = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');

        $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
        $upperFieldName = $this->fixFieldName($upperFieldName, $field);
        
        $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$upperFieldName.self::BRACKETS.";".self::PHP_CLOSE_TAG;
        $label = $dom->createTextNode($caption);

        $td1->appendChild($label);
        
        $value = $this->createDetailValue($dom, $objectName, $field);

        $td2->appendChild($value);

        $tr->appendChild($td1);
        $tr->appendChild($td2);

        return $tr;
    }
    
    /**
     * Create a value representation for a field in a detail row.
     *
     * @param DOMDocument $dom             The DOM document to append the value to.
     * @param string $objectName           The name of the object.
     * @param AppField $field              The field whose value is to be displayed.
     * @return DOMElement|DOMText          The generated value element or text node.
     */
    private function createDetailValue($dom, $objectName, $field) // NOSONAR
    {
        $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
        
        $yes = self::VAR."appLanguage->getYes()";
        $no = self::VAR."appLanguage->getNo()";

        $true = self::VAR."appLanguage->getTrue()";
        $false = self::VAR."appLanguage->getFalse()";

        if(($field->getElementType() == InputType::CHECKBOX) ||
            ($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null
            && $field->getReferenceData()->getType() == ReferenceType::YESNO)
        )
        {
            $val = "->option".$upperFieldName."(".$yes.", ".$no.")";
            $result = self::VAR.$objectName.$val;
        }
        else if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::ENTITY
            && $field->getReferenceData()->getEntity() != null
            && $field->getReferenceData()->getEntity()->getObjectName() != null
            && $field->getReferenceData()->getEntity()->getPropertyName() != null
            )
        {
            $objName = $field->getReferenceData()->getEntity()->getObjectName();
            $propName = $field->getReferenceData()->getEntity()->getPropertyName();
            $upperObjName = PicoStringUtil::upperCamelize($objName);
            $upperPropName = PicoStringUtil::upperCamelize($propName);
            $v = $this->getDetailValueString($field, $objectName.self::CALL_GET.$upperObjName.self::BRACKETS, $upperPropName);
            $val = self::CALL_ISSET.$upperObjName.self::BRACKETS.' ? ' . $v . ' : ""';
            $result = self::VAR.$objectName.$val;
        }
        else if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::MAP
            && $field->getReferenceData()->getMap() != null
            )
        {
            $v1 = 'isset('.self::MAP_FOR.$upperFieldName.')';
            $v2 = 'isset($mapFor'.$upperFieldName.'[$'.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS.'])';
            $v3 = 'isset($mapFor'.$upperFieldName.'[$'.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS.']["label"])';
            $v4 = self::MAP_FOR.$upperFieldName.'[$'.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS.']["label"]';
            $val = "$v1 && $v2 && $v3 ? $v4 : \"\"";
            $result = $val;
        }
        else if($field->getElementType() == InputType::SELECT 
        && $field->getReferenceData() != null
            && $field->getReferenceData()->getType() == ReferenceType::TRUE_FALSE
            )
        {
            $val = "->option".$upperFieldName."(".$true.", ".$false.")";
            $result = self::VAR.$objectName.$val;
        }
        else if($field->getElementType() == InputType::SELECT 
        && $field->getReferenceData() != null
            && $field->getReferenceData()->getType() == ReferenceType::ONE_ZERO
            )
        {
            $val = "->option".$upperFieldName."(\"1\", \"0\")";
            $result = self::VAR.$objectName.$val;
        }
        else
        {      
            $result = $this->getDetailValueString($field, $objectName, $upperFieldName, 4);
        }
        
        if(stripos($result, " = ") === false)
        {
            return $dom->createTextNode(self::PHP_OPEN_TAG.self::ECHO.$result.";".self::PHP_CLOSE_TAG);    
        }
        else
        {
            return $dom->createTextNode(self::PHP_OPEN_TAG.$result."".self::PHP_CLOSE_TAG);
        }
        
    }

    /**
     * Create a row to compare values between the main and approval entities.
     *
     * @param DOMDocument $dom             The DOM document to append the row to.
     * @param string $objectName           The name of the main object.
     * @param AppField $field              The field to compare.
     * @param string $objectApprovalName    The name of the approval object.
     * @return DOMElement                  The generated comparison row element.
     */
    private function createDetailCompareRow($dom, $objectName, $field, $objectApprovalName)
    {
        $yes = self::VAR."appLanguage->getYes()";
        $no = self::VAR."appLanguage->getNo()";
        $tr = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');
        $td3 = $dom->createElement('td');

        $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
        $upperFieldNameOri = $upperFieldName;
        $upperFieldName = $this->fixFieldName($upperFieldName, $field);

        $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$upperFieldName.self::BRACKETS.";".self::PHP_CLOSE_TAG;
        $label = $dom->createTextNode($caption);
        
        if($field->getElementType() == InputType::CHECKBOX)
        {
            // Element type is checkbox
            $val = "->option".$upperFieldName."(".$yes.", ".$no.")";
            $result = self::VAR.$objectName.$val;
            $result2 = self::VAR.$objectApprovalName.$val;
        }
        else if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::ENTITY
            && $field->getReferenceData()->getEntity() != null
            && $field->getReferenceData()->getEntity()->getObjectName() != null
            && $field->getReferenceData()->getEntity()->getPropertyName() != null
            )
        {
            // Element type is select with reference data of type entity
            // and the entity, object name, and property name are not null
            $objName = $field->getReferenceData()->getEntity()->getObjectName();
            $propName = $field->getReferenceData()->getEntity()->getPropertyName();
            $upperObjName = PicoStringUtil::upperCamelize($objName);
            $upperPropName = PicoStringUtil::upperCamelize($propName);

            $v1 = $this->getDetailValueString($field, $objectName.self::CALL_GET.$upperObjName.self::BRACKETS, $upperPropName);
            $v2 = $this->getDetailValueString($field, $objectApprovalName.self::CALL_GET.$upperObjName.self::BRACKETS, $upperPropName);

            $val1 = self::CALL_ISSET.$upperObjName.self::BRACKETS.' ? ' . $v1 . ' : ""'; 
            $val2 = self::CALL_ISSET.$upperObjName.self::BRACKETS.' ? ' . $v2 . ' : ""'; 
            $result = self::VAR.$objectName.$val1;
            $result2 = self::VAR.$objectApprovalName.$val2;
        }
        else if($field->getElementType() == InputType::SELECT 
            && $field->getReferenceData() != null 
            && $field->getReferenceData()->getType() == ReferenceType::MAP
            && $field->getReferenceData()->getMap() != null
            )
        {
            // Element type is select with reference data of type map
            // and the map is not null
            $v1 = 'isset('.self::MAP_FOR.$upperFieldName.')';
            $v2 = 'isset($mapFor'.$upperFieldName.'[$'.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS.'])';
            $v3 = 'isset($mapFor'.$upperFieldName.'[$'.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS.']["label"])';
            $v4 = self::MAP_FOR.$upperFieldName.'[$'.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS.']["label"]';
            $val = "$v1 && $v2 && $v3 ? $v4 : \"\"";
            $result = $val;

            $v12 = 'isset('.self::MAP_FOR.$upperFieldName.')';
            $v22 = 'isset($mapFor'.$upperFieldName.'[$'.$objectApprovalName.self::CALL_GET.$upperFieldName.self::BRACKETS.'])';
            $v32 = 'isset($mapFor'.$upperFieldName.'[$'.$objectApprovalName.self::CALL_GET.$upperFieldName.self::BRACKETS.']["label"])';
            $v42 = self::MAP_FOR.$upperFieldName.'[$'.$objectApprovalName.self::CALL_GET.$upperFieldName.self::BRACKETS.']["label"]';
            $val2 = "$v12 && $v22 && $v32 ? $v42 : \"\"";
            $result2 = $val2;
        }
        else
        {
            // Element type is not checkbox or select with reference data of type entity or map
            // Use the getDetailValueString method to get the value string
            $result = $this->getDetailValueString($field, $objectName, $upperFieldName, 4);
            $result2 = $this->getDetailValueString($field, $objectApprovalName, $upperFieldName, 4);
        }
        
        $value = $dom->createTextNode(self::PHP_OPEN_TAG.self::ECHO.$result.";".self::PHP_CLOSE_TAG);
        $value2 = $dom->createTextNode(self::PHP_OPEN_TAG.self::ECHO.$result2.";".self::PHP_CLOSE_TAG);

        $td1->appendChild($label);
        
        $valueWrapper1 = $dom->createElement('span');
        $valueWrapper2 = $dom->createElement('span');
        
        $valueWrapper1->setAttribute('class', self::PHP_OPEN_TAG.self::ECHO."AppFormBuilder::classCompareData(".self::VAR.$objectName."->notEquals".$upperFieldNameOri."(".self::VAR.$objectApprovalName.self::CALL_GET.$upperFieldNameOri.self::BRACKETS."));".self::PHP_CLOSE_TAG);
        $valueWrapper2->setAttribute('class', self::PHP_OPEN_TAG.self::ECHO."AppFormBuilder::classCompareData(".self::VAR.$objectName."->notEquals".$upperFieldNameOri."(".self::VAR.$objectApprovalName.self::CALL_GET.$upperFieldNameOri.self::BRACKETS."));".self::PHP_CLOSE_TAG);

        $valueWrapper1->appendChild($value);
        $valueWrapper2->appendChild($value2);
        
        $td2->appendChild($valueWrapper1);
        $td3->appendChild($valueWrapper2);

        $tr->appendChild($td1);
        $tr->appendChild($td2);
        $tr->appendChild($td3);

        return $tr;
    }

    /**
     * Returns a formatted detail value string based on the specified data format.
     *
     * @param AppField $field The field object containing element and data format information.
     * @param string $objectName The name of the object being processed.
     * @param string $upperFieldName The field name in UpperCamelCase format.
     * @param int $indent The indentation level for the generated code.
     * @return string The formatted detail value string.
     */
    public function getDetailValueString($field, $objectName, $upperFieldName, $indent = 0)
    {
        
        $result = '';
        if(($field->getElementType() == 'text' || $field->getElementType() == 'select') && $field->getDataFormat() != null)
        {
            // Check if the field has a data format defined
            
            if($field->getDataFormat()->getFormatType() == 'dateFormat')
            {
                // Date Format
                $val = "->dateFormat".$upperFieldName."(".$this->fixFormat($field->getDataFormat()->getDateFormat(), 'string').")";
                $result = self::VAR.$objectName.$val;
            }
            else if($field->getDataFormat()->getFormatType() == 'numberFormat')
            {
                // Number Format
                $val = "->numberFormat".$upperFieldName."(".$this->fixFormat($field->getDataFormat()->getDecimal(), 'int').", ".$this->fixFormat($field->getDataFormat()->getDecimalSeparator(), 'string').", ".$this->fixFormat($field->getDataFormat()->getThousandsSeparator(), 'string').")";
                $result = self::VAR.$objectName.$val;
            }
            else if($field->getDataFormat()->getFormatType() == 'stringFormat')
            {
                // String Format
                $val = "->format".$upperFieldName."(".$this->fixFormat($field->getDataFormat()->getStringFormat(), 'string').")";
                $result = self::VAR.$objectName.$val;
            }
        }
        else
        {
            if($field->getElementType() == 'text')
            {
                if($field->getDataType() == 'image')
                {
                    // Image Format
                    $result = $this->renderImage($objectName, $upperFieldName, $indent);
                }
                else if($field->getDataType() == 'audio')
                {
                    // Audio Format
                    $result = $this->renderAudio($objectName, $upperFieldName, $indent);
                }
                else if($field->getDataType() == 'video')
                {
                    // Video Format
                    $result = $this->renderVideo($objectName, $upperFieldName, $indent);
                }
                else if($field->getDataType() == 'file')
                {
                    // File Format
                    $result = $this->renderFile($objectName, $upperFieldName, $indent);
                }
                else
                {
                    $val = self::CALL_GET.$upperFieldName.self::BRACKETS;
                    $result = self::VAR.$objectName.$val;
                }
            }
            else
            {
                $val = self::CALL_GET.$upperFieldName.self::BRACKETS;
                $result = self::VAR.$objectName.$val;
            }
        }
        return $result;
    }
    
    /**
     * Renders an image element for the specified object and field name.
     *
     * @param string $objectName The name of the object.
     * @param string $upperFieldName The field name in UpperCamelCase format.
     * @param int $indent The indentation level for the generated code.
     * @return string The generated image element as a string.
     */
    public function renderImage($objectName, $upperFieldName, $indent = 0, $useLibrary = true)
    {
        if($useLibrary)
        {
            return 'PicoFileRenderer::renderImage($'.$objectName.'->get'.$upperFieldName.'())';
        }
        
        $result = [];
        $result[] = '';
        $result[] = self::TAB1.self::VAR.'data'.$upperFieldName.' = $'.$objectName.'->get'.$upperFieldName.'();';
        $result[] = self::TAB1.'if($data'.$upperFieldName.' != null && !empty($data'.$upperFieldName.')) {';
        $result[] = self::TAB1.self::TAB1.'if(is_array($data'.$upperFieldName.')) {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'foreach($data'.$upperFieldName.' as $key => $value'.$upperFieldName.') {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.'echo "<img src=\"".$value'.$upperFieldName.'."\" alt=\"".$value'.$upperFieldName.'."\" /> ";';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'}';
        $result[] = self::TAB1.self::TAB1.'} else {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'echo "<img src=\"".$data'.$upperFieldName.'."\" alt=\"".$data'.$upperFieldName.'."\" /> ";';
        $result[] = self::TAB1.self::TAB1.'}';
        $result[] = self::TAB1.'}';
        $result[] = '';
        
        return $this->addIndent(implode("\n", $result), $indent);
    }
    
    /**
     * Renders an audio element for the specified object and field name.
     *
     * @param string $objectName The name of the object.
     * @param string $upperFieldName The field name in UpperCamelCase format.
     * @param int $indent The indentation level for the generated code.
     * @return string The generated audio element as a string.
     */
    public function renderAudio($objectName, $upperFieldName, $indent = 0, $useLibrary = true)
    {
        if($useLibrary)
        {
            return 'PicoFileRenderer::renderAudio($'.$objectName.'->get'.$upperFieldName.'())';
        }
        $result = [];
        $result[] = '';
        $result[] = self::TAB1.self::VAR.'data'.$upperFieldName.' = $'.$objectName.'->get'.$upperFieldName.'();';
        $result[] = self::TAB1.'if($data'.$upperFieldName.' != null && !empty($data'.$upperFieldName.')) {';
        $result[] = self::TAB1.self::TAB1.'if(is_array($data'.$upperFieldName.')) {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'foreach($data'.$upperFieldName.' as $key => $value'.$upperFieldName.') {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.'echo "<audio controls><source src=\"".$value'.$upperFieldName.'."\" type=\"audio/mpeg\"></audio> ";';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'}';
        $result[] = self::TAB1.self::TAB1.'} else {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'echo "<audio controls><source src=\"".$data'.$upperFieldName.'."\" type=\"audio/mpeg\"></audio> ";';
        $result[] = self::TAB1.self::TAB1.'}';
        $result[] = self::TAB1.'}';
        $result[] = '';
        
        return $this->addIndent(implode("\n", $result), $indent);
    }
    
    /**
     * Renders a video element for the specified object and field name.
     *
     * @param string $objectName The name of the object.
     * @param string $upperFieldName The field name in UpperCamelCase format.
     * @param int $indent The indentation level for the generated code.
     * @return string The generated video element as a string.
     */
    public function renderVideo($objectName, $upperFieldName, $indent = 0, $useLibrary = true)
    {
        if($useLibrary)
        {
            return 'PicoFileRenderer::renderVideo($'.$objectName.'->get'.$upperFieldName.'())';
        }
        $result = [];
        $result[] = '';
        $result[] = self::TAB1.self::VAR.'data'.$upperFieldName.' = $'.$objectName.'->get'.$upperFieldName.'();';
        $result[] = self::TAB1.'if($data'.$upperFieldName.' != null && !empty($data'.$upperFieldName.')) {';
        $result[] = self::TAB1.self::TAB1.'if(is_array($data'.$upperFieldName.')) {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'foreach($data'.$upperFieldName.' as $key => $value'.$upperFieldName.') {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.'echo "<video controls><source src=\"".$value'.$upperFieldName.'."\" type=\"video/mp4\"></video> ";';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'}';
        $result[] = self::TAB1.self::TAB1.'} else {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'echo "<video controls><source src=\"".$data'.$upperFieldName.'."\" type=\"video/mp4\"></video> ";';
        $result[] = self::TAB1.self::TAB1.'}';
        $result[] = self::TAB1.'}';
        $result[] = '';
        
        return $this->addIndent(implode("\n", $result), $indent);
    }
    
    /**
     * Renders a anchor element for the specified object and field name.
     *
     * @param string $objectName The name of the object.
     * @param string $upperFieldName The field name in UpperCamelCase format.
     * @param int $indent The indentation level for the generated code.
     * @return string The generated anchor element as a string.
     */
    public function renderFile($objectName, $upperFieldName, $indent = 0, $useLibrary = true)
    {
        if($useLibrary)
        {
            return 'PicoFileRenderer::renderFile($'.$objectName.'->get'.$upperFieldName.'())';
        }
        $result = [];
        $result[] = '';
        $result[] = self::TAB1.self::VAR.'data'.$upperFieldName.' = $'.$objectName.'->get'.$upperFieldName.'();';
        $result[] = self::TAB1.'if($data'.$upperFieldName.' != null && !empty($data'.$upperFieldName.')) {';
        $result[] = self::TAB1.self::TAB1.'if(is_array($data'.$upperFieldName.')) {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'foreach($data'.$upperFieldName.' as $key => $value'.$upperFieldName.') {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.'echo "<a href=\"".$value'.$upperFieldName.'."\" download>".$value'.$upperFieldName.'."</a> ";';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'}';
        $result[] = self::TAB1.self::TAB1.'} else {';
        $result[] = self::TAB1.self::TAB1.self::TAB1.'echo "<a href=\"".$data'.$upperFieldName.'."\" download>".$data'.$upperFieldName.'."</a> ";';
        $result[] = self::TAB1.self::TAB1.'}';
        $result[] = self::TAB1.'}';
        $result[] = '';
        
        return $this->addIndent(implode("\n", $result), $indent);
    } 
    
    /**
     * Fixes the format of a string based on the given type.
     *
     * @param string $format The format to be fixed.
     * @param string $type The expected data type ('string', 'int', etc.).
     * @return string The corrected format.
     */
    public function fixFormat($format, $type)
    {
        $format = trim($format);
        if ($type == 'string' && strpos($format, '$') !== 0) {
            $format = sprintf("'%s'", $format);
        }
        return $format;
    }
    
    /**
     * Checks if the given data type is an input file type.
     *
     * @param string $dataType The data type to check.
     * @return bool true if the data type is an input file type, false otherwise.
     */
    public static function isInputFile($dataType)
    {
        return $dataType == 'file' || $dataType == 'image' || $dataType == 'audio' || $dataType == 'video';
    }
    
    /**
     * Checks if any of the given fields are input file types.
     *
     * @param AppField[] $fields The fields to check.
     * @return bool true if any field is an input file type, false otherwise.
     */
    public static function hasInputFile($fields)
    {
        foreach($fields as $field)
        {
            if(self::isInputFile($field->getDataType()))
            {
                return true;
            }
        }
        return false;
    }

    
    /**
     * Create an input control for an insert form.
     *
     * @param DOMDocument $dom         The DOM document to which the control will be added.
     * @param AppField $field          The field definition for the control.
     * @param string|null $id          The optional ID attribute for the control.
     * @return DOMElement              The created input control element.
     */
    private function createInsertControl($dom, $field, $id = null) // NOSONAR
    {
        $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
        $input = $dom->createElement('input');
        $multipleData = $field->getMultipleData();
        if($field->getElementType() == InputType::TEXT)
        {
            $input = $dom->createElement('input');
            
            $this->setInputTypeAttribute($input, $field->getDataType()); 

            if($multipleData)
            {
                $input->setAttribute('name', $field->getFieldName().'[]');
            }
            else
            {
                $input->setAttribute('name', $field->getFieldName());
            }

            $input = $this->addAttributeId($input, $id); 
            $dataType = $field->getDataType();

            if($multipleData)
            {
                $input->setAttribute('placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getTypeHere();'.self::PHP_CLOSE_TAG);
            }
            else
            {
                $input->setAttribute('value', '');
            }

            if(!self::isInputFile($dataType))
            {
                $input->setAttribute('autocomplete', 'off'); 
            }
            if($field->getRequired())
            {
                $input->setAttribute('required', 'required');
            }
            if($multipleData && !($dataType == 'password' || self::isInputFile($dataType)))
            {
                $input->setAttribute('data-multi-input', 'true');
            }
        }
        else if($field->getElementType() == InputType::TEXTAREA)
        {
            $input = $dom->createElement('textarea');
            $classes = array();
            $classes[] = 'form-control';
            $input->setAttribute('class', implode(' ', $classes));
            $input->setAttribute('name', $field->getFieldName());

            $input = $this->addAttributeId($input, $id);  
            $value = $dom->createTextNode('');
            $input->appendChild($value);
            $input->setAttribute('spellcheck', 'false');
            if($field->getRequired())
            {
                $input->setAttribute('required', 'required');
            }
        }
        else if($field->getElementType() == InputType::SELECT)
        {
            $referenceData = $field->getReferenceData();

            $input = $dom->createElement('select');
            $classes = array();
            $classes[] = 'form-control';
            $input->setAttribute('class', implode(' ', $classes));
            
            if($multipleData)
            {
                $input->setAttribute('name', $field->getFieldName().'[]');
                $input->setAttribute('data-placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getSelectItems();'.self::PHP_CLOSE_TAG);
                $input->setAttribute('data-search-placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getPlaceholderSearch();'.self::PHP_CLOSE_TAG);
                $input->setAttribute('data-label-selected', self::PHP_OPEN_TAG.'echo $appLanguage->getLabelSelected();'.self::PHP_CLOSE_TAG);
                $input->setAttribute('data-label-select-all', self::PHP_OPEN_TAG.'echo $appLanguage->getLabelSelectAll();'.self::PHP_CLOSE_TAG);
                $input->setAttributeNode($dom->createAttribute('multiple'));
                $input->setAttributeNode($dom->createAttribute('data-multi-select'));
            }
            else
            {
                $input->setAttribute('name', $field->getFieldName());
            }

            $input = $this->addAttributeId($input, $id);  
            $value = $dom->createElement('option');
            $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage->getLabelOptionSelectOne();".self::PHP_CLOSE_TAG;
            $textLabel = $dom->createTextNode($caption);
            $input->appendChild($dom->createTextNode(self::N_TAB6));
            $value->appendChild($textLabel);
            $value->setAttribute('value', '');
            $value->appendChild($textLabel);
            if(!$multipleData)
            {
                $input->appendChild($value);
            }
            
            $input = $this->appendOption($dom, $input, $referenceData);
            if($field->getRequired())
            {
                $input->setAttribute('required', 'required');
            }
        }
        else if($field->getElementType() == InputType::CHECKBOX)
        {
            $input = $dom->createElement('label');
            $inputStrl = $dom->createElement('input');
            $classes = array();
            $classes[] = 'form-check-input';
            $inputStrl->setAttribute('class', implode(' ', $classes));
            $inputStrl->setAttribute('type', 'checkbox');
            $inputStrl->setAttribute('name', $field->getFieldName());
            $inputStrl = $this->addAttributeId($inputStrl, $id);
            $inputStrl->setAttribute('value', '1');
            $input->appendChild($inputStrl);
            $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$upperFieldName.self::BRACKETS.";".self::PHP_CLOSE_TAG;
            $textLabel = $dom->createTextNode(' '.$caption);
            $input->appendChild($textLabel);
        }

        return $input;
    }
    
    /**
     * Create an input control for an update form.
     *
     * @param DOMDocument $dom         The DOM document to which the control will be added.
     * @param string $objectName       The name of the object being updated.
     * @param AppField $field          The field definition for the control.
     * @param string $primaryKeyName   The primary key field name.
     * @param string|null $id          The optional ID attribute for the control.
     * @return DOMElement              The created input control element.
     */
    private function createUpdateControl($dom, $objectName, $field, $primaryKeyName, $id = null) // NOSONAR
    {
        $upperFieldName = PicoStringUtil::upperCamelize($field->getFieldName());
        $fieldName = $field->getFieldName();
        $multipleData = $field->getMultipleData();
        if($fieldName == $primaryKeyName)
        {
            $fieldName = "app_builder_new_pk_".$fieldName;
        }
        $input = $dom->createElement('input');
        if($field->getElementType() == InputType::TEXT)
        {
            $input = $dom->createElement('input');
            $this->setInputTypeAttribute($input, $field->getDataType()); 

            if($multipleData)
            {
                $input->setAttribute('name', $field->getFieldName().'[]');
            }
            else
            {
                $input->setAttribute('name', $field->getFieldName());
            }

            $input = $this->addAttributeId($input, $id);  
            $dataType = $field->getDataType();
            
            if($multipleData)
            {
                if($dataType == 'password' || self::isInputFile($dataType))
                {
                    // do nothing
                    $input->setAttribute('multiple', 'multiple');
                }
                else
                {
                    $input->setAttribute('placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getTypeHere();'.self::PHP_CLOSE_TAG);
                    $input->setAttribute('data-initial-value', self::PHP_OPEN_TAG.AppBuilderBase::ECHO.'htmlspecialchars(json_encode('.AppBuilderBase::VAR."inputGet".AppBuilderBase::CALL_GET.$upperFieldName.self::BRACKETS."));".AppBuilderBase::PHP_CLOSE_TAG);
                }
            }
            else
            {
                if($dataType == 'password' || self::isInputFile($dataType))
                {
                    // do nothing
                }
                else
                {
                    $input->setAttribute('value', $this->createPhpOutputValue(self::VAR.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS));
                }
            }

            if(!self::isInputFile($dataType))
            {
                $input->setAttribute('autocomplete', 'off');
            }
            if($field->getRequired())
            {
                $input->setAttribute('required', 'required');
            }
            if($multipleData && !($dataType == 'password' || self::isInputFile($dataType)))
            {
                $input->setAttribute('data-multi-input', 'true');
            }
        }
        else if($field->getElementType() == InputType::TEXTAREA)
        {
            $input = $dom->createElement('textarea');
            $classes = array();
            $classes[] = 'form-control';
            $input->setAttribute('class', implode(' ', $classes));
            $input->setAttribute('name', $fieldName);

            $input = $this->addAttributeId($input, $id); 
            
            $value = $dom->createTextNode('');
            $input->appendChild($value);
            $value = $dom->createTextNode($this->createPhpOutputValue(self::VAR.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS));
            $input->appendChild($value);
            $input->setAttribute('spellcheck', 'false');
            if($field->getRequired())
            {
                $input->setAttribute('required', 'required');
            }
        }
        else if($field->getElementType() == InputType::SELECT)
        {
            $input = $dom->createElement('select');
            $classes = array();
            $classes[] = 'form-control';
            $input->setAttribute('class', implode(' ', $classes));

            if($multipleData)
            {
                $input->setAttribute('name', $field->getFieldName().'[]');
                $input->setAttribute('data-placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getSelectItems();'.self::PHP_CLOSE_TAG);
                $input->setAttribute('data-search-placeholder', self::PHP_OPEN_TAG.'echo $appLanguage->getPlaceholderSearch();'.self::PHP_CLOSE_TAG);
                $input->setAttribute('data-label-selected', self::PHP_OPEN_TAG.'echo $appLanguage->getLabelSelected();'.self::PHP_CLOSE_TAG);
                $input->setAttribute('data-label-select-all', self::PHP_OPEN_TAG.'echo $appLanguage->getLabelSelectAll();'.self::PHP_CLOSE_TAG);
                $input->setAttributeNode($dom->createAttribute('multiple'));
                $input->setAttributeNode($dom->createAttribute('data-multi-select'));
            }
            else
            {
                $input->setAttribute('name', $field->getFieldName());
            }

            $input = $this->addAttributeId($input, $id);

            $value = $dom->createElement('option');
            $input->appendChild($dom->createTextNode(self::N_TAB6));
            $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR."appLanguage->getLabelOptionSelectOne();".self::PHP_CLOSE_TAG;
            $textLabel = $dom->createTextNode($caption);
            $value->appendChild($textLabel);
            $value->setAttribute('value', '');
            $value->appendChild($textLabel);
            $input->appendChild($value);
            $referenceData = $field->getReferenceData();
            $input = $this->appendOption($dom, $input, $referenceData, self::VAR.$objectName.self::CALL_GET.$upperFieldName.self::BRACKETS);
            if($field->getRequired())
            {
                $input->setAttribute('required', 'required');
            }
        }
        else if($field->getElementType() == InputType::CHECKBOX)
        {
            $input = $dom->createElement('label');
            $inputStrl = $dom->createElement('input');
            $classes = array();
            $classes[] = 'form-check-input';
            $inputStrl->setAttribute('class', implode(' ', $classes));
            $inputStrl->setAttribute('type', 'checkbox');
            $inputStrl->setAttribute('name', $fieldName);

            $inputStrl = $this->addAttributeId($inputStrl, $id);
             
            $inputStrl->setAttribute('value', '1');
            $inputStrl->setAttribute("data-app-builder-encoded-script", base64_encode(self::PHP_OPEN_TAG.self::ECHO.self::VAR.$objectName.'->createChecked'.$upperFieldName.self::BRACKETS.';'.self::PHP_CLOSE_TAG));
            $input->appendChild($inputStrl);
            $caption = self::PHP_OPEN_TAG.self::ECHO.self::VAR.self::APP_ENTITY_LANGUAGE.self::CALL_GET.$upperFieldName.self::BRACKETS.";".self::PHP_CLOSE_TAG;
            $textLabel = $dom->createTextNode(' '.$caption);
            $input->appendChild($textLabel);
        }
        return $input;
    }

    /**
     * Add an ID attribute to a DOM element if provided.
     *
     * @param DOMElement $element      The DOM element to which the ID will be added.
     * @param string|null $id          The ID value to set on the element.
     * @return DOMElement              The modified DOM element with the ID attribute.
     */
    public function addAttributeId($element, $id)
    {
        if($id != null)
        {
            $element->setAttribute('id', $id);
        }  
        return $element;
    }
    
    /**
     * Append options to a select element from a reference data object.
     *
     * @param DOMDocument $dom         The DOM document to which the options will be added.
     * @param DOMElement $input        The select element to append options to.
     * @param MagicObject $referenceData The reference data containing the options.
     * @param string|null $selected     The optional selected value.
     * @return DOMElement              The select element with appended options.
     */
    private function appendOption($dom, $input,  $referenceData, $selected = null)
    {
        if($referenceData != null)
        {        
            if($referenceData->getType() == 'map')
            {
                $map = $referenceData->getMap();
                $input = $this->appendOptionList($dom, $input, $map, $selected);
            }
            else if($referenceData->getType() == 'truefalse')
            {
                $map = (new MagicObject())
                    ->setValue2((new MagicObject())->setValue('true')->setLabel(self::PHP_OPEN_TAG.'echo $appLanguage->getOptionLabelTrue();'.self::PHP_CLOSE_TAG))
                    ->setValue3((new MagicObject())->setValue('false')->setLabel(self::PHP_OPEN_TAG.'echo $appLanguage->getOptionLabelFalse();'.self::PHP_CLOSE_TAG));
                
                $input = $this->appendOptionList($dom, $input, $map, $selected);
            }
            else if($referenceData->getType() == 'yesno')
            {
                $map = (new MagicObject())
                    ->setValue2((new MagicObject())->setValue('yes')->setLabel(self::PHP_OPEN_TAG.'echo $appLanguage->getOptionLabelYes();'.self::PHP_CLOSE_TAG))
                    ->setValue3((new MagicObject())->setValue('no')->setLabel(self::PHP_OPEN_TAG.'echo $appLanguage->getOptionLabelNo();'.self::PHP_CLOSE_TAG));
                
                $input = $this->appendOptionList($dom, $input, $map, $selected);
            }
            else if($referenceData->getType() == 'onezero')
            {
                $map = (new MagicObject())
                    ->setValue2((new MagicObject())->setValue('1')->setLabel('1'))
                    ->setValue3((new MagicObject())->setValue('0')->setLabel('0'));

                $input = $this->appendOptionList($dom, $input, $map, $selected);
            }
            else if($referenceData->getType() == 'entity')
            {    
                $entity = $referenceData->getEntity();         
                

                if(isset($entity) && $entity->getEntityName() != null && $entity->getPrimaryKey() != null && $entity->getValue())
                {
                    $input = $this->appendOptionEntity($dom, $input, $referenceData, $selected);
                }
            }
        }
        return $input;
    }

    /**
     * Retrieves a unique list of option groups from the provided map.
     *
     * @param MagicObject[] $map The collection of objects containing options.
     * @return string[] A unique array of option group names.
     */
    private function getOptionGroupList($map)
    {
        $groups = array();
        foreach($map as $opt)
        {
            $group = $opt->getGroup();
            if($this->isNotEmpty($group))
            {
                $groups[] = $group;
            }
        }
        return array_unique($groups);
    }

    /**
     * Retrieves grouped options and creates corresponding DOM elements.
     *
     * @param DOMDocument $dom The DOM document to which the options will be added.
     * @param DOMElement $input The select element to append options to.
     * @param MagicObject[] $map The collection of objects containing options.
     * @param string[] $groups The list of option groups.
     * @param string|null $selected The optional selected value.
     * @return array An array of grouped DOMElement option elements.
     */
    private function getInGroupOption($dom, $input, $map, $groups, $selected)
    {
        $optionsInGroup = array();
        foreach($groups as $group)
        {
            foreach($map as $opt)
            {
                $value = $opt->getValue();
                if($group == $opt->getGroup())
                {
                    $this->inGroup[] = $value;
                    $caption = $this->buildCaption($opt->getLabel());
                    $option = $dom->createElement('option');
                    $option->setAttribute('value', $value);
                    $textLabel = $dom->createTextNode($caption);
                    $option->appendChild($textLabel);
                    $option = $this->addSelectAttribute($option, $opt);
                    if($selected != null)
                    {
                        $input->setAttribute('data-app-builder-encoded-script', base64_encode('data-value="'.self::PHP_OPEN_TAG.self::ECHO.$selected.';'.self::PHP_CLOSE_TAG.'"')); // NOSONAR
                        $option->setAttribute("data-app-builder-encoded-script", base64_encode(self::PHP_OPEN_TAG.self::ECHO.'AppFormBuilder::selected('.$selected.', '."'".$value."'".');'.self::PHP_CLOSE_TAG)); // NOSONAR
                    }
                    else if($this->isTrue($opt->getSelected()))
                    {
                        $option->setAttribute('selected', 'selected');
                    }
                    if(!isset($optionsInGroup[$group]))
                    {
                        $optionsInGroup[$group] = array();
                    }
                    $optionsInGroup[$group][] = $option;
                    
                }
            }
        }
        return $optionsInGroup;
    }

    /**
     * Builds and appends grouped options to a select element.
     *
     * @param DOMDocument $dom The DOM document to which the options will be added.
     * @param DOMElement $input The select element to append options to.
     * @param MagicObject[] $map The collection of objects containing options.
     * @param string[] $groups The list of option groups.
     * @param string|null $selected The optional selected value.
     * @return void
     */
    private function builOptionWithGroup($dom, $input, $map, $groups, $selected = null)
    {
        $this->inGroup = array();
        $optionsInGroup = $this->getInGroupOption($dom, $input, $map, $groups, $selected);
        $optionsNotInGroup = array();
        
        foreach($map as $opt)
        {
            $value = $opt->getValue();
            if(!in_array($value, $this->inGroup))
            {
                $caption = $this->buildCaption($opt->getLabel());
                $option = $dom->createElement('option');
                $option->setAttribute('value', $value);
                $textLabel = $dom->createTextNode($caption);
                $option->appendChild($textLabel);
                $option = $this->addSelectAttribute($option, $opt);
                if($selected != null)
                {
                    $input->setAttribute('data-app-builder-encoded-script', base64_encode('data-value="'.self::PHP_OPEN_TAG.self::ECHO.$selected.';'.self::PHP_CLOSE_TAG.'"'));
                    $option->setAttribute("data-app-builder-encoded-script", base64_encode(self::PHP_OPEN_TAG.self::ECHO.'AppFormBuilder::selected('.$selected.', '."'".$value."'".');'.self::PHP_CLOSE_TAG));
                }
                else if($this->isTrue($opt->getSelected()))
                {
                    $option->setAttribute('selected', 'selected');
                }
                $optionsNotInGroup[] = $option;
            }
        }
        foreach($optionsInGroup as $group=>$options)
        {
            $optGroup = $dom->createElement('optgroup');
            $optGroup->setAttribute("label", $group);
            foreach($options as $option)
            {
                $optGroup->appendChild($dom->createTextNode(self::N_TAB7));
                $optGroup->appendChild($option);
            }
            $optGroup->appendChild($dom->createTextNode(self::N_TAB6));
            $input->appendChild($dom->createTextNode(self::N_TAB6));
            $input->appendChild($optGroup);
            
        }

        foreach($optionsNotInGroup as $option)
        {
            $input->appendChild($dom->createTextNode(self::N_TAB6));
            $input->appendChild($option);
        }
    }

    /**
     * Builds and appends options to a select element without groups.
     *
     * @param DOMDocument $dom The DOM document to which the options will be added.
     * @param DOMElement $input The select element to append options to.
     * @param MagicObject[] $map The collection of objects containing options.
     * @param string|null $selected The optional selected value.
     * @return void
     */
    private function builOptionWithoutGroup($dom, $input, $map, $selected = null)
    {
        foreach($map as $opt)
        {
            $value = $opt->getValue();
            $caption = $this->buildCaption($opt->getLabel());
            $option = $dom->createElement('option');
            $option->setAttribute('value', $value);
            $textLabel = $dom->createTextNode($caption);
            $option->appendChild($textLabel);
            $option = $this->addSelectAttribute($option, $opt);
            if($selected != null)
            {
                $input->setAttribute('data-app-builder-encoded-script', base64_encode('data-value="'.self::PHP_OPEN_TAG.self::ECHO.$selected.';'.self::PHP_CLOSE_TAG.'"'));
                $option->setAttribute("data-app-builder-encoded-script", base64_encode(self::PHP_OPEN_TAG.self::ECHO.'AppFormBuilder::selected('.$selected.', '."'".$value."'".');'.self::PHP_CLOSE_TAG));
            }
            else if($this->isTrue($opt->getSelected()))
            {
                $option->setAttribute('selected', 'selected');
            }
            $input->appendChild($dom->createTextNode(self::N_TAB6));
            $input->appendChild($option);
        }
    }
    
    /**
     * Append a list of options to a select element from a map.
     *
     * @param DOMDocument $dom         The DOM document to which the options will be added.
     * @param DOMElement $input        The select element to append options to.
     * @param MagicObject $map         The map containing the options.
     * @param string|null $selected     The optional selected value.
     * @return DOMElement              The select element with appended options.
     */
    private function appendOptionList($dom, $input, $map, $selected = null)
    {
        $hasGroup = false;
        $groups = $this->getOptionGroupList($map);
        if(!empty($groups))
        {
            $hasGroup = true;
        }
        if($hasGroup)
        {
            $this->builOptionWithGroup($dom, $input, $map, $groups, $selected);
        }
        else
        {
            $this->builOptionWithoutGroup($dom, $input, $map, $selected);
        }
        $input->appendChild($dom->createTextNode(self::N_TAB5));
        return $input;
    }

    /**
     * Appends a list of options to a select element based on the provided map.
     *
     * @param DOMDocument $dom The DOM document to which the options will be added.
     * @param DOMElement $input The select element to append options to.
     * @param MagicObject[] $map The collection of objects containing options.
     * @param string|null $selected The optional selected value.
     * @return DOMElement The select element with appended options.
     */
    private function addSelectAttribute($input, $opt)
    {
        $reserved = array('value', 'label', 'group', 'selected');
        $arr = $opt->valueArray();
        foreach($arr as $key=>$value)
        {
            if(!in_array($key, $reserved))
            {
                $att = PicoStringUtil::snakeize($key);
                $att = str_replace('_', '-', $att);
                if(!PicoStringUtil::startsWith($att, '-data'))
                {
                    $att = "data-$att";
                }
                $input->setAttribute($att, $this->buildCaption($value));
            }
        }
        return $input;
    }

    /**
     * Build a caption for an option.
     *
     * @param string $caption          The original caption string.
     * @return string                  The formatted caption string.
     */
    private function buildCaption($caption)
    {
        $test = trim(preg_replace('/\s+/', '', $caption), " \r\n\t ");
        if(strpos($test, '{$') === 0)
        {
            if(PicoStringUtil::endsWith($test, '}'))
            {
                $caption2 = trim($caption);
                $caption2 = substr($caption2, 1, strlen($caption2) - 2);
                return self::PHP_OPEN_TAG.'echo '.$caption2.'; '.self::PHP_CLOSE_TAG;
            }
            return self::PHP_OPEN_TAG.'echo "'.addslashes($caption).'"; '.self::PHP_CLOSE_TAG;
        }
        return $caption;
    }
    
    /**
     * Append options to a select element based on an entity.
     *
     * @param DOMDocument $dom         The DOM document to which the options will be added.
     * @param DOMElement $input        The select element to append options to.
     * @param MagicObject $referenceData
     * @param string|null $selected     The optional selected value.
     * @return DOMElement              The select element with appended options.
     */
    private function appendOptionEntity($dom, $input, $referenceData, $selected = null)
    {
        $entity = $referenceData->getEntity();
        if($entity != null)
        {
            $specification = $entity->getSpecification();
            $sortable = $entity->getSortable();
            $additionalOutput = $entity->getAdditionalOutput();
            $paramAdditionalOutput = "";
            if($additionalOutput != null && !empty($additionalOutput))
            {
                $paramSelected = ($selected != null) ? ", $selected": ", null";
                $output = array();
                foreach($additionalOutput as $add)
                {
                    $output[] = AppBuilderBase::getStringOf(PicoStringUtil::camelize($add->getColumn()));
                }
                $paramAdditionalOutput = ', array('.implode(', ', $output).')';               
            }
            else
            {
                $paramSelected = ($selected != null) ? ", $selected": "";
            }
            
            $specStr = $this->buildSpecification($specification);
            $sortStr = $this->buildSortable($sortable);

            $pk = AppBuilderBase::getStringOf(PicoStringUtil::camelize($entity->getPrimaryKey()));
            $val = AppBuilderBase::getStringOf(PicoStringUtil::camelize($entity->getValue()));
            $textNodeFormat = $entity->getTextNodeFormat();
            $indent = $entity->getIndent();
            $group = $this->getOptionGroup($referenceData);
            $format = $this->getEntityOptionFormat($textNodeFormat);
            $indentStr = $this->getIndentString($indent);
            
            $option = $dom->createTextNode(self::NEW_LINE_N.self::TAB3.self::TAB3
            .self::PHP_OPEN_TAG.self::ECHO.'AppFormBuilder::getInstance()'
            .'->createSelectOption(new '.$entity->getEntityName().'(null, '.self::VAR.$this->appConfig->getGlobalVariableDatabase().'), '
            .self::NEW_LINE_N.self::TAB3.self::TAB3
            .$specStr.', '.self::NEW_LINE_N.self::TAB3.self::TAB3
            .$sortStr.', '.self::NEW_LINE_N.self::TAB3.self::TAB3
            .$pk.', '.$val.$paramSelected.$paramAdditionalOutput.')'.$group.$format.$indentStr.
            self::NEW_LINE_N.self::TAB3.self::TAB3.'; '.self::PHP_CLOSE_TAG.self::NEW_LINE_N.self::TAB3.self::TAB2);
            $input->appendChild($option);
        }
        return $input;
    }

    /**
     * Retrieves the option group based on reference data.
     *
     * @param MagicObject $referenceData The reference object used to obtain the group.
     * @return string The formatted option group configuration.
     */
    private function getOptionGroup($referenceData)
    {
        $result = "";
        $group = $referenceData->getEntity()->getGroup();
        if (isset($group) && !empty($group)) {
            $value = $group->getValue();
            $label = $group->getLabel();
            $source = $group->getSource();
            $entity = $group->getEntity();
            $map = $group->getMap();
            if ($source == "map" && $map != null && !empty($map)) {
                $arguments = sprintf('%s, %s, %s', $this->getStringOf($value), $this->getStringOf($label), $this->varExport($map));
                $result = self::NEW_LINE_N . self::TAB3 . self::TAB3 . "->setGroup($arguments)";
            } elseif ($this->isNotEmpty($value) && $this->isNotEmpty($label) && $this->isNotEmpty($entity)) {
                $arguments = sprintf('%s, %s, %s', $this->getStringOf($value), $this->getStringOf($label), $this->getStringOf($entity));
                $result = self::NEW_LINE_N . self::TAB3 . self::TAB3 . "->setGroup($arguments)";
            }
        }
        return $result;
    }

    /**
     * Converts a map object to a single-line PHP array representation.
     *
     * @param MagicObject $map The map object containing value-label pairs.
     * @return string A single-line string representation of the exported array.
     */
    private function varExport($map)
    {
        $exported = var_export($this->valueLabelToAssociatedArray($map->valueArray()), true);
        $oneLiner = preg_replace('/\s+/', ' ', trim($exported)); // Remove excessive whitespace and newlines
        $oneLiner = preg_replace('/,\s*\)/', ')', $oneLiner); // Remove trailing commas before closing parentheses
        $oneLiner = preg_replace('/,\s*\]/', ']', $oneLiner); // Remove trailing commas before closing brackets
        if (PicoStringUtil::startsWith($oneLiner, 'array ( ')) {
            $oneLiner = 'array(' . substr($oneLiner, 8);
        }
        return $oneLiner;
    }

    /**
     * Converts an array of value-label pairs into an associative array.
     *
     * @param array $array The input array containing value-label pairs.
     * @return array The converted associative array.
     */
    private function valueLabelToAssociatedArray($array)
    {
        $result = array();
        foreach ($array as $v) {
            if(isset($v["value"]))
            {
                if(!isset($v["label"]))
                {
                    $v["label"] = $v["value"];
                }
                $result[$v["value"]] = $v["label"];
            }
            
        }
        return $result;
    }

    /**
     * Checks whether a value is not empty.
     *
     * @param mixed $value The value to check.
     * @return bool True if the value is not null and not empty, otherwise false.
     */
    private function isNotEmpty($value)
    {
        return $value != null && !empty($value);
    }

    /**
     * Get the format for an entity option based on its text node format.
     *
     * @param string $textNodeFormat   The text node format string.
     * @return string                  The formatted string for the option.
     */
    private function getEntityOptionFormat($textNodeFormat)
    {
        $format = "";
        if(isset($textNodeFormat) && !empty($textNodeFormat))
        {
            $format = self::NEW_LINE_N.self::TAB3.self::TAB3."->setTextNodeFormat('".str_replace("'", "\\'", $textNodeFormat)."')";
        }
        return $format;
    }

    /**
     * Get the indent string based on the specified indent value.
     *
     * @param int $indent              The indent level.
     * @return string                  The formatted indent string.
     */
    private function getIndentString($indent)
    {
        $indentStr = "";
        if(isset($indent) && !empty($indent))
        {
            $indentVal = intval($indent);
            $indentStr = self::NEW_LINE_N.self::TAB3.self::TAB3."->setIndent($indentVal)";
        }
        return $indentStr;
    }
    
    /**
     * Build specification
     *
     * Constructs a specification string based on the provided array of specifications.
     *
     * @param array $specification Array of specifications.
     * @return string The constructed specification string.
     */
    private function buildSpecification($specification)
    {
        $specs = array();
        $specs[] = 'PicoSpecification::getInstance()';
        if($specification != null)
        {
            foreach($specification as $spc)
            {
                if($spc->getColumn() != null && $spc->getValue() !== null)
                {
                    $field = $spc->getColumn();
                    $field = AppBuilderBase::getStringOf($field);
                    $value = $spc->getValue();
                    $value = self::fixValue($value);
                    $specs[]  = self::NEW_LINE_N.self::TAB4.self::TAB3."->addAnd(new PicoPredicate($field, $value))";
                }
            }
        }
        return implode("", $specs);
    }
    
    /**
     * Build sortable
     *
     * Constructs a sortable string based on the provided array of sortable options.
     *
     * @param array $sortable Array of sortable options.
     * @return string The constructed sortable string.
     */
    private function buildSortable($sortable)
    {
        $specs = array();
        $specs[] = 'PicoSortable::getInstance()';
        if($sortable != null)
        {
            foreach($sortable as $srt)
            {
                if($srt->getSortBy() != null && $srt->getSortType() != null)
                {
                    $field = $srt->getSortBy();
                    $field = AppBuilderBase::getStringOf($field);
                    $type = $this->getSortType($srt->getSortType());
                    $specs[]  = self::NEW_LINE_N.self::TAB4.self::TAB3."->add(new PicoSort($field, $type))";
                }
            }
        }
        return implode("", $specs);
    }
    
    /**
     * Get sort type
     *
     * Returns the appropriate sort type string. If the provided sort type
     * is already a PicoSort constant, it returns it; otherwise, it wraps
     * the sort type in double quotes.
     *
     * @param string $sortType The sort type.
     * @return string The processed sort type.
     */
    public function getSortType($sortType)
    {
        if(strpos($sortType, 'PicoSort::') !== false)
        {
            return $sortType;
        }
        else
        {
            return '"'.$sortType.'"';
        }
    }

    /**
     * Fix value
     *
     * Formats the provided value according to its type. Returns 'null' for null values,
     * formats booleans to 'true' or 'false', and ensures string values are properly quoted.
     *
     * @param mixed $value The value to format.
     * @return mixed The formatted value.
     */
    public static function fixValue($value) // NOSONAR
    {
        if($value === null)
        {
            return 'null';
        }
        if($value === true)
        {
            return 'true';
        }
        if($value === false)
        {
            return 'false';
        }
        $value = trim($value);
        if(is_bool($value))
        {
            $value = ($value === true) ? 'true' : 'false';
        }
        if(is_string($value) && $value != 'true' && $value != 'false' && $value != 'null')
        {
            if(
                (
                    PicoStringUtil::startsWith(trim($value), '"')
                    && PicoStringUtil::endsWith(trim($value), '"')
                    )
                    ||
                    (
                    PicoStringUtil::startsWith(trim($value), "'")
                    && PicoStringUtil::endsWith(trim($value), "'")
                    )
                )
            {
                // do nothing                
            }
            else if(strpos($value, '$') !== 0)
            {
                $value = "'".$value."'";
            }
            
        }
        return $value;
    }

    /**
     * Set input type attribute
     *
     * Sets the HTML type attribute for the input element based on the provided data type.
     * Adds appropriate classes for styling.
     *
     * @param DOMElement $input The input element to modify.
     * @param string $dataType The data type for the input (e.g., int, float).
     * @param bool $asInputFilter Indicates if the input is for an input filter.
     * @return DOMElement The modified input element.
     */
    private function setInputTypeAttribute($input, $dataType, $asInputFilter = false) // NOSONAR
    {
        $classes = array();
        $classes[] = 'form-control';    
        if($dataType == 'int' || $dataType == 'integer')
        {
            $input->setAttribute('type', 'number');
            $input->setAttribute('step', '1');
        }
        else if($dataType == 'float' || $dataType == 'double')
        {
            $input->setAttribute('type', 'number');
            $input->setAttribute('step', 'any');
        }
        else if(self::isInputFile($dataType))
        {
            if($asInputFilter)
            {
                // Set the type to 'text' for input filters
                $input->setAttribute('type', 'text');
            }
            else
            {
                // Set the type to 'file' for file inputs
                $input->setAttribute('type', 'file');
            }
        }
        else
        {
            $dataType = $this->mapInputType($dataType);
            $input->setAttribute('type', $dataType);
        }
        $input->setAttribute('class', implode(' ', $classes));
        return $input;
    }

    /**
     * Create map input type
     *
     * Maps specific data types to corresponding HTML input types.
     *
     * @param string $dataType The data type to map.
     * @return string The corresponding HTML input type.
     */
    private function mapInputType($dataType)
    {
        $map = array(
            'datetime'=>'datetime-local'
        );
        if(isset($map[$dataType]))
        {
            return $map[$dataType];
        }
        return $dataType;
    }
    
    /**
     * Create button container table for create
     *
     * Generates a table element containing buttons for the create action.
     *
     * @param DOMDocument $dom The DOM document for creating elements.
     * @param string $name The name for the button container.
     * @param string $id The ID for the button container.
     * @param string $userAction The user action context.
     * @return DOMElement The generated button container table.
     */
    private function createButtonContainerTableCreate($dom, $name, $id, $userAction) // NOSONAR
    {
        $table = $this->createElementTableResponsive($dom);
        
        $tbody = $dom->createElement('tbody');
        
        $tr2 = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');
        
        $btn1 = $dom->createElement('button');
        $btn1->setAttribute('type', 'submit');
        $btn1->setAttribute('class', ElementClass::BUTTON_SUCCESS);
        $btn1->setAttribute('name', 'user_action');
        $btn1->setAttribute('id', 'create_new_data');
        $btn1->setAttribute('value', 'create');
        $btn1->appendChild($dom->createTextNode($this->getTextOfLanguage('button_save')));

        $btn2 = $dom->createElement('button');
        $btn2->setAttribute('type', 'button');
        $btn2->setAttribute('class', ElementClass::BUTTON_PRIMARY);
        $btn2->setAttribute('id', self::BACK_TO_LIST);
        $btn2->setAttribute('onclick', 'window.location=\'<?php echo $currentModule->getRedirectUrl();?>\';');
        $btn2->appendChild($dom->createTextNode($this->getTextOfLanguage('button_cancel')));
                
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn1);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn2);
        $td2->appendChild($dom->createTextNode(self::N_TAB4));
              
        $tr2->appendChild($td1);
        $tr2->appendChild($td2);
        
        $tbody->appendChild($tr2);
        
        $table->appendChild($tbody);

        return $table;
    }

    /**
     * Create button container table for update
     *
     * Generates a table element containing buttons for the update action, including
     * a hidden primary key input.
     *
     * @param DOMDocument $dom The DOM document for creating elements.
     * @param string $objectName The name of the object being updated.
     * @param string $primaryKeyName The name of the primary key field.
     * @return DOMElement The generated button container table.
     */
    private function createButtonContainerTableUpdate($dom, $objectName, $primaryKeyName)
    {
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);
        $table = $this->createElementTableResponsive($dom);
        
        $tbody = $dom->createElement('tbody');
        
        $tr2 = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');

        $btn1 = $dom->createElement('button');
        $btn1->setAttribute('type', 'submit');
        $btn1->setAttribute('class', ElementClass::BUTTON_SUCCESS);
        $btn1->setAttribute('name', 'user_action');
        $btn1->setAttribute('id', 'update_data');
        $btn1->setAttribute('value', 'update');
        $btn1->appendChild($dom->createTextNode($this->getTextOfLanguage('button_save')));

        $btn2 = $dom->createElement('button');
        $btn2->setAttribute('type', 'button');
        $btn2->setAttribute('class', ElementClass::BUTTON_PRIMARY);
        $btn2->setAttribute('id', self::BACK_TO_LIST);
        $btn2->setAttribute('onclick', 'window.location=\'<?php echo $currentModule->getRedirectUrl();?>\';');
        $btn2->appendChild($dom->createTextNode($this->getTextOfLanguage('button_cancel')));

        
        $pkInput = $dom->createElement('input');
        $pkInput->setAttribute("type", "hidden");
        $pkInput->setAttribute('name', $primaryKeyName);
        $pkInput->setAttribute('id', 'primary_key_value');
        $pkInput->setAttribute('value', self::PHP_OPEN_TAG.'echo $'.$objectName.self::CALL_GET.$upperPrimaryKeyName.self::BRACKETS.';'.self::PHP_CLOSE_TAG);
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn1);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn2);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($pkInput);
        $td2->appendChild($dom->createTextNode(self::N_TAB4));
              
        $tr2->appendChild($td1);
        $tr2->appendChild($td2);
        
        $tbody->appendChild($tr2);
        
        $table->appendChild($tbody);

        return $table;
    }
    
    /**
     * Create button container table for detail
     *
     * Generates a table element containing buttons for viewing the detail of an object,
     * with conditional buttons based on user permissions.
     *
     * @param DOMDocument $dom The DOM document for creating elements.
     * @param string $objectName The name of the object being detailed.
     * @param string $primaryKeyName The name of the primary key field.
     * @return DOMElement The generated button container table.
     */
    private function createButtonContainerTableDetail($dom,  $objectName, $primaryKeyName)
    {
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);
        $table = $this->createElementTableResponsive($dom);
        
        $tbody = $dom->createElement('tbody');
        
        $tr2 = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');
     
        $btn3 = $dom->createElement('button');
        $btn3->setAttribute('type', 'submit');
        $btn3->setAttribute('class', ElementClass::BUTTON_SUCCESS);
        $btn3->setAttribute('name', 'user_action');
        $btn3->setAttribute('id', 'approve_data');
        $btn3->setAttribute('value', self::PHP_OPEN_TAG.'echo UserAction::APPROVE;'.self::PHP_CLOSE_TAG);
        $btn3->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonApprove();'.self::PHP_CLOSE_TAG));
        $btn32 = clone $btn3;

        $btn4 = $dom->createElement('button');
        $btn4->setAttribute('type', 'submit');
        $btn4->setAttribute('class', ElementClass::BUTTON_WARNING);
        $btn4->setAttribute('name', 'user_action');
        $btn4->setAttribute('id', 'reject_data');
        $btn4->setAttribute('value', self::PHP_OPEN_TAG.'echo UserAction::REJECT;'.self::PHP_CLOSE_TAG);
        $btn4->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonReject();'.self::PHP_CLOSE_TAG));
        $btn42 = clone $btn4;

        $btn1 = $this->createCancelButton($dom, $this->getTextOfLanguage('button_update'), null, 'update_data', 'currentModule->getRedirectUrl(UserAction::UPDATE, '.AppBuilderBase::getStringOf($primaryKeyName).', $'.$objectName.self::CALL_GET.$upperPrimaryKeyName.self::BRACKETS.')');
        $btn2 = $this->createCancelButton($dom, $this->getTextOfLanguage('button_back_to_list'), null, self::BACK_TO_LIST, self::REDIRECT_TO_ITSELF);
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG));

        $approvalType = $this->appFeatures->getApprovalType();

        if($this->appFeatures->isApprovalRequired())
        {

            if($approvalType == 2)
            {
                $td2->appendChild($dom->createTextNode('if($inputGet->getNextAction() == UserAction::APPROVAL && UserAction::isRequireApproval($'.$objectName.'->getWaitingFor()) && $userPermission->isAllowedApprove()){ '.self::PHP_CLOSE_TAG)); // NOSONAR
                $td2->appendChild($dom->createTextNode(self::N_TAB5));
                $td2->appendChild($btn32);
                $td2->appendChild($dom->createTextNode(self::N_TAB5));
                $td2->appendChild($btn42);
                $td2->appendChild($dom->createTextNode(self::N_TAB5));
                $td2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'} else '));
            }

            $td2->appendChild($dom->createTextNode('if($inputGet->getNextAction() == UserAction::APPROVE && UserAction::isRequireApproval($'.$objectName.'->getWaitingFor()) && $userPermission->isAllowedApprove()){ '.self::PHP_CLOSE_TAG));
            $td2->appendChild($dom->createTextNode(self::N_TAB5));
            $td2->appendChild($btn3);
            $td2->appendChild($dom->createTextNode(self::N_TAB5));
            $td2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'} else if($inputGet->getNextAction() == UserAction::REJECT && UserAction::isRequireApproval($'.$objectName.'->getWaitingFor()) && $userPermission->isAllowedApprove()){ '.self::PHP_CLOSE_TAG));
            $td2->appendChild($dom->createTextNode(self::N_TAB5));
            $td2->appendChild($btn4);
            $td2->appendChild($dom->createTextNode(self::N_TAB5));
            $td2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'} else '));
        }

        $td2->appendChild($dom->createTextNode('if($userPermission->isAllowedUpdate()){ '.self::PHP_CLOSE_TAG));
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn1);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'} '.self::PHP_CLOSE_TAG."\n"));
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn2);

        $pkInputApprove = $dom->createElement('input');
        $pkInputApprove->setAttribute("type", "hidden");
        $pkInputApprove->setAttribute('name', $primaryKeyName);
        $pkInputApprove->setAttribute('id', 'primary_key_value');
        $pkInputApprove->setAttribute('value', self::PHP_OPEN_TAG.'echo $'.$objectName.self::CALL_GET.$upperPrimaryKeyName.self::BRACKETS.';'.self::PHP_CLOSE_TAG);

        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($pkInputApprove);

        $td2->appendChild($dom->createTextNode(self::N_TAB4));
              
        $tr2->appendChild($td1);
        $tr2->appendChild($td2);
        
        $tbody->appendChild($tr2);
        
        $table->appendChild($tbody);

        return $table;
    }
    
    /**
     * Create button container table for approval
     *
     * Generates a table element containing buttons for the approval action,
     * with conditions based on the current user action.
     *
     * @param DOMDocument $dom The DOM document for creating elements.
     * @param string $objectName The name of the object being approved.
     * @param string $primaryKeyName The name of the primary key field.
     * @return DOMElement The generated button container table.
     */
    private function createButtonContainerTableApproval($dom, $objectName, $primaryKeyName)
    {
        $upperPrimaryKeyName = PicoStringUtil::upperCamelize($primaryKeyName);
        $table = $this->createElementTableResponsive($dom);
        
        $tbody = $dom->createElement('tbody');
        
        $tr2 = $dom->createElement('tr');
        $td1 = $dom->createElement('td');
        $td2 = $dom->createElement('td');

        $btn1 = $dom->createElement('button');
        $btn1->setAttribute('type', 'submit');
        $btn1->setAttribute('class', ElementClass::BUTTON_SUCCESS);
        $btn1->setAttribute('name', 'user_action');
        $btn1->setAttribute('id', 'approve_data');
        $btn1->setAttribute('value', self::PHP_OPEN_TAG.'echo UserAction::APPROVE;'.self::PHP_CLOSE_TAG);
        $btn1->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonApprove();'.self::PHP_CLOSE_TAG));

        $btn12 = clone $btn1;

        $btn2 = $dom->createElement('button');
        $btn2->setAttribute('type', 'submit');
        $btn2->setAttribute('class', ElementClass::BUTTON_WARNING);
        $btn2->setAttribute('name', 'user_action');
        $btn2->setAttribute('id', 'reject_data');
        $btn2->setAttribute('value', self::PHP_OPEN_TAG.'echo UserAction::REJECT;'.self::PHP_CLOSE_TAG);
        $btn2->appendChild($dom->createTextNode(self::PHP_OPEN_TAG.'echo $appLanguage->getButtonReject();'.self::PHP_CLOSE_TAG));

        $btn22 = clone $btn2;

        $btn3 = $this->createCancelButton($dom, $this->getTextOfLanguage('button_cancel'), null, self::BACK_TO_LIST, self::REDIRECT_TO_ITSELF);
        $btn32 = clone $btn3;
        $btn4 = $this->createCancelButton($dom, $this->getTextOfLanguage('button_cancel'), null, self::BACK_TO_LIST, self::REDIRECT_TO_ITSELF);
        
        $pkInputApprove = $dom->createElement('input');
        $pkInputApprove->setAttribute("type", "hidden");
        $pkInputApprove->setAttribute('name', $primaryKeyName);
        $pkInputApprove->setAttribute('id', 'primary_key_value');
        $pkInputApprove->setAttribute('value', self::PHP_OPEN_TAG.'echo $'.$objectName.self::CALL_GET.$upperPrimaryKeyName.self::BRACKETS.';'.self::PHP_CLOSE_TAG);

        $pkInputApprove2 = clone $pkInputApprove;

        $pkInputReject = $dom->createElement('input');
        $pkInputReject->setAttribute("type", "hidden");
        $pkInputReject->setAttribute('id', 'primary_key_value');
        $pkInputReject->setAttribute('name', $primaryKeyName);
        $pkInputReject->setAttribute('value', self::PHP_OPEN_TAG.'echo $'.$objectName.self::CALL_GET.$upperPrimaryKeyName.self::BRACKETS.';'.self::PHP_CLOSE_TAG);

        
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::PHP_OPEN_TAG));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.'if($inputGet->getNextAction() == UserAction::APPROVAL)'));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::CURLY_BRACKET_OPEN));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::PHP_CLOSE_TAG));
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn12);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn22);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn32);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($pkInputApprove2);
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::PHP_OPEN_TAG));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::CURLY_BRACKET_CLOSE));

        $td2->appendChild($dom->createTextNode(self::N_TAB5.'else if($inputGet->getNextAction() == UserAction::APPROVE)'));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::CURLY_BRACKET_OPEN));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::PHP_CLOSE_TAG));
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn1);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn3);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($pkInputApprove);
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::PHP_OPEN_TAG));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::CURLY_BRACKET_CLOSE));
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5.'else'));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::CURLY_BRACKET_OPEN));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::PHP_CLOSE_TAG));
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn2);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($btn4);
        $td2->appendChild($dom->createTextNode(self::N_TAB5));
        $td2->appendChild($pkInputReject);
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::PHP_OPEN_TAG));
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::CURLY_BRACKET_CLOSE));
        
        $td2->appendChild($dom->createTextNode(self::N_TAB5.self::PHP_CLOSE_TAG));
        
        $td2->appendChild($dom->createTextNode(self::N_TAB4));
              
        $tr2->appendChild($td1);
        $tr2->appendChild($td2);
        
        $tbody->appendChild($tr2);
        
        $table->appendChild($tbody);

        return $table;
    }

    /**
     * Convert XML to HTML
     *
     * This function processes the provided XML string, removing the XML declaration
     * and decoding any encoded scripts found within the XML.
     *
     * @param string $xml The XML string to convert.
     * @return string The converted HTML string.
     */
    public function xmlToHtml($xml)
    {
        $start = stripos($xml, '<?xml');
        if($start !== false)
        {
            $end = stripos($xml, ''.self::PHP_CLOSE_TAG, $start);
            if($end !== false)
            {
                $xml = substr($xml, $end+2);
            }
        }      
        do
        {
            $search = 'data-app-builder-encoded-script="';
            $startPos = strpos($xml, $search);
            if($startPos !== false)
            {
                $endPos = stripos($xml, '"', $startPos + strlen($search));
                $stringFound = substr($xml, $startPos, 1+$endPos-$startPos);
                $successor = $this->decodeString($stringFound);
                $xml = str_replace($stringFound, $successor, $xml);
            }
        }
        while($startPos !== false);
        return $xml;
    }
    
    /**
     * Decode a base64 encoded string
     *
     * This function extracts the encoded script from a given string and decodes it.
     *
     * @param string $stringFound The encoded string to decode.
     * @return string The decoded string.
     */
    public function decodeString($stringFound)
    {
        $search = 'data-app-builder-encoded-script="';
        if(PicoStringUtil::startsWith($stringFound, $search))
        {
            $code = substr($stringFound, strlen($search), strlen($stringFound) - strlen($search) - 1);     
        }
        else
        {
            $code = $stringFound;
        }
        return base64_decode($code);
    }
    
    /**
     * Create PHP output for a given value
     *
     * This function returns a string formatted to echo the specified value within
     * PHP opening and closing tags.
     *
     * @param string $value The value to output.
     * @return string The formatted PHP output string.
     */
    public function createPhpOutputValue($value)
    {
        return self::PHP_OPEN_TAG.self::ECHO.$value.';'.self::PHP_CLOSE_TAG;
    }
    
    /**
     * Retrieve class attributes from a DOM element
     *
     * This function extracts the class attribute from the given DOM element
     * and returns it as an array of class names.
     *
     * @param DOMElement $node The DOM element from which to extract class names.
     * @return string[] An array of class names.
     */
    public function getClass($node)
    {
        $attr = $node->getAttribute('class');
        if($attr != null)
        {
            return explode(' ', $attr);
        }
        return array();
    }
    
    /**
     * Add a class to a DOM element
     *
     * This function adds a specified class to the class attribute of the
     * given DOM element.
     *
     * @param DOMElement $node The DOM element to modify.
     * @param string $class The class to add.
     * @return DOMElement The modified DOM element.
     */
    public function addClass($node, $class)
    {
        $classes = $this->getClass($node);
        $classes[] = $class;
        $node->setAttribute('class', implode(' ', $classes));
        return $node;
    }
    
    /**
     * Remove a class from a DOM element
     *
     * This function removes a specified class from the class attribute of the
     * given DOM element.
     *
     * @param DOMElement $node The DOM element to modify.
     * @param string $class The class to remove.
     * @return DOMElement The modified DOM element.
     */
    public function removeClass($node, $class)
    {
        $classes = $this->getClass($node);
        $classesCopy = array();
        foreach($classes as $cls)
        {
            if($cls != $class)
            {
                $classesCopy[] = $cls;
            }
        }
        $node->setAttribute('class', implode(' ', $classesCopy));
        return $node;
    }
    
    /**
     * Generate a single entity in the database
     *
     * This function creates a new entity in the specified table using the provided
     * database and configuration settings.
     *
     * @param PicoDatabase $database The database instance to use.
     * @param SecretObject $appConf The application configuration object.
     * @param string $entityName The name of the entity to generate.
     * @param string $tableName The name of the table to which the entity belongs.
     * @param string[] $nonupdatables An array of non-updatable fields.
     * @return void
     */
    public function generateEntity($database, $appConf, $entityName, $tableName, $nonupdatables)
    {
        $baseDir = $appConf->getBaseEntityDirectory();
        $baseNamespace = $appConf->getBaseEntityDataNamespace();
        $generator = new AppEntityGenerator($database, $baseDir, $tableName, $baseNamespace, $entityName);
        $generator->generateCustomEntity($entityName, $tableName, null, null, false, null, $nonupdatables);
    }
    
    /**
     * Generate the main entity in the database
     *
     * This function creates the main entity using the provided configuration
     * and reference data.
     *
     * @param PicoDatabase $database The database instance to use.
     * @param SecretObject $appConf The application configuration object.
     * @param MagicObject $entityMain The main entity object.
     * @param EntityInfo $entityInfo The entity information.
     * @param MagicObject[] $referenceData An array of reference data objects.
     * @return void
     */
    public function generateMainEntity($database, $appConf, $entityMain, $entityInfo, $referenceData)
    {
        $nonupdatables = AppField::getNonupdatableColumns($entityInfo);
        $entityName = $entityMain->getentityName();
        $tableName = $entityMain->getTableName();
        $baseDir = $appConf->getBaseEntityDirectory();
        $baseNamespace = $appConf->getBaseEntityDataNamespace();
        $generator = new AppEntityGenerator($database, $baseDir, $tableName, $baseNamespace, $entityName, $entityInfo, $this->updateEntity);
        $generator->setUpdateEntity($this->getUpdateEntity());
        $generator->setMainEntity(true);
        $generator->generateCustomEntity($entityMain->getEntityName(), $entityMain->getTableName(), null, $this->getSucessorMainColumns(), false, $referenceData, $nonupdatables);
    }
    
    /**
     * Generate the approval entity in the database
     *
     * This function creates an approval entity using the provided configuration
     * and reference data.
     *
     * @param PicoDatabase $database The database instance to use.
     * @param SecretObject $appConf The application configuration object.
     * @param MagicObject $entityMain The main entity object.
     * @param EntityInfo $entityInfo The entity information.
     * @param MagicObject $entityApproval The approval entity object.
     * @param MagicObject[] $referenceData An array of reference data objects.
     * @return void
     */
    public function generateApprovalEntity($database, $appConf, $entityMain, $entityInfo, $entityApproval, $referenceData)
    {
        $nonupdatables = AppField::getNonupdatableColumns($entityInfo);
        $entityName = $entityMain->getentityName();
        $tableName = $entityMain->getTableName();
        $baseDir = $appConf->getBaseEntityDirectory();
        $baseNamespace = $appConf->getBaseEntityDataNamespace();
        $generator = new AppEntityGenerator($database, $baseDir, $tableName, $baseNamespace, $entityName, $entityInfo, $this->updateEntity);
        $generator->setMainEntity(false);
        $generator->generateCustomEntity($entityApproval->getEntityName(), $entityApproval->getTableName(), $this->getPredecessorApprovalColumns($entityApproval), $this->getSucessorApprovalColumns(), true, $referenceData, $nonupdatables);
    }
    
    /**
     * Generate the trash entity in the database
     *
     * This function creates a trash entity using the provided configuration
     * and reference data.
     *
     * @param PicoDatabase $database The database instance to use.
     * @param SecretObject $appConf The application configuration object.
     * @param MagicObject $entityMain The main entity object.
     * @param EntityInfo $entityInfo The entity information.
     * @param MagicObject $entityTrash The trash entity object.
     * @param MagicObject[] $referenceData An array of reference data objects.
     * @return void
     */
    public function generateTrashEntity($database, $appConf, $entityMain, $entityInfo, $entityTrash, $referenceData)
    {
        $nonupdatables = AppField::getNonupdatableColumns($entityInfo);
        $entityName = $entityMain->getentityName();
        $tableName = $entityMain->getTableName();
        $baseDir = $appConf->getBaseEntityDirectory();
        $baseNamespace = $appConf->getBaseEntityDataNamespace();
        $generator = new AppEntityGenerator($database, $baseDir, $tableName, $baseNamespace, $entityName, $entityInfo, $this->updateEntity);
        $generator->setMainEntity(false);
        $generator->generateCustomEntity($entityTrash->getEntityName(), $entityTrash->getTableName(), $this->getPredecessorTrashColumns($entityTrash), $this->getSucessorTrashColumns(), true, $referenceData, $nonupdatables);
    }

    /**
     * Retrieve successor main columns based on application features
     *
     * This function returns an array of columns for the successor entity based on
     * the application's feature configuration.
     *
     * @return array An array of column definitions.
     */
    public function getSucessorMainColumns()
    {
        $cols = array();
        
        if($this->appFeatures->isSortOrder())
        {
            $cols["sortOrder"]       = array('Type'=>DataType::INT_11,     'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //sort_order",
        }
        if($this->appFeatures->isActivateDeactivate())
        {
            $cols["active"]          = array('Type'=>DataType::TINYINT_1,  'Null'=>'YES', 'Key'=>'', 'Default'=>'0',    'Extra'=>''); //active",
        }
        
        if($this->appFeatures->isApprovalRequired())
        {
            $cols["adminAskEdit"]    = array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //admin_ask_edit",
            $cols["ipAskEdit"]       = array('Type'=>DataType::VARCHAR_50, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //ip_ask_edit",
            $cols["timeAskEdit"]     = array('Type'=>DataType::TIMESTAMP,  'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //time_ask_edit",
            $cols["draft"]           = array('Type'=>DataType::TINYINT_1,  'Null'=>'YES', 'Key'=>'', 'Default'=>'0',    'Extra'=>''); //draft",
            $cols["waitingFor"]      = array('Type'=>DataType::INT_4,      'Null'=>'YES', 'Key'=>'', 'Default'=>'0',    'Extra'=>''); //waiting_for",
            $cols["approvalId"]      = array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //approval_id",

        }
        $result = array();
        foreach($cols as $key=>$value)
        {
            $value['Field'] = $this->entityInfo->get($key);
            $result[] = $value;
            
        }

        return $result;
    }
    
    /**
     * Retrieve predecessor approval columns for the approval entity
     *
     * This function returns the columns needed for the predecessor approval entity.
     *
     * @param MagicObject $entityApproval The approval entity object.
     * @return array An array of column definitions.
     */
    public function getPredecessorApprovalColumns($entityApproval)
    {
        return array(

            array('Field'=>$entityApproval->getPrimaryKey(), 'Type'=>DataType::VARCHAR_40, 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>null, 'Extra'=>'')
        );
    }
    
    /**
     * Retrieve successor approval columns based on application features
     *
     * This function returns an array of columns for the successor approval entity
     * based on the application's feature configuration.
     *
     * @return array An array of column definitions.
     */
    public function getSucessorApprovalColumns()
    {
        $cols = array();
        
        if($this->appFeatures->isSortOrder())
        {
            $cols["sortOrder"]       = array('Type'=>DataType::INT_11,     'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //sort_order",
        }
        if($this->appFeatures->isActivateDeactivate())
        {
            $cols["active"]          = array('Type'=>DataType::TINYINT_1,  'Null'=>'YES', 'Key'=>'', 'Default'=>'0',    'Extra'=>''); //active",
        }
        if($this->appFeatures->isApprovalRequired())
        {
            $cols["adminAskEdit"]    = array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //admin_ask_edit",
            $cols["ipAskEdit"]       = array('Type'=>DataType::VARCHAR_50, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //ip_ask_edit",
            $cols["timeAskEdit"]     = array('Type'=>DataType::TIMESTAMP,  'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //time_ask_edit",
            $cols["approvalStatus"]  = array('Type'=>DataType::INT_4,      'Null'=>'YES', 'Key'=>'', 'Default'=>'0',    'Extra'=>''); //waiting_for",
        }
        $result = array();
        foreach($cols as $key=>$value)
        {
            $value['Field'] = $this->entityInfo->get($key);
            $result[] = $value;
        }
        return $result;
    }
    
    /**
     * Retrieve predecessor trash columns for the trash entity
     *
     * This function returns the columns needed for the predecessor trash entity.
     *
     * @param MagicObject $entityTrash The trash entity object.
     * @return array An array of column definitions.
     */
    public function getPredecessorTrashColumns($entityTrash)
    {
        return array(

            array('Field'=>$entityTrash->getPrimaryKey(), 'Type'=>DataType::VARCHAR_40, 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>null, 'Extra'=>'')
        );
    }
    
    /**
     * Retrieve successor trash columns based on application features
     *
     * This function returns an array of columns for the successor trash entity
     * based on the application's feature configuration.
     *
     * @return array An array of column definitions.
     */
    public function getSucessorTrashColumns()
    {
        $cols = array();
        
        if($this->appFeatures->isSortOrder())
        {
            $cols["sortOrder"]       = array('Type'=>DataType::INT_11,     'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //sort_order",
        }
        if($this->appFeatures->isActivateDeactivate())
        {
            $cols["active"]          = array('Type'=>DataType::TINYINT_1,  'Null'=>'YES', 'Key'=>'', 'Default'=>'1',    'Extra'=>''); //active",
        }
        if($this->appFeatures->isApprovalRequired())
        {
            $cols["adminAskEdit"]    = array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //admin_ask_edit",
            $cols["ipAskEdit"]       = array('Type'=>DataType::VARCHAR_50, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //ip_ask_edit",
            $cols["timeAskEdit"]     = array('Type'=>DataType::TIMESTAMP,  'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //time_ask_edit",
        }

        $cols["adminDelete"]         = array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //admin_delete",
        $cols["ipDelete"]            = array('Type'=>DataType::VARCHAR_50, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //ip_delete",
        $cols["timeDelete"]          = array('Type'=>DataType::TIMESTAMP,  'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //time_delete",

        $cols["restored"]            = array('Type'=>DataType::TINYINT_1, 'Null'=>'YES', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');     //restored",
        $cols["adminRestore"]        = array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //admin_restore",
        $cols["ipRestore"]           = array('Type'=>DataType::VARCHAR_50, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //ip_restore",
        $cols["timeRestore"]         = array('Type'=>DataType::TIMESTAMP,  'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''); //time_restore",

        $result = array();

        foreach($cols as $key=>$value)
        {
            $value['Field'] = $this->entityInfo->get($key) != null ? $this->entityInfo->get($key) : PicoStringUtil::snakeize($key);
            $result[] = $value;
        }
        return $result;
    }

    /**
     * Retrieve column information for the entity
     *
     * This function returns the definitions for common columns in the entity.
     *
     * @return array An array of column definitions.
     */
    public function getColumnInfo()
    {
        return array(
            "adminCreate"  => array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //admin_create",
            "adminEdit"    => array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //admin_edit",
            "adminAskEdit" => array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //admin_ask_edit",
            "ipCreate"     => array('Type'=>DataType::VARCHAR_50, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //ip_create",
            "ipEdit"       => array('Type'=>DataType::VARCHAR_50, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //ip_edit",
            "ipAskEdit"    => array('Type'=>DataType::VARCHAR_50, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //ip_ask_edit",
            "timeCreate"   => array('Type'=>DataType::TIMESTAMP,  'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //time_create",
            "timeEdit"     => array('Type'=>DataType::TIMESTAMP,  'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //time_edit",
            "timeAskEdit"  => array('Type'=>DataType::TIMESTAMP,  'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //time_ask_edit",
            "sortOrder"    => array('Type'=>DataType::INT_11,     'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>''), //sort_order",
            "active"       => array('Type'=>DataType::TINYINT_1,  'Null'=>'YES', 'Key'=>'', 'Default'=>'1',    'Extra'=>''), //active",
            "draft"        => array('Type'=>DataType::TINYINT_1,  'Null'=>'YES', 'Key'=>'', 'Default'=>'0',    'Extra'=>''), //draft",
            "waitingFor"   => array('Type'=>DataType::INT_4,      'Null'=>'YES', 'Key'=>'', 'Default'=>'0',    'Extra'=>''), //waiting_for",
            "approvalId"   => array('Type'=>DataType::VARCHAR_40, 'Null'=>'YES', 'Key'=>'', 'Default'=>'NULL', 'Extra'=>'')  //approval_id",
        );
    }

    /**
     * Check if the provided value is considered true
     *
     * This function checks the value against various true-like representations.
     *
     * @param mixed $value The value to check.
     * @return bool Returns true if the value is considered true, false otherwise.
     */
    public static function isTrue($value)
    {
        return DataUtil::isTrue($value);
    }

    /**
     * Generate a require statement for the app header
     *
     * This function returns a string to include the main application header.
     *
     * @return string The require statement for the app header.
     */
    public function getIncludeHeader()
    {
        return "require_once \$appInclude->mainAppHeader(__DIR__);";
    }
    
    /**
     * Generate a require statement for the app footer
     *
     * This function returns a string to include the main application footer.
     *
     * @return string The require statement for the app footer.
     */
    public function getIncludeFooter()
    {
        return "require_once \$appInclude->mainAppFooter(__DIR__);";
    }
    
    /**
     * Construct a label for an entity
     *
     * This function creates a language object for the specified entity name.
     *
     * @param string $entityName The name of the entity for which to create a label.
     * @return string The constructed entity label.
     */
    public function constructEntityLabel($entityName)
    {
        return self::VAR."appEntityLanguage = new AppEntityLanguage(new $entityName(), ".self::VAR.self::APP_CONFIG.", \$currentUser->getLanguageId());";
    }
    
    /**
     * Add tab indentation to HTML content
     *
     * This function indents each line of the given HTML string by a specified number
     * of spaces, based on the current indentation level.
     *
     * @param string $html The HTML string to modify.
     * @param integer $indent The number of spaces to indent each line.
     * @return string The modified HTML string with added indentation.
     */
    public function addTab($html, $indent = 2)
    {
        $html = PicoStringUtil::windowsCariageReturn($html);
        $lines = explode(self::NEW_LINE, $html);
        foreach($lines as $index=>$line)
        {
            $line2 = ltrim($line, " ");
            $nspace = strlen($line) - strlen($line2);
            if($nspace % $indent == 0)
            {
                $ntab = (int) $nspace / $indent;
                $lines[$index] = $this->createTab($ntab).$line2;
            } 
        }
        return implode(self::NEW_LINE, $lines);
    }
    
    /**
     * Create a string of tabs for indentation
     *
     * This function generates a string consisting of a specified number of tab characters.
     *
     * @param integer $n The number of tabs to create.
     * @return string The generated string of tab characters.
     */
    private function createTab($n)
    {
        $search = "";
        for($i = 0; $i<$n; $i++)
        {
            $search .= self::TAB1;
        }
        return $search;
    }

    /**
     * Add indentation to each line of HTML content
     *
     * This function prepends a specified number of tabs to each line of the given
     * HTML string.
     *
     * @param string $html The HTML string to modify.
     * @param integer $indent The number of tabs to add to each line.
     * @return string The modified HTML string with added indentation.
     */
    public function addIndent($html, $indent = 1)
    {
        $tab = "";
        for($i = 0; $i<$indent; $i++)
        {
            $tab .= self::TAB1;
        }
        $html = PicoStringUtil::windowsCariageReturn($html);
        $lines = explode(self::NEW_LINE, $html);
        foreach($lines as $index=>$line)
        {
            $lines[$index] = $tab.$line;
        }
        return implode(self::NEW_LINE_N, $lines);
    }

    /**
     * Wrap HTML content in specified wrapper tags
     *
     * This function wraps the given HTML string in a div element with a specific
     * class based on the type of wrapper specified.
     *
     * @param string $html The HTML string to wrap.
     * @param string $wrapper The type of wrapper to use (insert, update, detail, list).
     * @return string The wrapped HTML string.
     */
    public function addWrapper($html, $wrapper)
    {
        $html = rtrim($html, self::NEW_LINE).self::NEW_LINE;
        if($wrapper == self::WRAPPER_INSERT)
        {
            $html = 
            '<div class="page page-jambi page-insert">'.self::NEW_LINE 
            .self::TAB1.'<div class="jambi-wrapper">'.self::NEW_LINE // NOSONAR
            .$html
            .self::TAB1.'</div>'.self::NEW_LINE // NOSONAR
            .'</div>'.self::NEW_LINE;
        }
        else if($wrapper == self::WRAPPER_UPDATE)
        {
            $html = 
            '<div class="page page-jambi page-update">'.self::NEW_LINE
            .self::TAB1.'<div class="jambi-wrapper">'.self::NEW_LINE
            .$html
            .self::TAB1.'</div>'.self::NEW_LINE
            .'</div>'.self::NEW_LINE;
        }
        else if($wrapper == self::WRAPPER_DETAIL)
        {
            $html = 
            '<div class="page page-jambi page-detail">'.self::NEW_LINE
            .self::TAB1.'<div class="jambi-wrapper">'.self::NEW_LINE
            .$html
            .self::TAB1.'</div>'.self::NEW_LINE
            .'</div>'.self::NEW_LINE;
        }
        else if($wrapper == self::WRAPPER_LIST)
        {
            $html = 
            '<div class="page page-jambi page-list">'.self::NEW_LINE
            .self::TAB1.'<div class="jambi-wrapper">'.self::NEW_LINE
            .$html
            .self::TAB1.'</div>'.self::NEW_LINE
            .'</div>'.self::NEW_LINE;
        }
        return rtrim($html, self::NEW_LINE);
    }

   /**
     * Get a formatted string representation of the provided input.
     *
     * This method checks if the input string is alphanumeric (excluding underscores).
     * If the input is alphanumeric, it returns a string suitable for a field reference,
     * prefixed with `Field::of()->`. Otherwise, it returns the input string wrapped in quotes.
     *
     * @param string $str The input string to format.
     * @return string The formatted string representation.
     */
    public static function getStringOf($str)
    {
        if(ctype_alnum(str_replace('_', '', $str)))
        {
            return 'Field::of()->'.$str;
        }
        else
        {
            return '"'.$str.'"';
        }
    }

    /**
     * Create a sort order section in the session.
     *
     * This method generates a block of code to handle the sorting of entities
     * based on the new order provided in the input. It initializes the object
     * for the entity, checks for new order data, and updates the sort order
     * in the database.
     *
     * @param string $objectName The name of the object to sort.
     * @param string $entityName The name of the entity being sorted.
     * @param string $primaryKeyName The name of the primary key for the entity.
     * @return string The generated code for the sort order section.
     */
    public function createSortOrderSection($objectName, $entityName, $primaryKeyName)
    {
        $lines = array();
        $lines[] = "if(".self::VAR."inputPost".self::CALL_GET."UserAction() == UserAction::SORT_ORDER)";
        $lines[] = self::CURLY_BRACKET_OPEN;
        
        $lines[] = self::TAB1.'if($inputPost->getNewOrder() != null && $inputPost->countableNewOrder())';
        $lines[] = self::TAB1.self::CURLY_BRACKET_OPEN;
        $lines[] = self::TAB1.self::TAB1.'foreach($inputPost->getNewOrder() as $dataItem)';


        $lines[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;

        $lines[] = self::TAB1.self::TAB1.self::TAB1."try";
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.'if(is_string($dataItem))';

        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1.'$dataItem = new SetterGetter(json_decode($dataItem));';
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;

        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::VAR.'rowId = $dataItem->getPrimaryKey();';
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::VAR.'sortOrder = intval($dataItem->getSortOrder());';

        

        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.'$specification = PicoSpecification::getInstance()';
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1.'->addAnd(PicoPredicate::getInstance()->equals('.self::getStringOf(PicoStringUtil::camelize($primaryKeyName)).', $rowId))';
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1.'->addAnd($dataFilter)';
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1.';';

        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::VAR.$objectName.' = new '.$entityName.'(null, $database);';

        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::VAR.$objectName.'->where($specification)';
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1.'->setSortOrder($sortOrder)';
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.self::TAB1.'->update();';     
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;


        $lines[] = self::TAB1.self::TAB1.self::TAB1."catch(Exception \$e)";
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_OPEN;

        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1."// Do something here to handle exception";
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::TAB1.'error_log($e->getMessage());';
				
        $lines[] = self::TAB1.self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $lines[] = self::TAB1.self::TAB1.self::CURLY_BRACKET_CLOSE;
        $lines[] = self::TAB1.self::CURLY_BRACKET_CLOSE;

        $lines[] = self::TAB1.self::VAR.'currentModule->redirectToItself();';

        $lines[] = self::CURLY_BRACKET_CLOSE;   
        return implode("\r\n", $lines);
    }
    
    /**
     * Fix variable input by ensuring it has a leading dollar sign.
     *
     * This method checks if the input string contains a leading dollar sign.
     * If not, it adds one. It also ensures that double dollar signs are
     * replaced with a single dollar sign.
     *
     * @param string $input The input string to fix.
     * @return string The fixed input string.
     */
    public function fixVariableInput($input)
    {
        if(strpos($input, '$') === false)
        {
            // tidak menemukan $
            $sub1 = ltrim($input, " \r\n\t ");
            $sub2 = '$'.$sub1;
            $input = str_replace($sub1, $sub2, $input);
        }
        return str_replace('$$', '$', $input);
    }
    

    /**
     * Get the target value.
     *
     * This method retrieves the current value of the target property.
     *
     * @return string The target value.
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set the target value.
     *
     * This method updates the target property with the provided value.
     *
     * @param string $target The new target value.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get the update entity status.
     *
     * This method retrieves the current status of the updateEntity property.
     *
     * @return boolean True if the updateEntity is enabled, false otherwise.
     */
    public function getUpdateEntity()
    {
        return $this->updateEntity;
    }

    /**
     * Set the update entity status.
     *
     * This method updates the updateEntity property with the provided boolean value.
     *
     * @param boolean $updateEntity The new status for updateEntity.
     * @return self Returns the current instance for method chaining.
     */ 
    public function setUpdateEntity($updateEntity)
    {
        $this->updateEntity = $updateEntity;

        return $this;
    }
    
    /**
     * Check if a given value is a callable function.
     *
     * This method verifies if the provided value is set and can be called
     * as a function. It returns true if the value is callable, otherwise false.
     *
     * @param mixed $function The value to check for callable status.
     * @return bool Returns true if the value is callable, false otherwise.
     */
    public function isCallable($function)
    {
        return isset($function) && is_callable($function);
    }
}
