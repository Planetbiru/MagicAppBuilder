<?php

use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";


$object = new MagicObject();

$yaml = '
prop1:
  prop2:
    prop3:
      prop4: Test
';

$object->loadYamlString($yaml, false, true, true);

$object2 = new MagicObject();

$yaml = '
prop11:
  prop12:
    prop13:
      prop14: Ok
';

$object2->loadYamlString($yaml, false, true, true);

$object->mergeWith($object2);

//echo $object;


$obj1 = new MagicObject();
$obj1->loadYamlString('
user:
  name: ALice
client:
  address: Jakarta
', false, true, true);

$obj2 = new MagicObject();
$obj2->loadYamlString('
user:
  email: alice@example.com
client:
  name: Ana
', false, true, true);


$obj1->mergeWith($obj2);

echo $obj1;