<?php

namespace AppBuilder;

use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Database\PicoDatabaseType;
use PDO;

/**
 * Provides utility methods for interacting with the application's database.
 *
 * This class includes methods to retrieve metadata about database tables,
 * such as their names and primary keys, across different database types
 * (PostgreSQL, MySQL, MariaDB, SQLite).
 */
class AppDatabase
{
    /**
     * Retrieves a list of tables and their primary keys from the database.
     *
     * This method supports multiple database types (PostgreSQL, MySQL, MariaDB, SQLite)
     * and fetches table metadata, including table names and primary keys. It excludes
     * tables with names ending in `_apv` or `_trash`.
     *
     * @param PicoDatabase $database The database connection instance.
     * @param string $databaseName The name of the database (used for MySQL/MariaDB).
     * @param string $schemaName The schema name (used for PostgreSQL).
     * @return array An associative array of tables with their names and primary keys.
     */
    public static function getTableList($database, $databaseName, $schemaName) // NOSONAR
    {
        $databaseType = $database->getDatabaseType();
        $queryBuilder = new PicoDatabaseQueryBuilder($database);
        $tables = array();
        if ($databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
            // PostgreSQL query to get primary keys
            $sql = "SELECT 
                        t.table_schema,
                        t.table_name,
                        kcu.column_name 
                    FROM 
                        information_schema.tables t
                    LEFT JOIN 
                        information_schema.table_constraints tc 
                        ON t.table_schema = tc.table_schema
                        AND t.table_name = tc.table_name
                        AND tc.constraint_type = 'PRIMARY KEY'
                    LEFT JOIN 
                        information_schema.key_column_usage kcu 
                        ON tc.constraint_name = kcu.constraint_name
                        AND t.table_schema = kcu.table_schema
                        AND t.table_name = kcu.table_name
                    WHERE 
                        t.table_type = 'BASE TABLE'
                        AND t.table_schema = ?
                        AND t.table_name NOT LIKE '%_apv'
                        AND t.table_name NOT LIKE '%_trash'
                    ORDER BY 
                        t.table_name ASC, 
                        kcu.ordinal_position ASC
                    ";
            $rs = $database->executeQuery($sql, [$schemaName]);
        } 
        else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) 
        {
            // MySQL query to get column information including primary keys
            $queryBuilder->newQuery()
                ->select("table_name, column_name, data_type, column_key")
                ->from("INFORMATION_SCHEMA.COLUMNS")
                ->where("TABLE_SCHEMA = ? AND table_name NOT LIKE '%_apv' AND table_name NOT LIKE '%_trash'", $databaseName);
            $rs = $database->executeQuery($queryBuilder);
        }
        else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE) 
        {
            // Fetch table name only
            $queryBuilder->newQuery()
                ->select("name")
                ->from("sqlite_master")
                ->where("type = 'table' AND name NOT LIKE '%_apv' AND name NOT LIKE '%_trash'");
            $rs = $database->executeQuery($queryBuilder);
        }

        // Process the rows (PostgreSQL/MySQL)
        if($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE)
        {
            // SQLite logic to get primary keys using PRAGMA
            while ($tableRow = $rs->fetch(PDO::FETCH_ASSOC)) 
            {
                $tableName = $tableRow['name'];
                $columnsQuery = "PRAGMA table_info($tableName);";
                $columnsRs = $database->executeQuery($columnsQuery);
                
                $primaryKeys = array();
                while ($columnRow = $columnsRs->fetch()) 
                {
                    
                    if ($columnRow['pk'] == 1) 
                    {  
                        // Check for primary key
                        $primaryKeys[] = $columnRow['name'];
                    }
                }
                $tables[$tableName] = [
                    'table_name' => $tableName,
                    'primary_key' => $primaryKeys
                ];
            }
            ksort($tables);
        }
        else 
        {
            $rows = $rs->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $data) {
                $tableName = $data['table_name'];

                if (!isset($tables[$tableName])) {
                    $pks = isset($data['column_name']) ? [$data['column_name']] : [];
                    $tables[$tableName] = [
                        'table_name' => $tableName,
                        'primary_key' => $pks
                    ];
                }

                // Check if the column is a primary key
                if (isset($data['column_key']) && $data['column_key'] == 'PRI' && !in_array($data['column_name'], $tables[$tableName]['primary_key'])) 
                {
                    $tables[$tableName]['primary_key'][] = $data['column_name'];
                }
            }
        }
        return $tables;
    }

    /**
     * Extract maximum length from column_type string if applicable.
     *
     * Supports MySQL, PostgreSQL, and SQLite types.
     *
     * @param string $columnType
     * @return int|null
     */
    public static function getMaximumLength($columnType)
    {
        // Normalize to lowercase and remove extra spaces
        $type = strtolower(trim($columnType));

        // Regex to match types with length, including:
        // - varchar(255)
        // - nvarchar(100)
        // - character varying(150)
        // - char(10)
        // - nchar(20)
        // - etc.
        if (preg_match('/^(varchar|nvarchar|char|nchar|character varying|character)\s*\(\s*(\d+)\s*\)/i', $type, $matches)) {
            return (int)$matches[2];
        }

        // No length information found
        return null;
    }

    /**
     * Retrieves column metadata for a given table, excluding special columns.
     *
     * @param SecretObject $appConfig Application configuration object.
     * @param SecretObject $databaseConfig Database configuration object.
     * @param PicoDatabase $database Database connection object.
     * @param string $tableName Name of the table to inspect.
     * @return array Returns an array containing fields, columns, primary keys, and skipped fields.
     */
    public static function getColumnList($appConfig, $databaseConfig, $database, $tableName)
    {
        
        try {
            $databaseName = $databaseConfig->getDatabaseName();
            $databaseSchema = $databaseConfig->getDatabaseSchema();
            
            $databaseType = $database->getDatabaseType();

            $excludeColumns = array();

            // Excluce special columns that should not be included in the list
            $excludeColumns[] = $appConfig->entityInfo->getDraft();
            $excludeColumns[] = $appConfig->entityInfo->getWaitingFor();
            $excludeColumns[] = $appConfig->entityInfo->getAdminAskEdit();
            $excludeColumns[] = $appConfig->entityInfo->getTimeAskEdit();
            $excludeColumns[] = $appConfig->entityInfo->getIpAskEdit();
            $excludeColumns[] = $appConfig->entityInfo->getApprovalId();
            $excludeColumns[] = $appConfig->entityInfo->getApprovalNote();
            $excludeColumns[] = $appConfig->entityInfo->getApprovalStatus();

            // PDO does not support array binding directly, so we need to convert the array to a string
            $excludeColumnsString = "'".implode("', '", $excludeColumns)."'";

            if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
                // MySQL Query for column details
                $queryBuilder = new PicoDatabaseQueryBuilder($database);
                $queryBuilder->newQuery()
                    ->select("column_name, column_type, data_type, column_key")
                    ->from("INFORMATION_SCHEMA.COLUMNS")
                    ->where(
                        "TABLE_SCHEMA = ? AND TABLE_NAME = ? AND column_name NOT IN ($excludeColumnsString)",
                        $databaseName,
                        $tableName
                    );
                $rs = $database->executeQuery($queryBuilder);

                $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
            } 
            else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
                // PostgreSQL Query for column details
                $queryBuilder = new PicoDatabaseQueryBuilder($database);
                $queryBuilder->newQuery()
                    ->select("c.column_name, c.data_type as data_type, c.data_type as column_type, CASE 
                        WHEN tc.constraint_type = 'PRIMARY KEY' THEN 'PRI'
                        ELSE 'NON-PRI'
                    END AS column_key")
                    ->from("information_schema.columns c")
                    ->leftJoin("information_schema.key_column_usage kcu")
                    ->on("c.column_name = kcu.column_name 
                    AND c.table_schema = kcu.table_schema 
                    AND c.table_name = kcu.table_name")
                    ->leftJoin("information_schema.table_constraints tc")
                    ->on("kcu.constraint_name = tc.constraint_name
                    AND tc.constraint_type = 'PRIMARY KEY'")
                    ->where("
                    c.table_catalog = ?
                    AND c.table_schema = ? 
                    AND c.table_name = ?
                    AND c.column_name NOT IN ($excludeColumnsString)",
                    $databaseName,
                    $databaseSchema,
                    $tableName
                );
                $rs = $database->executeQuery($queryBuilder);

                $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
            }
            else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
                // SQLite query for column details
                $rows = self::sqliteColumnDetails($database, $tableName, $excludeColumns);
            }
            $fields = array();
            $cols = array();
            $primaryKeys = array();
            $skipped = array();
            
            // Add skipped fields that should not be included in insert/edit forms
            $skipped[] = $appConfig->entityInfo->getDraft();
            $skipped[] = $appConfig->entityInfo->getWaitingFor();
            $skipped[] = $appConfig->entityInfo->getApprovalNote();
            $skipped[] = $appConfig->entityInfo->getApprovalId();
            $skipped[] = $appConfig->entityInfo->getAdminCreate();
            $skipped[] = $appConfig->entityInfo->getAdminEdit();
            $skipped[] = $appConfig->entityInfo->getTimeCreate();
            $skipped[] = $appConfig->entityInfo->getTimeEdit();
            $skipped[] = $appConfig->entityInfo->getIpCreate();
            $skipped[] = $appConfig->entityInfo->getIpEdit();
            
            foreach ($rows as $data) {
                $cols[] = $data['column_name'];
                $fields[] = array(
                    "column_name"    => $data['column_name'],
                    "column_type"    => $data['column_type'],
                    "data_type"      => $data['data_type'  ],
                    'maximum_length' => self::getMaximumLength($data['column_type'])
                );
                
                if (strtoupper($data['column_key']) == 'PRI') {
                    $primaryKeys[] = $data['column_name'];
                }
            }

            return array(
                'fields' => $fields,
                'columns' => $cols,
                'primary_keys' => $primaryKeys,
                'skipped_insert_edit' => $skipped
            );
        } catch (Exception $e) {
            error_log($e->getMessage());
            return array(
                'fields' => array(),
                'columns' => array(),
                'primary_keys' => array(),
                'skipped_insert_edit' => array()
            );
        }

    }

    /**
     * Retrieves column details for a SQLite table, excluding specified columns.
     *
     * @param PicoDatabase $database The database connection object.
     * @param string $tableName The name of the table to inspect.
     * @param array $excludeColumns List of column names to exclude from the result.
     * @return array Returns an array of column details.
     */
    private function sqliteColumnDetails($database, $tableName, $excludeColumns)
    {
        $queryBuilder = "PRAGMA table_info('$tableName')";
        $rs = $database->executeQuery($queryBuilder);
        $rawRows = $rs->fetchAll(PDO::FETCH_ASSOC);
        $rows = array();
        foreach($rawRows as $row)
        {
            if(!in_array($row['name'], $excludeColumns))
            {
                $rows[] = array(
                    'column_name' => $row['name'],
                    'data_type'   => $row['type'],
                    'column_key'  => $row['pk'] == 1 ? 'PRI' : '',
                    'column_type' => $row['type'],
                );
            }
        }
        return $rows;
    }
}