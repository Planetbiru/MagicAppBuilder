<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

try
{
	$inputPost = new InputPost();
	$appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    $applicationToUpdate = new EntityApplication(null, $databaseBuilder);

    $applicationToUpdate->find($appId);

	$applicationName = $inputPost->getName();
	$description = $inputPost->getDescription();
	$architecture = $inputPost->getArchitecture();
	
	$databaseConfig = new SecretObject($inputPost->getDatabase());
	$sessionsConfig = new SecretObject($inputPost->getSessions());
    $accountSecurity = new SecretObject($inputPost->getAccountSecurity());
	
    // fix data type
    $databaseConfig->setPort(intval($databaseConfig->getPort()));
    $databaseConfig->setConnectionTimeout(intval($databaseConfig->getConnectionTimeout()));
    $sessionsConfig->setMaxLifetime(intval($sessionsConfig->getMaxLifetime()));

    $appConfig = new SecretObject(null);

    $projectDirectory = $applicationToUpdate->getProjectDirectory();
    $baseApplicationDirectory = $applicationToUpdate->getBaseApplicationDirectory();

    $yml = $projectDirectory . "/default.yml";

    if(file_exists($yml))
    {
        $appConfig->loadYamlFile($yml, false, true, true);
    }
    else
    {
        $appConfig->loadYamlFile($configTemplatePath, false, true, true);
    }
    $app = $appConfig->getApplication();

    if(!isset($app))
    {
        $app = new SecretObject();
    }

	if($appConfig->getApplication() != null)
	{
		$appConfig->getApplication()->setName($applicationName);
		$appConfig->getApplication()->setDescription($description);
		$appConfig->getApplication()->setArchitecture($architecture);

        if($inputPost->getApplicationDirectory() != null)
        {
            $baseApplicationDirectory = $inputPost->getApplicationDirectory();
            $baseApplicationDirectory = rtrim($baseApplicationDirectory);
            $baseEntityDirectory = $baseApplicationDirectory . '/inc.lib/classes';
            $baseLanguageDirectory = $baseApplicationDirectory . '/inc.lang';

            $appConfig->getApplication()->setBaseApplicationDirectory($baseApplicationDirectory);
            $appConfig->getApplication()->setBaseEntityDirectory($baseEntityDirectory);
            $appConfig->getApplication()->setBaseLanguageDirectory($baseLanguageDirectory);
        }
        if($inputPost->getApplicationUrl() != null)
        {
            $baseApplicationUrl = $inputPost->getApplicationUrl();
            $appConfig->getApplication()->setBaseApplicationUrl($baseApplicationUrl);
        }
	}

    $moduleLocations = $inputPost->getModuleLocation();
    foreach($moduleLocations as $i=>$v)
    {
        $moduleLocations[$i]['active'] = $moduleLocations[$i]['active'] == "true";
    }
    $appConfig->getApplication()->setBaseModuleDirectory($moduleLocations);

    $existingDatabase = $appConfig->getDatabase();
    if($existingDatabase == null)
    {
        $existingDatabase = $databaseConfig;
    }
    else
    {
        $keys = array_keys($databaseConfig->valueArray());
        foreach($keys as $key)
        {
            if($databaseConfig->get($key) != "")
            {
                $existingDatabase->set($key, $databaseConfig->get($key));
            }
        }
    }

    $existingSessions = $appConfig->getSessions();
    if($existingSessions == null)
    {
        $existingSessions = $sessionsConfig;
    }
    else
    {
        $keys = array_keys($sessionsConfig->valueArray());
        foreach($keys as $key)
        {
            $camelKey = PicoStringUtil::camelize($key);
            if($sessionsConfig->get($key) != "" || $camelKey != "savePath" || $camelKey != "cookiePath")
            {
                $existingSessions->set($key, $sessionsConfig->get($key));
            }
        }
    }

    $existingAccountSecurity = $appConfig->getAccountSecurity();
    if($existingAccountSecurity == null)
    {
        $existingAccountSecurity = $accountSecurity;
    }
    else
    {
        $keys = array_keys($accountSecurity->valueArray());
        foreach($keys as $key)
        {
            $camelKey = PicoStringUtil::camelize($key);
            if($accountSecurity->get($key) != "" || $camelKey != "salt")
            {
                $existingAccountSecurity->set($key, $accountSecurity->get($key));
            }
        }
    }

    $existingSessions->setCookieSecure($existingSessions->isCookieSecure());
    $existingSessions->setCookieHttpOnly($existingSessions->isCookieHttpOnly());
    
    $sessionPath = trim($existingSessions->getSavePath());
    if(strlen($sessionPath) > 1 && !file_exists($sessionPath))
    {
        mkdir($sessionPath, 0755, true);
    }
	
	$appConfig->setDatabase($existingDatabase);
	$appConfig->setSessions($existingSessions);
    $appConfig->setAccountSecurity($existingAccountSecurity);
	$appConfig->setEntityInfo($inputPost->getEntityInfo());
    $appConfig->getApplication()->setMultiLevelMenu($inputPost->getMultiLevelMenu() == 1 || $inputPost->getMultiLevelMenu() == 'true');
    $appConfig->getApplication()->setActiveTheme($inputPost->getActiveTheme());
    $yamlData = $appConfig->dumpYaml();

    // Update application config in workspace direcory
    file_put_contents($yml, $yamlData);

	$workspaceDirectory = $activeWorkspace->getDirectory();
    if(PicoStringUtil::startsWith($workspaceDirectory, "./"))
    {
        $workspaceDirectory = dirname(__DIR__) . "/" . substr($workspaceDirectory, 2);
    }

    // Update application config in application directory
    $rootDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory());

    if(file_exists($rootDir))
    {
        $appDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.app");
        $classesDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.lib/classes");
        $vendorDir = FileDirUtil::normalizePath($applicationToUpdate->getBaseApplicationDirectory()."/inc.lib/vendor");
        $rootDirExists = true;
        $appDirExists = file_exists($appDir);
        $classesDirExists = file_exists($classesDir);
        $vendorDirExists = file_exists($vendorDir);

        if(!$rootDirExists || !$appDirExists || !$classesDirExists || !$vendorDirExists)
        {
            $applicationValid = false;
        }
        else
        {
            $applicationValid = true;
        }
        $yml2 = FileDirUtil::normalizePath($rootDir."/inc.cfg/application.yml");

        $appConfig2 = new SecretObject();
        $appConfig2->loadYamlFile($yml2, true, true, true);
        $appConfig2->setDatabase($existingDatabase);
        $appConfig2->setSessions($existingSessions);
        $appConfig2->setAccountSecurity($appConfig->getAccountSecurity());
        $appConfig2->setEntityInfo($appConfig->getEntityInfo());

        if($appConfig2->getApplication() == null)
        {
            $appConfig2->setApplication(new SecretObject());
        }
        $appConfig2->getApplication()->setName($appConfig->getApplication()->getName());
        $appConfig2->getApplication()->setDescription($appConfig->getApplication()->getDescription());
        $appConfig2->getApplication()->setArchitecture($appConfig->getApplication()->getArchitecture());
        $appConfig2->getApplication()->setBaseModuleDirectory($appConfig->getApplication()->getBaseModuleDirectory());
        $appConfig2->getApplication()->setMultiLevelMenu($inputPost->getMultiLevelMenu() == 1 || $inputPost->getMultiLevelMenu() == 'true');
        $appConfig2->getApplication()->setActiveTheme($inputPost->getActiveTheme());
        $yamlData2 = $appConfig2->dumpYaml();
        file_put_contents($yml2, $yamlData2);
    }
    else
    {
        $rootDirExists = false;
        $applicationValid = false;
    }

    // Update the application
    $applicationToUpdate
        ->setName($applicationName)
        ->setDescription($description)
        ->setArchitecture($architecture)
        ->setBaseApplicationDirectory($baseApplicationDirectory)
        ->setDirectoryExists($rootDirExists)
        ->setApplicationValid($applicationValid)
        ->update();
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
ResponseUtil::sendJSON(new stdClass);