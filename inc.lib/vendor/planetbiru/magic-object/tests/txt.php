<?php

use MagicObject\MagicObject;
use MagicObject\Txt;

require_once dirname(__DIR__) . "/vendor/autoload.php";

//echo Txt::kamshory();

$object = new MagicObject();
$object->setName('John Doe');
$object->setEmail('john.doe@example.com');
// Masks email property value by replacing certain characters with a masking character.
echo  $object->maskEmail(10, 6, '*')."\r\n";
echo  $object->getEmail()."\r\n";
