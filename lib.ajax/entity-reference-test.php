<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Request\InputGet;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";

if(!$database->isConnected())
{
    ResponseUtil::sendJSON(new stdClass);
    exit();
}

/**
 * Check if the database type is MySQL or MariaDB.
 *
 * @param string $databaseType The type of the database.
 * @return boolean Returns true if the database type is MySQL or MariaDB, otherwise false.
 */
function isMySql($databaseType)
{
    return $databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL;
}

/**
 * Check if the database type is PostgreSQL.
 *
 * @param string $databaseType The type of the database.
 * @return boolean Returns true if the database type is PostgreSQL, otherwise false.
 */
function isPostgreSql($databaseType)
{
    return $databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL;
}

/**
 * Check if the database type is SQLite.
 *
 * @param string $databaseType The type of the database.
 * @return boolean Returns true if the database type is SQLite, otherwise false.
 */
function isSqlite($databaseType)
{
    return $databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE;
}

/**
 * Get detailed information about a table including its columns and primary keys.
 *
 * @param SecretObject $databaseConfig The configuration of the database.
 * @param PicoDatabase $database The database connection.
 * @param string $tableName The name of the table.
 * @return array Returns an array with fields, columns, and primary keys information.
 */
function getTableInfo($databaseConfig, $database, $tableName)
{
    $databaseName = $databaseConfig->getDatabaseName();
    $databaseSchema = $databaseConfig->getDatabaseSchema();
    $databaseType = $database->getDatabaseType();

    if (isMySql($databaseType)) {
        // MySQL Query for column details
        $queryBuilder = new PicoDatabaseQueryBuilder($database);
        $queryBuilder->newQuery()
            ->select("column_name, column_type, data_type, column_key")
            ->from("INFORMATION_SCHEMA.COLUMNS")
            ->where(
                "TABLE_SCHEMA = ? AND TABLE_NAME = ? ",
                $databaseName,
                $tableName
            );
        $rs = $database->executeQuery($queryBuilder);

        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    } 
    else if (isPostgreSql($databaseType)) {
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
            ",
            $databaseName,
            $databaseSchema,
            $tableName
        );
        $rs = $database->executeQuery($queryBuilder);

        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    }
    else if (isSqlite($databaseType)) {
        // SQLite query for column details
        $queryBuilder = "PRAGMA table_info('$tableName')";
        $rs = $database->executeQuery($queryBuilder);
        $rawRows = $rs->fetchAll(PDO::FETCH_ASSOC);
        $rows = array();
        foreach($rawRows as $row)
        {
            $rows[] = array(
                'column_name' => $row['name'],
                'data_type'   => $row['type'],
                'column_key'  => $row['pk'] == 1 ? 'PRI' : '',
                'column_type' => $row['type'],
            );
        }
    }
    
    $fields = array();
    $cols = array();
    $primaryKeys = array();   
    
    foreach ($rows as $data) {
        $cols[] = $data['column_name'];
        $fields[] = array(
            "column_name" => $data['column_name'],
            "column_type" => $data['column_type'  ],
            "data_type"   => $data['data_type'  ]
        );
        
        if (strtoupper($data['column_key']) == 'PRI') {
            $primaryKeys[] = $data['column_name'];
        }
    }

    return array(
        'fields' => $fields,
        'columns' => $cols,
        'primary_keys' => $primaryKeys
    );
}

/**
 * Get a list of tables in the database with their primary keys.
 *
 * @param SecretObject $databaseConfig The configuration of the database.
 * @param PicoDatabase $database The database connection.
 * @return array Returns an array of tables with their primary keys.
 */
function getTableList($databaseConfig, $database) // NOSONAR
{
    $databaseName = $databaseConfig->getDatabaseName();
    $schemaName = $databaseConfig->getDatabaseSchema();
    $tables = array();
    try {
        $queryBuilder = new PicoDatabaseQueryBuilder($database);
        $databaseType = $database->getDatabaseType();
    
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
                    'tableName' => $tableName,
                    'primaryKeys' => $primaryKeys
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
                        'tableName' => $tableName,
                        'primaryKeys' => $pks
                    ];
                }
    
                // Check if the column is a primary key
                if (isset($data['column_key']) && $data['column_key'] == 'PRI' && !in_array($data['column_name'], $tables[$tableName]['primary_key'])) 
                {
                    $tables[$tableName]['primaryKeys'][] = $data['column_name'];
                }
            }
        }
    
    } catch (Exception $e) {
        // Do nothing
    }
    
    return $tables;
}

