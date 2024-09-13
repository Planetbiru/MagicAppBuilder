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
