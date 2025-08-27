<?php

// This script is generated automatically by MagicAppBuilder
// Visit https://github.com/Planetbiru/MagicAppBuilder

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use MagicAdmin\AppEntityLanguageImpl;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicApp\PicoModule;
use MagicAdmin\AppIncludeImpl;
use MagicAdmin\AppUserPermissionExtended;
use MagicAdmin\Entity\Data\Application;
use MagicAdmin\Entity\Data\Workspace;
use MagicApp\AppFormBuilder;
use MagicApp\Field;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\PicoFilterConstant;

require_once __DIR__ . "/inc.app/auth.php";

$inputGet = new InputGet();
$inputPost = new InputPost();

$currentModule = new PicoModule($appConfig, $database, $appModule, "/", "application-config", $appLanguage->getApplicationConfiguration());
$userPermission = new AppUserPermissionExtended($appConfig, $database, $appUserRole, $currentModule, $currentUser);
$appInclude = new AppIncludeImpl($appConfig, $currentModule);
if(!$userPermission->allowedAccess($inputGet, $inputPost))
{
	require_once $appInclude->appForbiddenPage(__DIR__);
	exit();
}

$dataFilter = null;

require_once $appInclude->mainAppHeader(__DIR__);

?>
    <div class="filter-section">
        <form action="" method="get" class="filter-form">
            <span class="filter-group">
                <span class="filter-group">
                <span class="filter-label"><?php echo $appLanguage->getWorkspace();?></span>
                <span class="filter-control">
                    <select class="form-control" name="workspace_id" data-value="<?php echo $inputGet->getWorkspaceId();?>" onchange="this.form.submit()">
                        <option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
                        <?php echo AppFormBuilder::getInstance()->createSelectOption(new Workspace(null, $database), 
                        PicoSpecification::getInstance()
                            ->addAnd(new PicoPredicate(Field::of()->active, true))
                            ->addAnd(new PicoPredicate(Field::of()->draft, false)), 
                        PicoSortable::getInstance()
                            ->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
                            ->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
                        Field::of()->workspaceId, Field::of()->name, $inputGet->getWorkspaceId())
                        ; ?>
                    </select>
                </span>
            </span>
                <span class="filter-label"><?php echo $appLanguage->getApplication();?></span>
                <span class="filter-control">
                    <select class="form-control" name="application_id" onchange="this.form.submit()">
                        <option value=""><?php echo $appLanguage->getLabelOptionSelectOne();?></option>
                        <?php echo AppFormBuilder::getInstance()->createSelectOption(new Application(null, $database), 
                        PicoSpecification::getInstance()
                            ->addAnd(new PicoPredicate(Field::of()->active, true))
                            ->addAnd(new PicoPredicate(Field::of()->draft, false))
                            ->addAnd(new PicoPredicate(Field::of()->workspaceId, $inputGet->getWorkspaceId())), 
                        PicoSortable::getInstance()
                            ->add(new PicoSort(Field::of()->sortOrder, PicoSort::ORDER_TYPE_ASC))
                            ->add(new PicoSort(Field::of()->name, PicoSort::ORDER_TYPE_ASC)), 
                        Field::of()->applicationId, Field::of()->name, $inputGet->getApplicationId())
                        ; ?>
                    </select>
                </span>
            </span>
            
            <span class="filter-group">
                <button type="submit" class="btn btn-success"><?php echo $appLanguage->getButtonLoad();?></button>
            </span>
        </form>
    </div>
    <div class="data-section" data-ajax-support="true" data-ajax-name="main-data">
    <?php
    $inputGet = new InputGet();
	$appId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    if(!empty($appId))
    {
    $applicationToUpdate = new EntityApplication(null, $databaseBuilder);

    try
    {
        $applicationToUpdate->find($appId);
        $projectDirectory = $applicationToUpdate->getProjectDirectory();
        $baseApplicationDirectory = $applicationToUpdate->getBaseApplicationDirectory();
        $configPath = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.cfg/application.yml");
        if(file_exists($configPath))
        {
            $yaml = file_get_contents($configPath);
    ?>

        <!-- Input / Output -->
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label for="yamlInput"><?php echo $appLanguage->getYamlInput();?></label>
          <textarea id="yamlInput" class="form-control" rows="5" spellcheck="false"><?php echo $yaml;?></textarea>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label for="yamlOutput"><?php echo $appLanguage->getYamlOutput();?></label>
          <textarea id="yamlOutput" class="form-control" rows="5" spellcheck="false"></textarea>
        </div>
      </div>
    </div>

    <!-- Env Mapping / Script -->
    <div class="row">
      <div class="col-md-6">
        <div class="form-group">
          <label for="envMapping"><?php echo $appLanguage->getEnvironmentVariablesMapping();?></label>
          <textarea id="envMapping" class="form-control" rows="5" readonly spellcheck="false"></textarea>
        </div>
      </div>
      <div class="col-md-6">
        <div class="form-group">
          <label for="envScript"><?php echo $appLanguage->getEnvironmentScript();?></label>
          <textarea id="envScript" class="form-control" rows="5" readonly spellcheck="false"></textarea>
        </div>
      </div>
    </div>

    <!-- Properties to Encrypt -->
    <div class="form-group">
      <label for="propsInput"><?php echo $appLanguage->getPropertiesToEncryptDecrypt();?></label>
      <textarea id="propsInput" class="form-control" rows="5" spellcheck="false"></textarea>
    </div>

    <!-- OS Selection -->
    <div class="form-group">
      <label for="osSelect"><?php echo $appLanguage->getOperatingSystem();?></label>
      <select id="osSelect" class="form-control">
        <option value="linux"><?php echo $appLanguage->getOperatingSystemLinuxMacos();?></option>
        <option value="windows"><?php echo $appLanguage->getOperatingSystemWindows();?></option>
      </select>
    </div>

    <!-- Encryption Key -->
    <div class="form-group">
      <label for="keyInput"><?php echo $appLanguage->getEncryptionKey();?></label>
      <div class="input-group">
        <input type="text" id="keyInput" class="form-control" minlength="32" maxlength="32">
        <div class="input-group-append">
          <button class="btn btn-outline-secondary" onclick="document.querySelector('#keyInput').value = generateRandomHex32()">
            <?php echo $appLanguage->getButtonGenerate();?>
          </button>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <button class="btn btn-info mr-2" onclick="generatePlaceholder()"><?php echo $appLanguage->getButtonGeneratePlaceholder();?></button>
    <button class="btn btn-primary mr-2" onclick="encryptYamlEnv()"><?php echo $appLanguage->getButtonEncryptGeneratePlaceholder();?></button>
    <button class="btn btn-success mr-2" onclick="encryptYaml()"><?php echo $appLanguage->getButtonEncrypt();?></button>
    <button class="btn btn-warning text-white" onclick="decryptYaml()"><?php echo $appLanguage->getButtonDecrypt();?></button>

    <script src="js/js-yaml.min.js"></script>
    <script src="js/crypto-js.min.js"></script>
    <script src="js/config-encryptor.min.js"></script>
<?php
        }
    }
    catch(Exception $e)
    {
        ?>
        <div class="alert alert-danger"><?php echo $appInclude->printException($e);?></div>
        <?php
    }
}
else
{
    ?>
    <div class="alert alert-primary"><?php echo $appLanguage->getMessageSelectApplication();?></div>
    <?php
}
?>
    </div>
    <?php
require_once $appInclude->mainAppFooter(__DIR__);
