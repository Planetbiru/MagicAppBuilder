<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\ScriptGenerator;
use AppBuilder\Util\ChartDataUtil;
use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicAdmin\Entity\Data\Admin;
use MagicAdmin\Entity\Data\Application;
use MagicAdmin\Entity\Data\ApplicationCreated;
use MagicObject\SecretObject;
use MagicObject\Request\InputPost;
use MagicObject\Constants\PicoHttpStatus;
use MagicObject\Constants\PicoMime;
use MagicObject\Database\PicoDatabaseType;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$async = false;
$currentTimeZone = date_default_timezone_get();
if(empty($currentTimeZone))
{
    $currentTimeZone = 'Asia/Jakarta';
}

$phpIni = new SecretObject($builderConfig->getPhpIni());
// Set max_execution_time
ini_set('max_execution_time', $phpIni->getMaxExecutionTime() != null ? intval($phpIni->getMaxExecutionTime()) : 600);

// Set memory_limit
// Check if memory_limit is set and is not empty
if($phpIni->getMemoryLimit() != null && $phpIni->getMemoryLimit() != "")
{
    // Set the memory limit to the value specified in the configuration
    ini_set('memory_limit', $phpIni->getMemoryLimit());
}

$inputPost = new InputPost();
$newAppId = trim($inputPost->getId());

$dir2 = FileDirUtil::normalizePath($activeWorkspace->getDirectory()."/applications/$newAppId");
if (!file_exists($dir2)) 
{
    mkdir($dir2, 0755, true);
}
$baseApplicationUrl = $inputPost->getApplicationUrl();
$author = $entityAdmin->getName();
$adminId = $entityAdmin->getAdminId();

$baseApplicationDirectory = $inputPost->getApplicationDirectory();
$baseApplicationDirectory = preg_replace('/[^:A-Za-z0-9\\-\/\\\\]/', '', $baseApplicationDirectory);
$baseApplicationDirectory = str_replace("\\", "/", $baseApplicationDirectory);
$baseApplicationDirectory = preg_replace('/\/+/', '/', $baseApplicationDirectory);
$baseApplicationDirectory = rtrim($baseApplicationDirectory, "/");

$path2 = $dir2 . "/default.yml";

$existing = new SecretObject();

if(file_exists($path2))
{
    $existing->loadYamlFile($path2, false, true, true);
}
else
{
    $existing->loadYamlFile($configTemplatePath, false, true, true);
}

$newApp = new SecretObject();

$application = new SecretObject();
try
{
    $application = $existing->getApplication() != null ? $existing->getApplication() : new SecretObject();
}
catch(Exception $e)
{
    // do nothing
}

if(!file_exists($baseApplicationDirectory))
{
    mkdir($baseApplicationDirectory, 0755, true);
}
$workspaceId = trim($inputPost->getWorkspaceId());
$applicationName = trim($inputPost->getApplicationName());
$applicationArchitecture = trim($inputPost->getApplicationArchitecture());
$applicationDirectory = trim($baseApplicationDirectory);
$applicationDescription = trim($inputPost->getApplicationDescription());
$onlineInstallation = $inputPost->getComposerOnline() == 'true' || $inputPost->getComposerOnline() == 1;
$projectDirectory = $dir2;

$application->setId($newAppId);
$application->setName($applicationName);
$application->setDescription($applicationDescription);
$application->setArchitecture($applicationArchitecture);

$appBaseNamespace = trim($inputPost->getApplicationNamespace());
$namespace = preg_replace('/[^A-Za-z0-9]/', '', $appBaseNamespace);
$application->setBaseApplicationNamespace($namespace);
$application->setBaseApplicationDirectory($applicationDirectory);
$application->setBaseApplicationUrl($baseApplicationUrl);
$application->setBaseEntityNamespace($appBaseNamespace . "\\Entity");
$application->setBaseEntityDataNamespace($appBaseNamespace . "\\Entity\\Data");
$application->setBaseEntityAppNamespace($appBaseNamespace . "\\Entity\\App");
$application->setBaseEntityTrashNamespace($appBaseNamespace . "\\Entity\\Trash");
$application->setBaseEntityDirectory($baseApplicationDirectory . "/inc.lib/classes");
$application->setBaseLanguageDirectory($baseApplicationDirectory . "/inc.lang");

