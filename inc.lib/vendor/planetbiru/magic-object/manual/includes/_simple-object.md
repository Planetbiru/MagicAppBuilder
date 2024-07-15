## Simple Object

### Set and Get Properties Value

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

// get JSON string of the object
echo $someObject;

// or you can debug with error_log
error_log($someObject);
```

### Unset Properties

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

$someObject->unsetPhone();
echo "Phone     : " . $someObject->getPhone() . "\r\n";

// get JSON string of the object
echo $someObject;

// or you can debug with
error_log($someObject);
```

### Check if Properties has Value

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$someObject = new MagicObject();
$someObject->setId(1);
$someObject->setRealName("Someone");
$someObject->setPhone("+62811111111");
$someObject->setEmail("someone@domain.tld");

echo "ID        : " . $someObject->getId() . "\r\n";
echo "Real Name : " . $someObject->getRealName() . "\r\n";
echo "Phone     : " . $someObject->getPhone() . "\r\n";
echo "Email     : " . $someObject->getEmail() . "\r\n";

$someObject->unsetPhone();
if($someObject->hasValuePhone())
{
    echo "Phone     : " . $someObject->getPhone() . "\r\n";
}
else
{
    echo "Phone value is not set\r\n";
}
// another way
if($someObject->issetPhone())
{
    echo "Phone     : " . $someObject->getPhone() . "\r\n";
}
else
{
    echo "Phone value is not set\r\n";
}
// get JSON string of the object
echo $someObject;

// or you can debug with
error_log($someObject);
```
