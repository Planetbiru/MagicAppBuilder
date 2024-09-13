# MagicObject Installation

To install **MagicObbject**

```
composer require planetbiru/magic-object
```

or if composer is not installed

```
php composer.phar require planetbiru/magic-object
```

To remove **MagicObbject**

```
composer remove planetbiru/magic-object
```

or if composer is not installed

```
php composer.phar remove planetbiru/magic-object
```

To install composer on your PC or download latest composer.phar, click https://getcomposer.org/download/ 
# MagicObject Implementation
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

## Extends MagicObject

User can extend `MagicObject` to many classes.

```php
<?php

use MagicObject\MagicObject;

/**
 * Example to extends MagicObject
 * 
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 */
class MyObject extends MagicObject
{
    
}
```

### Class Parameters

**@JSON**

`@JSON` is parameter to inform how the object will be serialized.

Attributes:
1. `property-naming-strategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `__toString()` method called.
- `CAMEL_CASE` all properties will be camel case when `__toString()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.


Default value: `CAMEL_CASE`

1. `prettify`

Allowed value:

- `true` JSON string will be prettified
- `false` JSON string will not be prettified

Default value: `false`

**@Yaml**

`@Yaml` is parameter to inform how the object will be serialized.

Attributes:
1. `property-naming-strategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `dumpYaml()` method called.
- `CAMEL_CASE` all properties will be camel case when `dumpYaml()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

Default value: `CAMEL_CASE`

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

## Object from INI

INI not support multilevel object. If multilevel object needed, use Yaml instead.

### From INI String

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new MagicObject();
$cfg->loadIniString("
app_name = MusicProductionManager
base_song_path = /var/www/songs
", false);

```

### From INI File

Load config from `config.ini` file

```ini
app_name = MusicProductionManager
base_song_path = /var/www/songs
```

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new MagicObject();
$cfg->loadIniFile(__DIR__ . "/config.ini", false);

```
## Environment Variable

Many application use environment variable to store the config. We can replace the config template with the environment variable. We must set the environment variable to the server before run the application.

 

```yaml
# config.yml

tire: 
  diameter: ${TIRE_DIAMETER}
  pressure: ${TIRE_PRESSURE}
body: 
  length: ${BODY_LENGTH}
  width: ${BODY_WIDTH}
  height: ${BODY_HEIGHT}
  color: ${BODY_COLOR}

```

Before execute this script, user must set environment variable for `TIRE_DIAMETER`, `TIRE_PRESSURE`, `BODY_LENGTH`, `BODY_WIDTH`, `BODY_HEIGHT`, and `BODY_COLOR` depend on the operating system used.

```php
<?php
use MagicObject\MagicObject;

require_once __DIR__ . "/vendor/autoload.php";

$car = new MagicObject();
// load file config.yml
// will replace value with environment variable
// load as object instead of associated array
$car->loadYamlFile("config.yml", true, true);

echo $car;

// to get color

echo $car->getBody()->getColor();

```

### Create Yaml File

```yaml
result_per_page: 20
song_base_url: ${SONG_BASE_URL}
song_base_path: ${SONG_BASE_PATH}
song_draft_base_url: ${SONG_DRAFT_BASE_URL}
song_draft_base_path: ${SONG_DRAFT_BASE_PATH}
proxy_provider: cloudflare
app_name: Music Production Manager
user_image:
  width: 512
  height: 512
album_image:
  width: 512
  height: 512
song_image:
  width: 512
  height: 512
database:
  time_zone_system: Asia/Jakarta
  default_charset: utf8
  driver: ${APP_DATABASE_TYPE}
  host: ${APP_DATABASE_SERVER}
  port: ${APP_DATABASE_PORT}
  username: ${APP_DATABASE_USER}
  password: ${APP_DATABASE_PASSWORD}
  database_name: ${APP_DATABASE_NAME}
  database_schema: public
  time_zone: ${APP_DATABASE_TIME_ZONE}
  salt: ${APP_DATABASE_SALT}
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
vocal_guide_instrument: piano
```

### Create Environment Variable

On Windows, users can directly create environment variables either via the graphical user interface (GUI) or the `setx` command line. PHP can immediately read environment variables after Windows is restarted.

On Linux, users must create a configuration on the Apache server by creating a file with the .conf extension in the `/etc/httpd/conf.d` folder then restart Apache web server.

**Windows**

Setup environtment variable on Windows using command lines.

```bash
SETX SONG_BASE_URL "https://domain.tld/path"
SETX APP_DATABASE_TYPE "mariadb"
SETX APP_DATABASE_SERVER "localhost"
SETX APP_DATABASE_PORT "3306"
SETX APP_DATABASE_USER "user"
SETX APP_DATABASE_PASSWORD "pass"
SETX APP_DATABASE_NAME "music"
SETX APP_DATABASE_TIME_ZONE "Asia/Jakarta"
SETX APP_DATABASE_SALT "GaramDapur"
```

**Linux**

Setup environtment variable on Linux using command lines create new file configuration used by Apache web server and consumed by PHP.

```bash
echo -e '' > /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv SONG_BASE_URL "https://domain.tld/path"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_TYPE "mariadb"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_SERVER "localhost"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_PORT "3306"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_USER "user"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_PASSWORD "pass"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_NAME "music"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_TIME_ZONE "Asia/Jakarta"' >> /etc/httpd/conf.d/mpm.conf
echo -e 'SetEnv APP_DATABASE_SALT "GaramDapur"' >> /etc/httpd/conf.d/mpm.conf

service httpd restart
```

## Secret Object

### Definition

Secret Objects are very important in applications that use very sensitive and secret configurations. This configuration must be encrypted so that it cannot be seen either when someone tries to open the configuration file, environment variables, or even when the developer accidentally debugs an object related to the database so that the properties of the database object are exposed including the host name, database name, username and even password.


```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";

	/**
	 * Database schema
	 *
	 * @EncryptIn
	 * @DecryptOut
	 * @var string
	 */
	protected $databseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}
```

### Class Parameters

**@JSON**

`@JSON` is parameter to inform how the object will be serialized.

Attributes:
`property-naming-strategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `__toString()` method called.
- `CAMEL_CASE` all properties will be camel case when `__toString()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

2. `prettify`

Allowed value:

- `true` JSON string will be prettified
- `false` JSON string will not be prettified

Default value: `false`

**@Yaml**

`@JSON` is parameter to inform how the object will be serialized.

Attributes:
`property-naming-strategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `dumpYaml()` method called.
- `CAMEL_CASE` all properties will be camel case when `dumpYaml()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

### Property Parameters

1. `@EncryptIn` annotation will encrypt the value before it is assigned to the associated property with the `set` method. 
2. `@DecryptIn` annotation will decrypt the value before it is assigned to the associated property with the `set` method. 
3. `@EncryptOut` annotation will encrypt the property when application call `get` method. 
4. `@DecryptOut` annotation will decrypt the property when application call `get` method. 

### Create Secure Config

When creating a secure application configuration, users can simply use the `@EncryptOut` annotation. MagicObject will load the configuration as entered but will encrypt it when dumped to a file. For configurations that will not be encrypted, do not use `@EncryptIn`, `@DecryptIn`, `@EncryptOut`, or `@DecryptOut`. 

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class SecretGenerator extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver;

	/**
	 * Database server host
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $host;

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port;

	/**
	 * Database username
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $username;

	/**
	 * Database user password
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $password;

	/**
	 * Database name
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $databaseName;

	/**
	 * Database schema
	 *
	 * @EncryptOut
	 * @var string
	 */
	protected $databseSchema;

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone;
}
```

```php

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml);
$generator = new SecretGenerator($config);

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

Do not use standard encryption keys when creating or using SecretObjects. Always use your own key. The encryption key must be generated using a callback function. Do not enter it as an object property or constant.

```php

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml, false, true, true);
$generator = new SecretGenerator($config, function(){
	// define your own key here
	return "6619f3e7a1a9f0e75838d41ff368f72868e656b251e67e8358bef8483ab0d51c";
});

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

or you can also call another function. 

```php

function getSecure()
{
	return "6619f3e7a1a9f0e75838d41ff368f72868e656b251e67e8358bef8483ab0d51c";
}

$yaml = "  
time_zone_system: Asia/Jakarta
default_charset: utf8
driver: mysql
host: localhost
port: 3306
username: root
password: password
database_name: music
database_schema: public
time_zone: Asia/Jakarta
salt: GaramDapur
";

$config = new MagicObject();
$config->loadYamlString($yaml, false, true, true);
$generator = new SecretGenerator($config, function(){
	// define your own key here
	return getSecure();
});

echo $generator; // will print JSON

$secretYaml = $generator->dumpYaml(null, 4, 0); // will print secret yaml

file_put_content("secret.yaml", $secretYaml); // will dump to file secret.yaml
```

### Implement Secure Config

Application configuration is usually written in a file or environment variable after being encrypted. This configuration cannot be read by anyone without decrypting it first. MagicObject will retrieve the encrypted value. If a user accidentally dumps an object using `var_dump` or `print_r`, then PHP will only display the encrypted value. When PHP makes a connection to the database using a credential, MagicObject will decrypt it but the value will not be stored in the object's properties.

Thus, to create an application configuration, it is enough to use the `@DecryptOut` annotation. Thus, MagicObject will only decrypt the configuration when it is ready to be used.

**Example 1**

```php
<?php

namespace MagicObject\Database;

use MagicObject\SecretObject;

class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";

	/**
	 * Database schema
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}
```

### Multilevel Object Secure

MagicObject also support Multilevel Yaml.

**Example**

```php
<?php

use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @JSON(property-naming-strategy=SNAKE_CASE)
 */
class ConfigSecret1 extends SecretObject
{
    /**
     * Database
     * 
     * @EncryptOut
     * @var mixed
     */
    protected $database;

    /**
     * Session
     * 
     * @EncryptOut
     * @var mixed
     */
    protected $session;
}

/**
 * @JSON(property-naming-strategy=SNAKE_CASE)
 */
class ConfigSecret2 extends SecretObject
{
    /**
     * Database
     * 
     * @DecryptOut
     * @var mixed
     */
    protected $database;

    /**
     * Session
     * 
     * @DecryptOut
     * @var mixed
     */
    protected $session;
}

$secret1 = new ConfigSecret1(null, function(){
    return bin2hex("This is your secure key for Scrt");
});

$yaml1 = "
result_per_page: 20
song_base_url: http//domain.tld/songs
song_base_path: /var/www/songs
song_draft_base_url: http//domain.tld/songs-draft
song_draft_base_path: /var/www/songs-draft
proxy_provider: cloudflare
app_name: Music Production Manager
user_image:
  width: 512
  height: 512
album_image:
  width: 512
  height: 512
song_image:
  width: 512
  height: 512
database:
  time_zone_system: Asia/Jakarta
  default_charset: utf8
  driver: mysql
  host: localhost
  port: 3306
  username: user
  password: pass
  database_name: music
  database_schema: public
  time_zone: Asia/Jakarta
  salt: Asia/Jakarta
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: files
  save_path: /tmp/session
vocal_guide_instrument: piano
";
$secret1->loadYamlString(
$yaml1,
false, true, true
);

echo $secret1->dumpYaml(null, 4);

$yaml2 = "
database:
    time_zone_system: UEMGavyLkN7rFAmoXBsdDKwuGC+zFttpPTAaqeMH0XUEZaAMKbyvykNtfqT+F8FcAbQCUHV66qjYfjArzgrHlA==
    default_charset: dawURojqYqXdvt2YdZ+kWsq47bcA7FKWnEfGHPMxJr3KUyKxAC0VrH8Khfqcm5iIzQFHE/1wQbdgxJNiffkayw==
    driver: Ur4FCOYvXGPpoMpHm3fwhdK5D3SaP0+MGe4IuPAvpzhAvRcjOW7EZe5VvDAf+0CLeus9tCqqE1sTXj/dxfmkaA==
    host: zyfIMxYi/qQAbazR+nOaLnXFUN9qDYiapxlvocKYxkL8uuN6zRrP3Jsj0mlj6UnLOlvhfDgF3Pq0PrP2ZORGNg==
    port: Cs3NTbxIXJ0lf1umQuuDXbPqdBPlVg+jeXi6UqqUUvVUQWgAhbfenfP8g81cONoY2dXof+P1V5Gr/q+iDLNv5Q==
    username: 5NImv2VEL1WSbt3cqx7gi/8f158SYtssj74zTN2fRrIDsGOxsnEa8+50H3Y1MCaJV7SnZo851dnjEhm38Tzjsg==
    password: /1SK1m9qVjbWnGa6/xai2H82OWvzXeErRvtQ0RYceGsr3fvAfGfqcDSY6pq8KoXg6MmtJS0FLyjXUq8dftM7yQ==
    database_name: ZQ0PTeDl7AOtjDBV1PHzlwMbPBVZmhXuO65O1pDYYCGQSMNf3GpQJi9SkvGApQ8kBKUgPAmchCTYb8ChL2szrQ==
    database_schema: 628aqGw8hLV8Malwg6jninVfNRgDUgANtXdPfIuz8IohoMQ4NROIuj7Y28/SeLD74NPoiBYFZpaON9+jV8QQug==
    time_zone: zfpl4IxctWh2Y/UnrANjuCKlnevw8MmoSboMtzV3oBzKKN0gg+TT/Zz/QieHkVd+pn7OHx2OXvLadkzzWQTMUQ==
    salt: hdsBgcmvq/rzfNUuhgQiBop4Hp4wqd5w03Il9zaOshK0LgGtaEK1WTcPx5OdL+9VvhmZvs7g/jDGYjYoHwXjIg==
session:
    name: 4u5xAE2K74pUxZyIVsphoYUka3vpnzUx7op6CnELtdWNEepz/jQLaKynItFt5dx6bv7wjBUFL1AaZaA4ypw/CP6xtR5WFQy+RV6V8VqYM8o=
    max_life_time: cspEenRp8+kwUY1RNvmEdWcLqmsRZ+UJZVjY4JwRsuIIfkr+J0w1SYCuRMzMtHG4/hZ5tlXhtGdRZyM7quCbZg==
    save_handler: LIlCCaWHVqg9R4G6ghxDZnuenMLgSI6HjiW+tVGNDa7UyIA7FkFtOgOJtvT/EolUc+kkJSXiMo+76QhvFFq8Dg==
    save_path: CZijzyucTzrj3tZ1M9PbQ6Hky1+4Gz3RnXwZNSe9/SL+9QZdpK4PoW2TSLsuQ+cKBgKgkncd7JXWgA3CFg0f1A==
result_per_page: 20
song_base_url: http//domain.tld/songs
song_base_path: /var/www/songs
song_draft_base_url: http//domain.tld/songs-draft
song_draft_base_path: /var/www/songs-draft
proxy_provider: cloudflare
app_name: 'Music Production Manager'
user_image:
    width: 512
    height: 512
album_image:
    width: 512
    height: 512
song_image:
    width: 512
    height: 512
vocal_guide_instrument: piano
";

$secret2 = new ConfigSecret2(null, function(){
    return bin2hex("This is your secure key for Scrt");
});$secret2->loadYamlString($yaml2, false, true, true);

echo $secret2->dumpYaml(null, 4);
```

### Secure Config from DynamicObject

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;
use MusicProductionManager\App\ShutdownManager;
use MusicProductionManager\Config\ConfigApp;
use MagicObject\SecretObject;
namespace MagicObject\Database;

require_once __DIR__ . "/vendor/autoload.php";

/**
 * PicoDatabaseCredentials class
 * The SecretObject will encrypt all attributes to prevent unauthorized user read the database configuration
 */
class PicoDatabaseCredentials extends SecretObject
{
	/**
	 * Database driver
	 *
	 * @var string
	 */
	protected $driver = 'mysql';

	/**
	 * Database server host
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * Database server port
	 * @var integer
	 */
	protected $port = 3306;

	/**
	 * Database username
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $username = "";

	/**
	 * Database user password
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $password = "";

	/**
	 * Database name
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databaseName = "";
	
	/**
	 * Database schema
	 *
	 * @DecryptOut
	 * @var string
	 */
	protected $databseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";
}

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());

$database = new PicoDatabase($databaseCredentials, 
    function($sql, $type) //NOSONAR
    {
        // callback when execute query that modify data
    }, 
    function($sql) //NOSONAR
    {
        // callback when execute all query
    }
);

try
{
    $database->connect();
    $shutdownManager = new ShutdownManager($database);
    $shutdownManager->registerShutdown();
}
catch(Exception $e)
{
    // do nothing
}

```
## Input POST/GET/COOKIE/REQUEST/SERVER

### Input POST

```php

use MagicObject\Request\InputPost;

require_once __DIR__ . "/vendor/autoload.php";

$inputPost = new InputPost();

$name = $inputPost->getRealName();
// equivalen to 
$name = $_POST['real_name'];

```

### Input GET

```php

use MagicObject\Request\InputGet;

require_once __DIR__ . "/vendor/autoload.php";

$inputGet = new InputGet();

$name = $inputGet->getRealName();
// equivalen to 
$name = $_GET['real_name'];

```

### Input COOKIE

```php

use MagicObject\Request\InputCookie;

require_once __DIR__ . "/vendor/autoload.php";

$inputCookie = new InputCookie();

$name = $inputCookie->getRealName();
// equivalen to 
$name = $_COOKIE['real_name'];

```

### Input REQUEST

```php

use MagicObject\Request\InputRequest;

require_once __DIR__ . "/vendor/autoload.php";

$inputRequest = new InputRequest();

$name = $inputRequest->getRealName();
// equivalen to 
$name = $_REQUEST['real_name'];

```

### Input SERVER

