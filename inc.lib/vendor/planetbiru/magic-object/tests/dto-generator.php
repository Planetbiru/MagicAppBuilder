<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Generator\PicoDtoGenerator;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";



$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__))."/test.yml", false, true, true);
$database = new PicoDatabase($databaseCredential);
$database->connect();


$gen = new PicoDtoGenerator($database, __DIR__."/entity", "song", "Music\\Dto", 'DtoSong', "Music\\Entity", 'EntitySong');
$gen->generate();