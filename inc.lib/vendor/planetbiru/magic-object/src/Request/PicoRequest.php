<?php

namespace MagicObject\Request;

/**
 * Class for handling HTTP requests.
 *
 * This class provides methods to manage and retrieve data from 
 * various types of HTTP requests (GET, POST, COOKIE, ENV, SERVER).
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoRequest extends PicoRequestBase
{
    const ACTION_DETAIL = "detail";
    const ACTION_EDIT = "edit";
    const ACTION_ADD = "add";

    /**
     * Constructor
     *
     * Initializes the request object based on the specified input type.
     *
     * @param int $inputType The type of input (GET, POST, COOKIE, ENV, SERVER).
     * @param boolean $forceScalar Flag to get scalar values only.
     */
    public function __construct($inputType = INPUT_GET, $forceScalar = false)
    {
        parent::__construct($forceScalar);
        if ($inputType == INPUT_GET && isset($_GET)) {
            $this->loadData($_GET);
        } elseif ($inputType == INPUT_POST && isset($_POST)) {
            $this->loadData($_POST);
        } elseif ($inputType == INPUT_COOKIE && isset($_COOKIE)) {
            $this->loadData($_COOKIE);
        } elseif ($inputType == INPUT_ENV && isset($_ENV)) {
            $this->loadData($_ENV);
        } elseif ($inputType == INPUT_SERVER && isset($_SERVER)) {
            $this->loadData($_SERVER);
        }
    }

    /**
     * Retrieve the raw request body.
     *
     * @return string The raw body of the request.
     */
    public static function getRequestBody()
    {
        return file_get_contents("php://input");
    }

    /**
     * Retrieve all request headers.
     *
     * @return array An associative array of request headers.
     */
    public static function getRequestHeaders()
    {
        if (!function_exists('getallheaders')) {
            $headers = [];
            foreach ($_SERVER as $name => $value) {
                /* RFC2616 (HTTP/1.1) defines header fields as case-insensitive entities. */
                if (strtolower(substr($name, 0, 5)) == 'http_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
            return $headers;
        } else {
            return getallheaders();
        }
    }

    /**
     * Retrieve a specific request header by its key.
     *
     * @param string $key The key of the header to retrieve.
     * @param array|null $allHeaders Optional array of all headers.
     * @return string|null The value of the header, or null if not found.
     */
    public static function getRequestHeader($key, $allHeaders = null)
    {
        if ($allHeaders === null) {
            $allHeaders = self::getRequestHeaders();
        }
        $key = strtolower($key);

        // Normalize the keys to lowercase
        $keys = array_keys($allHeaders);
        $values = array_values($allHeaders);
        foreach ($keys as $k => $headerKey) {
            $keys[$k] = strtolower($headerKey);
        }
        $allHeaders = array_combine($keys, $values);

        return isset($allHeaders[$key]) ? $allHeaders[$key] : null;
    }
}