```php

use MagicObject\Request\InputServer;

require_once __DIR__ . "/vendor/autoload.php";

$inputServer = new InputServer();

$remoteAddress = $inputServer->getRemoteAddr();
// equivalen to 
$remoteAddress = $_SERVER_['REMOTE_ADDR'];

```

### Filter Input

Filter input from InputGet, InputPost, InputRequest and InputCookie

```php

use MagicObject\Request\InputGet;
use MagicObject\Request\PicoFilterConstant;

require_once __DIR__ . "/vendor/autoload.php";

$inputGet = new InputGet();

$name = $inputGet->getRealName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
// equivalen to 
$name =  filter_input(
    INPUT_GET,
    'real_name',
    FILTER_SANITIZE_SPECIAL_CHARS
);


// another way to filter input
// it will update value taken from $_GET['real_name']
$inputGet->filterRealName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);

// so you can use it later
$name = $inputGet->getRealName();

```

List of filter

```
FILTER_SANITIZE_NO_DOUBLE_SPACE = 512;
FILTER_SANITIZE_PASSWORD = 511;
FILTER_SANITIZE_ALPHA = 510;
FILTER_SANITIZE_ALPHANUMERIC = 509;
FILTER_SANITIZE_ALPHANUMERICPUNC = 506;
FILTER_SANITIZE_NUMBER_UINT = 508;
FILTER_SANITIZE_NUMBER_INT = 519;
FILTER_SANITIZE_URL = 518;
FILTER_SANITIZE_NUMBER_FLOAT = 520;
FILTER_SANITIZE_STRING_NEW = 513;
FILTER_SANITIZE_ENCODED = 514;
FILTER_SANITIZE_STRING_INLINE = 507;
FILTER_SANITIZE_STRING_BASE64 = 505;
FILTER_SANITIZE_IP = 504;
FILTER_SANITIZE_NUMBER_OCTAL = 503;
FILTER_SANITIZE_NUMBER_HEXADECIMAL = 502;
FILTER_SANITIZE_COLOR = 501;
FILTER_SANITIZE_POINT = 500;
FILTER_SANITIZE_BOOL = 600;
FILTER_VALIDATE_URL = 273;
FILTER_VALIDATE_EMAIL = 274;
FILTER_SANITIZE_EMAIL = 517;
FILTER_SANITIZE_SPECIAL_CHARS = 515;
FILTER_SANITIZE_ASCII = 601;
```

## Session

Session variables keep information about one single user, and are available to all pages in one application.

### Session with File

**Yaml File**

```yaml
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: files
  save_path: /tmp/sessions
```

**PHP Script**

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(__DIR__ . "/.cfg/app.yml", true, true);

$sessConf = new SecretObject($cfg->getSession());
$sessions = new PicoSession($sessConf);

$sessions->startSession();
```

### Session with Redis

**Yaml File**

```yaml
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: redis
  save_path: tcp://127.0.0.1:6379?auth=myredispass
```

or

```yaml
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: redis
  save_path: tcp://127.0.0.1:6379?auth=${REDIS_AUTH}
```

**WARNING!**

You can not encrypt the `${REDIS_AUTH}` value. If you want to secure the config, encrypt entire `save_path` instead.

For example:

```yaml
session:
  name: MUSICPRODUCTIONMANAGER
  max_life_time: 86400
  save_handler: redis
  save_path: ${SESSION_SAVE_PATH}
```

`${SESSION_SAVE_PATH}` contains entire of `save_path` that encrypted with you secure key.

**PHP Script**

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(__DIR__ . "/.cfg/app.yml", true, true);

$sessConf = new SecretObject($cfg->getSession());
$sessions = new PicoSession($sessConf);

$sessions->startSession();
```

If you want to encrypt `session.save_path`, you must define your own class.

**PHP Script**

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Session\PicoSession;

require_once __DIR__ . "/vendor/autoload.php";

class SessionSecureConfig extends SecretObject
{
  /**
   * Session save path
   * save_path is encrypted and stored in ${SESSION_SAVE_PATH}. To decrypt it, use anotation DecryptOut
   * 
   * @DecryptOut
   * @var string
   */ 
  protected $savePath;
}

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(__DIR__ . "/.cfg/app.yml", true, true);

$sessConf = new SessionSecureConfig($cfg->getSession());
$sessions = new PicoSession($sessConf);

$sessions->startSession();
```
## Entity

Entity is class to access database. Entity is derived from MagicObject. Some annotations required to activated all entity features. 

**Constructor**

Parameters:

1. array|stdClass|object $data

Initial data

2. PicoDatabase $database

Database connection

When constructed, the `$album` object uses the `$database1` database connection. The application retrieves data with the primary key `123456` from `$database1` and then stores it in `$database2` without having to change many parameters and lots of code. Please note that to save `$album` data, you must use the `save` method instead of `insert` or `update` because there is a possibility that data with the same primary key already exists in `$database2`.

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="album")
 */
class Album extends MagicObject
{
	/**
	 * Album ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="longtext", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Release Date
	 * 
	 * @Column(name="release_date", type="date", nullable=true)
	 * @Label(content="Release Date")
	 * @var string
	 */
	protected $releaseDate;

	/**
	 * Number Of Song
	 * 
	 * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
	 * @Label(content="Number Of Song")
	 * @var integer
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $asDraft;

}
```

 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")

### Class Parameters

**@Entity**

`@Entity` is parameter to validate that the object is an entity.

**@JSON**

`@JSON` is parameter to inform how the object will be serialized.

Attributes:
1. `property-naming-strategy`

Allowed value:

- `SNAKE_CASE` all properties will be snake case when `__toString()` method called.
- `CAMEL_CASE` all properties will be camel case when `__toString()` method called.
- `UPPER_CAMEL_CASE` all properties will be camel case with capitalize first character when `__toString()` method called.

Default value: `CAMEL_CASE`

2. `prettify`

Allowed value:

- `true` JSON string will be prettified
- `false` JSON string will not be prettified

Default value: `false`

**@Table**

`@Table` is parameter contains table information.

Attributes:
`name`

`name` is the table name of the entity.

### Property Parameters

* @Id
* @GeneratedValue(strategy=GenerationType.UUID)
* @NotNull
* @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
* @Label(content="Album ID")
* @var string

**@Id**

`@Id` indicate that the column is primary key.

**@GeneratedValue**

`@GeneratedValue` indicated that the column is has autogenerated value.

Attributes:
- `strategy`
- `generator`

`strategy` is strategy to generate auto value.

Allowed value:

**1. GenerationType.UUID**

Generate 20 bytes unique ID

- 14 byte hexadecimal of uniqid https://www.php.net/manual/en/function.uniqid.php
- 6 byte hexadecimal or random number

**2. GenerationType.IDENTITY**

Autoincrement using database feature

**3. TABLE** Not implemented yet

**4. SEQUENCE** Not implemented yet

**5. AUTO** Not implemented yet 


MagicObject will not update `time_create`, `admin_create`, and `ip_create` because `updatable=false`. So, even if the application wants to update this value, this column will be ignored when performing an update query to the database.

`generator` is generator of the value.

**@NotNull**

`@NotNull` indicate that the column is not null. MagicObject will use it when user insert or update data with null values.

**@Column**

`@Column` is parameter to store the information of the column.

Attributes:
- `name`
- `type`
- `length`
- `nullable`
- `default_value`
- `insertable`
- `updatable`

`name` is column name.

`type` is column type.

`length` is column length if any.

`nullable` indicate that column value can be `null` or not. Available value of `nullable` is `true` and `false`. 

`default_value` is default value of the column.

`insertable` indicate that column will exists on `INSERT` statement. Available value of `insertable` is `true` and `false`. 

`updatable` indicate that column will exists on `UPDATE` statement. Available value of `updatable` is `true` and `false`. 


**@JoinColumn**

`@JoinColumn` is parameter to store the information of the join column.

Attributes:
- `name`
- `referenceColumnName`

`name` is column name of the master table.

`referenceColumnName` is column name of the join table. If `referenceColumnName` is not exists, MagicObject will use value on `name` as reference column name.

**@Label** is parameter to store label of the column.

Attributes:
- `content`

`content` is the content of the column label. Use quote to create label. For example `@Label(content="Album ID")`.


**@var**

`@var` is native annotation of class field. MagicObject use this annotation to fix the column value given.


### Usage

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;
use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Data\Entity\Album;

