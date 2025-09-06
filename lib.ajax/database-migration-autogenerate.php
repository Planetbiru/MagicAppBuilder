<?php

use AppBuilder\AppDatabase;
use MagicObject\Database\PicoDatabase;
use MagicObject\Request\InputPost;
use MagicObject\Response\PicoResponse;
use MagicObject\SecretObject;
use MagicObject\Util\PicoStringUtil;

require_once dirname(__DIR__) . "/inc.app/auth.php";

$inputPost = new InputPost();

$targetDriver = $inputPost->getTargetDriver();
$targetHost = $inputPost->getTargetHost();
$targetPort = $inputPost->getTargetPort();
$targetUsername = $inputPost->getTargetUsername();
$targetPassword = $inputPost->getTargetPassword();
$targetDatabaseFilePath = $inputPost->getTargetDatabaseFilePath();
$targetDatabaseName = $inputPost->getTargetDatabaseName();
$targetDatabaseSchema = $inputPost->getTargetDatabaseSchema();
$targetTimeZone = $inputPost->getTargetTimeZone();

$sourceDriver = $inputPost->getSourceDriver();
$sourceHost = $inputPost->getSourceHost();
$sourcePort = $inputPost->getSourcePort();
$sourceUsername = $inputPost->getSourceUsername();
$sourcePassword = $inputPost->getSourcePassword();
$sourceDatabaseFilePath = $inputPost->getSourceDatabaseFilePath();
$sourceDatabaseName = $inputPost->getSourceDatabaseName();
$sourceDatabaseSchema = $inputPost->getSourceDatabaseSchema();
$sourceTimeZone = $inputPost->getSourceTimeZone();

$databaseConfigSource = new SecretObject();
$databaseConfigSource->set('driver', $sourceDriver);
$databaseConfigSource->set('host', $sourceHost);
$databaseConfigSource->set('port', $sourcePort);
$databaseConfigSource->set('username', $sourceUsername);
$databaseConfigSource->set('password', $sourcePassword);
$databaseConfigSource->set('databaseFilePath', $sourceDatabaseFilePath);
$databaseConfigSource->set('databaseName', $sourceDatabaseName);
$databaseConfigSource->set('databaseSchema', $sourceDatabaseSchema);
$databaseConfigSource->set('timeZone', $sourceTimeZone);

$databaseSource = new PicoDatabase($databaseConfigSource);

$response = array();
try
{
    $databaseSource->connect();

    $result['databaseTarget'] = array(
        'driver' => $targetDriver,
        'host' => $targetHost,
        'port' => $targetPort,
        'username' => $targetUsername,
        'password' => $targetPassword,
        'databaseFilePath' => $targetDatabaseFilePath,
        'databaseName' => $targetDatabaseName,
        'databaseSchema' => $targetDatabaseSchema,
        'timeZone' => $targetTimeZone
    );

    $result['databaseSource'] = array(
        'driver' => $sourceDriver,
        'host' => $sourceHost,
        'port' => $sourcePort,
        'username' => $sourceUsername,
        'password' => $sourcePassword,
        'databaseFilePath' => $sourceDatabaseFilePath,
        'databaseName' => $sourceDatabaseName,
        'databaseSchema' => $sourceDatabaseSchema,
        'timeZone' => $sourceTimeZone
    );

    $result['maximumRecord'] = 100;
    $result['table'] = array();

    $tables = AppDatabase::getTableList($databaseSource, $sourceDatabaseName, $sourceDatabaseSchema);
    foreach($tables as $tableName=>$table)
    {
        $col = AppDatabase::getColumnList($appConfig, $databaseConfigSource, $databaseSource, $tableName);
        $map = array();
        foreach($col['fields'] as $index=>$column)
        {
            $columnName = $column['column_name'];
            $map[] = PicoStringUtil::snakeize($columnName)." : ".$columnName;
        }
        $tableObject = array(
            'target' => PicoStringUtil::snakeize($tableName),
            'source' => $tableName,
            'map' => $map
        );
        $result['table'][] = $tableObject;
    }
    $response['success'] = true;
    $response['data'] = $result;
}
catch(Exception $e)
{
    // Do nothing
    $response['success'] = false;
}

PicoResponse::sendJSON($response);