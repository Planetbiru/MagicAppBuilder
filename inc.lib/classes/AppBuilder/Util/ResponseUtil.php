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
    public static function sendJSON($data, $prettify = false)
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
        echo $body;
    }
}