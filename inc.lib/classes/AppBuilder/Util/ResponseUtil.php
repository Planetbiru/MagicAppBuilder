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


    /**
     * Sends an HTTP response to the client, optionally asynchronously.
     *
     * This function is responsible for sending the appropriate headers, status codes, and content to the client. 
     * It can handle both synchronous and asynchronous responses based on the value of the `$async` parameter. 
     * If asynchronous mode is enabled, the function will immediately return control to the client while the server continues to process in the background.
     *
     * @param string|null $response The content to send as the body of the response, typically in JSON or HTML format. If null, no body will be sent.
     * @param string|null $contentType The MIME type for the content (e.g., 'application/json'). If null, the default type will be used.
     * @param array|null $headers An associative array of headers to be sent with the response. Each header should be in the format 'Header-Name' => 'Header-Value'.
     * @param int|null $httpStatus The HTTP status code (e.g., 200 for OK, 404 for Not Found). If null, the status code will not be set.
     * @param bool $async Indicates whether the response should be sent asynchronously. If true, the response is sent in the background and the script continues execution.
     */
    public static function sendResponse($response, $contentType, $headers, $httpStatus, $async = false)
    {
        // If the response should be asynchronous
        if ($async) {
            // Ensure the client does not wait for the request to finish processing
            if (function_exists('ignore_user_abort')) {
                ignore_user_abort(true);  // Ignore if the user closes the connection
            }

            // Start output buffering to send the response at a later time
            ob_start();

            // Output the response content if it is provided
            if ($response != null) {
                echo $response;
            }
        }

        // Set the HTTP status code if provided
        if ($httpStatus) {
            http_response_code($httpStatus);  // Set the status code for the response
        }

        // Send custom headers if provided
        if ($headers !== null) {
            foreach ($headers as $key => $value) {
                header("$key: $value");  // Send each header
            }
        }

        // Set the content-type header if specified
        if (isset($contentType)) {
            header("Content-type: " . trim($contentType));  // Set the content type (e.g., 'application/json')
        }

        // If response content is provided, set the Content-Length header
        if ($response != null) {
            header("Content-length: " . strlen($response));  // Indicate the length of the response content
        }

        // Close the connection immediately (for asynchronous responses)
        header("Connection: close");

        // If asynchronous, end output buffering and flush the response to the client
        if ($async) {
            ob_end_flush();  // End the output buffering and flush it to the client
            ob_flush();      // Ensure all output is sent to the client
            flush();         // Send all buffered output to the client

            // If FastCGI is available, call fastcgi_finish_request() to terminate the request early
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();  // Finish the request and process asynchronously
            }
        } else {
            // If not asynchronous, just send the response normally
            echo $response;
        }
    }

}