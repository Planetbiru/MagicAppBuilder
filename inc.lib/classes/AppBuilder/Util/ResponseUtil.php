<?php

namespace AppBuilder\Util;

class ResponseUtil
{
    /**
     * Send JSON
     *
     * @param mixed $data
     * @param boolean $prettify
     * @return void
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
}