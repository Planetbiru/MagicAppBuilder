<?php

namespace AppBuilder\Util;

use JsonException;

/**
 * Class ResponseUtil
 *
 * A utility class for sending JSON responses to clients in a web application.
 * This class provides methods to encode data into JSON format, set the appropriate
 * HTTP headers, and handle both synchronous and asynchronous responses.
 *
 * It includes options for prettifying the JSON output for better readability
 * and ensures that the response is properly formatted as UTF-8.
 */
class ResponseUtil
{
    /**
     * Send a JSON response to the client.
     *
     * This method encodes the provided data into JSON format and sends it
     * to the client with the appropriate headers. Optionally, the JSON can be
     * prettified for easier readability. The response can also be sent
     * asynchronously.
     *
     * @param mixed $data Data to be encoded as JSON. Can be an array, object, or string.
     * @param bool $prettify Flag to determine if the JSON should be prettified (formatted with whitespace).
     *                       Defaults to false.
     * @param bool $async Flag to indicate if the response should be sent asynchronously.
     *                    Defaults to false. When true, the response is sent without waiting for further processing.
     * @return void
     *
     * @throws JsonException If encoding the data to JSON fails.
     */
    public static function sendJSON($data, $prettify = false, $async = false)
    {
        $body = self::getBody($data, $prettify);
        header("Content-type: application/json");
        if ($async) {
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);
            }
            ob_start();
            if ($body !== null) {
                echo $body;
            }
        }
        header("Connection: close");
        if ($async) {
            if ($body !== null) {
                ob_end_flush();
                header("Content-Length: " . strlen($body));
                ob_flush();
                flush();
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
            }
        } else if ($body !== null) {
            header("Content-Length: " . strlen($body));
            
            echo $body;
        }
    }
    
    /**
     * Encodes the provided data into JSON format.
     *
     * This method converts the input data into a JSON string. If the data
     * is a string, it returns it directly. If the data is an array or object,
     * it uses json_encode to convert it to JSON. Optionally, the JSON can be
     * prettified for better readability.
     *
     * @param mixed $data Data to be encoded as JSON. Can be an array, object, or string.
     * @param bool $prettify Flag to determine if the JSON should be prettified (formatted with whitespace).
     *                       Defaults to false.
     * @return string|null Encoded JSON string or null if no data is provided.
     */
    private static function getBody($data, $prettify = false)
    {
        // Jika $data adalah null, kembalikan string kosong
        if ($data === null) {
            return '';
        }

        // Jika $data adalah string, kembalikan langsung
        if (is_string($data)) {
            return $data;
        }

        // Jika $data adalah array atau object, encode sebagai JSON
        $options = $prettify ? JSON_PRETTY_PRINT : 0;
        return json_encode($data, $options);
    }

}