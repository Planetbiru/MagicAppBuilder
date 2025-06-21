<?php

use DatabaseExplorer\DatabaseExplorer;
use DatabaseExplorer\DatabaseExporter;
use MagicObject\Request\InputGet;
use MagicObject\Request\InputPost;
use MagicObject\Request\PicoFilterConstant;

require_once dirname(__DIR__) . "/inc.app/platform-check.php";
require_once dirname(__DIR__) . "/inc.app/auth.php";

if(basename($_SERVER['PHP_SELF']) == basename(__FILE__))
{
    // Prevent user to access this path
    exit();
}

class DataException extends Exception
{
    // Do not remove this class
}



if(!isset($inputGet))
{
    $inputGet = new InputGet();
}
if(!isset($inputGet))
{
    $inputPost = new InputPost();
}

$table = $inputGet->getTable(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS, false, false, true);
$page = $inputGet->getPage(PicoFilterConstant::FILTER_SANITIZE_NUMBER_UINT, false, false, true);
$query = $inputPost->getQuery();

// Get the row limit per page
$limit = $builderConfig->getDataLimit(); // Rows per page

// Ensure the page number is at least 1
if ($page < 1) {
    $page = 1;
}

// Ensure the row limit is at least 1
if ($limit < 1) {
    $limit = 1;
}

// Get the PDO instance and set error mode
$pdo = $database->getDatabaseConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// End database preparation

// Initialize variables for query processing
$queries = array();
$lastQueries = "";

if(isset($_POST['___export_database___']) || isset($_POST['___export_table___']))
{
    $maxRecord = 200;
    $maxQuerySize = 524288;
    
    $tables = [];
    if(isset($_POST['___export_table___']) && trim($_POST['___export_table___']) != "")
    {
        $tableName = $_POST['___export_table___'];
        $tables[] = $tableName;
        $filename = $tableName."-".date("Y-m-d-H-i-s").".sql";
    }
    else
    {
        $filename = $databaseConfig->getDatabaseName()."-".date("Y-m-d-H-i-s").".sql";
    }
    $filename = trim($filename, " - ");
    header("Content-type: text/plain");
    header("Content-disposition: attachment; filename=\"$filename\"");
    $exporter = new DatabaseExporter("", $pdo, $databaseConfig->getDatabaseName());
    $exporter->exportData($tables, $schemaName, $maxRecord, $maxQuerySize);
    
    echo $exporter->getExportData();
    exit();
}

if(isset($_POST['___export_database_structure___']) || isset($_POST['___export_table_structure___']))
{
    $maxRecord = 200;
    $maxQuerySize = 524288;
    
    $tables = [];
    if(isset($_POST['___export_table_structure___']) && trim($_POST['___export_table_structure___']) != "")
    {
        $tableName = $_POST['___export_table_structure___'];
        $tables[] = $tableName;
        $filename = $tableName."-".date("Y-m-d-H-i-s").".sql";
    }
    else
    {
        $filename = $databaseConfig->getDatabaseName()."-".date("Y-m-d-H-i-s").".sql";
    }
    $filename = trim($filename, " - ");
    header("Content-type: text/plain");
    header("Content-disposition: attachment; filename=\"$filename\"");
    $exporter = new DatabaseExporter("", $pdo, $databaseConfig->getDatabaseName());
    $exporter->exportStructure($tables, $schemaName);
    
    echo $exporter->getExportData();
    exit();
}

if(isset($_POST['___table_name___']) && isset($_POST['___primary_key_name___']) && isset($_POST['___column_list___']))
{
    $tableName = $_POST['___table_name___'];
    $primaryKeyName = $_POST['___primary_key_name___'];
    $columnList = $_POST['___column_list___'];
    $columnNames = explode(",", $columnList);

    if(isset($_POST['___primary_key_value___']))
    {
        $primaryKeyValue = addslashes($_POST['___primary_key_value___']);
        $values = [];
        foreach($columnNames as $key)
        {
            $values[] = "$key = '".addslashes($_POST[$key])."'";
        }
        $query = "UPDATE $tableName SET \r\n\t".implode(", \r\n\t", $values)." \r\nWHERE $primaryKeyName = '".$primaryKeyValue."';";
    }
    else
    {
        $values = [];
        foreach($columnNames as $key)
        {
            $values[$key] = "'".addslashes($_POST[$key])."'";
        }
        $field = implode(", ", array_keys($values));
        $value = implode(", ", array_values($values));
        $query = "INSERT INTO $tableName ($field) VALUES ($value)";
    }
}

// Split and sanitize the query if it exists
if ($query) {
    $arr = DatabaseExplorer::splitSQL($query);
    $query = implode(";\r\n", $arr);
    $queryArray = DatabaseExplorer::splitSqlNoLeftTrim($query);
    if (isset($queryArray) && is_array($queryArray) && !empty($queryArray)) {
        $q2 = array();
        foreach ($queryArray as $q) {
            $queries[] = $q['query'];
            $q2[] = "-- " . $q['query'];
        }
        $lastQueries = implode("\r\n", $q2);
    } else {
        $lastQueries = $query;
    }
} else {
    $queryArray = null;
}
// Execute queries and handle results
if ($query && !empty($queries)) {
    try {
        $queryResult = DatabaseExplorer::executeQueryResult($pdo, $q, $query, $queries);
    } catch (Exception $e) {
        $queryResult = self::ERROR . $e->getMessage();
    }
}
else {
    $queryResult = "";
}