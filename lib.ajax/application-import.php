<?php

use AppBuilder\AppImporter;
use AppBuilder\EntityInstaller\EntityWorkspace;
use AppBuilder\Util\FileDirUtil;
use MagicObject\Request\InputFiles;
use MagicObject\Request\InputPost;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/auth.php";

// Initialize POST and FILE input handlers
$inputPost = new InputPost();
$inputFile = new InputFiles();

/**
 * Updates the application configuration object with a new application ID and directory paths.
 *
 * This function modifies the application ID and updates the corresponding base directories
 * (entity and language) within the configuration object. If reset password configuration exists
 * and contains a base URL for email, it also updates that URL to reflect the new application ID.
 *
 * @param SecretObject $applicationConfig The current application configuration object to update.
 * @param string $applicationId The new application ID to assign.
 * @param string $applicationName The new application name to assign.
 * @param string $baseApplicationDirectory The base directory of the application used to derive paths.
 * @return SecretObject The updated application configuration object.
 */
function updateApplicationConfig($applicationConfig, $applicationId, $applicationName, $baseApplicationDirectory)
{
    if($applicationConfig->issetApplication())
    {
        $oldApplicationId = $applicationConfig->getApplication()->getId();
        $baseEntityDirectory = rtrim($baseApplicationDirectory, "\\/")."/inc.lib/classes";
        $baseLanguageDirectory = rtrim($baseApplicationDirectory, "\\/")."/inc.lang";
        $applicationConfig->getApplication()
            ->setId($applicationId)
            ->setName($applicationName)
            ->setBaseApplicationDirectory($baseApplicationDirectory)
            ->setBaseEntityDirectory($baseEntityDirectory)
            ->setBaseLanguageDirectory($baseLanguageDirectory)
            ;
        
        $baseUrl = $applicationConfig->getApplication()->getBaseApplicationUrl();
        if(!isset($baseUrl) || strpos($baseUrl, "://") === false)
        {
            // Fix base application URL
            $baseUrl = "http://" . $_SERVER['SERVER_NAME'] . "/" . basename(rtrim($baseApplicationDirectory, '/'));
            $applicationConfig->getApplication()->setBaseApplicationUrl($baseUrl);
        }

        if($applicationConfig->issetResetPassword() && $applicationConfig->getResetPassword()->getEmail())
        {
            $resetPasswordUrl = $applicationConfig->getResetPassword()->getEmail()->getBaseUrl();
            $resetPasswordUrl = str_replace($oldApplicationId, $applicationId, $resetPasswordUrl);
            $applicationConfig->getResetPassword()->getEmail()
                ->setBaseUrl($resetPasswordUrl);
        }
    }
    return $applicationConfig;
}
header('Content-Type: application/json');

