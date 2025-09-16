<?php

namespace MagicObject\Util;

use CurlHandle;
use MagicObject\Exceptions\CurlException;

/**
 * Class PicoCurlUtil
 *
 * This class provides an interface for making HTTP requests using cURL or PHP streams as a fallback.
 * @author Kamshory
 * @package MagicObject
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoCurlUtil {

    /**
     * cURL handle
     *
     * @var CurlHandle|null
     */
    private $curl;

    /**
     * Flag to indicate if the cURL extension is available
     *
     * @var bool
     */
    private $isCurlAvailable = false;

    /**
     * Response headers from the last request
     *
     * @var string[]
     */
    private $responseHeaders = array();

    /**
     * Response body from the last request
     *
     * @var string
     */
    private $responseBody = '';

    /**
     * HTTP status code from the last request
     *
     * @var int
     */
    private $httpCode = 0;

    /**
     * PicoCurlUtil constructor.
     * Initializes the cURL handle if available, otherwise sets the fallback flag.
     */
    public function __construct() {
        if (extension_loaded('curl')) {
            $this->isCurlAvailable = true;
            $this->curl = curl_init();
            $this->setOption(CURLOPT_RETURNTRANSFER, true);
            $this->setOption(CURLOPT_HEADER, true);
        } else {
            // No cURL extension, will use stream functions as a fallback
            $this->isCurlAvailable = false;
        }
    }

    /**
     * Sets a cURL option.
     *
     * @param int $option cURL option to set
     * @param mixed $value Value for the cURL option
     */
    public function setOption($option, $value) {
        if ($this->isCurlAvailable) {
            curl_setopt($this->curl, $option, $value);
        }
    }

    /**
     * Enables or disables SSL verification.
     *
     * @param bool $verify If true, SSL verification is enabled; if false, it is disabled.
     */
    public function setSslVerification($verify) {
        if ($this->isCurlAvailable) {
            $this->setOption(CURLOPT_SSL_VERIFYPEER, $verify);
            $this->setOption(CURLOPT_SSL_VERIFYHOST, $verify ? 2 : 0);
        }
    }

    /**
     * Executes a GET request.
     *
     * @param string $url URL for the request
     * @param array $headers Additional headers for the request
     * @return string Response body
     * @throws CurlException If an error occurs during execution
     */
    public function get($url, $headers = array()) {
        if ($this->isCurlAvailable) {
            $this->setOption(CURLOPT_URL, $url);
            $this->setOption(CURLOPT_HTTPHEADER, $headers);
            return $this->executeCurl();
        } else {
            return $this->executeStream($url, 'GET', null, $headers);
        }
    }

    /**
     * Executes a POST request.
     *
     * @param string $url URL for the request
     * @param mixed $data Data to send
     * @param array $headers Additional headers for the request
     * @return string Response body
     * @throws CurlException If an error occurs during execution
     */
    public function post($url, $data, $headers = array()) {
        if ($this->isCurlAvailable) {
            $this->setOption(CURLOPT_URL, $url);
            $this->setOption(CURLOPT_POST, true);
            $this->setOption(CURLOPT_POSTFIELDS, $data);
            $this->setOption(CURLOPT_HTTPHEADER, $headers);
            return $this->executeCurl();
        } else {
            return $this->executeStream($url, 'POST', $data, $headers);
        }
    }
    
    // Add other methods (put, delete) with a similar logic

    /**
     * Executes the cURL request and processes the response.
     *
     * @return string Response body
     * @throws CurlException If an error occurs during cURL execution
     */
    private function executeCurl() {
        $response = curl_exec($this->curl);
        if ($response === false) {
            throw new CurlException('Curl error: ' . curl_error($this->curl));
        }

        $headerSize = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        $this->responseHeaders = explode("\r\n", substr($response, 0, $headerSize));
        $this->responseBody = substr($response, $headerSize);
        $this->httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        return $this->responseBody;
    }

    /**
     * Executes the request using PHP streams.
     *
     * @param string $url
     * @param string $method
     * @param mixed $data
     * @param array $headers
     * @return string
     * @throws CurlException
     */
    private function executeStream($url, $method, $data, $headers) {
        $options = [
            'http' => [
                'method' => $method,
                'header' => implode("\r\n", $headers),
                'content' => $data,
                'ignore_errors' => true // To get a response even on 4xx/5xx errors
            ]
        ];
        
        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new CurlException('Stream error: Could not fetch URL');
        }

        // Parse headers and HTTP code
        $this->responseHeaders = $http_response_header;
        $statusLine = $this->responseHeaders[0];
        preg_match('{HTTP\/\S+\s(\d{3})}', $statusLine, $match);
        $this->httpCode = intval($match[1]);

        $this->responseBody = $response;
        return $this->responseBody;
    }
    
    /**
     * Gets the HTTP status code from the last response.
     *
     * @return int HTTP status code
     */
    public function getHttpCode() {
        return $this->httpCode;
    }

    /**
     * Gets the response headers from the last request.
     *
     * @return array Array of response headers
     */
    public function getResponseHeaders() {
        return $this->responseHeaders;
    }
    
    /**
     * Closes the cURL handle.
     */
    public function close() {
        if ($this->isCurlAvailable && is_resource($this->curl)) {
            curl_close($this->curl);
        }
    }

    /**
     * Destructor to close cURL when the object is destroyed.
     */
    public function __destruct() {
        $this->close();
    }
}