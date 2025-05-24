<?php

use DatabaseExplorer\DatabaseExporter;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$batchSize = 100;
$maxQuerySize = 524288;

$baseDirectory = dirname(__DIR__) . '/tmp/';

$applicationId = $inputPost->getApplicationId();
$databaseName = $inputPost->getDatabase();
$schemaName = $inputPost->getSchema();

$_GET['applicationId'] = $applicationId;
$_GET['databaseName'] = $databaseName;
$_GET['schemaName'] = $schemaName;

require_once __DIR__ . "/inc.db/config.php";

$applicationId = $inputGet->getApplicationId(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$databaseName = $inputGet->getDatabase(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$schemaName = $inputGet->getSchema(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);

if(!file_exists($baseDirectory))
{
    mkdir($baseDirectory, 0755, true);
}

$fileName = $inputPost->getFileName();
$tableName = $inputPost->getTableName();
$includeStructure = $inputPost->getIncludeStructure();
$includeData = $inputPost->getIncludeData();

$fileName = str_replace("..\\", "", $fileName);
$fileName = str_replace("../", "", $fileName);

// Lakukan ekspor struktur dan data
$output = '';

$tables = array($tableName);

$exporter = new DatabaseExporter($database->getDatabaseType(), $database->getDatabaseConnection());

if ($includeStructure) {
    $exporter->appendExportData("\r\n-- Database structure of `$tableName`\r\n");
    $exporter->exportTableStructure($tables, $schemaName);
}

if ($includeData) {
    $exporter->appendExportData("\r\n-- Database content of `$tableName`\r\n");
    $exporter->exportTableData($tables, $schemaName, $batchSize, $maxQuerySize);
}

// Tambahkan ke file
file_put_contents($baseDirectory . $fileName, $exporter->getExportData(), FILE_APPEND);

echo 'OK';
