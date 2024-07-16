<?php
use MagicObject\Database\PicoDatabaseQueryBuilder;
use MagicObject\Request\InputPost;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/database.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

$inputPost = new InputPost();

try
{ 
    $databaseName = $databaseConfig->getDatabaseName();
    $tableName = $inputPost->getTableName();
     
    $queryBuilder = new PicoDatabaseQueryBuilder($database);

    $queryBuilder->newQuery()
        ->select("column_name, data_type, column_key")
        ->from("INFORMATION_SCHEMA.COLUMNS")
        ->where("TABLE_SCHEMA = ? AND TABLE_NAME = ? and column_name != ? and column_name != ? and column_name != ? ", 
        $databaseName, $tableName, $tableName."_apv_id", 'draft', 'waiting_for' );
    error_log($queryBuilder);
    $rs = $database->executeQuery($queryBuilder);
    
	$rows = $rs->fetchAll();
	$column = "";
	$i = 0;

    $fields = array();
	$cols = array();
	$primaryKeys = array();


	foreach ($rows as $i=>$data) { 
		$cols[] = $data['column_name'];
		$fields[] = array("column_name"=>$data['column_name'], "data_type"=>$data['data_type']);
        if($data['column_key'] == 'PRI')
        {
            $primaryKeys[] = $data['column_name'];    
        }
	}

    $json = array(
        'fields'=>$fields,
        'columns'=>$cols,
        'primary_keys'=>$primaryKeys,
    );
	header('Content-type: application/json');
    error_log(json_encode($json));
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}