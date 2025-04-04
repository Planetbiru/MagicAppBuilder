<?php

use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

//echo PicoDataFormat::convertDecimalToDMS(6353.59, true, ":", 0, true, 2, true) . "\n"; // Output: 01:45:54

class TestDms extends MagicObject
{
    
}

$dms = new TestDms();
$dms->setDuration(6353.5913);
echo $dms->dmsDuration(true, ":", 2, true, 2, true) . "\n"; // Output: 01:45:54.59

$dms->setName(" Test Name   ");
echo "'".$dms->trimName() . "'\n"; // Output: 'Test Name'
