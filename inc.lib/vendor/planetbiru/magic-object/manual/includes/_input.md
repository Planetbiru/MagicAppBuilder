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
