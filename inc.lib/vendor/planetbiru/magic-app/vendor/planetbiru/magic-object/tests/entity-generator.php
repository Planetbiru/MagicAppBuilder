<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Generator\PicoEntityGenerator;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";



$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__))."/test.yml", false, true, true);
$database = new PicoDatabase($databaseCredential);
$database->connect();


$gen = new PicoEntityGenerator($database, __DIR__."/entity", "song", "Music\\Entity", 'EntitySong', true);
$gen->generate();