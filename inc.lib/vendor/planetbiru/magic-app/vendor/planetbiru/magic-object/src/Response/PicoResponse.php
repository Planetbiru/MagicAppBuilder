<?php

namespace MagicObject\Response;

use MagicObject\Constants\PicoHttpStatus;
use MagicObject\Constants\PicoMime;
use \stdClass;

/**
 * Response
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoResponse
{
    /**
     * Send response headers and response body to client
     *
     * @param mixed $data Data to sent to client
     * @param boolean $prettify Flag to prettify JSON
     * @param array $headers Response headers
     * @return void
     */
    public static function sendJSON($data, $prettify = false, $headers = null, $httpStatusCode = PicoHttpStatus::HTTP_OK)
    {
        $body = null;
        if ($data != null) {
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
        self::sendResponse($body, PicoMime::APPLICATION_JSON, $headers, $httpStatusCode);
    }

    /**
     * Send response headers and response body to client
     *
     * @param string $body Response body
     * @param string $contentType Content type
     * @param array $headers Response headers
     * @param boolean $async Send response asynchronously
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
     * Send response headers and response body as JSON to client
     *
     * @param stdClass|object|array|string $body Response body
     * @param array $headers Response headers
     * @param boolean $async Send response asynchronously
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
        $contentType = PicoMime::APPLICATION_JSON;
        $contentLength = $bodyToSent == null ? 0 : strlen($bodyToSent);
        $headers = self::getDefaultHeaders($headers, $contentType, $contentLength);
        if ($httpStatusCode != 200) {
            self::sendHttpStatus($httpStatusCode);
        }
        self::sendHeaders($headers);
        self::sendBody($bodyToSent, $async);
    }

    /**
     * Send response headers
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
     * Send response body
     *
     * @param string $body Response body
     * @param boolean $async Send response asynchronously
     * @return void
     */
    public static function sendBody($body, $async = false)
    {
        if ($async) {
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);
            }
            ob_start();

            if ($body != null) {
                echo $body;
            }
        }
        header("Connection: close");

        if ($async) {
            ob_end_flush();
            ob_flush();
            flush();
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
        } else if ($body != null) {
            echo $body;
        }
    }

    /**
     * Get default content type with key given
     *
     * @param string $contentType Content type
     * @return string Fixed content type
     */
    public static function getDefaultContentType($contentType)
    {
        if ($contentType == null) {
            return 'text/html';
        }
        if (stripos($contentType, 'json') !== false) {
            return PicoMime::APPLICATION_JSON;
        }
        return $contentType;
    }

    /**
     * Get default response headers
     *
     * @param array $headers Response headers
     * @param string $contentType Content type
     * @param integer $contentLength Content length
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
     * @param integer $code HTTP status code
     * @param string $text HTTP status text
     * @return integer
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
     * Get HTTP response code
     *
     * @param integer $code HTTP status code
     * @return string
     */
    public static function getHttpResponseCode($code)
    {
        return isset(PicoHttpStatus::$httpStatus[$code]) ? PicoHttpStatus::$httpStatus[$code] : null;
    }

    /**
     * Redirect browser to current URL.
     * WARNING! Use this only if there is a POST input that will control the process to prevent an endless loop that causes damage to the server. Modern browsers may prevent undesirable things from happening but other browsers may not have this feature.
     *
     * @return void
     */
    public function redirectToItself()
    {
        header("Location: ".$_SERVER['REQUEST_URI']);
    }
}