if ($inputPost->getUserAction() == 'preview' && $inputFile->file) {
    $file1 = $inputFile->file;
    if(class_exists('ZipArchive') === false) {
        throw new Exception("ZipArchive class is not available. Please ensure the PHP zip extension is installed and enabled.");
    }
    foreach ($file1->getAll() as $fileItem) {
        $temporaryName = $fileItem->getTmpName();
        $name = $fileItem->getName();
        $size = $fileItem->getSize();

        $zip = new ZipArchive();

        if ($zip->open($temporaryName) === true) {
            $yamlContent = $zip->getFromName('default.yml');

            if ($yamlContent !== false) {
                $applicationConfig = new MagicObject();
                $applicationConfig->loadYamlString($yamlContent, false, true, true);

                if ($applicationConfig->issetApplication()) {
                    echo json_encode([
                        "status" => "success",
                        "message" => "Application configuration loaded successfully.",
                        "data" => [
                            "application_name" => $applicationConfig->getApplication()->getName(),
                            "application_id" => $applicationConfig->getApplication()->getId(),
                            "base_application_directory" => $applicationConfig->getApplication()->getBaseApplicationDirectory()
                        ]
                    ]);
                } else {
                    echo json_encode([
                        "status" => "warning",
                        "message" => "The file 'default.yml' does not contain application information."
                    ]);
                }
            } else {
                echo json_encode([
                    "status" => "warning",
                    "message" => "The file 'default.yml' was not found in the ZIP archive."
                ]);
            }

            $zip->close();
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to open the ZIP file."
            ]);
        }
    }
}
else if ($inputPost->getUserAction() == 'import' && $inputFile->file) {
    $file1 = $inputFile->file;

    $applicationId = $inputPost->getApplicationId();
    $applicationName = $inputPost->getApplicationName();
    $baseApplicationDirectory = $inputPost->getBaseApplicationDirectory();
    $workspaceId = $activeWorkspace->getWorkspaceId();
    $workspace = new EntityWorkspace(null, $databaseBuilder);
    try {
        $workspace->find($workspaceId);
        $adminId = $entityAdmin->getAdminId();
        $author = $entityAdmin->getName();
        $workspaceDirectory = FileDirUtil::normalizePath($workspace->getDirectory() . "/applications");
        if(class_exists('ZipArchive') === false) {
            throw new Exception("ZipArchive class is not available. Please ensure the PHP zip extension is installed and enabled.");
        }
        foreach ($file1->getAll() as $fileItem) {
            $temporaryName = $fileItem->getTmpName();
            $name = $fileItem->getName();
            $size = $fileItem->getSize();

            $zip = new ZipArchive();

            if ($zip->open($temporaryName) === true) {
                $yamlContent = $zip->getFromName('default.yml');

                if ($yamlContent !== false) {
                    $applicationConfig = new MagicObject();
                    $applicationConfig->loadYamlString($yamlContent, false, true, true);

                    if ($applicationConfig->issetApplication()) {

                        $applicationConfig = updateApplicationConfig($applicationConfig, $applicationId, $applicationName, $baseApplicationDirectory);

                        $projectDirectory = $workspaceDirectory . "/" . $applicationId;
                        $yamlContent = $applicationConfig->dumpYaml();

                        if (!is_dir($projectDirectory)) {
                            mkdir($projectDirectory, 0755, true);
                        }
                        $zip->extractTo($projectDirectory);
                        $yml = $projectDirectory . '/default.yml';
                        file_put_contents($yml, $yamlContent);

                        // Ensure that file is exists
                        if(file_exists($yml))
                        {
                            $applicationImporter = new AppImporter($databaseBuilder);
                            $applicationImporter->importApplication($yml, $projectDirectory, $workspaceId, $author, $adminId);
                        }

                        echo json_encode([
                            "status" => "success",
                            "message" => "Application '$applicationName' has been imported successfully.",
                            "data" => [
                                "application_name" => $applicationName,
                                "application_id" => $applicationId,
                                "base_application_directory" => $baseApplicationDirectory
                            ]
                        ]);
                    } else {
                        echo json_encode([
                            "status" => "warning",
                            "message" => "The file 'default.yml' does not contain application information.",
                            "data" => [
                                "application_name" => $applicationName,
                                "application_id" => $applicationId,
                                "base_application_directory" => $baseApplicationDirectory
                            ]
                        ]);
                    }
                } else {
                    echo json_encode([
                        "status" => "warning",
                        "message" => "The file 'default.yml' was not found in the ZIP archive.",
                        "data" => [
                            "application_name" => $applicationName,
                            "application_id" => $applicationId,
                            "base_application_directory" => $baseApplicationDirectory
                        ]
                    ]);
                }

                $zip->close();
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Failed to open the ZIP file.",
                    "data" => [
                        "application_name" => $applicationName,
                        "application_id" => $applicationId,
                        "base_application_directory" => $baseApplicationDirectory
                    ]
                ]);
            }
        }
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid workspace.",
            "data" => [
                "application_name" => $applicationName,
                "application_id" => $applicationId,
                "base_application_directory" => $baseApplicationDirectory
            ]
        ]);
    }
}


