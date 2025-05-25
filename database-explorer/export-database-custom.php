<?php

use DatabaseExplorer\DatabaseExplorer;
use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/inc.app/app.php";



$config = new SecretObject();
$config->loadYamlString($yaml, false, true, true);

$database1 = new PicoDatabase($config->getDatabaseSource());
$database2 = new PicoDatabase($config->getDatabaseTarget());

$database1->connect();
$database2->connect();

$sql = DatabaseExplorer::getQueryShowColumns($database1->getDatabaseConnection(), null, "proyek");
$stmt = $database1->executeQuery($sql);
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row)
{

print_r($row);

$type = $row['Type'];

}

