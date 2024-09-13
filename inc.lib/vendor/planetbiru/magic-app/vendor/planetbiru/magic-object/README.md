# MagicObject

# Introduction

MagicObject is a library for making applications in the PHP language very easily. MagicObject can be derived into other classes with various intended uses.

Some examples of using MagicObject are as follows:

1. create dynamic objects
2. create objects with setters and getters
3. multi level object
4. access the  with entity
5. filtering and pagination
6. dump database
7. serialize and deserialize objects in JSON and Yaml format
8. importing data from another database with destination table and column names can be different from the source table and column names
9. reads INI, Yaml, and JSON files
10. Get the environment variable value
11. encrypt and decrypt application configuration
12. object from POST, GET, COOKIE, REQUEST, SERVER
13. PHP session
14. object label
15. multi language
16. multi database connection
17. upload file
18. annotations
19. debug objects 

# Installation

To install Magic Obbject

```
composer require planetbiru/magic-object
```

or if composer is not installed

```
php composer.phar require planetbiru/magic-object
```

To remove Magic Obbject

```
composer remove planetbiru/magic-object
```

or if composer is not installed

```
php composer.phar remove planetbiru/magic-object
```

To install composer on your PC or download latest composer.phar, click https://getcomposer.org/download/ 

To see available versions of MagicObject, visit https://packagist.org/packages/planetbiru/magic-object

# Advantages

MagicObject is designed to be easy to use and can even be coded using a code generator. An example of a code generator that successfully creates MagicObject code using only parameters is MagicAppBuilder. MagicObject provides many ways to write code. Users can choose the way that is easiest to implement.

MagicObject does not only pay attention to the ease of users in creating applications. MagicObject also pays attention to the efficiency of both time and resources used by applications so that applications can be run on servers with minimum specifications. This of course will save costs used both in application development and operations.

# Application Scaling

For large applications, users can scale the database and storage. So that a user can access any server, use Redis as a session repository. MagicObject clouds session storage with Redis which can be secured using a password.

![](https://github.com/Planetbiru/MagicObject/blob/main/scale-up.svg)

# Stable Version

Stable version of MagicObject is `1.17.2` or above. Please don't install versions bellow it.

# Tutorial

Tutorial is provided here https://github.com/Planetbiru/MagicObject/blob/main/tutorial.md


# Example

## Simple Object

## Yaml

**Yaml File**

```yaml
result_per_page: 20
song_base_url: ${SONG_BASE_URL}
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
```

**Configuration Object**

Create class `ConfigApp` by extends `MagicObject`

```php
<?php
namespace MusicProductionManager\Config;

use MagicObject\MagicObject;

class ConfigApp extends MagicObject
{
    /**
     * Constructor
     *
     * @param mixed $data Initial data
     * @param boolean $readonly Readonly flag
     */
    public function __construct($data = null, $readonly = false)
    {
        if($data != null)
        {
            parent::__construct($data);
        }
        $this->readOnly($readonly);
    }
    
}
```

```php
<?php

$cfg = new ConfigApp(null, true);
$cfg->loadYamlFile(dirname(__DIR__)."/.cfg/app.yml", true, true, true);

// to get database object,
// $cfg->getDatabase()
//
// to get database.host
// $cfg->getDatabase()->getHost()
// to get database.database_name
// $cfg->getDatabase()->getDatabaseName()
```

# Application

Applications that uses **MagicObjects** are :

1. **Music Production Manager** https://github.com/kamshory/MusicProductionManager
2. **AppBuilder** https://github.com/Planetbiru/AppBuilder
3. **Koperasi-Simpan-Pinjam-Syariah** https://github.com/kamshory/Koperasi-Simpan-Pinjam-Syariah
4. **Toserba Online** https://toserba-online.top/

# Credit

1. Kamshory - https://github.com/kamshory/