require_once dirname(__DIR__)."/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());
$database = new PicoDatabase($databaseCredentials);
try
{
    $database->connect();
  
    // create new 
  
    $album1 = new Album(null, $database);
    $album1->setAlbumId("123456");
    $album1->setName("Album 1");
    $album1->setAdminCreate("USER1");
    $album1->setDuration(300);
  
  
  
    // other way to create object
    // create object from stdClass or other object with match property (snake case or camel case)
    $data = new stdClass;
    // snake case
    $data->album_id = "123456";
    $data->name = "Album 1";
    $data->admin_create = "USER1";
    $data->duration = 300;
  
    // or camel case
    $data->albumId = "123456";
    $data->name = "Album 1";
    $data->adminCreate = "USER1";
    $data->duration = 300;
  
    $album1 = new Album($data, $database); 
  
    // other way to create object
    // create object from associated array with match property (snake case or camel case)
    $data = array();
    // snake case
    $data["album_id"] "123456";
    $data["name"] = "Album 1";
    $data["admin_create"] = "USER1";
    $data["duration"] = 300;
  
    // or camel case
    $data["albumId"] = "123456";
    $data["name"] = "Album 1";
    $data["adminCreate"] = "USER1";
    $data["duration"] = 300;
    $album1 = new Album($data, $database);
  
  
    // get value from form
    // this way is not safe
    $album1 = new Album($_POST, $database);
  
  
    // we can use other way
    $inputPost = new InputPost();
  
    // we can apply filter
    $inputPost->filterName(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
    $inputPost->filterDescription(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);
  
    // if property not present in $inputPost, we can set default value
    // please note that user can modify form and add update any unwanted properties to be updated
    $inputPost->checkboxActive(false);
    $inputPost->checkboxAsDraft(true);
  
    // we can remove any property data from object $inputPost before apply it to entity
    // it will not saved to database
    $inputPost->setSortOrder(null);
  
    $album1 = new Album($inputPost, $database);
  
    // insert to database
    $album1->insert();
  
    // insert or update
    $album1->save();
  
    // update
    // NoRecordFoundException if ID not found
    $album1->update();
  
    // convert to JSON
    $json = $album1->toString();
    // or
    $json = $album1 . "";
  
    // send to buffer output
    // automaticaly converted to string
    echo $album1;
  
    // find one by ID
    $album2 = new Album(null, $database);
    $album2->findOneByAlbumId("123456");
  
    // find multiple
    $album2 = new Album(null, $database);
    $albums = $album2->findByAdminCreate("USER1");
    $rows = $albums->getResult();
    foreach($rows as $albumSaved)
    {
        // $albumSaved is instance of Album
  
        // we can update data
        $albumSaved->setAdminEdit("USER1");
        $albumSaved->setTimeEdit(date('Y-m-d H:i:s'));
  
        // this value will not be saved to database because has no column
        $albumSaved->setAnyValue("ANY VALUE");
  
        $albumSaved->update();
    }
  
  
}
catch(Exception $e)
{
    // do nothing
}

```


### Insert

Insert new record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setDuration(300);
try
{
	$album->insert();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

To insert with any column value `NULL`

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setDuration(300);
$album1->setReleaseDate(null);
$album1->setNumberOfSong(null);
try
{
	// releaseDate and numberOfSong will set to NULL
	$album->insert(true);
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Save

Insert new record if not exists, otherwise update the record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminCreate("USER1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
try
{
	$album->save();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Note: If operation is update, nonupdatable column will not be updated

### Update

Update existing record

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
try
{
	$album->update();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

To update any column value to `NULL`

```php
$album1 = new Album(null, $database);
$album1->setAlbumId("123456");
$album1->setName("Album 1");
$album1->setAdminEdit("USER1");
$album1->setDuration(300);
$album1->setReleaseDate(null);
$album1->setNumberOfSong(null);
try
{
	// releaseDate and numberOfSong will set to NULL
	$album->update(true);
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select One By Specific Column

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAlbumId("123456");

	// to update the record

	// update begin
	$album1->setName("Album 1");
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select One By Combination of Columns

Logic `OR`

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAlbumIdOrNumbefOfSong("123456", 3);

	// to update the record

	// update begin
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Logic `AND`

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAdminCreateAndNumbefOfSong("USER1", 3);

	// to update the record

	// update begin
	$album1->setAdminEdit("USER1");
	$album1->setDuration(300);
	$album->update();
	// update end

	// to delete the record

	// delete begin
	$album->delete();
	// delete end
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Select Multiple By Combination of Columns

Logic `OR`

```php
$albumSelector = new Album(null, $database);
try
{
	$albums = $albumSelector->findByAlbumIdOrNumbefOfSong("123456", 3);
	
	$result = $albums->getResult();
	foreach($result as $album1)
	{
		// to update the record

		// update begin
		$album1->setAdminEdit("USER1");
		$album1->setDuration(300);
		$album->update();
		// update end

		// to delete the record

		// delete begin
		$album->delete();
		// delete end
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

Logic `AND`

```php
$albumSelector = new Album(null, $database);
try
{
	$albums = $albumSelector->findOneByAdminCreateAndNumbefOfSong("USER1", 3);
	
	$result = $albums->getResult();
	foreach($result as $album1)
	{
		// to update the record

		// update begin
		$album1->setAdminEdit("USER1");
		$album1->setDuration(300);
		$album->update();
		// update end

		// to delete the record

		// delete begin
		$album->delete();
		// delete end
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Find By Specification

Real applications do not always use simple logic to filter database records. Complex logic cannot be done using just one method. MagicObject provides features to make searches more specific.

**Example 1**

```php
$album = new Album(null, $database);
$rowData = array();
try
{
	$album->findOneByAlbumId($inputGet->getAlbumId());

	$sortable = new PicoSortable();
	$sort2 = new PicoSort('trackNumber', PicoSort::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);

	$spesification = new PicoSpecification();

	$predicate1 = new PicoPredicate();
	$predicate1->equals('albumId', $inputGet->getAlbumId());
	$spesification->addAnd($predicate1);

	$predicate2 = new PicoPredicate();
	$predicate2->equals('active', true);
	$spesification->addAnd($predicate2);
	
	// Up to this point we are still using albumId and active

	$pageData = $player->findAll($spesification, null, $sortable, true);
	$rowData = $pageData->getResult();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}

if(!empty($rowData))
{
	foreach($rowData $album)
	{
		// do something here
		// $album is instanceof Album class
		// You can use all its features
	}
}
```

**Example 2**

Album specification from `$_GET`

```php

/**
 * Create album specification
 * @param PicoRequestBase $inputGet
 * @return PicoSpecification
 */
function createAlbumSpecification($inputGet)
{
	$spesification = new PicoSpecification();

	if($inputGet->getAlbumId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('albumId', $inputGet->getAlbumId());
		$spesification->addAnd($predicate1);
	}

	if($inputGet->getName() != "" || $inputGet->getTitle() != "")
	{
		$spesificationTitle = new PicoSpecification();
		
		if($inputGet->getName() != "")
		{
			$predicate1 = new PicoPredicate();
			$predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
			$spesificationTitle->addOr($predicate1);
			
			$predicate2 = new PicoPredicate();
			$predicate2->like('title', PicoPredicate::generateLikeContains($inputGet->getName()));
			$spesificationTitle->addOr($predicate2);
		}
		if($inputGet->getTitle() != "")
		{
			$predicate3 = new PicoPredicate();
			$predicate3->like('name', PicoPredicate::generateLikeContains($inputGet->getTitle()));
			$spesificationTitle->addOr($predicate3);
			
			$predicate4 = new PicoPredicate();
			$predicate4->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
			$spesificationTitle->addOr($predicate4);
		}
		
		$spesification->addAnd($spesificationTitle);
	}
	
	
	if($inputGet->getProducerId() != "")
	{
		$predicate1 = new PicoPredicate();
		$predicate1->equals('producerId', $inputGet->getProducerId());
		$spesification->addAnd($predicate1);
	}
	
	return $spesification;
}

$album = new Album(null, $database);
$rowData = array();
try
{
	$album->findOneByAlbumId($inputGet->getAlbumId());

	$sortable = new PicoSortable();
	$sort2 = new PicoSort('albumId', PicoSort::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);

	$spesification = createAlbumSpecification(new InputGet());

	$pageData = $player->findAll($spesification, null, $sortable, true);
	$rowData = $pageData->getResult();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}

if(!empty($rowData))
{
	foreach($rowData $album)
	{
		// do something here
		// $album is instanceof Album class
		// You can use all its features
	}
}
```

**Example 3**

Song specification from `$_GET`

```php
<?php

namespace MusicProductionManager\Utility;

use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSpecification;
use MagicObject\Request\PicoRequestBase;

/**
 * Specification utility
 */
class SpecificationUtil
{
    /**
     * Create MIDI specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createMidiSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getMidiId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('midiId', $inputGet->getMidiId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getTitle() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getArtistVocalistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistVocalId', $inputGet->getArtistVocalistId());
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create Song specification
     * @param PicoRequestBase $inputGet
     * $@param array|null $additional
     * @return PicoSpecification
     */
    public static function createSongSpecification($inputGet, $additional = null) //NOSONAR
    {
        $spesification = new PicoSpecification();

        if($inputGet->getSongId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('songId', $inputGet->getSongId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getProducerId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('producerId', $inputGet->getProducerId());
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getProducerName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('producer.name', PicoPredicate::generateLikeContains($inputGet->getProducerName()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "" || $inputGet->getTitle() != "")
        {
            $spesificationTitle = new PicoSpecification();
            
            if($inputGet->getName() != "")
            {
                $predicate1 = new PicoPredicate();
                $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate1);
                
                $predicate2 = new PicoPredicate();
                $predicate2->like('title', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate2);
            }
            if($inputGet->getTitle() != "")
            {
                $predicate3 = new PicoPredicate();
                $predicate3->like('name', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate3);
                
                $predicate4 = new PicoPredicate();
                $predicate4->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate4);
            }
            
            $spesification->addAnd($spesificationTitle);
        }

        if($inputGet->getSubtitle() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('subtitle', PicoPredicate::generateLikeContains($inputGet->getSubtitle()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getVocalist() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistVocalist', $inputGet->getVocalist());
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getVocalistName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('vocalist.name', PicoPredicate::generateLikeContains($inputGet->getVocalistName()));
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getComposer() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistComposer', $inputGet->getComposer());
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getComposerName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('composer.name', PicoPredicate::generateLikeContains($inputGet->getComposerName()));
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getArranger() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistArranger', $inputGet->getArranger());
            $spesification->addAnd($predicate1);
        }
        
        if($inputGet->getArrangerName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('arranger.name', PicoPredicate::generateLikeContains($inputGet->getArrangerName()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getSubtitleComplete() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('subtitleComplete', $inputGet->getSubtitleComplete());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getVocal() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('vocal', $inputGet->getVocal());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getActive() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('active', $inputGet->getActive());
            $spesification->addAnd($predicate1);
        }

        if(isset($additional) && is_array($additional))
        {
            foreach($additional as $key=>$value)
            {
                $predicate2 = new PicoPredicate();          
                $predicate2->equals($key, $value);
                $spesification->addAnd($predicate2);
            }
        }
        
        return $spesification;
    }

    /**
     * Create Song specification
     * @param PicoRequestBase $inputGet
     * $@param array|null $additional
     * @return PicoSpecification
     */
    public static function createReferenceSpecification($inputGet, $additional = null)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getReferenceId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('referenceId', $inputGet->getReferenceId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getAlbum() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('album', $inputGet->getAlbum());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getTitle() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getArtistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistId', $inputGet->getArtistId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getActive() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('active', $inputGet->getActive());
            $spesification->addAnd($predicate1);
        }

        if(isset($additional) && is_array($additional))
        {
            foreach($additional as $key=>$value)
            {
                $predicate2 = new PicoPredicate();          
                $predicate2->equals($key, $value);
                $spesification->addAnd($predicate2);
            }
        }
        
        return $spesification;
    }

    /**
     * Create Song specification
     * @param PicoRequestBase $inputGet
     * $@param array|null $additional
     * @return PicoSpecification
     */
    public static function createSongDraftSpecification($inputGet, $additional = null) //NOSONAR
    {
        $spesification = new PicoSpecification();

        if($inputGet->getSongId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('songId', $inputGet->getSongId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "" || $inputGet->getTitle() != "")
        {
            $spesificationTitle = new PicoSpecification();
            
            if($inputGet->getName() != "")
            {
                $predicate1 = new PicoPredicate();
                $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate1);
                
                $predicate2 = new PicoPredicate();
                $predicate2->like('title', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate2);
            }
            if($inputGet->getTitle() != "")
            {
                $predicate3 = new PicoPredicate();
                $predicate3->like('name', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate3);
                
                $predicate4 = new PicoPredicate();
                $predicate4->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate4);
            }
            
            $spesification->addAnd($spesificationTitle);
        }

        if($inputGet->getArtistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistId', $inputGet->getArtistId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getActive() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('active', $inputGet->getActive());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getFrom() != "" && $inputGet->getTo() != "")
        {
            $to = $inputGet->getTo();
            if(strlen($to) < 11)
            {
                $to = $to . " 23:59:59";
            }
            $predicate1 = new PicoPredicate();
            $predicate1->greaterThanOrEquals('timeCreate', $inputGet->getFrom());
            $spesification->addAnd($predicate1);

            $predicate2 = new PicoPredicate();
            $predicate2->lessThanOrEquals('timeCreate', $to);
            $spesification->addAnd($predicate2);
        }
        else if($inputGet->getFrom())
        {
            $predicate1 = new PicoPredicate();
            $predicate1->greaterThanOrEquals('timeCreate', $inputGet->getFrom());
            $spesification->addAnd($predicate1);
        }
        else if($inputGet->getTo() != "")
        {
            $to = $inputGet->getTo();
            if(strlen($to) < 11)
            {
                $to = $to . " 23:59:59";
            }
            $predicate2 = new PicoPredicate();
            $predicate2->lessThanOrEquals('timeCreate', $to);
            $spesification->addAnd($predicate2);
        }

        if($inputGet->getLyric() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('lyric', PicoPredicate::generateLikeContains($inputGet->getLyric()));
            $spesification->addAnd($predicate1);
        }

        if(isset($additional) && is_array($additional))
        {
            foreach($additional as $key=>$value)
            {
                $predicate2 = new PicoPredicate();          
                $predicate2->equals($key, $value);
                $spesification->addAnd($predicate2);
            }
        }
        
        return $spesification;
    }

    /**
     * Create album specification
     * @param PicoRequestBase $inputGet
     * @return PicoSpecification
     */
    public static function createAlbumSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getAlbumId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('albumId', $inputGet->getAlbumId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "" || $inputGet->getTitle() != "")
        {
            $spesificationTitle = new PicoSpecification();
            
            if($inputGet->getName() != "")
            {
                $predicate1 = new PicoPredicate();
                $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate1);
                
                $predicate2 = new PicoPredicate();
                $predicate2->like('title', PicoPredicate::generateLikeContains($inputGet->getName()));
                $spesificationTitle->addOr($predicate2);
            }
            if($inputGet->getTitle() != "")
            {
                $predicate3 = new PicoPredicate();
                $predicate3->like('name', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate3);
                
                $predicate4 = new PicoPredicate();
                $predicate4->like('title', PicoPredicate::generateLikeContains($inputGet->getTitle()));
                $spesificationTitle->addOr($predicate4);
            }
            
            $spesification->addAnd($spesificationTitle);
        }
        
        
        if($inputGet->getProducerId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('producerId', $inputGet->getProducerId());
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create genre specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createGenreSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getGenreId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('genreId', $inputGet->getGenreId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create user type specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createUserTypeSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getUserTypeId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('userTypeId', $inputGet->getUserTypeId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create artist specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createArtistSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getArtistId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('artistId', $inputGet->getArtistId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }

    /**
     * Create user specification
     * @param PicoRequestBase $name
     * @return PicoSpecification
     */
    public static function createUserSpecification($inputGet)
    {
        $spesification = new PicoSpecification();

        if($inputGet->getUserId() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->equals('userId', $inputGet->getUserId());
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getName() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('name', PicoPredicate::generateLikeContains($inputGet->getName()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getUsername() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('username', PicoPredicate::generateLikeContains($inputGet->getUsername()));
            $spesification->addAnd($predicate1);
        }

        if($inputGet->getEmail() != "")
        {
            $predicate1 = new PicoPredicate();
            $predicate1->like('email', PicoPredicate::generateLikeContains($inputGet->getEmail()));
            $spesification->addAnd($predicate1);
        }
        
        return $spesification;
    }
}
```

Implementation

```php
$orderMap = array(
    'name'=>'name', 
    'title'=>'title', 
    'rating'=>'rating',
    'albumId'=>'albumId', 
    'album'=>'albumId', 
    'trackNumber'=>'trackNumber',
    'genreId'=>'genreId', 
    'genre'=>'genreId',
    'producerId'=>'producerId',
    'artistVocalId'=>'artistVocalId',
    'artistVocalist'=>'artistVocalId',
    'artistComposer'=>'artistComposer',
    'artistArranger'=>'artistArranger',
    'duration'=>'duration',
    'subtitleComplete'=>'subtitleComplete',
    'vocal'=>'vocal',
    'active'=>'active'
);
$defaultOrderBy = 'albumId';
$defaultOrderType = 'desc';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createSongSpecification($inputGet);

if($pagination->getOrderBy() == '')
{
	$sortable = new PicoSortable();
	$sort1 = new PicoSort('albumId', PicoSort::ORDER_TYPE_DESC);
	$sortable->addSortable($sort1);
	$sort2 = new PicoSort('trackNumber', PicoSort::ORDER_TYPE_ASC);
	$sortable->addSortable($sort2);
}
else
{
	$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $defaultOrderBy), $pagination->getOrderType($defaultOrderType));
}

$pageable = new PicoPageable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$songEntity = new Song(null, $database);
$pageData = $songEntity->findAll($spesification, $pageable, $sortable, true);

$rowData = $pageData->getResult();

if(!empty($rowData))
{
	foreach($rowData $song)
	{
		// do something here
		// $song is instanceof Song class
		// You can use all its features
	}
}
	
```

### Delete

To delete the database record, just invoke the `delete` method of the entity.

**Example 1**

Delete one record without select first

```php
$album1 = new Album(null, $database);
try
{
	$album1->deleteOneByAlbumId("123456");
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**Example 2**

Delete multiple records without select first

```php
$album1 = new Album(null, $database);
try
{
	$deleted = $album1->deleteByAdminCreate("123456");
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**Example 3**

Delete one record with select first

```php
$album1 = new Album(null, $database);
try
{
	$album1->findOneByAlbumId("123456");
	$album1->delete();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**Example 4**

Delete multiple records with select first

```php
$album1 = new Album(null, $database);
try
{
	$pageData = $album1->findByAdminCreate("123456");
	foreach($pageData->getResult() as $album)
	{
		// we can add logic before delete the record
		$album->delete();
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Join the Entity

Users can join entities with other entities. This joining can be done in stages, not limited to just two levels. Please note that using multi-level joins will reduce application performance and waste resource usage. Consider denormalizing the database for applications with large amounts of data.

The following example is a two-level entity join. Users can expand it into three levels, for example by joining the `Album` or `Artist` entity with another new entity.

WARNING:
Don't join entities recursively because it will make the system carry out an endless process.

**EntitySong**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="song")
 */
class EntitySong extends MagicObject
{
	/**
	 * Song ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="song_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Song ID")
	 * @var string
	 */
	protected $songId;

	/**
	 * Random Song ID
	 * 
	 * @Column(name="random_song_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Random Song ID")
	 * @var string
	 */
	protected $randomSongId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Album ID
	 * 
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Album
	 * @JoinColumn(name="album_id")
	 * @Label(content="Album")
	 * @var Album
	 */
	protected $album;

	/**
	 * Track Number
	 * 
	 * @Column(name="track_number", type="int(11)", length=11, nullable=true)
	 * @Label(content="Track Number")
	 * @var integer
	 */
	protected $trackNumber;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

	/**
	 * Artist Vocal
	 * 
	 * @Column(name="artist_vocalist", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Vocal")
	 * @var string
	 */
	protected $artistVocalist;

	/**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;

	/**
	 * Artist Composer
	 * 
	 * @Column(name="artist_composer", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Composer")
	 * @var string
	 */
	protected $artistComposer;

	/**
	 * Artist Composer
	 * 
	 * @JoinColumn(name="artist_composer")
	 * @Label(content="Artist Composer")
	 * @var Artist
	 */
	protected $composer;

	/**
	 * Artist Arranger
	 * 
	 * @Column(name="artist_arranger", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Arranger")
	 * @var string
	 */
	protected $artistArranger;

	/**
	 * Artist Arranger
	 * 
	 * @JoinColumn(name="artist_arranger")
	 * @Label(content="Artist Arranger")
	 * @var Artist
	 */
	protected $arranger;

	/**
	 * File Path
	 * 
	 * @Column(name="file_path", type="text", nullable=true)
	 * @Label(content="File Path")
	 * @var string
	 */
	protected $filePath;

	/**
	 * File Name
	 * 
	 * @Column(name="file_name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Name")
	 * @var string
	 */
	protected $fileName;

	/**
	 * File Type
	 * 
	 * @Column(name="file_type", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Type")
	 * @var string
	 */
	protected $fileType;

	/**
	 * File Extension
	 * 
	 * @Column(name="file_extension", type="varchar(20)", length=20, nullable=true)
	 * @Label(content="File Extension")
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * File Size
	 * 
	 * @Column(name="file_size", type="bigint(20)", length=20, nullable=true)
	 * @Label(content="File Size")
	 * @var integer
	 */
	protected $fileSize;

	/**
	 * File Md5
	 * 
	 * @Column(name="file_md5", type="varchar(32)", length=32, nullable=true)
	 * @Label(content="File Md5")
	 * @var string
	 */
	protected $fileMd5;

	/**
	 * File Upload Time
	 * 
	 * @Column(name="file_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="File Upload Time")
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * First Upload Time
	 * 
	 * @Column(name="first_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="First Upload Time")
	 * @var string
	 */
	protected $firstUploadTime;

	/**
	 * Last Upload Time
	 * 
	 * @Column(name="last_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time")
	 * @var string
	 */
	protected $lastUploadTime;

	/**
	 * File Path Midi
	 * 
	 * @Column(name="file_path_midi", type="text", nullable=true)
	 * @Label(content="File Path Midi")
	 * @var string
	 */
	protected $filePathMidi;

	/**
	 * Last Upload Time Midi
	 * 
	 * @Column(name="last_upload_time_midi", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Midi")
	 * @var string
	 */
	protected $lastUploadTimeMidi;

	/**
	 * File Path Xml
	 * 
	 * @Column(name="file_path_xml", type="text", nullable=true)
	 * @Label(content="File Path Xml")
	 * @var string
	 */
	protected $filePathXml;

	/**
	 * Last Upload Time Xml
	 * 
	 * @Column(name="last_upload_time_xml", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Xml")
	 * @var string
	 */
	protected $lastUploadTimeXml;

	/**
	 * File Path Pdf
	 * 
	 * @Column(name="file_path_pdf", type="text", nullable=true)
	 * @Label(content="File Path Pdf")
	 * @var string
	 */
	protected $filePathPdf;

	/**
	 * Last Upload Time Pdf
	 * 
	 * @Column(name="last_upload_time_pdf", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Pdf")
	 * @var string
	 */
	protected $lastUploadTimePdf;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Genre ID
	 * 
	 * @Column(name="genre_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Genre ID")
	 * @var string
	 */
	protected $genreId;

	/**
	 * Genre ID
	 * 
	 * @JoinColumn(name="genre_id")
	 * @Label(content="Genre ID")
	 * @var Genre
	 */
	protected $genre;

	/**
	 * Bpm
	 * 
	 * @Column(name="bpm", type="float", nullable=true)
	 * @Label(content="Bpm")
	 * @var double
	 */
	protected $bpm;

	/**
	 * Time Signature
	 * 
	 * @Column(name="time_signature", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Time Signature")
	 * @var string
	 */
	protected $timeSignature;

	/**
	 * Subtitle
	 * 
	 * @Column(name="subtitle", type="longtext", nullable=true)
	 * @Label(content="Subtitle")
	 * @var string
	 */
	protected $subtitle;

	/**
	 * Subtitle Complete
	 * 
	 * @Column(name="subtitle_complete", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $subtitleComplete;

	/**
	 * Lyric Midi
	 * 
	 * @Column(name="lyric_midi", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi")
	 * @var string
	 */
	protected $lyricMidi;

	/**
	 * Lyric Midi Raw
	 * 
	 * @Column(name="lyric_midi_raw", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi Raw")
	 * @var string
	 */
	protected $lyricMidiRaw;

	/**
	 * Vocal Guide
	 * 
	 * @Column(name="vocal_guide", type="longtext", nullable=true)
	 * @Label(content="Vocal Guide")
	 * @var string
	 */
	protected $vocalGuide;

	/**
	 * Vocal
	 * 
	 * @Column(name="vocal", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $vocal;

	/**
	 * Instrument
	 * 
	 * @Column(name="instrument", type="longtext", nullable=true)
	 * @Label(content="Instrument")
	 * @var string
	 */
	protected $instrument;

	/**
	 * Midi Vocal Channel
	 * 
	 * @Column(name="midi_vocal_channel", type="int(11)", length=11, nullable=true)
	 * @Label(content="Midi Vocal Channel")
	 * @var integer
	 */
	protected $midiVocalChannel;

	/**
	 * Rating
	 * 
	 * @Column(name="rating", type="float", nullable=true)
	 * @Label(content="Rating")
	 * @var double
	 */
	protected $rating;

	/**
	 * Comment
	 * 
	 * @Column(name="comment", type="longtext", nullable=true)
	 * @Label(content="Comment")
	 * @var string
	 */
	protected $comment;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Last Upload Time Image
	 * 
	 * @Column(name="last_upload_time_image", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Image")
	 * @var string
	 */
	protected $lastUploadTimeImage;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;
}
```

**Album**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 */
class Album extends MagicObject
{
	/**
	 * Album ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="longtext", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Release Date
	 * 
	 * @Column(name="release_date", type="date", nullable=true)
	 * @Label(content="Release Date")
	 * @var string
	 */
	protected $releaseDate;

	/**
	 * Number Of Song
	 * 
	 * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
	 * @Label(content="Number Of Song")
	 * @var integer
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $asDraft;

}
```

**Producer**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="producer")
 */
class Producer extends MagicObject
{
	/**
	 * Producer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;

}
```

**Artist**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="artist")
 */
class Artist extends MagicObject
{
	/**
	 * Artist ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="artist_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Artist ID")
	 * @var string
	 */
	protected $artistId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Stage Name
	 * 
	 * @Column(name="stage_name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Stage Name")
	 * @var string
	 */
	protected $stageName;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;

}
```

**Genre**

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="genre")
 */
class Genre extends MagicObject
{
	/**
	 * Genre ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="genre_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Genre ID")
	 * @var string
	 */
	protected $genreId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;

}
```

To join `song` and `album`, we create property

```php
	/**
	 * Album
	 * @JoinColumn(name="album_id")
	 * @Label(content="Album")
	 * @var Album
	 */
	protected $album;
```

Because `album_id` is primary key of table `album`, we not need to write reference column name.

To join `song` and `artist`, we create property

```php
	**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;
```

Primary key of table `artist` is `artist_id`, not `artist_vocalist`. We should write `referenceColumnName` in annotation `@JoinColumn`.

```php
	**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist" referenceColumnName="artist_id")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;
```

If entity miss the `referenceColumnName`, MagicObject will search the primary key of table `artist` and will use first primary key. Process will run slower. We are recommended to always write `referenceColumnName` to make it run faster.

```php
	/**
	 * Album
	 * @JoinColumn(name="album_id"  referenceColumnName="artist_id")
	 * @Label(content="Album")
	 * @var Album
	 */
	protected $album;

	/**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist" referenceColumnName="artist_id")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;
```

### Filter and Order by Join Columns

On real application, user may be filter and order data by column on join table. If the user in the column contains a dot (.) character, then MagicObject will create a select query with a join instead of a regular select query so that filters and orders can work as they should. This way, the process may run slower than with a regular select query.

```php
<?php

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoPredicate;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Database\PicoSpecification;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="album")
 */
class EntityAlbum extends MagicObject
{
	/**
	 * Album ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="longtext", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

    /**
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

	/**
	 * Release Date
	 * 
	 * @Column(name="release_date", type="date", nullable=true)
	 * @Label(content="Release Date")
	 * @var string
	 */
	protected $releaseDate;

	/**
	 * Number Of Song
	 * 
	 * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
	 * @Label(content="Number Of Song")
	 * @var integer
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $asDraft;

}

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="producer")
 */
class Producer extends MagicObject
{
	/**
	 * Producer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

}

$databaseCredential = new SecretObject();
$databaseCredential->loadYamlFile(dirname(dirname(__DIR__))."/test.yml", false, true, true);
$database = new PicoDatabase($databaseCredential);
$database->connect();

$album = new EntityAlbum(null, $database);
try
{
	$spesification = new PicoSpecification();	
	
	$predicate1 = new PicoPredicate();
	// for entity album, just use the colum name
	$predicate1->like('title', '%Album%');
	$spesification->addAnd($predicate1);

	$predicate2 = new PicoPredicate();
	$predicate2->lessThan('producer.birthDay', '2001-01-01');
	$spesification->addAnd($predicate2);

	$predicate3 = new PicoPredicate();
	// type releaseDate instead of release_date
	// because MagicObject use entyty property name, not real table column name 
	$predicate3->greaterThan('releaseDate', '2020-01-01');
	$spesification->addAnd($predicate3);


	$predicate4 = new PicoPredicate();
	$predicate4->equals('active', true);
	$spesification->addAnd($predicate4);
	
	$predicate4 = new PicoPredicate();
	$predicate4->equals('asDraft', false);
	$spesification->addAnd($predicate4);
	
	$sortable = new PicoSortable();
	
	$sortable->addSortable(new PicoSort("producer.birthDay", PicoSort::ORDER_TYPE_ASC));
	$sortable->addSortable(new PicoSort("producer.producerId", PicoSort::ORDER_TYPE_DESC));
	
	
	$pageData = $album->findAll($spesification, null, $sortable, true);
	$rowData = $pageData->getResult();
	foreach($rowData as $alb)
	{
		//echo $alb."\r\n\r\n";
	}
	
	$pageable = new PicoPageable(new PicoPage(1, 20));
	echo $album->findAllQuery($spesification, $pageable, $sortable, true);
	/**
	 * 	select album.*
		from album
		left join producer producer__jn__1
		on producer__jn__1.producer_id = album.producer_id
		where album.title like '%Album%'
		and producer__jn__1.birth_day < '2001-01-01'
		and album.release_date > '2020-01-01' and album.active = true
		and album.as_draft = false
		order by producer__jn__1.birth_day asc, producer__jn__1.producer_id desc
		limit 0, 20
	 */
	echo "\r\n-----\r\n";
	echo $spesification;
	echo "\r\n-----\r\n";
	echo $sortable;
	echo "\r\n-----\r\n";
	echo $pageable;
}
catch(Exception $e)
{
	echo $e->getMessage();
}
```

```php
	$predicate2 = new PicoPredicate();
	$predicate2->lessThan('producer.birthDay', '2001-01-01');
	$spesification->addAnd($predicate2);
```

`producer` is property of entity that join with other entity, not table name. `birthDay` and `producerId` are is property of entity `producer`, not column name of table `producer`.

### Filter Update


Consider the following case:

We have a query as follows:

```sql
UPDATE album
SET waiting_for = 3, admin_ask_edit = 'admin', time_ask_edit = '2024-05-10 07:08:09', ip_ask_edit = '::1'
WHERE album_id = '1234' AND waiting_for = 0;
```

The query above will be executed for each record checked by the user.

With PicoDatabaseQueryBuilder, we can create it easily as follows:

```php
if($inputGet->getUserAction() == UserAction::ACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId() as $rowId)
		{
			$album = new Album(null, $database);
			try
			{
				$query = new PicoDatabaseQueryBuilder($database);
				$query->newQuery()
					->update("album")
					->set("waiting_for = ?, admin_ask_edit = ?, time_ask_edit = ?, ip_ask_edit = ? ", 
						WaitingFor::ACTIVATE, $currentAction->getUserId(), $currentAction->getTime(), $currentAction->getIp())
					->where("album_id = ? and waiting_for = ? ", $rowId, WaitingFor::NOTHING);
				$database->execute($query);
			}
			catch(Exception $e)
			{
				// Do something here when record is not found
			}
		}
	}
}					
```

But what about using MagicObject?

Maybe you'll make it like this

```php
if($inputGet->getUserAction() == UserAction::ACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId() as $rowId)
		{
			$album = new Album(null, $database);
			try
			{
				$album->findOneByAlbumIdAndWaitingFor($rowId, WaitingFor::NOTHING);
				$album->setAdminAskEdit($currentAction->getUserId());
				$album->setTimeAskEdit($currentAction->getTime());
				$album->setIpAskEdit($currentAction->getIp());
				$album->setActive(WaitingFor::ACTIVATE)->update();
			}
			catch(Exception $e)
			{
				// Do something here when record is not found
			}
		}
	}
}
```

The method above looks very elegant. But have you encountered any problems with the method above?

Yes. By using a query builder, the application only runs one query, for example

```sql
UPDATE album
SET waiting_for = 3, admin_ask_edit = 'admin', time_ask_edit = '2024-05-10 07:08:09', ip_ask_edit = '::1'
WHERE album_id = '1234' AND waiting_for = 0;
```

However, by using MagicObject, we actually make two inefficient queries.

```sql
SELECT album.*
WHERE album_id = '1234' AND waiting_for = 0;
```

and

```sql
UPDATE album
SET waiting_for = 3, admin_ask_edit = 'admin', time_ask_edit = '2024-05-10 07:08:09', ip_ask_edit = '::1'
WHERE album_id = '1234' ;
```

Of course, if the `Album` entity has joins with other tables, for example `Producer`, `Client` etc., the number of queries that will be executed by the database will be greater and in fact these queries are not needed at all in the application logic.

In large-scale applications, of course this method will cause problems. Imagine if an application interacted with the database 30 to 40 percent more than it should. Of course the user must provide a database server with greater specifications than necessary. This of course will cause unnecessary costs.

MagicObject provides a more efficient way for this case by using the `where` method and specification.

See the following example:

```php
if($inputGet->getUserAction() == UserAction::ACTIVATE)
{
	if($inputPost->countableCheckedRowId())
	{
		foreach($inputPost->getCheckedRowId() as $rowId)
		{
			$album = new Album(null, $database);
			try
			{
				$album->where(PicoSpecification::getInstance()
					->addAnd(PicoPredicate::getInstance()->setAlbumId($rowId))
					->addAnd(PicoPredicate::getInstance()->setWaitingFor(WaitingFor::NOTHING))
				)
				->setAdminAskEdit($currentAction->getUserId())
				->setTimeAskEdit($currentAction->getTime())
				->setIpAskEdit($currentAction->getIp())
				->setActive(WaitingFor::ACTIVATE)
				->update();
			}
			catch(Exception $e)
			{
				// Do something here when record is not found
			}
		}
	}
}
```

```php
$album
->where(PicoSpecification::getInstance()
->addAnd(PicoPredicate::getInstance()->setAlbumId($rowId))
->addAnd(PicoPredicate::getInstance()->setWaitingFor(WaitingFor::NOTHING))
)
```

will create criteria for the actions to be carried out next. In this case, these actions are

```php
$album
->setAdminAskEdit($currentAction->getUserId())
->setTimeAskEdit($currentAction->getTime())
->setIpAskEdit($currentAction->getIp())
->setActive(WaitingFor::ACTIVATE)
->update();
```

Note that the object returned by the `where` method is instance of `PicoDatabasePersistenceExtended` not instance of `MagicObject`. Of course, we will no longer be able to use the methods in MagicObject. 

### Filter Delete

Just like in the case of updates, deletes with more complicated specifications are also possible using the delete filter. Instead of selecting with specifications and then deleting them, deleting with specifications will be more efficient because the application only performs one query to the database.

```sql
DELETE FROM album
WHERE album_id = '1234' AND waiting_for = 0;
```

```php
$album
->where(PicoSpecification::getInstance()
->addAnd(PicoPredicate::getInstance()->setAlbumId('1234'))
->addAnd(PicoPredicate::getInstance()->setWaitingFor(0))
)
->delete();
```

We'll look at an example of delete with a more complex filter that can't be done with the deleteBy method.

```sql
DELETE FROM album
WHERE album_id = '1234' AND (waiting_for = 0 or waiting_for IS NULL)

```

```php
$specfification = new PicoSpecification();
$specfification->addAnd(new PicoPredicate('albumId', '1234'));
$spec2 = new PicoSpecification();
$predicate1 = new PicoPredicate('waitingFor', 0);
$predicate1 = new PicoPredicate('waitingFor', null);
$spec2->addOr($predicate1);
$spec2->addOr($predicate2);
$specfification->addAnd($spec2);

$album = new Album(null, $database);
$album->where($specfification)->delete();

```

### Subquery

For large data with a very large number of records, using joins, whether inner joins, outer joins, left joins or right joins, will require a lot of resources, which will reduce application and database performance. MagicObject version 1.10 introduces searches using subqueries instead of joins so that the data search process becomes faster.

Using subqueries is not without its drawbacks. The unavoidable disadvantages of subqueries are as follows:

1. just take one column from the reference table
2. Cannot use columns in the reference table either for filter (where) or for sorting (order by).

Users must be aware of these two shortcomings before deciding to use a subquery.

Example:

```php
/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="album")
 */
class EntityAlbum extends MagicObject
{
	/**
	 * Album ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="longtext", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

    /**
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

	/**
	 * Release Date
	 * 
	 * @Column(name="release_date", type="date", nullable=true)
	 * @Label(content="Release Date")
	 * @var string
	 */
	protected $releaseDate;

	/**
	 * Number Of Song
	 * 
	 * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
	 * @Label(content="Number Of Song")
	 * @var integer
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $asDraft;

}

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="producer")
 */
class Producer extends MagicObject
{
	/**
	 * Producer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

}

$album = new EntityAlbum(null, $database);

$subqueryMap = array(
	'producer'=>array(
		'entityName'=>'Producer',
		'tableName'=>'producer',
		'primaryKey'=>'producer_id',
		'columnName'=>'producer_id',
		'objectName'=>'producer',
		'propertyName'=>'name'
	)
);

$result = $album->findAll(null, null, null, true, $subqueryMap);
	
foreach($result->getResult() as $row)
{
	echo $row;
}
```

### Find All with Option

1. `MagicObject::FIND_OPTION_NO_COUNT_DATA`
2. `MagicObject::FIND_OPTION_NO_FETCH_DATA`

If the `MagicObject::FIND_OPTION_NO_COUNT_DATA` option is provided, the MagicObject will not count data even if a PicoPageable is provided. This will reduce data processing time but the user does not know how much data actually is. This option can be choosed when dealing with very large data.

If the `MagicObject::FIND_OPTION_NO_FETCH_DATA` option is given, MagicObject will not directly fetch data and store it in the object. Users must retrieve data one by one using the fetch method. Every time an application receives data from the database, it sends it directly to either a file or an output buffer instead of collecting it in a list in memory. This option can be choosed when handling very large data so that it does not consume much memory. 

Example:

```php
/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="album")
 */
class EntityAlbum extends MagicObject
{
	/**
	 * Album ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Description
	 * 
	 * @Column(name="description", type="longtext", nullable=true)
	 * @Label(content="Description")
	 * @var string
	 */
	protected $description;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

    /**
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

	/**
	 * Release Date
	 * 
	 * @Column(name="release_date", type="date", nullable=true)
	 * @Label(content="Release Date")
	 * @var string
	 */
	protected $releaseDate;

	/**
	 * Number Of Song
	 * 
	 * @Column(name="number_of_song", type="int(11)", length=11, nullable=true)
	 * @Label(content="Number Of Song")
	 * @var integer
	 */
	protected $numberOfSong;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

	/**
	 * As Draft
	 * 
	 * @Column(name="as_draft", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $asDraft;

}

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE, prettify=true)
 * @Table(name="producer")
 */
class Producer extends MagicObject
{
	/**
	 * Producer ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=false)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Gender
	 * 
	 * @Column(name="gender", type="varchar(2)", length=2, nullable=true)
	 * @Label(content="Gender")
	 * @var string
	 */
	protected $gender;

	/**
	 * Birth Day
	 * 
	 * @Column(name="birth_day", type="date", nullable=true)
	 * @Label(content="Birth Day")
	 * @var string
	 */
	protected $birthDay;

	/**
	 * Phone
	 * 
	 * @Column(name="phone", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone")
	 * @var string
	 */
	protected $phone;

	/**
	 * Phone2
	 * 
	 * @Column(name="phone2", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone2")
	 * @var string
	 */
	protected $phone2;

	/**
	 * Phone3
	 * 
	 * @Column(name="phone3", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Phone3")
	 * @var string
	 */
	protected $phone3;

	/**
	 * Email
	 * 
	 * @Column(name="email", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email")
	 * @var string
	 */
	protected $email;

	/**
	 * Email2
	 * 
	 * @Column(name="email2", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email2")
	 * @var string
	 */
	protected $email2;

	/**
	 * Email3
	 * 
	 * @Column(name="email3", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Email3")
	 * @var string
	 */
	protected $email3;

	/**
	 * Website
	 * 
	 * @Column(name="website", type="text", nullable=true)
	 * @Label(content="Website")
	 * @var string
	 */
	protected $website;

	/**
	 * Address
	 * 
	 * @Column(name="address", type="text", nullable=true)
	 * @Label(content="Address")
	 * @var string
	 */
	protected $address;

	/**
	 * Picture
	 * 
	 * @Column(name="picture", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $picture;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Image Update
	 * 
	 * @Column(name="image_update", type="timestamp", length=19, nullable=true)
	 * @Label(content="Image Update")
	 * @var string
	 */
	protected $imageUpdate;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

}

$album = new EntityAlbum(null, $database);

$subqueryMap = array(
	'producer'=>array(
		'entityName'=>'Producer',
		'tableName'=>'producer',
		'primaryKey'=>'producer_id',
		'columnName'=>'producer_id',
		'objectName'=>'producer',
		'propertyName'=>'name'
	)
);

$result = $album->findAll(null, null, null, true, null, MagicObject::FIND_OPTION_NO_COUNT_DATA | MagicObject::FIND_OPTION_NO_FETCH_DATA);

while($data = $result->fetch())
{
	echo $data;
}
```

### Method

**1. find**

&raquo; search data from database by primary key value given and return one record. This method require database connection.

Method type: native

Parameters:

- mixed $parameters

Parameters can be strings, integers, floats, booleans, or arrays of strings, integers, floats, and booleans. Parameters cannot be null. If the parameter is an array, then the order of the values ​​in the parameter must be the same as the order of the primary key in the entity and the number of elements must be the same as the number of primary keys in the entity. If the number of array elements in the parameter is more than the number of primary keys, then the elements behind will be ignored. If the number of array elements in the parameter is less than the number of primary keys, an exception will be thrown. The data type provided must match the primaty keys.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE album_id = '123456';
	$album->find('123456');
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**2. findAll**

&raquo; search multiple record data from database with specification, pagable and sortable. This method require database connection.

Method type: native

Parameters:

- PicoSpecification $specification Specification
- PicoPageable $pageable Pageable
- PicoSortable $sortable Sortable
- boolean $passive Flag that object is passive
- array $subqueryMap Subquery map
- integer $findOption Find option

Return: PicoPageable

**Example**

See Specification, Pageable and Sortable

**3. findOneBy**

&raquo; search data from database by any column values and return one record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE album_id = '123456' AND active = true;
	// albumId is string
	// active is boolean
	$album->findOneByAlbumIdAndActive('123456', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**4. findOneIfExistsBy**

&raquo; search data from database by any column values and return one record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE album_id = '123456' AND active = true;
	// albumId is string
	// active is boolean
	$album->findOneIfExistsByAlbumIdAndActive('123456', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**5. deleteOneBy**

&raquo; delete data from database by any column values and return one record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// DELETE FROM album WHERE album_id = '123456' AND active = true;
	// albumId is string
	// active is boolean
	$album->deleteOneByAlbumIdAndActive('123456', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**6.findFirstBy**

&raquo; search data from database by any column values and return first record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id ASC LIMIT 1 OFFSET 0;
	// albumId is string
	// active is boolean
	$album->findFirstByProducerIdAndActive('7890123', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**7. findFirstIfExistsBy**

&raquo; search data from database by any column values and return first record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

**Example**

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id ASC LIMIT 1 OFFSET 0;
	// albumId is string
	// active is boolean
	$album->findFirstIfExistsByProducerIdAndActive('7890123', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**8. findLastBy**

&raquo; search data from database by any column values and return last record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id DESC LIMIT 1 OFFSET 0;
	$album->findLastByProducerIdAndActive('7890123', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**9. findLastIfExistsBy**

&raquo; search data from database by any column values and return last record. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$album = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id DESC LIMIT 1 OFFSET 0;
	$album->findLastIfExistsByProducerIdAndActive('7890123', true);
	// echo $album;
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**10. findBy**

&raquo; search multiple record data from database by any column values. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true;
	$pageData = $albumFinder->findByProducerIdAndActive('7890123', true);
	foreach($pageData as $album)
	{
		// echo $album;
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**11. findAscBy**

&raquo; search multiple record data from database order by primary keys ascending. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT * FROM album WHERE producer_id = '7890123' AND active = true ORDER BY album_id ASC;
	$pageData = $albumFinder->findAscByProducerIdAndActive('7890123', true);
	foreach($pageData as $album)
	{
		// echo $album;
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**12. findDescBy**

&raquo; search multiple record data from database order by primary keys descending. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT album WHERE producer_id = '7890123' AND active = true ORDER BY album_id DESC;
	$pageData = $albumFinder->findDescByProducerIdAndActive('7890123', true);
	foreach($pageData as $album)
	{
		// echo $album;
	}
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**13. listBy**

&raquo; search multiple record data from database. Similar to findBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.
**14. listAscBy**

&raquo; search multiple record data from database order by primary keys ascending. Similar to findAscBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.

**15. listDescBy**

&raquo; search multiple record data from database order by primary keys descending. Similar to findDescBy but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.

**16. listAllAsc**

&raquo; search multiple record data from database without filter order by primary keys ascending. Similar to findAllAsc but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.

**17. listAllDesc**

&raquo; search multiple record data from database without filter order by primary keys descending. Similar to findAllDesc but return object does not contain a connection to the database so objects cannot be saved directly to the database. This method require database connection.

**18. countBy**

&raquo; count data from database.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT COUNT(album_id) FROM album WHERE producer_id = '7890123' AND active = true;
	$count = $albumFinder->countByProducerIdAndActive('7890123', true);
	
	// $count is an integer value
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**19. existsBy**

&raquo; check data from database. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// SELECT COUNT(album_id) FROM album WHERE producer_id = '7890123' AND active = true;
	$exists = $albumFinder->existsByProducerIdAndActive('7890123', true);
	// $exists is a boolean value
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

**20. deleteBy**

&raquo; delete data from database without read it first. This method require database connection.

Method type: magic

Parameters:

The user can provide one or more parameters in the form of strings, integers, floats, booleans or null. The number of parameters provided must match the method being called. The data type provided must match the property data type of the entity mentioned in the method being called.

```php
$albumFinder = new Album(null, $database);
try
{
	// DELETE FROM album WHERE producer_id = '7890123' AND active = true;
	$albumFinder->deleteByProducerIdAndActive('7890123', true);
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

### Specification

Specifications are implemented in the PicoSpecification and PicoPredicate classes. PicoSpecification is a framework that can contain one or more PicoPredicate.

For example, we have the following query:

```sql
DELETE FROM album
WHERE album_id = '1234' AND (waiting_for = 0 or waiting_for IS NULL)

```

We can write it as follows:

```php
$specfification = new PicoSpecification();
$specfification->addAnd(new PicoPredicate('albumId', '1234'));
$spec2 = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->equals('waitingFor', 0);
$predicate1 = new PicoPredicate();
$predicate1->equals('waitingFor', null);
$spec2->addOr($predicate1);
$spec2->addOr($predicate2);
$specfification->addAnd($spec2);

$album = new Album(null, $database);
$album->where($specfification)->delete();

```

**PicoSpecification**

Method:

1. addAnd

Parameters:

- PicoSpecification|PicoPredicate|array

2. addOr

Parameters:

- PicoSpecification|PicoPredicate|array

We can form specifications in an unlimited number of stages. Note that users need to simplify the logic before implementing it into the specification.

**PicoPredicate**

Constructor:

Parameters:

- string $field = null
- mixed $value = null

If the field is given a value, the constructor will call the `equals` method with the `field` and `value` from the constructor. This will make it easier for users to create specifications in a line of code.

Methods:

- equals(string $fieldName, mixed $value)
- isNull(string $fieldName)
- notEquals(string $fieldName, mixed $value)
- isNotNull(string $fieldName)
- like(string $fieldName, mixed $value)
- notLike(string $fieldName, mixed $value)
- in(string $fieldName, mixed[] $value)
- notIn(string $fieldName, mixed[] $value)
- lessThan(string $fieldName, string|integer|float $value)
- greaterThan(string $fieldName, string|integer|float $value)
- lessThanOrEquals(string $fieldName, string|integer|float $value)
- greaterThanOrEquals(string $fieldName, string|integer|float $value)

Static Methods:
- getInstance()
- generateLikeStarts(string $value)
- generateLikeEnds(string $value)
- generateLikeContains(string $value)
- functionUpper(string $value)
- functionLower(string $value)
- functionAndValue(string $value)

Example:

We want to create a query like this:

`SELECT * FROM album WHERE producer_id = 'asdf'`

Instead of using the `findByProducerId` method, we will use a specification. The advantage of using a specification is that we can easily add parameters to the specification for specific needs.

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->equals('producerId', 'asdf');
$specification->addAnd($predicate1)
```

**Logic AND**

Since we are only using one predicate, we use the `addAnd` method. We can add predicates to the specification. For example, we will add the following condition:

`SELECT * FROM album WHERE producer_id = 'asdf' AND active = true`

So we can write it as

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->equals('producerId', 'asdf');
$specification->addAnd($predicate1)
$predicate2 = new PicoPredicate();
$predicate2->equals('active', true);
$specification->addAnd($predicate2)
```

We can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->equals('producerId', 'asdf'))
    ->addAnd(PicoPredicate::getInstance()->equals('active', true))
;
```

For predicates with `equals` comparison, we can specify them by passing parameters into the constructor, so we can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(new PicoPredicate('producerId', 'asdf'))
    ->addAnd(new PicoPredicate('active', true))
;
```

And because `addAnd` and `addOr` can accept parameters in the form of `PicoSpecification`, `PicoPredicate` and also `array`, then our code can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(['producerId', 'asdf'])
    ->addAnd(['active', true])
;
```

will be:

```sql
SELECT * FROM producer WHERE producer_id = 'asdf' AND active = true
```

When a user passes an array as a parameter to the `addAnd` and `addOr` methods, MagicObject will convert it to an instance of `PicoPredicate` with `equals` comparison. If using `array` is easier, feel free to use it but it is recommended to use `PicoPredicate` so that it can be used directly by MagicObject. If the second parameter is an array, then the comparison logic becomes `in` instead of `equals`.

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(['producerId', ['asdf', 'qwerty']])
    ->addAnd(['active', true])
;
```

will be:

```sql
SELECT * FROM producer WHERE producer_id IN ('asdf', 'qwerty') AND active = true
```

For comparisons other than `equals`, we must specify them explicitly. Here are some examples of specifying AND logic.

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->notEquals('producerId', 'asdf');
$specification->addAnd($predicate1)
$predicate2 = new PicoPredicate();
$predicate2->notEquals('active', true);
$specification->addAnd($predicate2)
```

`$predicate1->notEquals('active', true)` does not automatically become `$predicate1->equals('active', false)`. Don't forget about `null` values ​​that may exist in some rows of data.

We can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->notEquals('producerId', 'asdf'))
    ->addAnd(PicoPredicate::getInstance()->notEquals('active', true))
;
```

Since the comparison used is not `equals`, there is no shorter way to write the code above.

MagicObject will use `is` instead of `=` and `is not` instead of `!=` if the given value is `null`. If the supplied values ​​are a list, use `in` and `notIn` instead of `equals` and `notEquals`.

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->in('producerId', ['asdf', 'qwerty', 'zxcv']))
    ->addAnd(PicoPredicate::getInstance()->notEquals('active', null))
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->in('producerId', ['asdf', 'qwerty', 'zxcv']))
    ->addAnd(PicoPredicate::getInstance()->isNotNull('active'))
;
```

To search for partial text in a row, we can use `like`. In MySQL and MariaDB, `like` comparison is `case insensitive` but in other databases it is `case sensitive`. For that, `like` comparison is usually combined with `upper` or `lower` function to make the comparison `case insensitive`.

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('kamshory')))
    ->addAnd(['active', true])
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->like(PicoPredicate::functionAndValue('lower', 'name'), PicoPredicate::generateLikeContains('kamshory')))
    ->addAnd(['active', true])
;
```

or 

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->like('lower(name)', PicoPredicate::generateLikeContains('kamshory')))
    ->addAnd(['active', true])
;
```

or 

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->like('lower(name)', '%kamshory%'))
    ->addAnd(['active', true])
;
```

The first writing style is more recommended.

**Logic OR**

The `OR` logic requires at least two criteria. Never use `OR` logic if it only uses one criterion because it will not mean anything. For example, we will add the following condition:

`SELECT * FROM album WHERE producer_id = 'asdf' OR active = true`

So we can write it as

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->equals('producerId', 'asdf');
$specification->addOr($predicate1)
$predicate2 = new PicoPredicate();
$predicate2->equals('active', true);
$specification->addOr($predicate2)
```

We can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->equals('producerId', 'asdf'))
    ->addOr(PicoPredicate::getInstance()->equals('active', true))
;
```

For predicates with `equals` comparison, we can specify them by passing parameters into the constructor, so we can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addOr(new PicoPredicate('producerId', 'asdf'))
    ->addOr(new PicoPredicate('active', true))
;
```

And because `addAnd` and `addOr` can accept parameters in the form of `PicoSpecification`, `PicoPredicate` and also `array`, then our code can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addOr(['producerId', 'asdf'])
    ->addOr(['active', true])
;
```

As in `addAnd`, if the second parameter is an array, then the comparison logic becomes `in` instead of `equals`.

```php
$specification = PicoSpecification::getInstance()
    ->addOr(['producerId', ['asdf', 'qwerty']])
    ->addOr(['active', true])
;
```

For comparisons other than `equals`, we must specify them explicitly. Here are some examples of specifying OR logic.

```php
$specification = new PicoSpecification();
$predicate1 = new PicoPredicate();
$predicate1->notEquals('producerId', 'asdf');
$specification->addOr($predicate1)
$predicate2 = new PicoPredicate();
$predicate2->notEquals('active', true);
$specification->addOr($predicate2)
```

`$predicate1->notEquals('active', true)` does not automatically become `$predicate1->equals('active', false)`. Don't forget about `null` values ​​that may exist in some rows of data.

We can also write them with

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->notEquals('producerId', 'asdf'))
    ->addOr(PicoPredicate::getInstance()->notEquals('active', true))
;
```

Since the comparison used is not `equals`, there is no shorter way to write the code above.

MagicObject will use `is` instead of `=` and `is not` instead of `!=` if the given value is `null`. If the supplied values ​​are a list, use `in` and `notIn` instead of `equals` and `notEquals`.

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->in('producerId', ['asdf', 'qwerty', 'zxcv']))
    ->addOr(PicoPredicate::getInstance()->notEquals('active', null))
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->in('producerId', ['asdf', 'qwerty', 'zxcv']))
    ->addOr(PicoPredicate::getInstance()->isNotNull('active'))
;
```

To search for partial text in a row, we can use `like`. In MySQL and MariaDB, `like` comparison is `case insensitive` but in other databases it is `case sensitive`. For that, `like` comparison is usually combined with `upper` or `lower` function to make the comparison `case insensitive`.

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('kamshory')))
    ->addOr(['active', true])
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionAndValue('lower', 'name'), PicoPredicate::generateLikeContains('kamshory')))
    ->addOr(['active', true])
;
```

or 

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->like('lower(name)', PicoPredicate::generateLikeContains('kamshory')))
    ->addOr(['active', true])
;
```

or 

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->like('lower(name)', '%kamshory%'))
    ->addOr(['active', true])
;
```

The first writing style is more recommended.

**Nested Logic**

In real applications, logic is not always simple. Even very simple applications often have nested and complex logic. Nested logic is `AND` inside `OR` or `OR` inside `AND`.

1. **OR inside AND**

Example:

`SELECT * FROM album WHERE active = true and (lower(name) like '%jakarta%' or lower(title) like '%jakarta%')`

We can create specifications within specifications.

```php
$specification = new PicoSpecification();

// create predicate1
$predicate1 = new PicoPredicate();
$predicate1->equals('active', true);

// create predicate2
$predicate2 = new PicoPredicate();
$predicate2->equals(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('jakarta'));

// create predicate3
$predicate3 = new PicoPredicate();
$predicate3->equals(PicoPredicate::functionLower('title'), PicoPredicate::generateLikeContains('jakarta'));


// create specification2
$specification2 = new PicoSpecification();

// add predicate2 into specification2 with logic OR
$specification2->addOr($predicate2);

// add predicate3 into specification2 with logic OR
$specification2->addOr($predicate3);

// add predicate1 into specification with logic AND
$specification->addAnd($predicate1)

// add specification2 into specification with logic AND
$specification->addAnd($specification2)
```

A shorter code to create the above specification is as follows

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(PicoPredicate::getInstance()->equals('active', true))
    ->addAnd(
        PicoSpecification::getInstance()
            ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('jakarta')))
            ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('title'), PicoPredicate::generateLikeContains('jakarta')))
    )
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addAnd(['active', true])
    ->addAnd(
        PicoSpecification::getInstance()
            ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('name'), PicoPredicate::generateLikeContains('jakarta')))
            ->addOr(PicoPredicate::getInstance()->like(PicoPredicate::functionLower('title'), PicoPredicate::generateLikeContains('jakarta')))
    )
