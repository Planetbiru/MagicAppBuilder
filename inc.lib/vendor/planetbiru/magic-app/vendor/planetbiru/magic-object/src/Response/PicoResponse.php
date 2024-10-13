<?php

namespace MagicObject\Response;

use MagicObject\Constants\PicoHttpStatus;
use \stdClass;

/**
 * Class PicoResponse
 * Handles sending HTTP responses with various content types and status codes.
 *
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoResponse
{
    const APPLICATION_JSON = "application/json";

    /**
     * Send a JSON response to the client.
     *
     * @param mixed $data Data to send to the client
     * @param bool $prettify Flag to prettify JSON
     * @param array|null $headers Response headers
     * @param int $httpStatusCode HTTP status code (default: 200)
     * @return void
     */
    public static function sendJSON($data, $prettify = false, $headers = null, $httpStatusCode = PicoHttpStatus::HTTP_OK)
    {
        $body = null;
        if ($data !== null) {
            if (is_string($data)) {
                $body = $data;
            } else {
                if ($prettify) {
                    $body = json_encode($data, JSON_PRETTY_PRINT);
                } else {
                    $body = json_encode($data);
                }
            }
        }
        self::sendResponse($body, self::APPLICATION_JSON, $headers, $httpStatusCode);
    }

    /**
     * Send the response headers and body to the client.
     *
     * @param string|null $body Response body
     * @param string|null $contentType Content type
     * @param array|null $headers Response headers
     * @param int $httpStatusCode HTTP status code (default: 200)
     * @param bool $async Send response asynchronously
     * @return void
     */
    public static function sendResponse($body, $contentType = null, $headers = null, $httpStatusCode = PicoHttpStatus::HTTP_OK, $async = false)
    {
        $contentLength = $body == null ? 0 : strlen($body);
        $headers = self::getDefaultHeaders($headers, $contentType, $contentLength);
        if ($httpStatusCode != 200) {
            self::sendHttpStatus($httpStatusCode);
        }
        self::sendHeaders($headers);
        self::sendBody($body, $async);
    }

    /**
     * Send response as JSON to the client.
     *
     * @param stdClass|object|array|string|null $body Response body
     * @param array|null $headers Response headers
     * @param int $httpStatusCode HTTP status code (default: 200)
     * @param bool $async Send response asynchronously
     * @return void
     */
    public static function sendResponseJSON($body, $headers = null, $httpStatusCode = PicoHttpStatus::HTTP_OK, $async = false)
    {
        if($body == null)
        {
            $bodyToSent = null;
        }
        else
        {
            if(is_string($body))
            {
                $bodyToSent = $body;
            }
            else if(is_array($body))
            {
                $bodyToSent = json_encode($body);
            }
            else if(is_object($body))
            {
                if($body instanceof stdClass)
                {
                    $bodyToSent = json_encode($body);
                }
                else
                {
                    // force convert to string with __toString() method if exists
                    $bodyToSent = $body."";
                }
            }
            else
            {
                $bodyToSent = null;
            }
        }
        $contentType = self::APPLICATION_JSON;
        $contentLength = $bodyToSent == null ? 0 : strlen($bodyToSent);
        $headers = self::getDefaultHeaders($headers, $contentType, $contentLength);
        if ($httpStatusCode != 200) {
            self::sendHttpStatus($httpStatusCode);
        }
        self::sendHeaders($headers);
        self::sendBody($bodyToSent, $async);
    }

    /**
     * Send response headers to the client.
     *
     * @param array $headers Response headers
     * @return void
     */
    public static function sendHeaders($headers)
    {
        if ($headers != null && is_array($headers)) {
            foreach ($headers as $key => $value) {
                header($key . ": " . $value);
            }
        }
    }

    /**
     * Send response body to the client.
     *
     * @param string|null $body Response body
     * @param bool $async Send response asynchronously
     * @return void
     */
    public static function sendBody($body, $async = false)
    {
        if ($async) {
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);
            }
            ob_start(); // Mulai output buffering
        }

        // Jika body tidak null, kirimkan
        if ($body !== null) {
            echo $body; // Tampilkan body
        }

        // Mengatur header koneksi
        header("Connection: close");

        // Jika dalam mode asinkron, lakukan flush
        if ($async) {
            ob_end_flush(); // Selesaikan buffer
            header("Content-Length: " . strlen($body)); // Tentukan panjang konten
            flush(); // Kirim output ke klien
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request(); // Selesaikan permintaan FastCGI
            }
        } else {
            // Jika tidak asinkron, atur Content-Length
            if ($body !== null) {
                header("Content-Length: " . strlen($body)); // Tentukan panjang konten
                echo $body; // Tampilkan body
            }
        }
    }

    /**
     * Get default content type based on the provided content type.
     *
     * @param string|null $contentType Content type
     * @return string Fixed content type
     */
    public static function getDefaultContentType($contentType)
    {
        if ($contentType == null) {
            return 'text/html';
        }
        if (stripos($contentType, 'json') !== false) {
            return self::APPLICATION_JSON;
        }
        return $contentType;
    }

    /**
     * Get default response headers.
     *
     * @param array|null $headers Response headers
     * @param string|null $contentType Content type
     * @param int $contentLength Content length
     * @return array Fixed response headers
     */
    public static function getDefaultHeaders($headers, $contentType, $contentLength = 0)
    {
        if ($headers == null) {
            $headers = array();
        }
        $headers['Content-type'] = $contentType;
        if ($contentLength != 0) {
            $headers['Content-length'] = $contentLength;
        }
        return $headers;
    }

    /**
     * Send http status
     *
     * @param int $code HTTP status code
     * @param string $text HTTP status text
     * @return int
     */
    public static function sendHttpStatus($code = 0, $text = null)
    {
        if ($text == null) {
            if (function_exists('http_response_code')) {
                $text = http_response_code($code);
            } else {
                $text = self::getHttpResponseCode($code);
            }
        }
        $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
        if ($text !== null) {
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . $code . ' ' . $text);
            $GLOBALS['http_response_code'] = $code;
        } else {
            $text = 'Unknown http status code "' . htmlentities($code) . '"';
            header($protocol . ' ' . $code . ' ' . $text);
        }
        return $code;
    }

    /**
     * Get HTTP response code text.
     *
     * @param int $code HTTP status code
     * @return string|null
     */
    public static function getHttpResponseCode($code)
    {
        return isset(PicoHttpStatus::$httpStatus[$code]) ? PicoHttpStatus::$httpStatus[$code] : null;
    }

    /**
     * Redirect the browser to the current URL.
     * WARNING! Use this only if there is a POST input that will control the process to prevent an endless loop that causes damage to the server. Modern browsers may prevent undesirable things from happening but other browsers may not have this feature.
     *
     * @return void
     */
    public static function redirectToItself()
    {
        header("Location: ".$_SERVER['REQUEST_URI']);
        exit(); // Ensures no further code execution after redirection
    }
}
