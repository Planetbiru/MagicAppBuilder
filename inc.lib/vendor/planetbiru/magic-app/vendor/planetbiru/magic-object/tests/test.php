<?php

use MagicObject\MagicObject;
use MagicObject\Request\InputServer;
use MagicObject\Util\PicoTestValueUtil;

require_once dirname(__DIR__) . "/vendor/autoload.php";

//echo (new PicoTestValueUtil())->doReturnAttributeChecked()->whenEquals(1, 1);

//$inputServer = new InputServer();
//echo $inputServer->userLanguage(true);


$someObject = new MagicObject();


$someObject->pushData("Text 1");
$someObject->pushData("Text 2");
$someObject->pushData(3);
$someObject->pushData(4.1);
$someObject->pushData(true);

echo "After Push\r\n";

echo $someObject."\r\n\r\n";

echo "Pop\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
echo $someObject->popData()."\r\n";
echo "After Pop\r\n";
echo $someObject."\r\n\r\n";
