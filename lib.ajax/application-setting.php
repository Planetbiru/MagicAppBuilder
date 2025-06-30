<?php

use AppBuilder\AppArchitecture;
use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\MagicObject;
use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\Spicy;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$constShowActive = ' show active';
$constSelected = ' selected';
$constChecked = ' checked';
$inputGet = new InputGet();
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$applicationEntity = new EntityApplication(null, $databaseBuilder);
try
{

$applicationEntity->find($applicationId);

// Replace applicationId
$applicationId = $applicationEntity->getApplicationId();
$baseApplicationDirectory = $applicationEntity->getBaseApplicationDirectory();

// Load themes
$baseThemeDirectory = $baseApplicationDirectory . '/lib.themes';
$themes = [];
if (file_exists($baseThemeDirectory) && is_dir($baseThemeDirectory)) {
    // Fetch directory
    $directories = scandir($baseThemeDirectory);
    foreach ($directories as $dir) {
        $path = $baseThemeDirectory . '/' . $dir;
        if ($dir === '.' || $dir === '..') {
            continue;
        }
        if (is_dir($path)) {
            $themes[] = $dir;
        }
    }
}

$themeConfig = array();
foreach($themes as $theme)
{
    $themePath = $baseThemeDirectory."/$theme/info.yml";
    if(!file_exists($themePath))
    {
        continue;
    }
    $spicy = new Spicy();
    $themeConfig[$theme] = new MagicObject($spicy->load(file_get_contents($themePath)));
}
if(empty($themeConfig))
{
    $defaultThemeConfig = new MagicObject();
    $defaultThemeConfig->setId("default");
    $defaultThemeConfig->setName("Default");
    $defaultThemeConfig->setMultiLevelMenu(false);
    $themeConfig[] = $defaultThemeConfig;
}

$appBaseConfigPath = $activeWorkspace->getDirectory()."/applications";
$appConfig = new SecretObject();
$appConfig->setDatabase(new SecretObject());
$appConfig->setSessions(new SecretObject());

if($applicationId != null)
{
    $appConfigPath = $activeWorkspace->getDirectory()."/applications/".$applicationId."/default.yml";
    if(file_exists($appConfigPath))
    {
        $appConfig->loadYamlFile($appConfigPath, false, true, true);
    }
}

if($appConfig->getApplication() == null)
{
    $appConfig->setApplication(new SecretObject()); 
}
if($appConfig->getDatabase() == null)
{
    $appConfig->setDatabase(new SecretObject()); 
}
if($appConfig->getSessions() == null)
{
    $appConfig->setSessions(new SecretObject()); 
}
$cfgDatabase = new SecretObject($appConfig->getDatabase());
$cfgSession = new SecretObject($appConfig->getSessions());
$app = new SecretObject($appConfig->getApplication());

$databases = new MagicObject();

$databases->loadYamlString("
supportedDatabase:
  mariadb: 
    name: MariaDB
    base: nonfile-base
  mysql: 
    name: MySQL
    base: nonfile-base
  pgsql: 
    name: PostgreSQL
    base: nonfile-base
  sqlite: 
    name: SQLite
    base: file-base
", false, true, true);

$supportedDatabase = $databases->getSupportedDatabase();

$selectedDatabaseSystem = $supportedDatabase->get($cfgDatabase->getDriver());
if(isset($selectedDatabaseSystem) && $selectedDatabaseSystem instanceof MagicObject)
{
    $databases->setSelectedBase($selectedDatabaseSystem->getBase());
}
else
{
    $databases->setSelectedBase('nonfile-base');
}

$nameInIndonesian = array(
    "name" => "nama",
    "sort_order" => "sort_order",
    "active" => "aktif",
    "draft" => "draft",
    "waiting_for" => "waiting_for",
    "admin_create" => "admin_buat",
    "admin_edit" => "admin_ubah",
    "admin_ask_edit" => "admin_minta_ubah",
    "admin_delete" => "admin_hapus",
    "admin_approve" => "admin_persetujuan",
    "admin_restore" => "admin_pemulihan",
    "time_create" => "waktu_buat",
    "time_edit" => "waktu_ubah",
    "time_ask_edit" => "waktu_minta_ubah",
    "time_delete" => "waktu_hapus",
    "time_approve" => "waktu_persetujuan",
    "time_restore" => "waktu_pemulihan",
    "ip_create" => "ip_buat",
    "ip_edit" => "ip_ubah",
    "ip_ask_edit" => "ip_minta_ubah",
    "ip_delete" => "ip_hapus",
    "ip_approve" => "ip_persetujuan",
    "ip_restore" => "ip_pemulihan",
    "approval_id" => "approval_id",
    "approval_note" => "approval_note",
    "approval_status" => "approval_status",
    "restored" => "dipulihkan",
);

?><form name="formdatabase" id="formdatabase" method="post" action="" class="config-table">
    <div class="collapsible-card">
        <div id="accordion-setting" class="accordion">
        <div class="card">
            <div class="card-header" id="heading0">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse0" aria-expanded="true" aria-controls="collapse0">
                    Application
                    </button>
                </h5>
            </div>

            <div id="collapse0" class="collapse collapsed" aria-labelledby="heading0" data-parent="#accordion-setting">
                <div class="card-body">
                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td>Application Name</td>
                                <td><input class="form-control" type="text" name="application_name" value="<?php echo $app->getName(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Architecture</td>
                                <td>
                                    <select class="form-control" name="application_architecture">
                                        <option value="<?php echo AppArchitecture::MONOLITH;?>"<?php echo $app->getArchitecture() == AppArchitecture::MONOLITH ? $constSelected : ''; ?>>Monolith Application</option>
                                        <option value="<?php echo AppArchitecture::MICROSERVICES;?>"<?php echo $app->getArchitecture() == AppArchitecture::MICROSERVICES ? $constSelected : ''; ?>>Microservices Application</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Description</td>
                                <td><textarea class="form-control" name="description" spellcheck="false"><?php echo $app->getDescription(); ?></textarea></td>
                            </tr>
                            <tr>
                                <td>Application Directory</td>
                                <td><span class="directory-container input-with-checker"><input class="form-control" type="text" name="application_directory" value="<?php echo $app->getBaseApplicationDirectory(); ?>"></span></td>
                            </tr>
                            <tr>
                                <td>Application URL</td>
                                <td><span class="directory-container input-with-checker"><input class="form-control" type="text" name="application_url" value="<?php echo $app->getBaseApplicationUrl(); ?>"></span></td>
                            </tr>
                            <tr>
                                <td>Path</td>
                                <td class="paths">
                                    <table class="path-manager">
                                    <thead>
                                        <tr>
                                        <td>Name</td>
                                        <td>Path</td>
                                        <td width="20"></td>
                                        <td width="35"></td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $moduleLocation = $appConfig->getApplication() != null && $appConfig->getApplication()->getBaseModuleDirectory() != null && is_array($appConfig->getApplication()->getBaseModuleDirectory()) ? $appConfig->getApplication()->getBaseModuleDirectory() : [new SecretObject([
                                            "name"=>"root",
                                            "path"=>"/",
                                            "active"=>false
                                        ])];
                                       
                                        foreach($moduleLocation as $index=>$location)
                                        {
                                        ?>
                                        <tr>
                                        <td><input class="form-control" type="text" name="name[<?php echo $index;?>]" value="<?php echo $location->getName();?>"></td>
                                        <td><input class="form-control" type="text" name="path[<?php echo $index;?>]" value="<?php echo $location->getPath();?>"></td>
                                        <td><input type="checkbox" name="checked[<?php echo $index;?>]"<?php echo $location->isActive() ? $constChecked:'';?>></td>
                                        <td><button type="button" class="btn btn-danger path-remover"><i class="fa-regular fa-trash-can"></i></button></td>
                                        </tr>
                                        <?php
                                        }
                                        
                                        ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                        <td colspan="4">
                                        <button type="button" class="btn btn-primary add-path"><i class="fa-regular fa-plus"></i> Add</button>
                                        </td>
                                        </tr>
                                    </tfoot>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td>Multi-Level Menu</td>
                                <td><label for="multi_level_menu"><input type="checkbox" name="multi_level_menu" id="multi_level_menu" value="1"<?php echo $app->getMultiLevelMenu() ? $constChecked:'';?>> Multi-Level Menu</label></td>
                            </tr>
                            <tr>
                                <td>Active Theme</td>
                                <td>
                                    <select class="form-control" name="active_theme" id="active_theme">
                                        <?php
                                        foreach($themeConfig as $index=>$theme)
                                        {
                                            if($theme->getId() == $app->getActiveTheme())
                                            {
                                                $selected = ' selected';
                                            }
                                            else
                                            {
                                                $selected = '';
                                            }
                                            $disabled = '';
                                            if($app->getMultiLevelMenu() != $theme->getMultiLevelMenu())
                                            {
                                                $disabled = 'disabled';
                                            }
                                            ?>
                                            <option 
                                            value="<?php echo $theme->getId(); ?>" 
                                            data-multi-level-menu="<?php echo $theme->getMultiLevelMenu() ? 'true' : 'false'; ?>"
                                            <?php echo $selected;?>
                                            <?php echo $disabled;?>
                                            ><?php echo $theme->getName();?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="accordion" class="accordion">
        <div class="card">
            <div class="card-header" id="heading1">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                    Database
                    </button>
                </h5>
            </div>

            <div id="collapse1" class="collapse collapsed" aria-labelledby="heading1" data-parent="#accordion-setting">
                <div class="card-body">

                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                            <td>Driver</td>
                            <td>
                                <select class="form-control" name="database_driver" id="database_driver">
                                    <?php
                                    $arr = $supportedDatabase->valueArray();
                                    foreach($arr as $key=>$value)
                                    {
                                        ?>
                                        <option value="<?php echo $key;?>" <?php echo $cfgDatabase->getDriver() == $key ? $constSelected : ''; ?> data-base="<?php echo $value['base'];?>"><?php echo $value['name'];?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </td>
                            </tr>
                            <tr class="database-credential file-base" data-current-database-type="<?php echo $databases->getSelectedBase();?>">
                                <td>Database File Path</td>
                                <td><span class="directory-container input-with-checker" data-isfile="true"><input class="form-control" type="text" name="database_database_file_path" id="database_database_file_path" value="<?php echo $cfgDatabase->getDatabaseFilePath(); ?>"></span></td>
                            </tr>
                            <tr class="database-credential nonfile-base" data-current-database-type="<?php echo $databases->getSelectedBase();?>">
                                <td>Host</td>
                                <td><input class="form-control" type="text" name="database_host" id="database_host" value="<?php echo $cfgDatabase->getHost(); ?>"></td>
                            </tr>
                            <tr class="database-credential nonfile-base" data-current-database-type="<?php echo $databases->getSelectedBase();?>">
                                <td>Port</td>
                                <td><input class="form-control" type="number" min="0" step="1" name="database_port" id="database_port" value="<?php echo $cfgDatabase->getPort(); ?>"></td>
                            </tr>
                            <tr class="database-credential nonfile-base" data-current-database-type="<?php echo $databases->getSelectedBase();?>">
                                <td>Username</td>
                                <td><input class="form-control" type="text" name="database_username" id="database_username" value="<?php echo $cfgDatabase->getUsername(); ?>"></td>
                            </tr>
                            <tr class="database-credential nonfile-base" data-current-database-type="<?php echo $databases->getSelectedBase();?>">
                                <td>Password</td>
                                <td><input class="form-control" name="database_password" type="password" id="database_password" value=""></td>
                            </tr>
                            <tr class="database-credential nonfile-base" data-current-database-type="<?php echo $databases->getSelectedBase();?>">
                                <td>Name</td>
                                <td><input class="form-control" type="text" name="database_database_name" id="database_database_name" value="<?php echo $cfgDatabase->getDatabaseName(); ?>"></td>
                            </tr>
                            <tr class="database-credential nonfile-base" data-current-database-type="<?php echo $databases->getSelectedBase();?>">
                                <td>Schema</td>
                                <td><input class="form-control" type="text" name="database_database_schema" id="database_database_schema" value="<?php echo $cfgDatabase->getDatabaseSchema(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Time Zone</td>
                                <td><input class="form-control" type="text" name="database_time_zone" id="database_time_zone" value="<?php echo $cfgDatabase->getTimeZone(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Time Zone System</td>
                                <td><input class="form-control" type="text" name="database_time_zone_system" id="database_time_zone_system" value="<?php echo $cfgDatabase->getTimeZoneSystem(); ?>"></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <button class="btn btn-primary" type="button" id="test-database-connection"><i class="fa-solid fa-right-left"></i> Test Connection</button>
                                    <button class="btn btn-success" type="button" id="create-database" style="display: none;"><i class="fas fa-database"></i> Create Database</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="heading2">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapse2">
                    Session and Cookie
                    </button>
                </h5>
            </div>

            <div id="collapse2" class="collapse collapsed" aria-labelledby="heading2" data-parent="#accordion-setting">
                <div class="card-body">

                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <tr>
                                <td>Session Name</td>
                                <td><input class="form-control" type="text" name="sessions_name" id="sessions_name" value="<?php echo $cfgSession->getName(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Session Life Time</td>
                                <td><input class="form-control" type="number" min="0" step="1" name="sessions_max_life_time" id="sessions_max_life_time" value="<?php echo $cfgSession->getMaxLifeTime(); ?>"></td>
                            </tr>
                            <tr>
                                <td>Session Save Handler</td>
                                <td>
                                    <select class="form-control" name="sessions_save_handler" id="sessions_save_handler">
                                    <option value="files" <?php echo $cfgSession->getSaveHandler() == 'files' ? $constSelected : ''; ?>>files</option>
                                    <option value="redis" <?php echo $cfgSession->getSaveHandler() == 'redis' ? $constSelected : ''; ?>>redis</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Session Save Path</td>
                                <td><input class="form-control" type="text" name="sessions_save_path" id="sessions_save_path" value="<?php echo $cfgSession->getSavePath();?>"></td>
                            </tr>

                            <tr>
                                <td>Cookie Path</td>
                                <td><input class="form-control" type="text" name="sessions_cookie_path" id="sessions_cookie_path" value="<?php echo $cfgSession->getCookiePath();?>"></td>
                            </tr>
                            <tr>
                                <td>Cookie Domain</td>
                                <td><input class="form-control" type="text" name="sessions_cookie_domain" id="sessions_cookie_domain" value="<?php echo $cfgSession->getCookieDomain();?>"></td>
                            </tr>
                            <tr>
                                <td>Cookie Secure</td>
                                <td><label for="sessions_cookie_secure"><input type="checkbox" name="sessions_cookie_secure" id="sessions_cookie_secure" value="1"<?php echo $cfgSession->isCookieSecure() ? $constChecked:'';?>> Yes</label></td>
                            </tr>
                            <tr>
                                <td>Cookie HTTP Only</td>
                                <td><label for="sessions_cookie_http_only"><input type="checkbox" name="sessions_cookie_http_only" id="sessions_cookie_http_only" value="1"<?php echo $cfgSession->isCookieSecure() ? $constChecked:'';?>> Yes</label></td>
                            </tr>
                            <tr>
                                <td>Cookie Same Site</td>
                                <td>
                                    <select class="form-control" name="sessions_cookie_samesite" id="sessions_cookie_samesite">
                                        <option value="None" <?php echo $cfgSession->getCookieSameSite() == 'None' ? $constSelected : ''; ?>>None</option>
                                        <option value="Lax" <?php echo $cfgSession->getCookieSameSite() == 'Lax' ? $constSelected : ''; ?>>Lax</option>
                                        <option value="Strict" <?php echo $cfgSession->getCookieSameSite() == 'Strict' ? $constSelected : ''; ?>>Strict</option>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header" id="heading3">
                <h5 class="mb-0">
                    <button type="button" class="btn" data-toggle="collapse" data-target="#collapse3" aria-expanded="true" aria-controls="collapse3">
                    Reserved Columns
                    </button>
                </h5>
            </div>

            <div id="collapse3" class="collapse collapsed" aria-labelledby="heading3" data-parent="#accordion-setting">
                <div class="card-body">
                    <table class="config-table" width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                            <?php
                            $entityConstant = new SecretObject($appConfig->getEntityInfo());
                            if (empty($entityConstant->valueArray())) {
                            $entityConstant = new SecretObject($builderConfig->getEntityInfo());
                            }
                            $arr = $entityConstant->valueArray(true);

                            if (!empty($arr)) {
                            foreach ($arr as $key => $value) {
                            ?>
                                <tr>
                                <td><?php echo $key ?></td>
                                <td><input class="form-control entity-info-key" type="text" name="entity_info_<?php echo $key ?>" value="<?php echo $value; ?>" data-original="<?php echo $key; ?>" data-indonesian="<?php echo $nameInIndonesian[$key]; ?>"></td>
                                </tr>
                            <?php
                            }
                            }
                            ?>
                            <tr>
                                <td></td>
                                <td>
                                    <button class="btn btn-primary use-indonesian" type="button"><i class="fas fa-check"></i> Use Indonesian</button>
                                    <button class="btn btn-primary use-original" type="button"><i class="fas fa-check"></i> Use Original</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <input type="hidden" name="application_id" value="<?php echo $applicationId;?>">
                </div>
            </div>
        </div>
    </div>
</form>
<?php
}
catch(Exception $e)
{
    // Do nothing
}