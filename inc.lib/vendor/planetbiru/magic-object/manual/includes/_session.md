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