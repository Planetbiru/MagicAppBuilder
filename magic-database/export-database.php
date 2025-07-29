<?php

use DatabaseExplorer\DatabaseExporter;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth-core.php";

if(!isset($entityAdmin) || $entityAdmin->getAdminLevelId() != "superuser")
{
    exit(); // Bye non superuser
}

$inputPost = new InputPost();

$batchSize = 100;
$maxQuerySize = 524288;

$tmpDir = dirname(__DIR__) . '/tmp';
if(!file_exists($tmpDir))
{
    mkdir($tmpDir, 0755, true);
}
else
{
    chmod($tmpDir, 0755);
}

$baseDirectory = realpath(dirname(__DIR__) . '/tmp'); // Ensure absolute path to base directory
if ($baseDirectory === false) {
    http_response_code(500);
    exit(json_encode(["success" => false, "error" => "Base directory not found."]));
}

// Sanitize application-related input
$applicationId = $inputPost->getApplicationId();
$databaseName = $inputPost->getDatabaseName();
$schemaName = $inputPost->getSchema();
$targetDatabaseType = $inputPost->getTargetDatabaseType();

$_GET['applicationId'] = $applicationId;
$_GET['databaseName'] = $databaseName;
$_GET['schemaName'] = $schemaName;
$_GET['targetDatabaseType'] = $targetDatabaseType;

require_once dirname(__DIR__) . "/database-explorer/inc.db/config.php";

// Sanitize from POST
$applicationId = $inputPost->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$databaseName = $inputPost->getDatabaseName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$schemaName = $inputPost->getSchema(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$targetDatabaseType = $inputPost->getTargetDatabaseType(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);

// Ensure tmp folder exists
if (!file_exists($baseDirectory)) {
    mkdir($baseDirectory, 0755, true);
}

// Fetch and sanitize input data
$fileName = $inputPost->getFileName();
$tableName = $inputPost->getTableName();
$includeStructure = $inputPost->getIncludeStructure();
$includeData = $inputPost->getIncludeData();

// Sanitize file name (allow only alphanum, dash, underscore, and `.sql`)
$fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);
$fileName = basename($fileName);

// Validate file extension must be .sql
if (!preg_match('/\.sql$/i', $fileName)) {
    $fileName .= '.sql';
}

// Build full file path
$filePath = $baseDirectory . DIRECTORY_SEPARATOR . $fileName;

// Ensure the file is located inside the base directory
$realPath = realpath(dirname($filePath));
if ($realPath === false || strpos($realPath, $baseDirectory) !== 0) {
    http_response_code(400);
    exit(json_encode(["success" => false, "error" => "Invalid file path."]));
}

// Begin export process
$tables = [$tableName];
$exporter = new DatabaseExporter($databaseBuilder->getDatabaseType(), $databaseBuilder->getDatabaseConnection(), $databaseName);

header('Content-type: application/json');

try {
    if ($includeStructure) {
        $exporter->appendExportData("\r\n-- Database structure of `$tableName`\r\n");
        $exporter->exportTableStructure($tables, $schemaName, $targetDatabaseType);
    }

    if ($includeData) {
        $exporter->appendExportData("\r\n-- Database content of `$tableName`\r\n");
        $exporter->exportTableData($tables, $schemaName, $targetDatabaseType, $batchSize, $maxQuerySize);
    }

    // Write export data to file (append mode)
    file_put_contents($filePath, $exporter->getExportData(), FILE_APPEND);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
