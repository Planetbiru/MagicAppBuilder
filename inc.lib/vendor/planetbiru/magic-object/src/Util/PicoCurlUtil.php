<?php

namespace MagicObject\Util;

use CurlHandle;
use MagicObject\Exceptions\CurlException;

/**
 * Class PicoCurlUtil
 *
 * This class provides an interface for making HTTP requests using cURL.
 */
class PicoCurlUtil {

    /**
     * cURL handle
     *
     * @var CurlHandle
     */
    private $curl;

    /**
     * Response headers from the last request
     *
     * @var string[]
     */
    private $responseHeaders = [];

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
     * Initializes the cURL handle.
     */
    public function __construct() {
        $this->curl = curl_init();
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_HEADER, true);
    }

    /**
     * Sets a cURL option.
     *
     * @param int $option cURL option to set
     * @param mixed $value Value for the cURL option
     */
    public function setOption($option, $value) {
        curl_setopt($this->curl, $option, $value);
    }

    /**
     * Enables or disables SSL verification.
     *
     * @param bool $verify If true, SSL verification is enabled; if false, it is disabled.
     */
    public function setSslVerification($verify) {
        $this->setOption(CURLOPT_SSL_VERIFYPEER, $verify);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, $verify ? 2 : 0);
    }

    /**
     * Executes a GET request.
     *
     * @param string $url URL for the request
     * @param array $headers Additional headers for the request
     * @return string Response body
     * @throws CurlException If an error occurs during cURL execution
     */
    public function get($url, $headers = []) {
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this->execute();
    }

    /**
     * Executes a POST request.
     *
     * @param string $url URL for the request
     * @param mixed $data Data to send
     * @param array $headers Additional headers for the request
     * @return string Response body
     * @throws CurlException If an error occurs during cURL execution
     */
    public function post($url, $data, $headers = []) {
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_POST, true);
        $this->setOption(CURLOPT_POSTFIELDS, $data);
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this->execute();
    }

    /**
     * Executes a PUT request.
     *
     * @param string $url URL for the request
     * @param mixed $data Data to send
     * @param array $headers Additional headers for the request
     * @return string Response body
     * @throws CurlException If an error occurs during cURL execution
     */
    public function put($url, $data, $headers = []) {
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_CUSTOMREQUEST, "PUT");
        $this->setOption(CURLOPT_POSTFIELDS, $data);
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this->execute();
    }

    /**
     * Executes a DELETE request.
     *
     * @param string $url URL for the request
     * @param array $headers Additional headers for the request
     * @return string Response body
     * @throws CurlException If an error occurs during cURL execution
     */
    public function delete($url, $headers = []) {
        $this->setOption(CURLOPT_URL, $url);
        $this->setOption(CURLOPT_CUSTOMREQUEST, "DELETE");
        $this->setOption(CURLOPT_HTTPHEADER, $headers);
        return $this->execute();
    }

    /**
     * Executes the cURL request and processes the response.
     *
     * @return string Response body
     * @throws CurlException If an error occurs during cURL execution
     */
    private function execute() {
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
        curl_close($this->curl);
    }

    /**
     * Destructor to close cURL when the object is destroyed.
     */
    public function __destruct() {
        $this->close();
    }
}
