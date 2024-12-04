<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;
use AppBuilder\Util\ResponseUtil;
use MagicObject\Database\PicoDatabaseType;

require_once dirname(__DIR__) . "/inc.app/app.php";
require_once dirname(__DIR__) . "/inc.app/database.php";
require_once dirname(__DIR__) . "/inc.app/sessions.php";

try {
    $databaseName = $databaseConfig->getDatabaseName();
    $schemaName = $databaseConfig->getDatabaseSchema();
    $databaseType = $database->getDatabaseType();
    $queryBuilder = new PicoDatabaseQueryBuilder($database);

    $tables = [];

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
                ORDER BY 
                    t.table_name ASC, 
                    kcu.ordinal_position ASC";
        $rs = $database->executeQuery($sql, [$schemaName]);
    } 
    else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_MYSQL) {
        // MySQL query to get column information including primary keys
        $queryBuilder->newQuery()
            ->select("table_name, column_name, data_type, column_key")
            ->from("INFORMATION_SCHEMA.COLUMNS")
            ->where("TABLE_SCHEMA = ? ", $databaseName);
        $rs = $database->executeQuery($queryBuilder);
    }
    else if ($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE) {
        // SQLite logic to get primary keys using PRAGMA
        $tablesQuery = "SELECT name FROM sqlite_master WHERE type='table';";
        $rs = $database->executeQuery($tablesQuery);
    }

    // Process the rows (PostgreSQL/MySQL)
	if($databaseType == PicoDatabaseType::DATABASE_TYPE_SQLITE)
	{
		while ($tableRow = $rs->fetch()) {
            $tableName = $tableRow['name'];
            $columnsQuery = "PRAGMA table_info($tableName);";
            $columnsRs = $database->executeQuery($columnsQuery);
            
            $primaryKeys = [];
            while ($columnRow = $columnsRs->fetch()) {
				
                if ($columnRow['pk'] == 1) {  // Check for primary key
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
    else{
		$rows = $rs->fetchAll();

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
			if (isset($data['column_key']) && $data['column_key'] == 'PRI') {
				$tables[$tableName]['primary_key'][] = $data['column_name'];
			}
		}
	}

    // Send the JSON response
    ResponseUtil::sendJSON($tables);

} catch (Exception $e) {
    // Log the error for debugging purposes
    error_log("Error: " . $e->getMessage());
    ResponseUtil::sendJSON(["error" => "An error occurred while processing your request."]);
}