$databaseFilePath = $baseApplicationDirectory . "/inc.database/database.sqlite";
$databaseDirectory = dirname($databaseFilePath);
if(!file_exists($databaseDirectory))
{
    mkdir($databaseDirectory, 0755, true);
}

$systemModulePath = "/";
$paths = $inputPost->getPaths();
foreach ($paths as $idx => $val) {
    $paths[$idx]['active'] = $paths[$idx]['active'] == 'true';
    if($paths[$idx]['active'] == 'true')
    {
        $systemModulePath = $paths[$idx]['path'];
    }
}

$application->setBaseModuleDirectory($paths);
$application->setBaseIncludeDirectory("inc.app");
$application->setBaseAssetDirectory("lib.assets");

$composer = new SecretObject();
$composer->setBaseDirectory('inc.lib');
$composer->setPsr0(true);
$composer->setPsr4(false);

$psr4BaseDirectory = new SecretObject();
$psr4BaseDirectory = array(
    array(
        'namespace' => $appBaseNamespace,
        'directory' => 'classes'
    )
);

$composer->setPsr0BaseDirecory($psr4BaseDirectory);
$composer->setPsr4BaseDirecory(null);

$application->setComposer($composer);

$application->setMagicApp(array(
    'version' => trim($inputPost->getMagicAppVersion())
));

$application->setActiveTheme('default');
$application->setHeaderFile('header.php');
$application->setFooterFile('footer.php');
$application->setForbiddenPage('403.php');
$application->setNotFoundPage('404.php');

// For offline installation
$composerPath = dirname(__DIR__) . "/inc.lib/vendor/planetbiru/magic-app/composer.json";
$composerJson = json_decode(file_get_contents($composerPath), true);
$magicObjectVersion = $composerJson['require']['planetbiru/magic-object'];
$magicObjectVersion = str_replace("^", '', $magicObjectVersion);

$application->setMagicObject(array(
    'version' => trim($magicObjectVersion)
));

$newApp->setApplication($application);

$entityInfo = array(
    'name' => 'name',
    'active' => 'active',
    'draft' => 'draft',
    'waiting_for' => 'waiting_for',
    'admin_create' => 'admin_create',
    'admin_edit' => 'admin_edit',
    'admin_ask_edit' => 'admin_ask_edit',
    'admin_delete' => 'admin_delete',
    'admin_restore' => 'admin_restore',
    'time_create' => 'time_create',
    'time_edit' => 'time_edit',
    'time_ask_edit' => 'time_ask_edit',
    'time_delete' => 'time_delete',
    'time_restore' => 'time_restore',
    'ip_create' => 'ip_create',
    'ip_edit' => 'ip_edit',
    'ip_ask_edit' => 'ip_ask_edit',
    'ip_delete' => 'ip_delete',
    'ip_restore' => 'ip_restore',
    'sort_order' => 'sort_order',
    'approval_id' => 'approval_id',
    'approval_note' => 'approval_note',
    'approval_status' => 'approval_status',
    'restored' => 'restored'
);

$entityApvInfo = array(
    'approval_status' => 'approval_status'
);

$databaseConfig = array(
    'driver' => PicoDatabaseType::DATABASE_TYPE_SQLITE,
    'database_file_path' => $databaseFilePath,
    'host' => '',
    'port' => 0,
    'username' => '',
    'password' => '',
    'database_name' => '',
    'database_schema' => '',
    'time_zone' => $currentTimeZone,
    'time_zone_system' => $currentTimeZone,
    'connection_timeout' => 10
);

$newApp->setEntityInfo($entityInfo);
$newApp->setEntityApvInfo($entityApvInfo);

