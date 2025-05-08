<?php

namespace AppBuilder;

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
}