## Multilevel Object

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
$tire = new MagicObject();
$body = new MagicObject();

$tire->setDiameter(12);
$tire->setPressure(60);

$body->setLength(320);
$body->setWidth(160);
$body->Height(140);
$body->setColor("red");

$car->setTire($tire);
$car->setBody($body);

echo $car;

/*
{"tire":{"diameter":12,"pressure":60},"body":{"length":320,"width":160,"height":140,"color":"red"}}
*/

// to get color

echo $car->getBody()->getColor();

```

### Parse Yaml

```php

$song = new MagicObject();
$song->loadYamlString(
"
songId: 1234567890
title: Lagu 0001
duration: 320
album:
  albumId: 234567
  name: Album 0001
genre:
  genreId: 123456
  name: Pop
vocalist:
  vovalistId: 5678
  name: Budi
  agency:
    agencyId: 1234
    name: Agency 0001
    company:
      companyId: 5678
      name: Company 1
      pic:
        - name: Kamshory
          gender: M
        - name: Mas Roy
          gender: M
timeCreate: 2024-03-03 12:12:12
timeEdit: 2024-03-03 13:13:13
",
false, true, true
);

// to get company name
echo $song->getVocalist()->getAgency()->getCompany()->getName();
echo "\r\n";
// add company properties
$song->getVocalist()->getAgency()->getCompany()->setCompanyAddress("Jalan Jendral Sudirman Nomor 1");
// get agency
echo $song->getVocalist()->getAgency();
echo "\r\n";

// please note that $song->getVocalist()->getAgency()->getCompany()->getPic() is an array, not a MagicObject
// to get pic
foreach($song->getVocalist()->getAgency()->getCompany()->getPic() as $pic)
{
	echo $pic;
	echo "\r\n----\r\n";
}
```