;
```

2. **AND inside OR**

Example:

`SELECT * FROM album WHERE waiting_for = 0 or (waiting_for is null and approval_id is null)`

We can create specifications within specifications.

```php
$specification = new PicoSpecification();

// create predicate1
$predicate1 = new PicoPredicate();
$predicate1->equals('waitingFor', 0);

// create predicate2
$predicate2 = new PicoPredicate();
$predicate2->equals('waitingFor', null);

// create predicate3
$predicate3 = new PicoPredicate();
$predicate3->equals('approvalId', null);


// create specification2
$specification2 = new PicoSpecification();

// add predicate2 into specification2 with logic AND
$specification2->addAnd($predicate2);

// add predicate3 into specification2 with logic AND
$specification2->addAnd($predicate3);

// add predicate1 into specification with logic OR
$specification->addOr($predicate1)

// add specification2 into specification with logic OR
$specification->addOr($specification2)
```

A shorter code to create the above specification is as follows

```php
$specification = PicoSpecification::getInstance()
    ->addOr(PicoPredicate::getInstance()->equals('waitingFor', 0))
    ->addOr(
        PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->equals('waitingFor', null))
            ->addAnd(PicoPredicate::getInstance()->equals('approvalId', null))
    )
;
```

or

```php
$specification = PicoSpecification::getInstance()
    ->addOr(['waitingFor', 0])
    ->addOr(
        PicoSpecification::getInstance()
            ->addAnd(PicoPredicate::getInstance()->equals('waitingFor', null))
            ->addAnd(PicoPredicate::getInstance()->equals('approvalId', null))
    )
