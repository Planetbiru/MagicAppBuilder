<?php
use MagicObject\Database\PicoDatabaseQueryBuilder;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/database.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

try
{
	$databaseName = $databaseConfig->getDatabaseName();
     
    $queryBuilder = new PicoDatabaseQueryBuilder($database);

    $queryBuilder->newQuery()
        ->select("table_name, column_name, data_type, column_key")
        ->from("INFORMATION_SCHEMA.COLUMNS")
        ->where("TABLE_SCHEMA = ? ", 
        $databaseName);

    $rs = $database->executeQuery($queryBuilder);
    
	$rows = $rs->fetchAll();
	$column = "";
	$i = 0;

	$tables = array();
    $fields = array();
	$cols = array();

	foreach ($rows as $i=>$data) { 
		
		if(!isset($tables[$data['table_name']]) || $data['column_key'] == 'PRI')
		{
			if(!isset($tables[$data['table_name']]))
			{
				$tables[$data['table_name']] = array();
				$tables[$data['table_name']]['table_name'] = $data['table_name'];
				$tables[$data['table_name']]['primary_key'] = array();
			}
			$tables[$data['table_name']]['primary_key'][] = $data['column_name'];
		}
	}
	header('Content-type: application/json');
	echo json_encode($tables);
}
catch(Exception $e)
{
    error_log($e->getMessage());
    // do nothing
}
