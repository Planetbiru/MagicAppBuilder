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