$newApp->setCurrentAction(array(
    'user_function' => '$currentAction->getUserId()',
    'time_function' => '$currentAction->getTime()',
    'ip_function' => '$currentAction->getIp()'
));

$paginationConfig = new SecretObject(array(
    'page_size' => 20,
    'page_range' => 3,
    'prev' => '<i class="fa-solid fa-angle-left"></i>',
    'next' => '<i class="fa-solid fa-angle-right"></i>',
    'first' => '<i class="fa-solid fa-angles-left"></i>',
    'last' => '<i class="fa-solid fa-angles-right"></i>'
));

$newApp->setDatabase($databaseConfig);
$newApp->setData($paginationConfig);
$newApp->setGlobalVariableDatabase('database');

$cfgSession = new SecretObject();
$cfgSession->setName('MAGICSESSION');
$cfgSession->setMaxLifetime(86400);
$cfgSession->setSaveHandler('files');
$cfgSession->setSavePath('');
$cfgSession->setCookiePath('/');
$cfgSession->setCookieDomain('');
$cfgSession->setCookieSecure(false);
$cfgSession->setCookieHttpOnly(false);
$cfgSession->setCookieSameSite('Strict');

$newApp->setSessions($cfgSession);


// Get application menu from YAML file instead of database
$newApp->setDevelopmentMode(true);
$newApp->setDebugMode(true);
$newApp->setDebugModeError(true);
$newApp->setDebugModeErrorLog(true);

// Use dummy user instead of database
$newApp->setBypassRole(true);

// Set access localhost only
// This is for testing purpose only, 
// should be set to false in production, 
// online installation, 
// and when `bypassRole` set to false`
$newApp->setAccessLocalhostOnly(true);

$newApp->setDateFormatDetail('j F Y H:i:s');



$resetPasswordContainer = new SecretObject();
$resetPasswordContainer->loadYamlString(
'
resetPassword:
  email:
    host: smtp.domain.tld
    port: 587
    auth: true
    username: noreply
    password: password
    encryption: tls
    subject: Reset Password
    from:
      name: '.$applicationName.'
      email: noreply@domain.tld
    baseUrl: '.$baseApplicationUrl.'
'    
);

$newApp->setAccountSecurity([
    'algorithm' => 'sha1',
    'salt' => ''
]);

$newApp->setResetPassword($resetPasswordContainer->getResetPassword());

$ipForwarding = new SecretObject();
$ipForwarding->setEnabled(false);
$ipForwarding->setHeaders(array(
    'CF-Connecting-IP',
    'X-Forwarded-For',
    'True-Client-IP'
));

$newApp->setIpForwarding($ipForwarding);
$newApp->setMultiLevelMenu(false);

$configYaml = (new SecretObject($newApp))->dumpYaml();
file_put_contents($path2, $configYaml);

$now = date("Y-m-d H:i:s");

$entityApplication = new EntityApplication(null, $databaseBuilder);
$entityApplication->setApplicationId($newAppId);
$entityApplication->setName($applicationName);
$entityApplication->setDescription($applicationDescription);
$entityApplication->setProjectDirectory($projectDirectory);
$entityApplication->setBaseApplicationDirectory($applicationDirectory);
$entityApplication->setUrl($baseApplicationUrl);
$entityApplication->setArchitecture($applicationArchitecture);
$entityApplication->setAuthor($author);
$entityApplication->setAdminId($adminId);
$entityApplication->setWorkspaceId($workspaceId);
$entityApplication->setAdminCreate($adminId);
$entityApplication->setAdminEdit($adminId);   
$entityApplication->setTimeCreate($now);
$entityApplication->setTimeEdit($now);
$entityApplication->setIpCreate($_SERVER['REMOTE_ADDR']);
$entityApplication->setIpEdit($_SERVER['REMOTE_ADDR']);
$entityApplication->setActive(true);

try
{
    $entityApplication->setApplicationStatus("created");
    $entityApplication->insert();
}
catch(Exception $e)
{
    // Do nothing    
}

