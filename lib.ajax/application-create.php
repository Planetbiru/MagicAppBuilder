<?php

use AppBuilder\Generator\ScriptGenerator;
use MagicObject\SecretObject;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Constants\PicoHttpStatus;
use MagicObject\Response\PicoResponse;

require_once dirname(__DIR__) . "/inc.app/app.php";

$inputPost = new InputPost();
$path = $workspaceDirectory."/application-list.yml";
$dir = dirname($path);
if (!file_exists($dir)) 
{
    mkdir($dir, 0755, true);
}
$newAppId = trim($inputPost->getId());

$baseApplicationDirectory = $inputPost->getDirectory();
$baseApplicationDirectory = preg_replace('/[^:A-Za-z0-9\/\\\\]/', '', $baseApplicationDirectory);
$baseApplicationDirectory = str_replace("\\", "/", $baseApplicationDirectory);
$baseApplicationDirectory = preg_replace('/\/+/', '/', $baseApplicationDirectory);
$baseApplicationDirectory = rtrim($baseApplicationDirectory, "/");

$dir2 = $workspaceDirectory."/applications/$newAppId";

if (!file_exists($dir2)) 
{
    mkdir($dir2, 0755, true);
}
$path2 = $dir2 . "/default.yml";

$application = array(
    'id' => $newAppId,
    'name' => trim($inputPost->getName()),
    'type' => trim($inputPost->getType()),
    'description' => trim($inputPost->getDescription()),
    'documentRoot' => trim($baseApplicationDirectory),
    'author' => trim($inputPost->getAuthor()),
    'selected' => false
);

$existing = new SecretObject();
$existing->loadYamlFile($path);
$existingApplication = $existing->valueArray();
$replaced = false;
if (isset($existingApplication) && is_array($existingApplication)) 
{
    foreach ($existingApplication as $key => $val) 
    {
        if ($val['id'] == $newAppId) 
        {
            $existingApplication[$key] = $application;
            $replaced = true;
        }
    }
    if (!$replaced) 
    {
        $existingApplication[] = $application;
    }
} 
else 
{
    $existingApplication = array($application);
}

file_put_contents($path, (new SecretObject($existingApplication))->dumpYaml());

$newApp = new SecretObject();

$application = new SecretObject();
try
{
    $newApp->loadYamlFile($path2);
    $application = $newApp->getApplication() != null ? $newApp->getApplication() : new SecretObject();
}
catch(Exception $e)
{
    // do nothing
}

if(!file_exists($baseApplicationDirectory))
{
    mkdir($baseApplicationDirectory, 0755, true);
}

$application->setId($newAppId);
$application->setName(trim($inputPost->getName()));
$application->setType(trim($inputPost->getType()));

$namespace = preg_replace('/[^A-Za-z0-9]/', '', trim($inputPost->getNamespace()));
$application->setBaseApplicationNamespace($namespace);
$application->setBaseApplicationDirectory(trim($baseApplicationDirectory));
$application->setBaseEntityNamespace(trim($inputPost->getNamespace()) . "\\Entity");
$application->setBaseEntityDataNamespace(trim($inputPost->getNamespace()) . "\\Entity\\Data");
$application->setBaseEntityAppNamespace(trim($inputPost->getNamespace()) . "\\Entity\\App");
$application->setBaseEntityDirectory(trim($baseApplicationDirectory) . "/inc.lib/classes");
$application->setBaseLanguageDirectory(trim($baseApplicationDirectory) . "/inc.lang");

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
        'namespace' => trim($inputPost->getNamespace()),
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
    'time_create' => 'time_create',
    'time_edit' => 'time_edit',
    'time_ask_edit' => 'time_ask_edit',
    'ip_create' => 'ip_create',
    'ip_edit' => 'ip_edit',
    'ip_ask_edit' => 'ip_ask_edit',
    'sort_order' => 'sort_order',
    'approval_id' => 'approval_id',
    'approval_note' => 'approval_note',
    'approval_status' => 'approval_status'
);

$newApp->setEntityInfo($entityInfo);
$newApp->setCurrentAction(array(
    'user_function' => '$currentAction->getUserId()',
    'time_function' => '$currentAction->getTime()',
    'ip_function' => '$currentAction->getIp()'
));
$newApp->setGlobalVariableDatabase('database');

file_put_contents($path2, (new SecretObject($newApp))->dumpYaml());

PicoResponse::sendResponse(new stdClass, false, null, PicoHttpStatus::HTTP_OK, true);

$scriptGenerator = new ScriptGenerator();

$app = new SecretObject();
$newApp->loadYamlFile($path2, false, true, true);

$appConf = $newApp->getApplication();
$baseDir = $appConf->getBaseApplicationDirectory();
$scriptGenerator->prepareApplication($newApp->getApplication(), $baseDir);
