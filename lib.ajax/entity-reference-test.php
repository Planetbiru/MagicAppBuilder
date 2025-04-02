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
 * Undocumented function
 *
 * @param string $databaseType
 * @return boolean
 */
function isMySql($databaseType)
{
    return $databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL;
}
/**
 * Undocumented function
 *
 * @param string $databaseType
 * @return boolean
 */
function isPostgreSql($databaseType)
{
    return $databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL;
}
/**
 * Undocumented function
 *
 * @param string $databaseType
 * @return boolean
 */
function isSqlite($databaseType)
{
    return $databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE;
}
/**
 * Undocumented function
 *
 * @param SecretObject $databaseConfig
 * @param PicoDatabase $database
 * @return array
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
    
    $validation = array(
        'tableName' => $validTableName && !empty($referenceTableName),
        'primaryKey' => $validPrimaryKey && !empty($referencePrimaryKey),
        'valueColumn' => $validValueColumn && !empty($referenceValueColumn),
        'referenceObjectName' => $validReferenceObjectName && !empty($referenceObjectName),
        'referencePropertyName' => $validReferencePropertyName && !empty($validReferencePropertyName)
    );
    
    ResponseUtil::sendJSON($validation);
} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
    ResponseUtil::sendJSON(new stdClass);
}
