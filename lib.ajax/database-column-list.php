<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Database\PicoDatabaseType;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/database.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();

try {
    $databaseName = $databaseConfig->getDatabaseName();
    $databaseSchema = $databaseConfig->getDatabaseSchema();
    $tableName = $inputPost->getTableName();
    $databaseType = $database->getDatabaseType();

    $excludeColumns = [];
    $excludeColumns[] = $appConfig->entityInfo->getDraft();
    $excludeColumns[] = $appConfig->entityInfo->getWaitingFor();
    $excludeColumns[] = $appConfig->entityInfo->getAdminAskEdit();
    $excludeColumns[] = $appConfig->entityInfo->getTimeAskEdit();
    $excludeColumns[] = $appConfig->entityInfo->getIpAskEdit();
    $excludeColumns[] = $appConfig->entityInfo->getApprovalId();
    $excludeColumns[] = $appConfig->entityInfo->getApprovalNote();
    $excludeColumns[] = $appConfig->entityInfo->getApprovalStatus();

    $queryBuilder = new PicoDatabaseQueryBuilder($database);

    if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
        // MySQL Query for column details
        $queryBuilder->newQuery()
            ->select("column_name, column_type, data_type, column_key")
            ->from("INFORMATION_SCHEMA.COLUMNS")
            ->where(
                "TABLE_SCHEMA = ? AND TABLE_NAME = ? and column_name not in(?)",
                $databaseName,
                $tableName,
                $excludeColumns
            );
    } 
    else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_PGSQL) {
        // PostgreSQL Query for column details
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
            AND c.column_name not in(?)",
            $databaseName,
            $databaseSchema,
            $tableName,
            $excludeColumns
        );
    }
    else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
        // SQLite query for column details
        $queryBuilder->newQuery()
            ->select("name AS column_name, type AS data_type")
            ->from("PRAGMA_TABLE_INFO('$tableName')")
            ->where(
                "name NOT IN (?)",
                $excludeColumns
            );
            error_log($queryBuilder);
    }
    
    $rs = $database->executeQuery($queryBuilder);

    $rows = $rs->fetchAll();
    $column = "";
    $i = 0;

    $fields = [];
    $cols = [];
    $primaryKeys = [];

    $skipped = array();
    $skipped[] = $appConfig->entityInfo->get('draft');
    $skipped[] = $appConfig->entityInfo->get('waitingFor');
    $skipped[] = $appConfig->entityInfo->get('approvalNote');
    $skipped[] = $appConfig->entityInfo->get('approvalId');
    $skipped[] = $appConfig->entityInfo->get('adminCreate');
    $skipped[] = $appConfig->entityInfo->get('adminEdit');
    $skipped[] = $appConfig->entityInfo->get('adminAskEdit');
    $skipped[] = $appConfig->entityInfo->get('timeCreate');
    $skipped[] = $appConfig->entityInfo->get('timeEdit');
    $skipped[] = $appConfig->entityInfo->get('timeAskEdit');
    $skipped[] = $appConfig->entityInfo->get('ipCreate');
    $skipped[] = $appConfig->entityInfo->get('ipEdit');
    $skipped[] = $appConfig->entityInfo->get('ipAskEdit');
    
    foreach ($rows as $i => $data) {
        $cols[] = $data['column_name'];
        $fields[] = array(
            "column_name" => $data['column_name'],
            "column_type" => $data['data_type'],
            "data_type" => $data['data_type']
        );
        
        // Handle Primary Key Detection for SQLite
        if ($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
            // Check if the column is a primary key
            $queryPrimaryKey = $database->query("PRAGMA table_info($tableName)");
            $primaryKeysData = $queryPrimaryKey->fetchAll();
            foreach ($primaryKeysData as $pk) {
                if ($pk['name'] == $data['column_name'] && $pk['pk'] == 1) {
                    $primaryKeys[] = $data['column_name'];
                }
            }
        }
    }

    $json = array(
        'fields' => $fields,
        'columns' => $cols,
        'primary_keys' => $primaryKeys,
        'skipped_insert_edit' => $skipped
    );
    ResponseUtil::sendJSON($json);
} catch (Exception $e) {
    error_log($e->getMessage());
    // do nothing
}
