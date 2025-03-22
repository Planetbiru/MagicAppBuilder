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
	 * @var int
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
	protected $databaseSchema = "public";

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

1. `propertyNamingStrategy`

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
`propertyNamingStrategy`

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
	 * @var int
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
	protected $databaseSchema;

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
	 * @var int
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
	protected $databaseSchema = "public";

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
    return bin2hex("You secret here");
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
    username: AAAA5NImv2VEL1Wcqx7gi/8f158SYtssj74zTN2fRrIDsGOxsnEa8+50H3Y1MCaJV7SnZo851dnjEhm38Tzjsg==
    password: WWWE/1SK1m9qVjbWnGa6/xa2OWvzXeErRvtQ0RYceGsr3fvAfGfqcDSY6pq8KoXg6MmtJS0FLyjXUq8dftM7yQ==
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
    return bin2hex("You secret here");
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
	 * @var int
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
	protected $databaseSchema = "public";

	/**
	 * Application time zone
	 *
	 * @var string
	 */
	protected $timeZone = "Asia/Jakarta";

	/**
	 * Charset
	 *
	 * @var string
	 */
	protected $charset = "utf8";
}

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

$databaseCredentials = new PicoDatabaseCredentials($cfg->getDatabase());

$database = new PicoDatabase($databaseCredentials, 
    function($sql, $type) // NOSONAR
    {
        // callback when execute query that modify data
    }, 
    function($sql) // NOSONAR
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