;
```

MagicObject version 1.20 offers the simplest way to create specifications with `AND` logic and `equal` or `in` comparisons.

For example:

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->ipCreate = '::1';
$specs->ipEdit = null;

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

will be:

```sql
select album.* 
from album
where album.name in ('Album 1', 'Album 2') and album.number_of_song = 11
and album.active = true and album.as_draft = false and album.ip_create = '::1'
and album.ip_edit is null
```

Instead of writing very long code to create a specification, users can simply write a few very short lines of code. However, it should be noted that this method only applies to `AND` logic with `equals` and `in` comparisons.

When the user assigns the value of `active` to `true`, then MagicObject will add the predicate `active = true`, likewise when the user assigns the value of `asDraft` to `false`. Since `name` is assigned an array value, the comparison used is `in`. It should be noted that the specification is not an object that stores the given properties as its own properties but rather it will add the predicate each time the predicate is entered.

For example, the code is as follows:

```php

$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->name = ['Album 1', 'Album 2'];
$specs->active = true;
$specs->asDraft = false;
$specs->ipCreate = '::1';
$specs->ipCreate = null;

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

You have given the value `$specs->ipCreate = '::1'` and you don't if change that value to `null` for example. So the above code is wrong because it stumbles with the wrong logic i.e. `ip_create = '::1' ,
album.ip_create is null`.

If you mean `ipCreate = '::1' or ipCreate = null`, then you can use the following way:

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->ipCreate = ['::1', null];

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

or

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->addAnd(
	PicoSpecification::getInstance()
		->addOr(['ipCreate', '::1'])
		->addOr(['ipCreate', null])
);

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

If you want to set a column from a reference table, you can use the `set` method. Suppose you want to expect MagicObject to make the following query:

```sql
SELECT album.* FROM album
INNER JOIN producer ON producer.producer_id = album.producer_id
WHERE album.active = true AND producer.active = true
```

So you can write

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->active = true;
$specs->set('producer.active', true); // use `set` method instead

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

What if you want to use full `OR` logic instead of `AND` logic? You can use the following way:

```php
$album = new EntityAlbum(null, $database);

$specs = new PicoSpecification();
$specs->setDefaultLogicOr();

