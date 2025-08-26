<?php

use AppBuilder\EntityInstaller\EntityApplication;
use AppBuilder\EntityInstaller\EntityWorkspace;
use MagicObject\Exceptions\FileNotFoundException;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$temps = array();

try {
    $inputPost = new InputPost();
    $workspaceId = $inputPost->getWorkspaceId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    $workspaceToExport = new EntityWorkspace(null, $databaseBuilder);
    $workspaceToExport->findOneByWorkspaceId($workspaceId);

    $applicationLoader = new EntityApplication(null, $databaseBuilder);
    $pageData = $applicationLoader->findByWorkspaceIdAndActive($workspaceId, true);

    if($pageData->getTotalResult())
    {
        // Create temporary zip file
        $zipFilename = tempnam(sys_get_temp_dir(), 'ws_export_');
        $temps[] = $zipFilename;
        $zip = new ZipArchive();

        if ($zip->open($zipFilename, ZipArchive::OVERWRITE) !== true) {
            throw new FileNotFoundException("Unable to create ZIP archive");
        }
        foreach($pageData->getResult() as $applicationToExport)
        {
        
            $applicationName = $applicationToExport->getName();
            $projectDirectory = $applicationToExport->getProjectDirectory();

            if (!is_dir($projectDirectory)) {
                throw new FileNotFoundException("Invalid project directory");
            }

            // Create temporary zip file
            $zipFilenameApp = tempnam(sys_get_temp_dir(), 'app_export_');
            $temps[] = $zipFilenameApp;
            $zipApp = new ZipArchive();

            if ($zipApp->open($zipFilenameApp, ZipArchive::OVERWRITE) !== true) {
                throw new FileNotFoundException("Unable to create ZIP archive");
            }

            $projectDirLength = strlen($projectDirectory) + 1;

            // Recursive iteration to include all files and folders
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($projectDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                $filePath = $file->getPathname();

                // Skip symlinks (optional, for security)
                if (is_link($filePath)) {
                    continue;
                }

                $relativePath = substr($filePath, $projectDirLength);
                if ($file->isDir()) {
                    $zipApp->addEmptyDir($relativePath);
                } else {
                    $zipApp->addFile($filePath, $relativePath);
                }
            }

            $zipApp->close();

            // Sanitize output filename
            $fileNameInZip = $applicationToExport->getApplicationId() . '.zip';

            $zip->addFile($zipFilenameApp, $fileNameInZip);
        }
    }

    $zip->close();

    $workspaceName = $workspaceToExport->getName();
    $downloadFileName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $workspaceName) . '.zip';

    // Send the zip to browser for download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $downloadFileName . '"');
    header('Content-Length: ' . filesize($zipFilename));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    readfile($zipFilename);
    unlink($zipFilename);
    exit;
}
catch(Exception $e) {
    http_response_code(500);
    echo "Failed to export application: " . htmlspecialchars($e->getMessage());
}