// params:
// 1. response body
// 2. mime type
// 3. headers
// 4. http status code
// 5. is async
ResponseUtil::sendResponse("{}", PicoMime::APPLICATION_JSON, null, PicoHttpStatus::HTTP_OK, true);

$app = new SecretObject();
$newApp->loadYamlFile($path2, false, true, true);

$appConf = $newApp->getApplication();
$baseDir = $appConf->getBaseApplicationDirectory();

$dir3 = $baseDir."/inc.cfg";
if(!file_exists($dir3))
{
    mkdir($dir3, 0755, true);
}
$path3 = $dir3."/application.yml";
file_put_contents($path3, $configYaml);

$path4 = $dir3."/menu.yml";

file_put_contents($path4, "
menu:
  - 
    title: Home
    icon: fa fa-home
    submenu:
      - 
        title: Home
        href: index.php
        icon: fa fa-home
        specialAccess: true
    active: false
  - 
    title: Reference
    icon: fa fa-book
    submenu: [ ]
    active: false
  - 
    title: Master
    icon: fa fa-folder
    submenu: [ ]
    active: true
  - 
    title: Settings
    icon: fa fa-cog
    submenu:
      - 
        title: Admin
        href: admin.php
        icon: fa fa-user
        specialAccess: true
        active: true
      - 
        title: Admin Level
        href: admin-level.php
        icon: fa fa-user
        specialAccess: true
        active: true
      - 
        title: Admin Role
        href: admin-role.php
        icon: fa fa-user
        specialAccess: true
        active: true
      - 
        title: Module
        href: module.php
        icon: fa fa-cog
        specialAccess: true
        active: true
      - 
        title: Module Group
        href: module-group.php
        icon: fa fa-cog
        specialAccess: true
        active: true
      - 
        title: Message Folder
        href: message-folder.php
        icon: fa fa-folder
        specialAccess: false
        active: true
      - 
        title: Data Restoration
        href: data-restoration.php
        icon: fa fa-trash-restore
        specialAccess: false
        active: true
    active: false

");
$path5 = "$dir3/.htaccess";
file_put_contents($path5, "Options -Indexes
<Files .htaccess>
  Order Allow,Deny
  Deny from all
</Files>");
$dir6 = $baseDir."/inc.database";
if(!file_exists($dir6))
{
    mkdir($dir6, 0755, true);
}

$dir7 = $baseDir."/lib.upload";
if(!file_exists($dir7))
{
    mkdir($dir7, 0755, true);
}
if($async)
{
    $pathPreparation = '"'.FileDirUtil::normalizationPath(__DIR__) . "/application-preparation.php".'"';
    $param1 = http_build_query($_COOKIE);
    $param2 = $newAppId;
    $param3 = $onlineInstallation ? 'true' : 'false';
    $param4 = $magicObjectVersion;
    $encoded = base64_encode(json_encode(array($param1, $param2, $param3, $param4)));
  
    if(PHP_OS == 'WINNT')
    {
        $pathPreparation = str_replace("/", "\\", $pathPreparation);
        $command = "php $pathPreparation $encoded > NUL 2>&1";
        shell_exec($command);
    }
    else
    {
        $command = "php $pathPreparation $encoded > /dev/null 2>&1";
        exec($command);
    }
}
else
{
    $scriptGenerator = new ScriptGenerator();
    $scriptGenerator->prepareApplication($builderConfig, $newApp->getApplication(), $baseDir, $onlineInstallation, $magicObjectVersion, $entityApplication, $systemModulePath);
}

try
{
    $admin = new Admin(null, $databaseBuilder);
    $admin
        ->setAdminId($adminId)
        ->setWorkspaceId($workspaceId)
        ->setApplicationId($newAppId)
        ->update();
    ChartDataUtil::updateChartData(new Application(null, $databaseBuilder), new ApplicationCreated(null, $databaseBuilder), date('Ym'));
}
catch(Exception $e)
{
    error_log($e->getMessage());
}