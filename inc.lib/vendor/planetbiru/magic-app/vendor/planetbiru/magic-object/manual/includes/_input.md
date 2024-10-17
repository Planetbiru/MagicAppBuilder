## Input POST/GET/COOKIE/REQUEST/SERVER

In PHP, handling user input can be done through various superglobals, such as $_POST, $_GET, $_COOKIE, $_REQUEST, and $_SERVER. Each of these superglobals serves a specific purpose for gathering data from different types of requests.

### Input POST

The `$_POST` superglobal is used to collect data sent via HTTP POST requests. In the provided code snippet:

```php

use MagicObject\Request\InputPost;

require_once __DIR__ . "/vendor/autoload.php";

$inputPost = new InputPost();

$name = $inputPost->getRealName();
// equivalen to 
$name = $_POST['real_name'];

```

This snippet utilizes a class called `InputPost` to retrieve the value associated with the key `'real_name'` from the `POST` request.

### Input GET

The `$_GET` superglobal retrieves data sent via URL parameters in a GET request. The corresponding code is:

```php

use MagicObject\Request\InputGet;

require_once __DIR__ . "/vendor/autoload.php";

$inputGet = new InputGet();

$name = $inputGet->getRealName();
// equivalen to 
$name = $_GET['real_name'];

```

Here, the `InputGet` class is used similarly to fetch the `'real_name'` from the `GET` request.

### Input COOKIE

The `$_COOKIE` superglobal accesses cookies sent by the client. The code example is:

```php

use MagicObject\Request\InputCookie;

require_once __DIR__ . "/vendor/autoload.php";

$inputCookie = new InputCookie();

$name = $inputCookie->getRealName();
// equivalen to 
$name = $_COOKIE['real_name'];

```

### Input REQUEST

The `$_REQUEST` superglobal can collect data from both POST and GET requests, as well as cookies. The example is:

```php

use MagicObject\Request\InputRequest;

require_once __DIR__ . "/vendor/autoload.php";

$inputRequest = new InputRequest();

$name = $inputRequest->getRealName();
// equivalen to 
$name = $_REQUEST['real_name'];

```

This allows for a unified approach to retrieve the `'real_name'` regardless of its source.

### Input SERVER

```php

use MagicObject\Request\InputServer;

require_once __DIR__ . "/vendor/autoload.php";

$inputServer = new InputServer();

$remoteAddress = $inputServer->getRemoteAddr();
// equivalen to 
$remoteAddress = $_SERVER_['REMOTE_ADDR'];

```

This retrieves the remote address of the client making the request.

### Filter Input

Filtering user input is crucial for security, as it helps prevent malicious data from affecting your application. The code snippet for filtering input looks like this:

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

In this example, the getRealName method can take a filtering constant to sanitize the input value. The filter `FILTER_SANITIZE_SPECIAL_CHARS` is used to escape special characters, preventing XSS (Cross-Site Scripting) attacks.

List of filter

```
PicoFilterConstant::FILTER_DEFAULT                      = 516;
PicoFilterConstant::FILTER_SANITIZE_NO_DOUBLE_SPACE     = 512;
PicoFilterConstant::FILTER_SANITIZE_PASSWORD            = 511;
PicoFilterConstant::FILTER_SANITIZE_ALPHA               = 510;
PicoFilterConstant::FILTER_SANITIZE_ALPHANUMERIC        = 509;
PicoFilterConstant::FILTER_SANITIZE_ALPHANUMERICPUNC    = 506;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_UINT         = 508;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_INT          = 519;
PicoFilterConstant::FILTER_SANITIZE_URL                 = 518;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_FLOAT        = 520;
PicoFilterConstant::FILTER_SANITIZE_STRING_NEW          = 513;
PicoFilterConstant::FILTER_SANITIZE_ENCODED             = 514;
PicoFilterConstant::FILTER_SANITIZE_STRING_INLINE       = 507;
PicoFilterConstant::FILTER_SANITIZE_STRING_BASE64       = 505;
PicoFilterConstant::FILTER_SANITIZE_IP                  = 504;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_OCTAL        = 503;
PicoFilterConstant::FILTER_SANITIZE_NUMBER_HEXADECIMAL  = 502;
PicoFilterConstant::FILTER_SANITIZE_COLOR               = 501;
PicoFilterConstant::FILTER_SANITIZE_POINT               = 500;
PicoFilterConstant::FILTER_SANITIZE_BOOL                = 600;
PicoFilterConstant::FILTER_VALIDATE_URL                 = 273;
PicoFilterConstant::FILTER_VALIDATE_EMAIL               = 274;
PicoFilterConstant::FILTER_SANITIZE_EMAIL               = 517;
PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS       = 515;
PicoFilterConstant::FILTER_SANITIZE_ASCII               = 601;
```


### Conclusion

In summary, handling input in PHP through superglobals is straightforward but requires careful filtering to ensure security. Using classes like `InputPost`, `InputGet`, `InputCookie`, `InputRequest`, and `InputServer` can abstract the underlying superglobal accesses, making the code cleaner and potentially more secure by enforcing consistent input handling and sanitization practices.