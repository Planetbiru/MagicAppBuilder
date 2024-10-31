<?php

namespace MagicObject\Util\Database;

/**
 * Interface PicoDatabaseUtilInterface
 *
 * This interface defines a set of methods for manipulating and managing the structure and data within a database.
 * It provides a contract for classes that implement it to offer functionality related to table and column management,
 * data importing, and database configuration settings.
 * 
 * The available methods in this interface include, but are not limited to:
 * 
 * - Retrieving the list of columns from a specific table.
 * - Obtaining the auto-increment key from table information.
 * - Generating the table structure in SQL format, with options to create or drop tables.
 * - Configuring columns, fixing default values, and importing data from source tables to target tables.
 * - Processing and mapping imported data, as well as fixing data types according to the desired format.
 * 
 * Implementations of this interface should consider validation and error handling to ensure data integrity 
 * and security during database operations.
 */
interface PicoDatabaseUtilInterface
{
    public function getColumnList($database, $picoTableName);
    public function getAutoIncrementKey($tableInfo);
    public function dumpStructure($tableInfo, $picoTableName, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4');
    public function createColumn($column);
    public function fixDefaultValue($defaultValue, $type);
    public function dumpData($columns, $picoTableName, $data, $maxRecord = 100, $callbackFunction = null);
    public function dumpRecords($columns, $picoTableName, $data);
    public function dumpRecord($columns, $picoTableName, $record);
    public function showColumns($database, $tableName);
    public function autoConfigureImportData($config);
    public function updateConfigTable($databaseSource, $databaseTarget, $tables, $sourceTables, $target, $existingTables);
    public function createMapTemplate($databaseSource, $databaseTarget, $target);
    public function importData($config, $callbackFunction);
    public function isNotEmpty($array);
    public function importDataTable($databaseSource, $databaseTarget, $tableNameSource, $tableNameTarget, $tableInfo, $maxRecord, $callbackFunction);
    public function getMaxRecord($tableInfo, $maxRecord);
    public function processDataMapping($data, $columns, $maps = null);
    public function fixImportData($data, $columns);
    public function fixData($value);
    public function fixBooleanData($data, $name, $value);
    public function fixIntegerData($data, $name, $value);
    public function fixFloatData($data, $name, $value);
    public function insert($tableName, $data);
}