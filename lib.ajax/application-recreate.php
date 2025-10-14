<?php

// Recreate application in the workspace based on existing directory

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\Util\FileDirUtil;
use MagicObject\Constants\PicoHttpStatus;
use MagicObject\Constants\PicoMime;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();
$baseApplicationDirectory = $inputPost->getBaseApplicationDirectory(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
$baseApplicationDirectory = trim($baseApplicationDirectory);


if (empty($baseApplicationDirectory)) {
    ResponseUtil::sendResponse(
        json_encode(['error' => 'Base application directory is required']),
        PicoMime::APPLICATION_JSON,
        null,
        PicoHttpStatus::HTTP_OK
    );
    exit;
}
$dirs = array();

$dirs[] = FileDirUtil::normalizePath($baseApplicationDirectory);
$dirs[] = FileDirUtil::normalizePath($baseApplicationDirectory."/inc.app");
$dirs[] = FileDirUtil::normalizePath($baseApplicationDirectory."/inc.cfg");
$dirs[] = FileDirUtil::normalizePath($baseApplicationDirectory."/inc.lib/classes");
$dirs[] = FileDirUtil::normalizePath($baseApplicationDirectory."/inc.lib/vendor");
$yamlPath = FileDirUtil::normalizePath($baseApplicationDirectory."/inc.cfg/application.yml");
$dirs[] = $yamlPath;

// Check directory

foreach($dirs as $idx=>$p)
{
    if(!file_exists($p))
    {
        ResponseUtil::sendResponse(
            json_encode([
                'success' => false, 
                'message' => 'Application is not valid because a mandatory file or directory is missing.'
            ]),
            PicoMime::APPLICATION_JSON,
            null,
            PicoHttpStatus::HTTP_NOT_FOUND
        );
        exit;
    }
}

$basename = trim(basename($baseApplicationDirectory));

$configToCheck = new SecretObject();
$configToCheck->loadYamlFile($yamlPath, false, true, true);
$application = $configToCheck->getApplication();
if($application != null && $application->getId() != null)
{

    $appId = trim($application->getId());
    if($basename != $appId)
    {
        ResponseUtil::sendResponse(
            json_encode([
                'success' => false,
                'message' => "Basename ($basename) does not match application ID ($appId). If you want to import it, please rename it first.",
                'data' => [
                    'path' => $baseApplicationDirectory,
                    'applicationName' => $application->getName(),
                    'applicationId' => $appId
                ]
            ]),
            PicoMime::APPLICATION_JSON,
            null,
            PicoHttpStatus::HTTP_OK
        );
        exit;
    }

    $existingApplication = new EntityApplication(null, $databaseBuilder);

    try
    {
        $inputPost = new InputPost();
        $existingApplication->find($appId);
        ResponseUtil::sendResponse(
            json_encode([
                'success' => false,
                'message' => "Application ID already exists ($appId). If you want to import it, please rename it first.",
                'data' => [
                    'path' => $baseApplicationDirectory,
                    'applicationName' => $application->getName(),
                    'applicationId' => $appId
                ]
            ]),
            PicoMime::APPLICATION_JSON,
            null,
            PicoHttpStatus::HTTP_OK
        );
        exit;
    }
    catch(Exception $e)
    {
        
        $dir2 = FileDirUtil::normalizePath($activeWorkspace->getDirectory()."/applications/$newAppId");
        $yml2 = FileDirUtil::normalizePath($dir2."/inc.cfg/default.yml");
        
        // Create directories
        
        if(!file_exists($dir2))
        {
            mkdir($dir2, 0755, true);
        }
        
        // Copy file
        copy($yamlPath, $yml2);
        
        // Send response
        ResponseUtil::sendResponse(
            json_encode([
                'success' => true, 
                'message' => 'Application is recreated.',
                'data' => [
                    'path' => $baseApplicationDirectory,
                    'applicationName' => $application->getName(),
                    'applicationId' => $appId
                ]
            ]),
            PicoMime::APPLICATION_JSON,
            null,
            PicoHttpStatus::HTTP_OK
        );
        exit;
    }
}
else
{
    // Send response
    ResponseUtil::sendResponse(
        json_encode([
            'success' => false, 
            'message' => 'Application is not valid'
        ]),
        PicoMime::APPLICATION_JSON,
        null,
        PicoHttpStatus::HTTP_OK
    );
    exit;
}

