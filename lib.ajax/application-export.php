<?php

use AppBuilder\EntityInstaller\EntityApplication;
use MagicObject\Exceptions\FileNotFoundException;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

try {
    $inputPost = new InputPost();
    $appId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

    $applicationToExport = new EntityApplication(null, $databaseBuilder);
    $applicationToExport->findOneByApplicationId($appId);

    $applicationName = $applicationToExport->getName();
    $projectDirectory = $applicationToExport->getProjectDirectory();

    if (!is_dir($projectDirectory)) {
        throw new FileNotFoundException("Invalid project directory");
    }

    // Create temporary zip file
    $zipFilename = tempnam(sys_get_temp_dir(), 'app_export_');
    $zip = new ZipArchive();

    if ($zip->open($zipFilename, ZipArchive::OVERWRITE) !== true) {
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
            $zip->addEmptyDir($relativePath);
        } else {
            $zip->addFile($filePath, $relativePath);
        }
    }

    $zip->close();

    // Sanitize output filename
    $downloadFileName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $applicationName) . '.zip';

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
