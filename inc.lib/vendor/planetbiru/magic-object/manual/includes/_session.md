## Session

Session variables keep information about one single user, and are available to all pages in one application.

### Session with File

**Yaml File**

```yaml
session:
  name: ${SESSION_NAME}
  max_life_time: ${SESSION_MAX_LIFE_TIME}
  save_handler: ${SESSION_SAVE_HANDLER}
  save_path: ${SESSION_SAVE_PATH}
  cookie_lifetime: ${SESSION_COOKIE_LIFETIME}
  cookie_path: ${SESSION_COOKIE_PATH}
  cookie_secure: ${SESSION_COOKIE_SECURE}
  cookie_httponly: ${SESSION_COOKIE_HTTPONLY}
  cookie_domain: ${SESSION_COOKIE_DOMAIN}
```

### Explanation:
  
-   **name**: Specifies the name of the session. The value `${SESSION_NAME}` refers to an environment variable or a parameter that should be defined elsewhere in the application or server environment. It determines the session's name identifier.
    
-   **max_life_time**: Defines the maximum lifetime of the session in seconds. The value `${SESSION_MAX_LIFE_TIME}` is expected to come from an environment variable or configuration setting, controlling how long the session will last before it expires.
    
-   **save_handler**: Specifies the handler used to save session data. In this case, `${SESSION_SAVE_HANDLER}` likely refers to the session handler, such as `redis`, `files`, or `database`, depending on the server configuration.
    
-   **save_path**: Defines the path where session data will be stored. The value `${SESSION_SAVE_PATH}` is an environment variable or configuration parameter, which could specify a directory or Redis connection string (if `redis` is used as the save handler).
    
-   **cookie_lifetime**: Specifies the lifetime of the session cookie in seconds, which controls how long the cookie will persist in the user's browser. `${SESSION_COOKIE_LIFETIME}` is the environment variable or configuration value that sets this duration.
    
-   **cookie_path**: The path on the server where the session cookie is valid. `${SESSION_COOKIE_PATH}` determines the directory or URL prefix for which the session cookie is set.
    
-   **cookie_secure**: A boolean flag that determines whether the session cookie should be transmitted over HTTPS only. If `${SESSION_COOKIE_SECURE}` is set to `true`, the cookie will only be sent over secure connections.
    
-   **cookie_httponly**: A boolean flag that indicates whether the session cookie is accessible only through the HTTP protocol (i.e., not accessible via JavaScript). If `${SESSION_COOKIE_HTTPONLY}` is `true`, it increases security by preventing client-side access to the cookie.
    
-   **cookie_domain**: Specifies the domain to which the session cookie is valid. `${SESSION_COOKIE_DOMAIN}` can define the domain for which the session cookie should be sent, allowing cross-subdomain access to the session.


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
  name: ${SESSION_NAME}
  max_life_time: ${SESSION_MAX_LIFE_TIME}
  save_handler: ${SESSION_SAVE_HANDLER}
  save_path: ${SESSION_SAVE_PATH}
  cookie_lifetime: ${SESSION_COOKIE_LIFETIME}
  cookie_path: ${SESSION_COOKIE_PATH}
  cookie_secure: ${SESSION_COOKIE_SECURE}
  cookie_httponly: ${SESSION_COOKIE_HTTPONLY}
  cookie_domain: ${SESSION_COOKIE_DOMAIN}
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

This setup ensures that the session save path is securely managed and decrypted at runtime.

### Conclusion

This implementation provides a robust framework for session management in a PHP application, allowing flexibility in storage options (files or Redis) while emphasizing security through encryption. The use of YAML for configuration keeps the setup clean and easily adjustable. By encapsulating session configuration in dedicated classes, you enhance maintainability and security.