$inputPost = new InputPost();

try {

    $tableName = $inputPost->getTableName();
    $referenceTableName = $inputPost->getReferenceTableName();
    $referencePrimaryKey = $inputPost->getReferencePrimaryKey();
    $referenceValueColumn = $inputPost->getReferenceValueColumn();
    $referenceObjectName = $inputPost->getReferenceObjectName();
    $referencePropertyName = $inputPost->getReferencePropertyName();
    
    // Main table information
    $data = getTableInfo($databaseConfig, $database, $tableName);
    
    // Reference table information
    $referenceData = getTableInfo($databaseConfig, $database, $referenceTableName);
    
    $validTableName = false;
    $validPrimaryKey = false;
    $validValueColumn = false;
    $validReferenceObjectName = false;
    $validReferencePropertyName = false;
    
    if(isset($data) && !empty($data['columns']) && !in_array($referenceObjectName, $data['columns']))
    {
        $validReferenceObjectName = true;
    }
    
    if(isset($referenceData) && !empty($referenceData['fields']))
    {
        $validTableName = true;    
        if(!empty($referenceData['primary_keys']) && in_array($referencePrimaryKey, $referenceData['primary_keys']))
        {
            $validPrimaryKey = true;
        }
        if(!empty($referenceData['columns']) && in_array($referenceValueColumn, $referenceData['columns']))
        {
            $validValueColumn = true;
        }
        if(!empty($referenceData['columns']) && in_array($referencePropertyName, $referenceData['columns']))
        {
            $validReferencePropertyName = true;
        }
    }
    
    $skipped = array();
    
    $skipped[] = $appConfig->entityInfo->getDraft();
    $skipped[] = $appConfig->entityInfo->getWaitingFor();
    $skipped[] = $appConfig->entityInfo->getApprovalNote();
    $skipped[] = $appConfig->entityInfo->getApprovalId();
    $skipped[] = $appConfig->entityInfo->getAdminCreate();
    $skipped[] = $appConfig->entityInfo->getAdminEdit();
    $skipped[] = $appConfig->entityInfo->getAdminAskEdit();
    $skipped[] = $appConfig->entityInfo->getTimeCreate();
    $skipped[] = $appConfig->entityInfo->getTimeEdit();
    $skipped[] = $appConfig->entityInfo->getTimeAskEdit();
    $skipped[] = $appConfig->entityInfo->getIpCreate();
    $skipped[] = $appConfig->entityInfo->getIpEdit();
    $skipped[] = $appConfig->entityInfo->getIpAskEdit();
    $skipped[] = $appConfig->entityInfo->getActive();
    
    $skipped = array_merge($skipped, $referenceData['primary_keys']);
    
    $columns = array();
    foreach($referenceData['columns'] as $column)
    {
        if(!in_array($column, $skipped))
        {
            $columns[] = $column;
        }
    }
    
    $validation = array(
        'tableName' => $validTableName && !empty($referenceTableName),
        'primaryKey' => $validPrimaryKey && !empty($referencePrimaryKey),
        'valueColumn' => $validValueColumn && !empty($referenceValueColumn),
        'referenceObjectName' => $validReferenceObjectName && !empty($referenceObjectName),
        'referencePropertyName' => $validReferencePropertyName && !empty($validReferencePropertyName),
        'columns' => $columns,
        'primaryKeys' => $referenceData['primary_keys'],
        'tables' => null
    );
    
    if(!$validation['tableName'])
    {
        $validation['tables'] = getTableList($databaseConfig, $database);
    }
    
    ResponseUtil::sendJSON($validation);
} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
    ResponseUtil::sendJSON(new stdClass);
}
