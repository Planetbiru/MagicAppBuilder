<?php

namespace MagicObject\Util\Database;

/**
 * Interface PicoDatabaseUtilInterface
 *
 * Defines methods for managing database structures and data, including table and column management,
 * data import/export, and database configuration.
 * 
 * Key methods include:
 * - Retrieving column lists from tables.
 * - Handling auto-increment keys.
 * - Generating SQL for creating/dropping tables.
 * - Configuring columns, fixing defaults, and importing data.
 * - Mapping and correcting data types during import.
 *
 * Implementations must ensure data integrity, validation, and error handling during operations.
 */
interface PicoDatabaseUtilInterface // NOSONAR
{
    public function getColumnList($database, $picoTableName);
    public function getAutoIncrementKey($tableInfo);
    public function dumpStructure($tableInfo, $picoTableName, $createIfNotExists = false, $dropIfExists = false, $engine = 'InnoDB', $charset = 'utf8mb4');
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