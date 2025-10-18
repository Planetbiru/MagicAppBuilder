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
$baseApplicationDirectory = FileDirUtil::normalizePath($baseApplicationDirectory);
$baseApplicationDirectory = trim($baseApplicationDirectory);
$baseApplicationDirectory = rtrim($baseApplicationDirectory, "/");
$newAppId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

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
$originalYamlPath = FileDirUtil::normalizePath($baseApplicationDirectory."/inc.cfg/application.yml");
$dirs[] = $originalYamlPath;

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
$configToCheck->loadYamlFile($originalYamlPath, false, true, true);
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
        
        $newProjectDir = FileDirUtil::normalizePath($activeWorkspace->getDirectory()."/applications/$newAppId");
        $newYamlPath = FileDirUtil::normalizePath($newProjectDir."/default.yml");
        
        // Update Application ID in YAML file
        
        $configToCheck->getApplication()->setId($newAppId);
        
        $configContent = $configToCheck->dumpYaml();
        
        // Update application YAML in application directory
        file_put_contents($originalYamlPath, $configContent);
        
        // Create directories
        
        if(!file_exists($newProjectDir))
        {
            mkdir($newProjectDir, 0755, true);
        }
        
        // Copy application directory to workspace
        file_put_contents($newYamlPath, $configContent);
        
        // Success
        
        // Now, create the application to database
        $now = date("Y-m-d H:i:s");
        
        $applicationName = $application->getName();
        $applicationDescription = $application->getDescription();
        $projectDirectory = $newProjectDir;
        $applicationDirectory = $baseApplicationDirectory;
        $baseApplicationUrl = $application->getUrl();
        $applicationArchitecture = $application->getArchitecture();
        $author = $entityAdmin->getName();
        $adminId = $entityAdmin->getAdminId();
        $workspaceId = $activeWorkspace->getWorkspaceId();
        

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
        catch(Exception $e)
        {
            // Send response
            ResponseUtil::sendResponse(
                json_encode([
                    'success' => false, 
                    'message' => 'Failed to recreate application: '.$e->getMessage()
                ]), 
                PicoMime::APPLICATION_JSON,
                null,
                PicoHttpStatus::HTTP_OK
            );
            exit;
        }
        finally
        {   
            // Clean up if needed    
        }
        
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

