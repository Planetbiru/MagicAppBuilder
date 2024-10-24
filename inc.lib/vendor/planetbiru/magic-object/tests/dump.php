<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseType;
use MagicObject\Generator\PicoDatabaseDump;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__))."/test.yml", false, true, true);
$database = new PicoDatabase($databaseCredential->getDatabase(), function(){}, function($sql){
	//echo $sql;
});
$database->connect();

$song = new Song(null, $database);
/*
$speficication = null
$pageable = null
$sortable = null
$passive = true
$subqueryMap = null
$findOption = MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA
*/
$pageData = $song->findAll(null, null, null, true, null, MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA);
$dumpForSong = new PicoDatabaseDump();

$dumpForSong->dumpData($pageData, PicoDatabaseType::DATABASE_TYPE_MYSQL, new Song(), $maxRecord, function($sql){
    $fp = fopen("dump.sql", "a");
    fputs($fp, $sql);
    fclose($fp);
});
