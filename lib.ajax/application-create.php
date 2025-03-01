<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\ScriptGenerator;
use AppBuilder\Util\FileDirUtil;
use MagicAdmin\Entity\Data\Admin;
use MagicObject\SecretObject;
use MagicObject\Request\InputPost;
use MagicObject\Constants\PicoHttpStatus;
use MagicObject\Constants\PicoMime;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$newAppId = trim($inputPost->getId());

$dir2 = FileDirUtil::normalizePath($activeWorkspace->getDirectory()."/applications/$newAppId");
if (!file_exists($dir2)) 
{
    mkdir($dir2, 0755, true);
}

$author = $entityAdmin->getName();
$adminId = $entityAdmin->getAdminId();

$baseApplicationDirectory = $inputPost->getDirectory();
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
$applicationName = trim($inputPost->getName());
$applicationArchitecture = trim($inputPost->getArchitecture());
$applicationDirectory = trim($baseApplicationDirectory);
$applicationDescription = trim($inputPost->getDescription());
$composerOnline = $inputPost->getComposerOnline() == 1;
$projectDirectory = $dir2;

$application->setId($newAppId);
$application->setName($applicationName);
$application->setDescription($applicationDescription);
$application->setArchitecture($applicationArchitecture);

$appBaseNamespace = trim($inputPost->getNamespace());
$namespace = preg_replace('/[^A-Za-z0-9]/', '', $appBaseNamespace);
$application->setBaseApplicationNamespace($namespace);
$application->setBaseApplicationDirectory($applicationDirectory);
$application->setBaseEntityNamespace($appBaseNamespace . "\\Entity");
$application->setBaseEntityDataNamespace($appBaseNamespace . "\\Entity\\Data");
$application->setBaseEntityAppNamespace($appBaseNamespace . "\\Entity\\App");
$application->setBaseEntityDirectory($baseApplicationDirectory . "/inc.lib/classes");
$application->setBaseLanguageDirectory($baseApplicationDirectory . "/inc.lang");

$databaseFilePath = $baseApplicationDirectory . "/inc.database/database.sqlite";
$databaseDirectory = dirname($databaseFilePath);
if(!file_exists($databaseDirectory))
{
    mkdir($databaseDirectory, 0755, true);
}

$paths = $inputPost->getPaths();
foreach ($paths as $idx => $val) {
    $paths[$idx]['active'] = $paths[$idx]['active'] == 'true';
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
    'time_create' => 'time_create',
    'time_edit' => 'time_edit',
    'time_ask_edit' => 'time_ask_edit',
    'time_delete' => 'time_delete',
    'ip_create' => 'ip_create',
    'ip_edit' => 'ip_edit',
    'ip_ask_edit' => 'ip_ask_edit',
    'ip_delete' => 'ip_delete',
    'sort_order' => 'sort_order',
    'approval_id' => 'approval_id',
    'approval_note' => 'approval_note',
    'approval_status' => 'approval_status'
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
    'time_zone' => 'Asia/Jakarta'
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

$configYaml = (new SecretObject($newApp))->dumpYaml();
file_put_contents($path2, $configYaml);

PicoResponse::sendResponse("{}", PicoMime::APPLICATION_JSON, null, PicoHttpStatus::HTTP_OK, true);

$scriptGenerator = new ScriptGenerator();

$app = new SecretObject();
$newApp->loadYamlFile($path2, false, true, true);

$appConf = $newApp->getApplication();
$baseDir = $appConf->getBaseApplicationDirectory();
$scriptGenerator->prepareApplication($builderConfig, $newApp->getApplication(), $baseDir, $composerOnline);

$dir3 = $baseDir."/inc.cfg";
if(!file_exists($dir3))
{
    mkdir($dir3, 0755, true);
}
$path3 = $dir3."/application.yml";
file_put_contents($path3, $configYaml);

$now = date("Y-m-d H:i:s");

$entityApplication = new EntityApplication(null, $databaseBuilder);
try
{
    $entityApplication->setApplicationId($newAppId);
    $entityApplication->setName($applicationName);
    $entityApplication->setDescription($applicationDescription);
    $entityApplication->setProjectDirectory($projectDirectory);
    $entityApplication->setBaseApplicationDirectory($applicationDirectory);
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
    $entityApplication->save();

    $admin = new Admin(null, $databaseBuilder);
    $admin
        ->setAdminId($adminId)
        ->setWorkspaceId($workspaceId)
        ->setApplicationId($newAppId)
        ->update();
}
catch(Exception $e)
{
    error_log($e->getMessage());
}