<?php

use DatabaseExplorer\DatabaseExporter;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

function isEndsWith($haystack, $needle) {
    $length = strlen($needle);
    if ($length === 0) {
        return true;
    }
    return substr($haystack, -$length) === $needle;
}
$inputPost = new InputPost();

$batchSize = 100;
$maxQuerySize = 524288;

$baseDirectory = dirname(__DIR__) . '/tmp/';

// Sanitize application-related input
$applicationId = $inputPost->getApplicationId();
$databaseName = $inputPost->getDatabase();
$schemaName = $inputPost->getSchema();

$_GET['applicationId'] = $applicationId;
$_GET['databaseName'] = $databaseName;
$_GET['schemaName'] = $schemaName;

require_once __DIR__ . "/inc.db/config.php";

// Sanitize from GET
$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$databaseName = $inputGet->getDatabase(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$schemaName = $inputGet->getSchema(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);

// Make sure tmp folder exists
if (!file_exists($baseDirectory)) {
    mkdir($baseDirectory, 0755, true);
}

// Fetch and sanitize input data
$fileName = $inputPost->getFileName();
$tableName = $inputPost->getTableName();
$includeStructure = $inputPost->getIncludeStructure();
$includeData = $inputPost->getIncludeData();

// Sanitize file name (allow alphanum, dash, underscore, and `.sql`)
$fileName = basename(preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName));
if (!isEndsWith($fileName, '.sql')) {
    $fileName .= '.sql';
}

// Final path
$filePath = $baseDirectory . $fileName;

// Begin export
$output = '';
$tables = [$tableName];

$exporter = new DatabaseExporter($database->getDatabaseType(), $database->getDatabaseConnection());

header('Content-type: application/json');
try {
    if ($includeStructure) {
        $exporter->appendExportData("\r\n-- Database structure of `$tableName`\r\n");
        $exporter->exportTableStructure($tables, $schemaName);
    }

    if ($includeData) {
        $exporter->appendExportData("\r\n-- Database content of `$tableName`\r\n");
        $exporter->exportTableData($tables, $schemaName, $batchSize, $maxQuerySize);
    }

    // Write to file
    file_put_contents($filePath, $exporter->getExportData(), FILE_APPEND);
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
