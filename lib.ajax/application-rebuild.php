<?php

// Rebuild application structure based on existing configuration

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\ScriptGenerator;
use AppBuilder\Util\FileDirUtil;
use MagicObject\SecretObject;
use MagicObject\Constants\PicoHttpStatus;
use MagicObject\Constants\PicoMime;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$appId = trim($appId);

if (empty($appId)) {
    ResponseUtil::sendResponse(
        json_encode(['success' => false, 'message' => 'Application ID is required']),
        PicoMime::APPLICATION_JSON,
        null,
        PicoHttpStatus::HTTP_OK
    );
    exit;
}

$applicationToUpdate = new EntityApplication(null, $databaseBuilder);

try
{
	  $inputPost = new InputPost();
    $applicationToUpdate->find($appId);
}
catch(Exception $e)
{
    ResponseUtil::sendResponse(
        json_encode(['success' => false, 'message' => 'Application not found']),
        PicoMime::APPLICATION_JSON,
        null,
        PicoHttpStatus::HTTP_OK
    );
    exit;
}


// Read the existing application configuration YAML
$appDir = FileDirUtil::normalizePath($activeWorkspace->getDirectory()."/applications/$appId");
$configPath = "$appDir/default.yml";

if (!file_exists($configPath)) {
    ResponseUtil::sendResponse(
        json_encode(['success' => false, 'message' => 'Configuration file not found: '.$configPath]),
        PicoMime::APPLICATION_JSON,
        null,
        PicoHttpStatus::HTTP_NOT_FOUND
    );
    exit;
}

// Load configuration from YAML
$appConfig = new SecretObject();
$appConfig->loadYamlFile($configPath, false, true, true);

// Get the "application" node
$application = $appConfig->getApplication();
$baseDir = $application->getBaseApplicationDirectory();
$baseUrl = $application->getBaseApplicationUrl();

// Ensure the application directory exists
if (!file_exists($baseDir)) {
    mkdir($baseDir, 0755, true);
}

// Prepare the ScriptGenerator
$builderConfig = isset($builderConfig) ? $builderConfig : null;
$onlineInstallation = false;

if($application->getMagicObject() != null && $application->getMagicObject()->getVersion() != null)
{
    $magicObjectVersion = $application->getMagicObject()->getVersion();
}
else
{
    // Get MagicApp and MagicObject versions from composer.json

    $composerPath1 = dirname(__DIR__) . "/inc.lib/composer.json";
    $composerJson1 = json_decode(file_get_contents($composerPath1), true);

    $composerPath2 = dirname(__DIR__) . "/inc.lib/vendor/planetbiru/magic-app/composer.json";
    $composerJson2 = json_decode(file_get_contents($composerPath2), true);
    
    $magicAppVersion = $composerJson1['require']['planetbiru/magic-app'];
    
    $magicObjectVersion = $composerJson2['require']['planetbiru/magic-object'];
    $magicObjectVersion = str_replace("^", '', $magicObjectVersion);
    $magicAppVersion = str_replace("^", '', $magicAppVersion);
    $application->setMagicObject(new SecretObject(array(
        'version' => $magicObjectVersion
    )));
    $application->setMagicApp(new SecretObject(array(
        'version' => $magicAppVersion
    )));
}

$entityApplication = new EntityApplication(null, $databaseBuilder);
$systemModulePath = '/';

if(file_exists($yml))
{
    $appConfig = new SecretObject();
    $appConfig->loadYamlFile($yml, false, true, true);
    if($application->issetBaseModuleDirectory())
    {
        if($application->getBaseModuleDirectory())
        {
            $baseModuleDirs = $application->getBaseModuleDirectory();
            foreach($baseModuleDirs as $dir)
            {
                if($dir->isActive() && $dir->issetPath())
                {
                    // get the first active module path
                    $systemModulePath = $dir->getPath();
                    break;
                }
            }
        }
    }
}

// Run the generator
$scriptGenerator = new ScriptGenerator();
$scriptGenerator->prepareApplication(
    $builderConfig,
    $application,
    $baseDir,
    $onlineInstallation,
    $magicObjectVersion,
    $entityApplication,
    $systemModulePath
);

$rootDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory());
$classesDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.lib/classes");
$vendorDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.lib/vendor");

// Create/update application.yml

$yml2 = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.cfg/application.yml");

$appConfig2 = new SecretObject($appConfig);

$yamlData2 = $appConfig2->dumpYaml();
if(!file_exists(dirname($yml2)))
{
    mkdir(dirname($yml2), 0755, true);
}
file_put_contents($yml2, $yamlData2);
$menuPath = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.cfg/menu.yml");

$htaccessContent = 'Options -Indexes
<Files .htaccess>
  Order Allow,Deny
  Deny from all
</Files>';
$htaccessPath1 = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.cfg/.htaccess");

file_put_contents($htaccessPath1, $htaccessContent);


file_put_contents($menuPath, "
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


if(!file_exists($rootDir."/inc.lang"))
{
    mkdir($rootDir."/inc.lang", 0755, true);
}
if(!file_exists($rootDir."/inc.database"))
{
    mkdir($rootDir."/inc.database", 0755, true);
}
if(!file_exists($rootDir."/lib.upload"))
{
    mkdir($rootDir."/lib.upload", 0755, true);
}

$applicationToUpdate->setApplicationValid(true)
  ->setDirectoryExists(true)
  ->update();

// Send response
ResponseUtil::sendResponse(
    json_encode(['success' => true, 'message' => 'Application prepared successfully']),
    PicoMime::APPLICATION_JSON,
    null,
    PicoHttpStatus::HTTP_OK
);
