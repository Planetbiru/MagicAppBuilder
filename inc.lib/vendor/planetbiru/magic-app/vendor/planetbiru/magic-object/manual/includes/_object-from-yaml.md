## Object from Yaml

### From Yaml String

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
// load yaml string
// will not replace value with environment variable
// load as object instead of associated array
$car->loadYamlString("
tire: 
  diameter: 12
  pressure: 60
body: 
  length: 320
  width: 160
  height: 140
  color: red
", false, true, true);

echo $car;
/*
{"tire":{"diameter":12,"pressure":60},"body":{"length":320,"width":160,"height":140,"color":"red"}}
*/

// to get color

echo $car->getBody()->getColor();

```

### From Yaml File

```yaml
# config.yml

tire: 
  diameter: 12
  pressure: 60
body: 
  length: 320
  width: 160
  height: 140
  color: red

```

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
// load file config.yml
// will not replace value with environment variable
// load as object instead of associated array
$car->loadYamlFile("config.yml", false, true, true);

echo $car;
/*
{"tire":{"diameter":12,"pressure":60},"body":{"length":320,"width":160,"height":140,"color":"red"}}
*/

// to get color

echo $car->getBody()->getColor();

```