$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->ipCreate = null;

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```

When you set any predicate to a specification, MagicObject will always add the predicate with `OR` logic instead of `AND` logic. Please note that you must call the `setDefaultLogicOr` method before you set a predicate. If you call the `setDefaultLogicOr` method after you set a predicate, you will end up with a logical mess. 

To avoid errors when calling the `setDefaultLogicOr` method, it is recommended to use the following method:

```php
$album = new EntityAlbum(null, $database);

$specs = PicoSpecification::getInstance()->setDefaultLogicOr();

$specs->name = ['Album 1', 'Album 2'];
$specs->numberOfSong = 11;
$specs->active = true;
$specs->asDraft = false;
$specs->setIpCreate(null);

try
{
	$album->findAll($specs);
}
catch(Exception $e)
{
	error_log($e);
}
```
### Pageable and Sortable

In MagicObject, pageable is used to divide data rows into several pages. This is required by the application to display a lot of data per page. While sortable is used to sort data before the data is divided per page.

Pageable can stand alone without sortable. However, this method is not recommended because the data sequence is not as expected. If new data is entered, users will have difficulty finding where it is located in the list and on which page the data will appear. The solution is to add a sortable that will sort the data based on certain columns. For example, the time of data creation is descending, then the new data will be on the first page. Conversely, if sorted based on the time of data creation is ascending, then the new data will be on the last page.

Sortable can use multiple columns. The order in which the columns are determined will determine the priority order in sorting the data.

An example of implementing pageable and sortable in MagicObject.

```php
$pageNumber = 1;
$pageSize = 20;

$albumFinder = new Album(null, $database);
$specification = null;
$sortable = new PicoSortable();
$sort1 = new PicoSort('releaseDate', PicoSort::ORDER_TYPE_DESC);
$sortable->addSortable($sort1);
$page = new PicoPage($pageNumber, $pageSize);
$pageable = new PicoPageable($page);

try
{
    $albumFinder->findAll($specification, $pagable, $sortable);
    $pageData = $albumFinder->getResult();
    foreach($pageData as $album)
    {
        echo $album."\r\n";
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
}

```

or

```php
$pageNumber = 1;
$pageSize = 20;

$albumFinder = new Album(null, $database);
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('releaseDate', PicoSort::ORDER_TYPE_DESC))
;
$pageable = new PicoPageable(new PicoPage($pageNumber, $pageSize));

try
{
    $albumFinder->findAll(null, $pagable, $sortable);
    $pageData = $albumFinder->getResult();
    foreach($pageData as $album)
    {
        echo $album."\r\n";
    }
}
catch(Exception $e)
{
    error_log($e->getMessage());
}

```

**PicoPageable**

Constructor:

Parameters:

- PicoPage|PicoLimit|array $page
- PicoSortable|array $sortable

Method:

- getSortable
- setSortable
- addSortable
- createOrderBy
- getPage
- setPage
- getOffsetLimit
- setOffsetLimit

**PicoPage**

Constructor:

Parameters:

- integer $pageNumber
- integer $pageSize

Metods:

- getPageNumber
- setPageNumber
- getPageSize
- setPageSize

**PicoLimit**

Constructor:

Parameters:

- integer $offset
- integer $limit

Metods:

- getOffset
- setOffset
- getLimit
- setLimit

**PicoSortable**

Constructor:

Parameters:

Users can enter an even number of parameters where odd numbered parameters are columns while even numbered parameters are methods.

Example:

```php
$sortable = new PicoSortable("name", "asc", "phone", "desc");
```

Method:

- add
- addSortable
- createSortable
- createOrderBy
- isEmpty
- getSortable

Static methods:

- getInstance

addSortable

Parameters:

- PicoSort|array

**PicoSort**

Constructor:

Parameters:

- string $sortBy
- string $sortType

Metods:

- getSortBy
- setSortBy
- getSortType
- setSortType
- __call

Static methods:

- getInstance
- fixSortType


Example:


**Pageable without Sortable**

```php
$pageable = new PicoPageable(new Page(1, 100));
// page number = 1
// page size = 100
// no sortable
```

**Pageable with Sortable**

```php
$sortable = new PicoSortable();

$sort1 = new PicoSort('userName', 'asc');
$sort2 = new PicoSort('email', 'desc');
$sort3 = new PicoSort('phone', 'asc');

$sortable->add($sort1);
$sortable->add($sort2);
$sortable->add($sort3);

$pageable = new PicoPageable(new Page(1, 100), $sortable);
// page number = 1
// page size = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', 'asc'))
    ->add(new PicoSort('email', 'desc'))
    ->add(new PicoSort('phone', 'asc'))
;
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', PicoSort::ORDER_TYPE_ASC))
    ->add(new PicoSort('email', PicoSort::ORDER_TYPE_DESC))
    ->add(new PicoSort('phone', PicoSort::ORDER_TYPE_ASC))
;

$pageable = new PicoPageable(new Page(1, 100), $sortable);
// page number = 1
// page size = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

**With Limit and Sortable**

```php
$sortable = new PicoSortable();

$sort1 = new PicoSort('userName', 'asc');
$sort2 = new PicoSort('email', 'desc');
$sort3 = new PicoSort('phone', 'asc');

$sortable->add($sort1);
$sortable->add($sort2);
$sortable->add($sort3);

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = new PicoSortable(
    'userName', 'asc',
    'email', 'desc',
    'phone', 'asc'
);

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', 'asc'))
    ->add(new PicoSort('email', 'desc'))
    ->add(new PicoSort('phone', 'asc'))
;

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(new PicoSort('userName', PicoSort::ORDER_TYPE_ASC))
    ->add(new PicoSort('email', PicoSort::ORDER_TYPE_DESC))
    ->add(new PicoSort('phone', PicoSort::ORDER_TYPE_ASC))
;

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

or

```php
$sortable = PicoSortable::getInstance()
    ->add(PicoSort::getInstance()->sortByUserName(PicoSort::ORDER_TYPE_ASC))
    ->add(PicoSort::getInstance()->sortByEmail(PicoSort::ORDER_TYPE_DESC))
    ->add(PicoSort::getInstance()->sortByPhone(PicoSort::ORDER_TYPE_ASC))
;

