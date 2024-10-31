## Object from INI


When working with configuration files, the INI format is a popular choice due to its simplicity and readability. However, PHP's native INI parsing functions come with significant limitations, particularly concerning reserved words. These restrictions can hinder developers' ability to create flexible and user-defined content in INI files. To address this issue, MagicObject has developed its own custom INI parser, providing greater freedom and functionality.

### The Limitations of Native INI Parsing in PHP

PHP's built-in functions for parsing INI files, such as `parse_ini_file()`, are convenient but have notable drawbacks:

1.  **Reserved Words**: PHP defines certain keywords as reserved words, which cannot be used as keys in INI files. This limitation can frustrate developers who want to use these words in their configuration settings.
    
2.  **Data Types**: The native parser treats all values as strings, making it difficult to handle different data types without additional processing.
    
3.  **Error Handling**: The native functions offer limited error reporting, making it challenging to debug issues in INI files.
    
4.  **Lack of Flexibility**: PHP’s native INI parser does not support advanced features like sections or comments beyond basic syntax, which can be a hindrance for complex configurations.
    

### The MagicObject Solution

To provide developers with a more robust and flexible solution, MagicObject introduces its own custom INI parser. Here are some of the benefits of using MagicObject's INI parser:

**1. Freedom from Reserved Words**

One of the most significant advantages of the custom parser is its ability to handle reserved words. Developers can define keys in their INI files without worrying about conflicts with PHP's reserved keywords, leading to greater flexibility in configuration design.

**2. Enhanced Data Type Support**

MagicObject's INI parser can intelligently handle various data types. This means that values can be parsed as integers, booleans, or arrays, reducing the need for manual type conversion after reading the configuration.

**3. Comprehensive Error Handling**

The custom parser includes robust error handling features, providing detailed feedback when issues arise. This improved debugging capability allows developers to quickly identify and resolve problems in their INI files.

**4. Advanced Features**

The MagicObject INI parser supports advanced features such as:

-   **Nested Sections**: Allowing developers to create hierarchical configurations.
-   **Comments**: Supporting inline comments to enhance readability and maintainability.
-   **Dynamic Keys**: Enabling the creation of dynamic keys that can change based on specific conditions.

**5. Improved Performance**

The custom parser is optimized for performance, ensuring that reading and processing INI files is efficient, even for larger configurations. This enhancement is crucial for applications that rely on quick access to configuration settings.

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