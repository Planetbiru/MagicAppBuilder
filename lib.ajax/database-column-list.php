<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Database\PicoDatabaseType;

require_once dirname(__DIR__) . "/inc.app/auth.php";
require_once dirname(__DIR__) . "/inc.app/database.php";


$inputPost = new InputPost();

try {
    $databaseName = $databaseConfig->getDatabaseName();
    $databaseSchema = $databaseConfig->getDatabaseSchema();
    $tableName = $inputPost->getTableName();
    $databaseType = $database->getDatabaseType();

    $excludeColumns = array();
    $excludeColumns[] = $appConfig->entityInfo->getDraft();
    $excludeColumns[] = $appConfig->entityInfo->getWaitingFor();
    $excludeColumns[] = $appConfig->entityInfo->getAdminAskEdit();
    $excludeColumns[] = $appConfig->entityInfo->getTimeAskEdit();
    $excludeColumns[] = $appConfig->entityInfo->getIpAskEdit();
    $excludeColumns[] = $appConfig->entityInfo->getApprovalId();
    $excludeColumns[] = $appConfig->entityInfo->getApprovalNote();
    $excludeColumns[] = $appConfig->entityInfo->getApprovalStatus();

    if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MARIADB || $databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
        // MySQL Query for column details
        $queryBuilder = new PicoDatabaseQueryBuilder($database);
        $queryBuilder->newQuery()
            ->select("column_name, column_type, data_type, column_key")
            ->from("INFORMATION_SCHEMA.COLUMNS")
            ->where(
                "TABLE_SCHEMA = ? AND TABLE_NAME = ? and column_name not in(?)",
                $databaseName,
                $tableName,
                $excludeColumns
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
            AND c.column_name not in(?)",
            $databaseName,
            $databaseSchema,
            $tableName,
            $excludeColumns
        );
        $rs = $database->executeQuery($queryBuilder);

        $rows = $rs->fetchAll(PDO::FETCH_ASSOC);
    }
    else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
        // SQLite query for column details
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
    }
    
    $column = "";
    $i = 0;

    $fields = array();
    $cols = array();
    $primaryKeys = array();
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
            "column_type" => $data['column_type'  ],
            "data_type"   => $data['data_type'  ]
        );
        
        if (strtoupper($data['column_key']) == 'PRI') {
            $primaryKeys[] = $data['column_name'];
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
    ResponseUtil::sendJSON(new stdClass);
}
