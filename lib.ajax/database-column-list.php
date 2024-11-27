<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Request\InputPost;
use AppBuilder\Util\ResponseUtil;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/database.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();

try {
    $databaseName = $databaseConfig->getDatabaseName();
    $tableName = $inputPost->getTableName();

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
            "column_type" => $data['column_type'],
            "data_type" => $data['data_type']
        );
        if ($data['column_key'] == 'PRI') {
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
}