$pageable = new PicoPageable(new PicoLimit(0, 100), $sortable);
// offset = 0
/// limit = 100
// ORDER BY user_name ASC, email DESC, phone ASC
```

1. Construtor with page as PicoPageable and sortable as PicoSortable

`$pageable = new PicoPageable(new PicoPage(1, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoPage(3, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 200`


2. Construtor with page as PicoPage and sortable as array

`$pageable = new PicoPageable(new PicoPage(1, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoPage(5, 50), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 50 OFFSET 200`

3. Construtor with page as PicoLimit and sortable as PicoSortable

`$pageable = new PicoPageable(new PicoLimit(0, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoLimit(50, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 50`

4. Construtor with page as PicoLimit and sortable as array

`$pageable = new PicoPageable(new PicoLimit(0, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 0`

`$pageable = new PicoPageable(new PicoLimit(20, 100), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 20`

5. Construtor with page as array and sortable as PicoSortable

`$pageable = new PicoPageable(array(10, 100), new PicoSortable('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 100 OFFSET 900`

6. Construtor with page as array and sortable as array

`$pageable = new PicoPageable(array(3, 200), array('userName', 'asc', 'email', 'desc', 'phone', 'asc'));`

will be

`ORDER BY user_name ASC, email DESC, phone ASC LIMIT 200 OFFSET 400`

## Filtering, Ordering and Pagination

MagicObject will filter data according to the given criteria. On the other hand, MagicObject will only retrieve data on the specified page by specifying `limit` and `offset` data in the `select` query.

Example parameters:

`genre_id=0648d4e176da4df4472d&album_id=&artist_vocal_id=&name=&vocal=&lyric_complete=&active=&page=2&orderby=title&ordertype=asc`

Create entity according to database

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="song")
 */
class EntitySong extends MagicObject
{
	/**
	 * Song ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="song_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Song ID")
	 * @var string
	 */
	protected $songId;

	/**
	 * Random Song ID
	 * 
	 * @Column(name="random_song_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Random Song ID")
	 * @var string
	 */
	protected $randomSongId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Album ID
	 * 
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Album
	 * @JoinColumn(name="album_id")
	 * @Label(content="Album")
	 * @var Album
	 */
	protected $album;

	/**
	 * Track Number
	 * 
	 * @Column(name="track_number", type="int(11)", length=11, nullable=true)
	 * @Label(content="Track Number")
	 * @var integer
	 */
	protected $trackNumber;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

	/**
	 * Artist Vocal
	 * 
	 * @Column(name="artist_vocalist", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Vocal")
	 * @var string
	 */
	protected $artistVocalist;

	/**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;

	/**
	 * Artist Composer
	 * 
	 * @Column(name="artist_composer", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Composer")
	 * @var string
	 */
	protected $artistComposer;

	/**
	 * Artist Composer
	 * 
	 * @JoinColumn(name="artist_composer")
	 * @Label(content="Artist Composer")
	 * @var Artist
	 */
	protected $composer;

	/**
	 * Artist Arranger
	 * 
	 * @Column(name="artist_arranger", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Arranger")
	 * @var string
	 */
	protected $artistArranger;

	/**
	 * Artist Arranger
	 * 
	 * @JoinColumn(name="artist_arranger")
	 * @Label(content="Artist Arranger")
	 * @var Artist
	 */
	protected $arranger;

	/**
	 * File Path
	 * 
	 * @Column(name="file_path", type="text", nullable=true)
	 * @Label(content="File Path")
	 * @var string
	 */
	protected $filePath;

	/**
	 * File Name
	 * 
	 * @Column(name="file_name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Name")
	 * @var string
	 */
	protected $fileName;

	/**
	 * File Type
	 * 
	 * @Column(name="file_type", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Type")
	 * @var string
	 */
	protected $fileType;

	/**
	 * File Extension
	 * 
	 * @Column(name="file_extension", type="varchar(20)", length=20, nullable=true)
	 * @Label(content="File Extension")
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * File Size
	 * 
	 * @Column(name="file_size", type="bigint(20)", length=20, nullable=true)
	 * @Label(content="File Size")
	 * @var integer
	 */
	protected $fileSize;

	/**
	 * File Md5
	 * 
	 * @Column(name="file_md5", type="varchar(32)", length=32, nullable=true)
	 * @Label(content="File Md5")
	 * @var string
	 */
	protected $fileMd5;

	/**
	 * File Upload Time
	 * 
	 * @Column(name="file_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="File Upload Time")
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * First Upload Time
	 * 
	 * @Column(name="first_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="First Upload Time")
	 * @var string
	 */
	protected $firstUploadTime;

	/**
	 * Last Upload Time
	 * 
	 * @Column(name="last_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time")
	 * @var string
	 */
	protected $lastUploadTime;

	/**
	 * File Path Midi
	 * 
	 * @Column(name="file_path_midi", type="text", nullable=true)
	 * @Label(content="File Path Midi")
	 * @var string
	 */
	protected $filePathMidi;

	/**
	 * Last Upload Time Midi
	 * 
	 * @Column(name="last_upload_time_midi", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Midi")
	 * @var string
	 */
	protected $lastUploadTimeMidi;

	/**
	 * File Path Xml
	 * 
	 * @Column(name="file_path_xml", type="text", nullable=true)
	 * @Label(content="File Path Xml")
	 * @var string
	 */
	protected $filePathXml;

	/**
	 * Last Upload Time Xml
	 * 
	 * @Column(name="last_upload_time_xml", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Xml")
	 * @var string
	 */
	protected $lastUploadTimeXml;

	/**
	 * File Path Pdf
	 * 
	 * @Column(name="file_path_pdf", type="text", nullable=true)
	 * @Label(content="File Path Pdf")
	 * @var string
	 */
	protected $filePathPdf;

	/**
	 * Last Upload Time Pdf
	 * 
	 * @Column(name="last_upload_time_pdf", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Pdf")
	 * @var string
	 */
	protected $lastUploadTimePdf;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Genre ID
	 * 
	 * @Column(name="genre_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Genre ID")
	 * @var string
	 */
	protected $genreId;

	/**
	 * Genre ID
	 * 
	 * @JoinColumn(name="genre_id")
	 * @Label(content="Genre ID")
	 * @var Genre
	 */
	protected $genre;

	/**
	 * Bpm
	 * 
	 * @Column(name="bpm", type="float", nullable=true)
	 * @Label(content="Bpm")
	 * @var double
	 */
	protected $bpm;

	/**
	 * Time Signature
	 * 
	 * @Column(name="time_signature", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Time Signature")
	 * @var string
	 */
	protected $timeSignature;

	/**
	 * Subtitle
	 * 
	 * @Column(name="subtitle", type="longtext", nullable=true)
	 * @Label(content="Subtitle")
	 * @var string
	 */
	protected $subtitle;

	/**
	 * Subtitle Complete
	 * 
	 * @Column(name="subtitle_complete", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $subtitleComplete;

	/**
	 * Lyric Midi
	 * 
	 * @Column(name="lyric_midi", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi")
	 * @var string
	 */
	protected $lyricMidi;

	/**
	 * Lyric Midi Raw
	 * 
	 * @Column(name="lyric_midi_raw", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi Raw")
	 * @var string
	 */
	protected $lyricMidiRaw;

	/**
	 * Vocal Guide
	 * 
	 * @Column(name="vocal_guide", type="longtext", nullable=true)
	 * @Label(content="Vocal Guide")
	 * @var string
	 */
	protected $vocalGuide;

	/**
	 * Vocal
	 * 
	 * @Column(name="vocal", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $vocal;

	/**
	 * Instrument
	 * 
	 * @Column(name="instrument", type="longtext", nullable=true)
	 * @Label(content="Instrument")
	 * @var string
	 */
	protected $instrument;

	/**
	 * Midi Vocal Channel
	 * 
	 * @Column(name="midi_vocal_channel", type="int(11)", length=11, nullable=true)
	 * @Label(content="Midi Vocal Channel")
	 * @var integer
	 */
	protected $midiVocalChannel;

	/**
	 * Rating
	 * 
	 * @Column(name="rating", type="float", nullable=true)
	 * @Label(content="Rating")
	 * @var double
	 */
	protected $rating;

	/**
	 * Comment
	 * 
	 * @Column(name="comment", type="longtext", nullable=true)
	 * @Label(content="Comment")
	 * @var string
	 */
	protected $comment;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Last Upload Time Image
	 * 
	 * @Column(name="last_upload_time_image", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Image")
	 * @var string
	 */
	protected $lastUploadTimeImage;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;
}
```

Filtering and pagination

```php
<?php
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Pagination\PicoPagination;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Request\InputGet;
use MagicObject\Response\Generated\PicoSelectOption;
use MagicObject\Util\Dms;
use MusicProductionManager\Constants\ParamConstant;
use MusicProductionManager\Data\Entity\Album;
use MusicProductionManager\Data\Entity\Artist;
use MusicProductionManager\Data\Entity\EntitySong;
use MusicProductionManager\Data\Entity\EntitySongComment;
use MusicProductionManager\Data\Entity\Genre;
use MusicProductionManager\Data\Entity\Producer;
use MusicProductionManager\Utility\SpecificationUtil;
use MusicProductionManager\Utility\UserUtil;

require_once "inc/auth-with-login-form.php";
require_once "inc/header.php";

$inputGet = new InputGet();

$allowChangeVocalist = UserUtil::isAllowSelectVocalist($currentLoggedInUser);
$allowChangeComposer = UserUtil::isAllowSelectComposer($currentLoggedInUser);
$allowChangeArranger = UserUtil::isAllowSelectArranger($currentLoggedInUser);

?>
<div class="filter-container">
<form action="" method="get">
<div class="filter-group">
	<span>Genre</span>
	<select class="form-control" name="genre_id" id="genre_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Genre(null, $database), array('value'=>'genreId', 'label'=>'name'), $inputGet->getGenreId()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Album</span>
	<select class="form-control" name="album_id" id="album_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Album(null, $database), array('value'=>'albumId', 'label'=>'name'), $inputGet->getAlbumId(), null, new PicoSortable('sortOrder', PicoSort::ORDER_TYPE_DESC)); ?>
	</select>
</div>
<div class="filter-group">
	<span>Producer</span>
	<select class="form-control" name="producer_id" id="producer_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Producer(null, $database), array('value'=>'producerId', 'label'=>'name'), $inputGet->getProducerId()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Composer</span>
	<select class="form-control" name="composer" id="composer">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getComposer()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Arranger</span>
	<select class="form-control" name="arranger" id="arranger">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getArranger()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Vocalist</span>
	<select class="form-control" name="vocalist" id="vocalist">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getVocalist()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Title</span>
	<input class="form-control" type="text" name="title" id="title" autocomplete="off" value="<?php echo $inputGet->getTitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
</div>
<div class="filter-group">
	<span>Subtitle</span>
	<input class="form-control" type="text" name="subtitle" id="subtitle" autocomplete="off" value="<?php echo $inputGet->getSubtitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
</div>

<div class="filter-group">
	<span>Subtitle Complete</span>
	<select class="form-control" name="subtitle_complete" id="subtitle_complete">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedSubtitleComplete("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedSubtitleComplete("0");?>>No</option>
	</select>
</div>

<div class="filter-group">
	<span>Vocal</span>
	<select class="form-control" name="vocal" id="vocal">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedVocal("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedVocal("0");?>>No</option>
	</select>
</div>

<div class="filter-group">
	<span>Active</span>
	<select class="form-control" name="active" id="active">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedActive("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedActive("0");?>>No</option>
	</select>
</div>

<input class="btn btn-success" type="submit" value="Show">

</form>
</div>
<?php
$orderMap = array(
'name'=>'name', 
'title'=>'title', 
'rating'=>'rating',
'albumId'=>'albumId', 
'album'=>'albumId', 
'trackNumber'=>'trackNumber',
'genreId'=>'genreId', 
'genre'=>'genreId',
'producerId'=>'producerId',
'artistVocalId'=>'artistVocalId',
'artistVocalist'=>'artistVocalId',
'artistComposer'=>'artistComposer',
'artistArranger'=>'artistArranger',
'duration'=>'duration',
'subtitleComplete'=>'subtitleComplete',
'vocal'=>'vocal',
'active'=>'active'
);
$defaultOrderBy = 'albumId';
$defaultOrderType = 'desc';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createSongSpecification($inputGet);

if($pagination->getOrderBy() == '')
{
$sortable = new PicoSortable();
$sort1 = new PicoSort('albumId', PicoSort::ORDER_TYPE_DESC);
$sortable->addSortable($sort1);
$sort2 = new PicoSort('trackNumber', PicoSort::ORDER_TYPE_ASC);
$sortable->addSortable($sort2);
}
else
{
$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $defaultOrderBy), $pagination->getOrderType($defaultOrderType));
}

$pageable = new PicoPageable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$songEntity = new EntitySong(null, $database);
$rowData = $songEntity->findAll($spesification, $pageable, $sortable, true);

$result = $rowData->getResult();

?>

<script>
$(document).ready(function(e){
	let pg = new Pagination('.pagination', '.page-selector', 'data-page-number', 'page');
	pg.init();
	$(document).on('change', '.filter-container form select', function(e2){
		$(this).closest('form').submit();
	});
});
</script>

<?php
if(!empty($result))
{
?>
<div class="pagination">
<div class="pagination-number">
<?php
foreach($rowData->getPagination() as $pg)
{
	?><span class="page-selector<?php echo $pg['selected'] ? ' page-selected':'';?>" data-page-number="<?php echo $pg['page'];?>"><a href="#"><?php echo $pg['page'];?></a></span><?php
}
?>
</div>
</div>
<div class="table-list-container" style="overflow-x:auto">
<table class="table text-nowrap">
<thead>
	<tr>
	<th scope="col" width="20"><i class="ti ti-edit"></i></th>
	<th scope="col" width="20"><i class="ti ti-player-play"></i></th>
	<th scope="col" width="20"><i class="ti ti-download"></i></th>
	<th scope="col" width="20">#</th>
	<th scope="col" class="col-sort" data-name="name">Name</th>
	<th scope="col" class="col-sort" data-name="title">Title</th>
	<th scope="col" class="col-sort" data-name="rating">Rate</th>
	<th scope="col" class="col-sort" data-name="album_id">Album</th>
	<th scope="col" class="col-sort" data-name="producer_id">Producer</th>
	<th scope="col" class="col-sort" data-name="track_number">Trk</th>
	<th scope="col" class="col-sort" data-name="genre_id">Genre</th>
	<th scope="col" class="col-sort" data-name="artist_vocalist">Vocalist</th>
	<th scope="col" class="col-sort" data-name="artist_composer">Composer</th>
	<th scope="col" class="col-sort" data-name="artist_arranger">Arranger</th>
	<th scope="col" class="col-sort" data-name="duration">Duration</th>
	<th scope="col" class="col-sort" data-name="vocal">Vocal</th>
	<th scope="col" class="col-sort" data-name="subtitle_complete">Sub</th>
	<th scope="col" class="col-sort" data-name="active">Active</th>
	</tr>
</thead>
<tbody>
	<?php
	$no = $pagination->getOffset();
	foreach($result as $song)
	{
	$no++;
	$songId = $song->getSongId();
	$linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&song_id=".$songId;
	$linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&song_id=".$songId;
	$linkDelete = basename($_SERVER['PHP_SELF'])."?action=delete&song_id=".$songId;
	$linkDownload = "read-file.php?type=all&song_id=".$songId;
	?>
	<tr data-id="<?php echo $songId;?>">
	<th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
	<th scope="row"><a href="#" class="play-data" data-url="<?php echo $cfg->getSongBaseUrl()."/".$song->getSongId()."/".basename($song->getFilePath());?>?hash=<?php echo str_replace(array(' ', '-', ':'), '', $song->getLastUploadTime());?>"><i class="ti ti-player-play"></i></a></th>
	<th scope="row"><a href="<?php echo $linkDownload;?>"><i class="ti ti-download"></i></a></th>
	<th class="text-right" scope="row"><?php echo $no;?></th>
	<td class="text-nowrap"><a href="<?php echo $linkDetail;?>" class="text-data text-data-name"><?php echo $song->getName();?></a></td>
	<td class="text-nowrap"><a href="<?php echo $linkDetail;?>" class="text-data text-data-title"><?php echo $song->getTitle();?></a></td>
	<td class="text-data text-data-rating text-nowrap"><?php echo $song->hasValueRating() ? $song->getRating() : "";?></td>
	<td class="text-data text-data-album-name text-nowrap"><?php echo $song->hasValueAlbum() ? $song->getAlbum()->getName() : "";?></td>
	<td class="text-data text-data-producer-name text-nowrap"><?php echo $song->hasValueProducer() ? $song->getProducer()->getName() : "";?></td>
	<td class="text-data text-data-track-number text-nowrap"><?php echo $song->hasValueTrackNumber() ? $song->getTrackNumber() : "";?></td>
	<td class="text-data text-data-genre-name text-nowrap"><?php echo $song->hasValueGenre() ? $song->getGenre()->getName() : "";?></td>
	<td class="text-data text-data-artist-vocal-name text-nowrap"><?php echo $song->hasValueVocalist() ? $song->getVocalist()->getName() : "";?></td>
	<td class="text-data text-data-artist-composer-name text-nowrap"><?php echo $song->hasValueComposer() ? $song->getComposer()->getName() : "";?></td>
	<td class="text-data text-data-artist-arranger-name text-nowrap"><?php echo $song->hasValueArranger() ? $song->getArranger()->getName() : "";?></td>
	<td class="text-data text-data-duration text-nowrap"><?php echo (new Dms())->ddToDms($song->getDuration() / 3600)->printDms(true, true); ?></td>
	<td class="text-data text-data-vocal text-nowrap"><?php echo $song->isVocal() ? 'Yes' : 'No';?></td>
	<td class="text-data text-data-subtitle-complete text-nowrap"><?php echo $song->isSsubtitleComplete() ? 'Yes' : 'No';?></td>
	<td class="text-data text-data-active text-nowrap"><?php echo $song->isActive() ? 'Yes' : 'No';?></td>
	</tr>
	<?php
	}
	?>
	
</tbody>
</table>
</div>

<div class="pagination">
<div class="pagination-number">
<?php
foreach($rowData->getPagination() as $pg)
{
	?><span class="page-selector<?php echo $pg['selected'] ? ' page-selected':'';?>" data-page-number="<?php echo $pg['page'];?>"><a href="#"><?php echo $pg['page'];?></a></span><?php
}
?>
</div>
</div>

<?php
}
?>

<script>
let playerModal;


$(document).ready(function(e){
let playerModalSelector = document.querySelector('#songPlayer');
playerModal = new bootstrap.Modal(playerModalSelector, {
	keyboard: false
});

$('a.play-data').on('click', function(e2){
	e2.preventDefault();
	$('#songPlayer').find('audio').attr('src', $(this).attr('data-url'));
	playerModal.show();
});
$('.close-player').on('click', function(e2){
	e2.preventDefault();
	$('#songPlayer').find('audio')[0].pause();
	playerModal.hide();
});
});
</script>

<div style="background-color: rgba(0, 0, 0, 0.11);" class="modal fade" id="songPlayer" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="songPlayerLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="addAlbumDialogLabel">Play Song</h5>
			<button type="button" class="btn-primary btn-close close-player" aria-label="Close"></button>
		</div>
		<div class="modal-body">
			<audio style="width: 100%; height: 40px;" controls></audio>
		</div>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-success close-player">Close</button>
		</div>
	</div>
</div>
</div>

<div class="lazy-dom modal-container modal-update-data" data-url="lib.ajax/song-update-dialog.php"></div>

<script>
let updateSongModal;

$(document).ready(function(e){

$(document).on('click', '.edit-data', function(e2){
	e2.preventDefault();
	e2.stopPropagation();
	
	let songId = $(this).closest('tr').attr('data-id') || '';
	let dialogSelector = $('.modal-update-data');
	dialogSelector.load(dialogSelector.attr('data-url')+'?song_id='+songId, function(data){
	
	let updateSongModalElem = document.querySelector('#updateSongDialog');
	updateSongModal = new bootstrap.Modal(updateSongModalElem, {
		keyboard: false
	});
	updateSongModal.show();
	downloadForm('.lazy-dom-container', function(){
		if(!allDownloaded)
		{
			initModal2();
			console.log('loaded')
			allDownloaded = true;
		}
		loadForm();
	});
	})
});

$(document).on('click', '.save-update-song', function(){
	if($('.song-dialog audio').length > 0)
	{
	$('.song-dialog audio').each(function(){
		$(this)[0].pause();
	});
	}
	let dataSet = $(this).closest('form').serializeArray();
	$.ajax({
	type:'POST',
	url:'lib.ajax/song-update.php',
	data:dataSet, 
	dataType:'json',
	success: function(data)
	{
		updateSongModal.hide();
		let formData = getFormData(dataSet);
		let dataId = data.song_id;
		$('[data-id="'+dataId+'"] .text-data.text-data-name').text(data.name);
		$('[data-id="'+dataId+'"] .text-data.text-data-title').text(data.title);
		$('[data-id="'+dataId+'"] .text-data.text-data-rating').text(data.rating);
		$('[data-id="'+dataId+'"] .text-data.text-data-track-number').text(data.track_number);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-vocal-name').text(data.artist_vocal_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-composer-name').text(data.artist_composer_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-arranger-name').text(data.artist_arranger_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-album-name').text(data.album_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-genre-name').text(data.genre_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-duration').text(data.duration);
		$('[data-id="'+dataId+'"] .text-data.text-data-vocal').text(data.vocal === true || data.vocal == 1 || data.vocal == "1" ?'Yes':'No');
		$('[data-id="'+dataId+'"] .text-data.text-data-active').text(data.active === true || data.active == 1 || data.active == "1" ?'Yes':'No');
	}
	})
});
});
</script>
<?php
require_once "inc/footer.php";
?>
```

## Multiple Database Connection

MagicObject requires a database connection for object construction using database connections. In this way, users can use multiple database connections in a module very easily.

Example:

```php
$album = new Album(null, $database1);
try
{
	$album->find("123456");
	$album->currentDatabase($database2);
	$album->save();
}
catch(Exception $e)
{
	error_log($e->getMessage());
}
```

From the example above, it can be seen that an entity can even use two databases interchangeably.

When constructed, the `$album` object uses the `$database1` database connection. The application retrieves data with the primary key `123456` from `$database1` and then stores it in `$database2` without having to change many parameters and lots of code. Please note that to save `$album` data, you must use the `save` method instead of `insert` or `update` because there is a possibility that data with the same primary key already exists in `$database2`.

```yml
database_target:
  driver: mysql
  host: server1.domain.com
  port: 3306
  username: root
  password: Tantrum2025
  database_name: sipro
  databseSchema: public
  timeZone: Asia/Jakarta
database_source:
  driver: mysql
  host: server1.domain.com
  port: 3306
  username: root
  password: Tantrum2025
  database_name: sipro_ori
  databseSchema: public
  timeZone: Asia/Jakarta
maximum_record: 100
```

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());

$database = new PicoDatabase($databaseCredentials, 
    function($sql, $type) //NOSONAR
    {
        // callback when execute query that modify data
    }, 
    function($sql) //NOSONAR
    {
        // callback when execute all query
    }
);

try
{
    $database->connect();
    $shutdownManager = new ShutdownManager($database);
    $shutdownManager->registerShutdown();
}
catch(Exception $e)
{
    // do nothing
}

## Dump Database

We can dump database to another database type. We do not need any database converter. Just define the target database type when we dump the database.

```php
<?php

use MagicObject\Database\PicoDatabaseType;
use MagicObject\Generator\PicoDatabaseDump;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";


/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="song")
 */
class Song extends MagicObject
{
	/**
	 * Song ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="song_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Song ID")
	 * @var string
	 */
	protected $songId;

	/**
	 * Random Song ID
	 * 
	 * @Column(name="random_song_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Random Song ID")
	 * @var string
	 */
	protected $randomSongId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Album ID
	 * 
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Track Number
	 * 
	 * @Column(name="track_number", type="int(11)", length=11, nullable=true)
	 * @Label(content="Track Number")
	 * @var integer
	 */
	protected $trackNumber;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Artist Vocal
	 * 
	 * @Column(name="artist_vocalist", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Vocal")
	 * @var string
	 */
	protected $artistVocalist;

	/**
	 * Artist Composer
	 * 
	 * @Column(name="artist_composer", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Composer")
	 * @var string
	 */
	protected $artistComposer;

	/**
	 * Artist Arranger
	 * 
	 * @Column(name="artist_arranger", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Arranger")
	 * @var string
	 */
	protected $artistArranger;

	/**
	 * File Path
	 * 
	 * @Column(name="file_path", type="text", nullable=true)
	 * @Label(content="File Path")
	 * @var string
	 */
	protected $filePath;

	/**
	 * File Name
	 * 
	 * @Column(name="file_name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Name")
	 * @var string
	 */
	protected $fileName;

	/**
	 * File Type
	 * 
	 * @Column(name="file_type", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Type")
	 * @var string
	 */
	protected $fileType;

	/**
	 * File Extension
	 * 
	 * @Column(name="file_extension", type="varchar(20)", length=20, nullable=true)
	 * @Label(content="File Extension")
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * File Size
	 * 
	 * @Column(name="file_size", type="bigint(20)", length=20, nullable=true)
	 * @Label(content="File Size")
	 * @var integer
	 */
	protected $fileSize;

	/**
	 * File Md5
	 * 
	 * @Column(name="file_md5", type="varchar(32)", length=32, nullable=true)
	 * @Label(content="File Md5")
	 * @var string
	 */
	protected $fileMd5;

	/**
	 * File Upload Time
	 * 
	 * @Column(name="file_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="File Upload Time")
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * First Upload Time
	 * 
	 * @Column(name="first_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="First Upload Time")
	 * @var string
	 */
	protected $firstUploadTime;

	/**
	 * Last Upload Time
	 * 
	 * @Column(name="last_upload_time", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time")
	 * @var string
	 */
	protected $lastUploadTime;

	/**
	 * File Path Midi
	 * 
	 * @Column(name="file_path_midi", type="text", nullable=true)
	 * @Label(content="File Path Midi")
	 * @var string
	 */
	protected $filePathMidi;

	/**
	 * Last Upload Time Midi
	 * 
	 * @Column(name="last_upload_time_midi", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Midi")
	 * @var string
	 */
	protected $lastUploadTimeMidi;

	/**
	 * File Path Xml
	 * 
	 * @Column(name="file_path_xml", type="text", nullable=true)
	 * @Label(content="File Path Xml")
	 * @var string
	 */
	protected $filePathXml;

	/**
	 * Last Upload Time Xml
	 * 
	 * @Column(name="last_upload_time_xml", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Xml")
	 * @var string
	 */
	protected $lastUploadTimeXml;

	/**
	 * File Path Pdf
	 * 
	 * @Column(name="file_path_pdf", type="text", nullable=true)
	 * @Label(content="File Path Pdf")
	 * @var string
	 */
	protected $filePathPdf;

	/**
	 * Last Upload Time Pdf
	 * 
	 * @Column(name="last_upload_time_pdf", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Pdf")
	 * @var string
	 */
	protected $lastUploadTimePdf;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var double
	 */
	protected $duration;

	/**
	 * Genre ID
	 * 
	 * @Column(name="genre_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Genre ID")
	 * @var string
	 */
	protected $genreId;

	/**
	 * Bpm
	 * 
	 * @Column(name="bpm", type="float", nullable=true)
	 * @Label(content="Bpm")
	 * @var double
	 */
	protected $bpm;

	/**
	 * Time Signature
	 * 
	 * @Column(name="time_signature", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Time Signature")
	 * @var string
	 */
	protected $timeSignature;

	/**
	 * Subtitle
	 * 
	 * @Column(name="subtitle", type="longtext", nullable=true)
	 * @Label(content="Subtitle")
	 * @var string
	 */
	protected $subtitle;

	/**
	 * Subtitle Complete
	 * 
	 * @Column(name="subtitle_complete", type="tinyint(1)", length=1, nullable=true)
	 * @Label(content="Subtitle Complete")
	 * @var boolean
	 */
	protected $subtitleComplete;

	/**
	 * Lyric Midi
	 * 
	 * @Column(name="lyric_midi", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi")
	 * @var string
	 */
	protected $lyricMidi;

	/**
	 * Lyric Midi Raw
	 * 
	 * @Column(name="lyric_midi_raw", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi Raw")
	 * @var string
	 */
	protected $lyricMidiRaw;

	/**
	 * Vocal Guide
	 * 
	 * @Column(name="vocal_guide", type="longtext", nullable=true)
	 * @Label(content="Vocal Guide")
	 * @var string
	 */
	protected $vocalGuide;

	/**
	 * Vocal
	 * 
	 * @Column(name="vocal", type="tinyint(1)", length=1, nullable=true)
	 * @Label(content="Vocal")
	 * @var boolean
	 */
	protected $vocal;

	/**
	 * Instrument
	 * 
	 * @Column(name="instrument", type="longtext", nullable=true)
	 * @Label(content="Instrument")
	 * @var string
	 */
	protected $instrument;

	/**
	 * Midi Vocal Channel
	 * 
	 * @Column(name="midi_vocal_channel", type="int(11)", length=11, nullable=true)
	 * @Label(content="Midi Vocal Channel")
	 * @var integer
	 */
	protected $midiVocalChannel;

	/**
	 * Rating
	 * 
	 * @Column(name="rating", type="float", nullable=true)
	 * @Label(content="Rating")
	 * @var double
	 */
	protected $rating;

	/**
	 * Comment
	 * 
	 * @Column(name="comment", type="longtext", nullable=true)
	 * @Label(content="Comment")
	 * @var string
	 */
	protected $comment;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Last Upload Time Image
	 * 
	 * @Column(name="last_upload_time_image", type="timestamp", length=19, nullable=true)
	 * @Label(content="Last Upload Time Image")
	 * @var string
	 */
	protected $lastUploadTimeImage;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @Label(content="Active")
	 * @var boolean
	 */
	protected $active;

}

```

### Dump Structure

We can dump database structure without connect to real database. Just define the target database type. If we will dump multiple table, we must use dedicated instance of `PicoDatabaseDump`.

```php
$song = new Song();
$dumpForSong = new PicoDatabaseDump();
echo $dumpForSong->dumpStructure($song, PicoDatabaseType::DATABASE_TYPE_MYSQL, true, true);
```

### Dump Data

We can dump data by connecting to real database. Don't forget to define the target database type. If we will dump multiple table, we must use dedicated instance of `PicoDatabaseDump`.

```php
$song = new Song(null, $database);
$pageData = $song->findAll();
$dumpForSong = new PicoDatabaseDump();
echo $dumpForSong->dumpData($pageData, PicoDatabaseType::DATABASE_TYPE_MYSQL);
```


## Object Label

```
<?php

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="user_type")
 */
class UserType extends MagicObject
{
	/**
	 * User Type ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="user_type_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="User Type ID")
	 * @var string
	 */
	protected $userTypeId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(255)", length=255, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Admin
	 * 
	 * @Column(name="admin", type="tinyint(1)", length=1, nullable=true)
	 * @var boolean
	 */
	protected $admin;

	/**
	 * Sort Order
	 * 
	 * @Column(name="sort_order", type="int(11)", length=11, nullable=true)
	 * @Label(content="Sort Order")
	 * @var integer
	 */
	protected $sortOrder;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=19, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=19, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(40)", length=40, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, default_value="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var boolean
	 */
	protected $active;

}
```

```php

require_once dirname(__DIR__) . "/vendor/autoload.php";

$userType = new UserType();

// print label of adminCreate property
echo $userType->labelAdminCreate();
// it will print "Admin Create"
```

## Database Query Builder

Database Query Builder is a feature for creating object-based database queries. The output of the Database Query Builder is a query that can be directly executed by the database used.

Database Query Builder is actually designed for all relational databases but is currently only available in two languages, namely MySQL and PostgreSQL. MagicObject internally uses the Database Query Builder to create queries based on given methods and parameters.

Database Query Builder is required to create native queries that cannot be created automatically by MagicObject. Apart from that, Database Query Builder is also used for performance reasons where native queries will run faster because they only retrieve the desired columns and do not perform unnecessary joins.

```php
<?php

use MagicObject\Database\PicoDatabaseQueryBuilder;

use MagicObject\Database\PicoDatabase;
use MagicObject\Database\PicoDatabaseCredentials;
use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Config\ConfigApp;

use MusicProductionManager\Data\Entity\Album;

require_once dirname(__DIR__)."/vendor/autoload.php";

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());
$database = new PicoDatabase($databaseCredentials);
try
{
    $database->connect();
  
    $queryBuilder = new PicoDatabaseQueryBuilder($database);
  
    $queryBuilder
        ->newQuery()
        ->select("u.*")
        ->from("user")
        ->alias("u")
        ->where("u.username = ? and u.password = ? and u.active = ?", $username, $password, true)
        ;
    $stmt = $database->executeQuery($queryBuilder);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($rows as $user)
    {
        var_dump($user);
    }
  
}
catch(Ecxeption $e)
{
  
}
```

### Methods

**newQuery()**

`newQuery()` is method to clear all properties from previous query. Allways invoke this method before create new query to ensure the query is correct.

**insert()**

`insert()` is method to start the `INSERT` query.

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')` will be executed by the database as is. You have to escape them manually before use it.

**into($query)**

`into($query)` is method for `INTO`

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

**fields($query)**

`fields($query)` is method to send field on query `INSERT`. The parameter can be an array or string.

**values($query)**

`values($query)` is method to send values on query `INSERT`. The parameter can be an array or string.

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')");
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')` will be executed by the database as is. You have to escape them manually before use it.

Example 2:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values("(?, ?, ?, ?)", '123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12');
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be escaped before being executed by the database. You don't need to escape it first.

Example 3:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields(array("song_id", "name", "title", "time_create"))
    ->values(array('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be executed by the database as is. You have to escape them manually before use it.


Example 4:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields("(song_id, name, title, time_create)")
    ->values(array('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be executed by the database as is. You have to escape them manually before use it.


Example 5:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$data = array(
    "song_id"=>'123456', 
    "name"=>'Lagu 0001', 
    "title"=>'Membendung Rindu', 
    "time_create"=>'2024-03-03 10:11:12'
    );
$queryBuilder->newQuery()
    ->insert()
    ->into("song")
    ->fields(array_keys($data))
    ->values(array_values($data));
/*
insert into song
(song_id, name, title, time_create)
values('123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12')
*/
```

This way, `'123456', 'Lagu 0001', 'Membendung Rindu', '2024-03-03 10:11:12'` will be escaped before being executed by the database. You don't need to escape it first.


**select($query)**

`select($query)` is metod for query `SELECT`

**alias($query)**

`alias($query)` is method for query `AS`

**delete()**

`delete` is method for query `DELETE`

**from($query)**

`from($query)` is method for query `FROM`

**where($query)**

`from($query)` is method for query `WHERE`

Example 1:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("*")
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
select *
from song
where time_create > '2023-01-01'
*/

$queryBuilder->newQuery()
    ->detele()
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
delete
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be executed by the database as is. You have to escape them manually before use it.


Example 2:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create > '2023-01-01' ");
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be executed by the database as is. You have to escape them manually before use it.


Example 3:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create > ? ", '2023-01-01');
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-01'
*/

$queryBuilder->newQuery()
    ->delete()
    ->from("song")
    ->where("time_create > ? ", '2023-01-01');
/*
delete
from song
where time_create > '2023-01-01'
*/
```

This way, `'2023-01-01'` will be escaped before being executed by the database. You don't need to escape it first.


Example 4:

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$queryBuilder->newQuery()
    ->select("song_id, name as song_code, title, time_create")
    ->from("song")
    ->where("time_create < ? ", date('Y-m-d H:i:s'));
/*
select song_id, name as song_code, title, time_create
from song
where time_create > '2023-01-00 12:12:12'
*/

$queryBuilder->newQuery()
    ->delete()
    ->from("song")
    ->where("time_create < ? ", date('Y-m-d H:i:s'));
/*
delete
from song
where time_create > '2023-01-00 12:12:12'
*/
```

**join($query)**

**leftJoin($query)**

**rightJoin($query)**

**innerJoin($query)**

**outerJoin($query)**

Example

```php
$queryBuilder = new PicoDatabaseQueryBuilder($database);
$active = true;
$queryBuilder->newQuery()
    ->select("song.*, album.name as album_name")
    ->from("song")
    ->leftJoin("album")
    ->on("album.album_id = song.album_id")
    ->where("song.active = ? ", $active);
/*
select song.*, album.name as album_name
from song
left join album
on album.album_id = song.album_id
where song.active = true
*/
```

This way, `$active` will be escaped before being executed by the database. You don't need to escape it first.

## Upload File

Uploading lots of files with arrays is difficult for some developers, especially novice developers. There is a significant difference between uploading a single file and multiple files.

When the developer decides to change the form from single file to multiple files or vice versa, the backend developer must change the code to handle the uploaded files.

```html
<!-- single file -->
<form action="" method="post" enctype="multipart/form-data">
     <input name="myupload" type="file" />
     <input type="submit" />
</form>
```

```html
<!-- multiple files -->
<form action="" method="post" enctype="multipart/form-data">
     <input name="myupload[]" type="file" webkitdirectory multiple />
     <input type="submit" />
</form>
```

```php
<?php

use MagicObject\File\PicoUplodFile;

require_once "vendor/autoload.php";

$files = new PicoUplodFile();

$file1 = $files->get('myupload');
// or 
// $file1 = $files->myupload;

$targetDir = __DIR__;

foreach($file1->getAll() as $fileItem)
{
	$temporaryName = $fileItem->getTmpName();
	$name = $fileItem->getName();
	$size = $fileItem->getSize();
	echo "$name | $temporaryName | $size\r\n";
	move_uploaded_file($temporaryName, $targetDir."/".$name);
}

```

Developers simply retrieve data using the `getAll` method and developers will get all files uploaded by users either via single file or multiple file forms. If necessary, the developer can check whether the file was uploaded using a single file or multiple file form with the `isMultiple()` method

```php

if($file1->isMultiple())
{
	// do something here
}
else
{
	// do something here
}
```

## Language

MagicObject supports multilingual applications. MagicObject allows developers to create entities that support a wide variety of languages that users can choose from. At the same time, different users can use different languages.

To create table with multiple language, create new class from `DataTable` object. We can copy data from aother object to `DataTable` easly.

```php
<?php

use MagicObject\DataTable;
use MagicObject\MagicObject;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="song")
 */
class Song extends MagicObject
{
	/**
	 * Song ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="song_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Song ID")
	 * @var string
	 */
	protected $songId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;
    
    /**
	 * Composer
	 * 
	 * @Column(name="composer", type="text", nullable=true)
	 * @Label(content="Composer")
	 * @var string
	 */
	protected $composer;
    
    /**
	 * Vocalist
	 * 
	 * @Column(name="vocalist", type="text", nullable=true)
	 * @Label(content="Vocalist")
	 * @var string
	 */
	protected $vocalist;
}

/**
 * House
 * 
 * @Attributes(id="house" width="100%" style="border-collapse:collapse; color:#333333")
 * @ClassList(content="table table-responsive")
 * @DefaultColumnLabel(content="Language")
 * @Language(content="en")
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 * @Id(content="house")
 */
class Multibahasa extends DataTable
{
    
}

$song = new Song();
$song
    ->setSongId("11111")
    ->setTitle("Lagu Satu")
    ->setComposer("Kamshory")
    ->setVocalist("Roy")
    ;

$translated = new Multibahasa($song);
echo $translated;


// add language from array
$translated->addLanguage("id", 
    array(
        "songId" => "ID Lagu",
        "title" => "Judul",
        "composer" => "Pengarang",
        "vocalist" => "Penyanyi"
    )
);
$translated->selectLanguage('id');
echo $translated;

// add language from stdClass
$translator1 = new stdClass;

$translator1->songId = "ID Lagu";
$translator1->title = "Judul";
$translator1->composer = "Pengarang";
$translator1->vocalist = "Penyanyi";

$translated->addLanguage("id", $translator1);
$translated->selectLanguage('id');
echo $translated;

// add language from specific class
class Bahasa
{
    public $songId = "ID Lagu";
    public $title = "Judul";
    public $composer = "Pengarang";
    public $vocalist = "Penyanyi";
}

$translator2 = new Bahasa();

$translated->addLanguage("id", $translator2);
$translated->selectLanguage('id');
echo $translated;

``` 

```php
<?php

use MagicObject\DataTable;
use MagicObject\Util\ClassUtil\PicoObjectParser;

require_once dirname(__DIR__) . "/vendor/autoload.php";

/**
 * House
 * 
 * @Attributes(id="house" width="100%" style="border-collapse:collapse; color:#333333")
 * @ClassList(content="table table-responsive")
 * @DefaultColumnLabel(content="Language")
 * @Language(content="en")
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="album")
 * @Id(content="house")
 */
class House extends DataTable
{
    /**
     * ID
     *
     * @Label(content="ID")
     * @Column(name="id")
     * @var string
     */
    protected $id;
    
    /**
     * Address
     *
     * @Label(content="Address")
     * @Column(name="address")
     * @var string
     */
    protected $address;
    
    /**
     * Color
     *
     * @Label(content="Color")
     * @Column(name="color")
     * @var string
     */
    protected $color;

    /**
     * Time Create
     *
     * @Label(content="Time Create")
     * @Column(name="timeCreate")
     * @var DateTime
     */
    protected $timeCreate;
    
}

class BahasaIndonesia extends stdClass
{
    public $id = "ID";
    
    public $address = "Alamat";
    
    public $color = "Warna";

    public $timeCreate = "Waktu Buat";
}

$data = PicoObjectParser::parseYamlRecursive(
"id: 1
address: Jalan Inspeksi no 9
color: blue
"
);

$language = new BahasaIndonesia();

$rumah = new House($data);
$rumah->addLanguage('id', $language);
$rumah->selectLanguage('id');
$rumah->addClass('table');

$apa = $rumah;
echo $apa;
```


## Database Migration

MagicObject allows users to import data from a database with different table names and column names between the source database and the destination database. This feature is used by developers who develop applications that are already used in production environments.

On the one hand, the application requires a new database structure according to what is defined by the developer. On the other hand, users want to use existing data.

Instead of altering tables from an existing database according to the new data, users can use the data import feature provided by MagicObject. Users can define column mappings and also define queries that will be executed after the database import is performed.

Users simply create an import data configuration file dan import script as follows:

**Import Configuration**

File `import.yml`

```yml
database_target:
  driver: mysql
  host: server1.domain.com
  port: 3306
  username: root
  password: Tantrum2025
  database_name: sipro
  databseSchema: public
  timeZone: Asia/Jakarta
database_source:
  driver: mysql
  host: server1.domain.com
  port: 3306
  username: root
  password: Tantrum2025
  database_name: sipro_ori
  databseSchema: public
  timeZone: Asia/Jakarta
maximum_record: 100
table:
  - source: modul
    target: modul
    map: 
    - 'default_data : default'
    - 'sort_order : order'
    pre_import_script: 
    - "truncate modul"
    maximum_record: 2000
  - source: hak_akses
    target: hak_akses
    map:
    - 'allowed_detail : view'
    - 'allowed_create : insert'
    - 'allowed_update : update'
    - 'allowed_delete : delete'
    pre_import_script: 
    - "truncate hak_akses"
    post_import_script: 
    - "update hak_akses set allowed_list = true, allowed_approve = true, allowed_sort_order = true"
    maximum_record: 50
```

**Explanation**

- `database_source` is the source database configuration
- `database_target` is the target database configuration
- `table` is an array containing all the tables to be imported. Tables not listed in `table` will not be imported.
- `maximum_record` is the maximum number of records in a single insert query. Note that MagicObject does not care about the size of the data in bytes. If you need to adjust the maximum records per table, specify `maximum_record` on the table you want to set.

1. `source` (required)

Table name of the source database

2. `target` (required)

Table name of the target database

3. `maximum_record` (optional)

`maximum records` on a table is used to reset the number of records per `insert` query on a table for that table. This setting will override the global setting.

Table name of the target database

4. `map` (optional)

`map` is an array of text separated by colons. On the left side of the colon are the column names in the target table and database while on the right side of the colon are the column names in the source table and database. 

5. `pre_import_script` (optional)

`pre_import_script` is an array of queries that will be executed before the data import begins. `pre_import_script` is usually used to clear data from a table and reset all sequence or auto increment values ​​from the target table.

6. `post_import_script` (optional)

`post_import_script` is an array of queries that will be executed after the data import is complete. `post_import_script` can be used for various purposes such as fixing some data on the target table including taking values ​​from other tables. Therefore post_script must be run after all tables have been successfully imported.

**Import Script**

File `import.php`

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/inc.lib/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

$fp = fopen(__DIR__.'/db.sql', 'w');
fclose($fp);
$sql = PicoDatabaseUtilMySql::importData($config, function($sql, $source, $target){
    $fp = fopen(__DIR__.'/db.sql', 'a');
    fwrite($fp, $sql.";\r\n\r\n");
    fclose($fp);
});
```

**Executing Script**

```bash
php import.php
```

MagicObject will create a database query that is saved into a file named `db.sql`. The data is taken from the `database_source` but the table and column names have been adjusted to the `database_target`. This query can be run in the `database_target`. If you want to empty a table before importing data, you can add a pre_import_script to each table. Keep in mind that all pre_import_scripts will be executed before MagicObject starts importing data.

If the database is too complex, users can use the PicoDatabaseUtilMySql::autoConfigureImportData() method to create a configuration template to avoid missing table and column names. Users simply specify the source database and the target database. MagicObject will check the tables and columns in both databases. If a table exists in the target database but not in the source database, MagicObject will write ??? as its source name. Users can manually change the name of this table. In the same table, if a column exists in the target database but not in the source database, MagicObject will write ??? as its source name. Users can manually change the name of this column.

Here is an example of how to create a database import configuration template.

**Import Configuration**

File `import.yml`

```yml
database_target:
  driver: mysql
  host: server1.domain.com
  port: 3306
  username: root
  password: Tantrum2025
  database_name: sipro
  databseSchema: public
  timeZone: Asia/Jakarta
database_source:
  driver: mysql
  host: server1.domain.com
  port: 3306
  username: root
  password: Tantrum2025
  database_name: sipro_ori
  databseSchema: public
  timeZone: Asia/Jakarta
maximum_record: 100
```

**Import Template Script**

File `configure-import.php`

```php
<?php

use MagicObject\SecretObject;
use MagicObject\Util\Database\PicoDatabaseUtilMySql;

require_once dirname(__DIR__) . "/vendor/autoload.php";

$config = new SecretObject();
$config->loadYamlFile('import.yml', true, true, true);

PicoDatabaseUtilMySql::autoConfigureImportData($config);
file_put_contents('import.yml', $config->dumpYaml(0, 2));
```

**Executing Script**

```bash
php configure-import.php
```
