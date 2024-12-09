<?php

namespace MagicApp\Utility;

use Exception;
use ZipArchive;

/**
 * A utility class for creating and downloading ZIP files in PHP.
 *
 * This class provides methods to compress files or directories into a ZIP archive
 * and offer it as a downloadable file. It includes functionality for:
 * - Zipping all files in a directory recursively.
 * - Zipping an array of specified file paths.
 * - Handling temporary file creation and cleanup.
 *
 * Example Use Cases:
 * - Provide downloadable backups of folders.
 * - Compress selected files into a single archive for sharing.
 * 
 * **Example**
 * 
 * ```php
 * <?php
 * try {
 *     $folderPath = 'path/to';
 *     ZipDownloader::downloadFolderAsZip($folderPath, 'my-files.zip');
 * } catch (Exception $e) {
 *     echo "Error: " . $e->getMessage();
 * }
 * ```
 * or
 * 
 * ```php
 * <?php
 * try {
 *     $files = [
 *         'path/to/file1.txt',
 *         'path/to/file2.jpg',
 *         'path/to/file3.pdf'
 *     ];
 *     ZipDownloader::downloadFilesAsZip($files, 'my-files.zip');
 * } catch (Exception $e) {
 *     echo "Error: " . $e->getMessage();
 * }
 * ```
 *
 * Features:
 * - Automatically handles folder structures in ZIP files.
 * - Validates file paths and directories before processing.
 * - Integrates seamless ZIP file download via HTTP headers.
 */
class ZipDownloader
{
    const CANNOT_CREATE_ZIP_FILE = "Cannot create ZIP file: ";
    const FILE_NOT_FOUND = "File not found: ";
    const FOLDER_NOT_FOUND = "Folder not found: ";

    /**
     * Create a ZIP file from a folder and download it.
     *
     * @param string $folderPath Path to the folder containing files to include in the ZIP.
     * @param string $zipFileName Name of the ZIP file to create.
     * @throws Exception If the folder doesn't exist or can't create the ZIP file.
     */
    public static function downloadFolderAsZip($folderPath, $zipFileName)
    {
        if (empty($folderPath) || !is_dir($folderPath)) {
            throw new ZipDownloadException(self::FOLDER_NOT_FOUND . $folderPath);
        }

        $zip = new ZipArchive();
        $zipFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFileName;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new ZipDownloadException(self::CANNOT_CREATE_ZIP_FILE . $zipFilePath);
        }

        // Add folder contents to ZIP
        self::addFolderToZip($folderPath, $zip);

        $zip->close();

        // Send the ZIP file for download
        self::downloadZipFile($zipFilePath, $zipFileName);
    }

    /**
     * Create a ZIP file from an array of file paths and download it.
     *
     * @param string[] $filePaths Array of file paths to include in the ZIP.
     * @param string $zipFileName Name of the ZIP file to create.
     * @throws Exception If a file path is invalid or the ZIP cannot be created.
     */
    public static function downloadFilesAsZip($filePaths, $zipFileName)
    {
        if (empty($filePaths) || !is_array($filePaths)) {
            throw new ZipDownloadException('Invalid file paths provided.');
        }

        $zip = new ZipArchive();
        $zipFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFileName;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new ZipDownloadException(self::CANNOT_CREATE_ZIP_FILE . $zipFilePath);
        }

        // Add each file to the ZIP
        foreach ($filePaths as $filePath) {
            if (!file_exists($filePath)) {
                throw new ZipDownloadException(self::FILE_NOT_FOUND . $filePath);
            }

            $zip->addFile($filePath, basename($filePath));
        }

        $zip->close();

        // Send the ZIP file for download
        self::downloadZipFile($zipFilePath, $zipFileName);
    }

    /**
     * Create a ZIP file from an associated array of file paths and download it.
     *
     * @param array $filePaths Associative array of file paths and their human-readable names.
     *                         Key: Original file path, Value: Human-readable file name in ZIP.
     * @param string $zipFileName Name of the ZIP file to create.
     * @throws Exception If a file path is invalid or the ZIP cannot be created.
     */
    public static function downloadFilesAsNamedZip($filePaths, $zipFileName)
    {
        if (empty($filePaths) || !is_array($filePaths)) {
            throw new ZipDownloadException('Invalid file paths provided.');
        }

        $zip = new ZipArchive();
        $zipFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipFileName;

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new ZipDownloadException(self::CANNOT_CREATE_ZIP_FILE . $zipFilePath);
        }

        // Add each file to the ZIP with the specified name
        foreach ($filePaths as $filePath => $humanReadableName) {
            if (!file_exists($filePath)) {
                throw new ZipDownloadException(self::FILE_NOT_FOUND . $filePath);
            }

            $zip->addFile($filePath, $humanReadableName);
        }

        $zip->close();

        // Send the ZIP file for download
        self::downloadZipFile($zipFilePath, $zipFileName);
    }

    /**
     * Recursively add files and folders to a ZIP archive.
     *
     * @param string $folderPath Path to the folder.
     * @param ZipArchive $zip ZipArchive object.
     * @param string $basePath Base path for relative paths in the ZIP.
     */
    private static function addFolderToZip($folderPath, $zip, $basePath = '')
    {
        $files = scandir($folderPath);
        if ($files !== false) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $fullPath = $folderPath . DIRECTORY_SEPARATOR . $file;
                $relativePath = ltrim($basePath . DIRECTORY_SEPARATOR . $file, DIRECTORY_SEPARATOR);

                if (is_dir($fullPath)) {
                    // Add directory to ZIP (important for maintaining structure)
                    $zip->addEmptyDir($relativePath);
                    // Recursively add contents of the directory
                    self::addFolderToZip($fullPath, $zip, $relativePath);
                } else {
                    // Add file to ZIP
                    $zip->addFile($fullPath, $relativePath);
                }
            }
        }
    }

    /**
     * Send the generated ZIP file to the client for download and clean up.
     *
     * @param string $zipFilePath Path to the ZIP file on the server.
     * @param string $zipFileName Name of the ZIP file for the download.
     */
    private static function downloadZipFile($zipFilePath, $zipFileName)
    {
        if (empty($zipFilePath) || !file_exists($zipFilePath)) {
            throw new ZipDownloadException(self::FILE_NOT_FOUND . $zipFilePath);
        }

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
        header('Content-Length: ' . filesize($zipFilePath));
        readfile($zipFilePath);

        // Clean up the temporary ZIP file
        unlink($zipFilePath);
    }
}
