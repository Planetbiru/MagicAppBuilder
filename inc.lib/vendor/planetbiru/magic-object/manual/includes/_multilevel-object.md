## Multilevel Object

In the first code snippet, you are creating a multilevel object structure to represent a car with its components, specifically the tire and body.

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

**Explanation**

1. **Creating Objects**: Instances of `MagicObject` are created for the car, tire, and body.
2. **Setting Properties**: You set properties on the tire (diameter and pressure) and the body (length, width, height, and color) using the `set` methods.
3. **Nested Objects**: The tire and body are associated with the car using `setTire()` and `setBody()`.
4. **JSON Output**: When you output `$car`, it returns a JSON representation of the object structure.
5. **Accessing Properties**: You can retrieve the color of the car's body through the method chaining `getBody()->getColor()`.

### Parse Yaml

The second code snippet demonstrates how to load and manipulate structured data from a YAML string.

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

**Explanation**

1. **Loading YAML**: The `loadYamlString()` method is used to load a YAML string into the `MagicObject`. This creates a structured object based on the YAML data.
2. **Accessing Nested Data**: You can navigate through the object hierarchy to retrieve values, like getting the company name with `getVocalist()->getAgency()->getCompany()->getName()`.
3. **Adding Properties**: You can also set additional properties, like the company address.
4. **Iterating Over Arrays**: The `pic` property is an array of objects, so you can loop through it to access individual picture details.

### Conclusion

Both examples demonstrate how MagicObject allows you to create complex object structures and manage nested data efficiently. The use of YAML for configuration or data storage makes it easier to define complex structures in a human-readable format. The ability to set and get properties through method chaining enhances the flexibility and readability of your code.