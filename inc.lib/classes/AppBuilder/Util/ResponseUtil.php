<?php

namespace AppBuilder\Util;

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
     * @param boolean $prettify Flag to determine if the JSON should be prettified (formatted with whitespace).
     *                           Defaults to false.
     * @param boolean $async Flag to indicate if the response should be sent asynchronously.
     *                       Defaults to false. When true, the response is sent without waiting for further processing.
     * @return void
     *
     * @throws \JsonException If encoding the data to JSON fails.
     */
    public static function sendJSON($data, $prettify = false, $async = false)
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
}