# MagicObject

# Introduction

MagicObject is a powerful library for creating applications in PHP with ease. It allows for the derivation of classes with various intended uses. Below are some of its key features:

# Features

1. **Dynamic Object Creation**: Easily create objects at runtime.
2. **Setters and Getters**: Simplify property management with automatic methods.
3. **Multi-Level Objects**: Support for nested objects.
4. **Entity Access**: Streamline interactions with entities.
5. **Filtering and Pagination**: Built-in methods for managing data display.
6. **Native Query**: Defining native queries for a function will increase flexibility and resource efficiency in accessing the database.
7. **Multiple Database Connection**: MagicObject supports the configuration of multiple database connections, allowing applications to interact with different databases simultaneously.
8. **Database Dumping**: Export database contents efficiently.
9. **Serialization/Deserialization**: Handle JSON and YAML formats seamlessly.
10. **Data Importing**: Import data even if source and destination schemas differ.
11. **File Reading**: Read INI, YAML, and JSON configuration files.
12. **Environment Variable Access**: Easily fetch environment variable values.
13. **Configuration Encryption**: Secure application settings.
14. **HTTP Data Handling**: Create objects from global request variables (POST, GET, etc.).
15. **Session Management**: Integrate with PHP sessions.
16. **Object Labeling**: Enhance object identification.
17. **Multi-Language Support**: Facilitate localization.
18. **File Uploads**: Handle file uploads efficiently.
19. **Annotations**: Add metadata to objects for better structure.
20. **Debugging**: Tools to debug and inspect objects.

This library provides a versatile toolkit for building robust PHP applications!

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


# MagicObject Version 2

## What is New

1.  **Native Query**
    
    -   Introduced support for native SQL queries, allowing users to execute raw SQL statements directly within the framework. This feature enhances flexibility and provides greater control over complex queries that may not be easily constructed using the ORM layer.
2.  **Multiple Database Connection**
    
    -   Added the ability to configure and manage multiple database connections. This allows developers to connect to different databases within the same application seamlessly, facilitating multi-database architectures and more complex application requirements.
3.  **Enable or Disable Entity Cache on Join**
    
    -   Introduced a feature to enable or disable entity caching specifically for join operations. This gives developers fine-tuned control over caching strategies, improving performance while also allowing for fresh data retrieval when necessary.
4.  **Enhanced Documentation**
    
    -   Comprehensive updates to the documentation for classes, properties, functions, and annotations. This includes clearer explanations, examples, and usage guidelines, making it easier for developers to understand and utilize the framework effectively.
5.  **Bug Fixes on Previous Version**
    
    -   Addressed various bugs and issues reported in earlier versions. This includes performance improvements, stability enhancements, and corrections of minor errors that could affect the functionality of the framework.

## Additional Features

-   **Improved Error Handling**: Enhanced mechanisms for error detection and handling, providing more informative messages to assist developers in troubleshooting.
-   **Performance Optimizations**: Various internal optimizations that improve the overall performance of the framework, particularly in database interactions.
-   **Backward Compatibility**: Ensured backward compatibility with version 1, allowing for a smooth transition for existing users to upgrade without significant changes to their codebase.

## Migration Notes

-   When upgrading from version 1 to version 2, please review the migration notes for any breaking changes or required adjustments to your codebase. Detailed guidelines are provided to facilitate a smooth upgrade process.

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
     * @param bool $readonly Readonly flag
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

# Contributors

1. Kamshory - https://github.com/kamshory/