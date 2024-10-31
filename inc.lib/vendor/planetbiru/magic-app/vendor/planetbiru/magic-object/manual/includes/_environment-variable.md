## Environment Variable

Environment variables play a crucial role in modern application development, providing a flexible way to manage configuration settings. Instead of hardcoding values directly into your code, you can utilize environment variables to keep sensitive information secure and maintain a clean separation between your application's logic and its configuration. This approach not only enhances security but also makes your application more portable and easier to manage.


### Benefits of Using Environment Variables

1.  **Security**: Sensitive information such as API keys, database credentials, and configuration settings can be kept out of your codebase, reducing the risk of accidental exposure.
    
2.  **Portability**: Environment variables allow you to run the same code across different environments (development, staging, production) without needing to modify the codebase.
    
3.  **Simplicity**: Managing configurations through environment variables simplifies deployment processes, particularly in cloud environments where configurations can be set per instance.

### Example Configuration Using YAML and Environment Variables

Here's a basic example of how you can define application settings using a YAML file that references environment variables.

**Sample YAML Configuration**

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

### Setting Environment Variables

Before running your application, ensure that you have set the appropriate environment variables for `TIRE_DIAMETER`, `TIRE_PRESSURE`, `BODY_LENGTH`, `BODY_WIDTH`, `BODY_HEIGHT`, and `BODY_COLOR` based on the operating system you are using.

**Example PHP Code**

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

**Another Sample YAML Configuration**

In addition to the previous example, here's another configuration file that utilizes environment variables for various settings:

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

### Setting Up Environment Variables

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

### Conclusion

Utilizing environment variables for configuration management is an essential practice for modern application development. By separating configuration from code, you can enhance security, portability, and maintainability. MagicObject’s ability to integrate with environment variables through YAML configuration files allows for a flexible and powerful setup that can adapt to various environments seamlessly. This approach empowers developers to build more secure and robust applications while simplifying deployment and management processes.