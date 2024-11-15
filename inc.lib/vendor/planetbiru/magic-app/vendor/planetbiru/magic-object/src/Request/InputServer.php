<?php

namespace MagicObject\Request;

/**
 * Class for handling server input data.
 *
 * This class provides methods to retrieve various server-related information
 * from the PHP $_SERVER superglobal array, such as request method, server
 * address, and script name.
 *
 * Available methods:
 * - `getPhpSelf()` returns `$_SERVER['PHP_SELF']`
 * - `getGatewayInterface()` returns `$_SERVER['GATEWAY_INTERFACE']`
 * - `getServerAddr()` returns `$_SERVER['SERVER_ADDR']`
 * - `getScriptName()` returns `$_SERVER['SCRIPT_NAME']`
 * - `getServerSoftware()` returns `$_SERVER['SERVER_SOFTWARE']`
 * - `getServerProtocol()` returns `$_SERVER['SERVER_PROTOCOL']`
 * - `getRequestMethod()` returns `$_SERVER['REQUEST_METHOD']`
 * - `getRequestTime()` returns `$_SERVER['REQUEST_TIME']`
 * - `getRequestTimeFloat()` returns `$_SERVER['REQUEST_TIME_FLOAT']`
 * - `getQueryString()` returns `$_SERVER['QUERY_STRING']`
 * - `getDocumentRoot()` returns `$_SERVER['DOCUMENT_ROOT']`
 * - `getHttps()` returns `$_SERVER['HTTPS']`
 * - `getRemoteAddr()` returns `$_SERVER['REMOTE_ADDR']`
 * - `getRemotePort()` returns `$_SERVER['REMOTE_PORT']`
 * - `getRemoteUser()` returns `$_SERVER['REMOTE_USER']`
 * - `getRedirectRemoteUser()` returns `$_SERVER['REDIRECT_REMOTE_USER']`
 * - `getScriptFilename()` returns `$_SERVER['SCRIPT_FILENAME']`
 * - `getServerAdmin()` returns `$_SERVER['SERVER_ADMIN']`
 * - `getServerPort()` returns `$_SERVER['SERVER_PORT']`
 * - `getServerSignature()` returns `$_SERVER['SERVER_SIGNATURE']`
 * - `getPathTranslated()` returns `$_SERVER['PATH_TRANSLATED']`
 * - `getRequestUri()` returns `$_SERVER['REQUEST_URI']`
 * - `getPhpAuthDigest()` returns `$_SERVER['PHP_AUTH_DIGEST']`
 * - `getPhpAuthUser()` returns `$_SERVER['PHP_AUTH_USER']`
 * - `getPhpAuthPw()` returns `$_SERVER['PHP_AUTH_PW']`
 * - `getAuthType()` returns `$_SERVER['AUTH_TYPE']`
 * - `getPathInfo()` returns `$_SERVER['PATH_INFO']`
 * - `getOrigPathInfo()` returns `$_SERVER['ORIG_PATH_INFO']`
 *
 * @author Kamshory
 * @package MagicObject\Request
 * @link https://www.php.net/manual/en/reserved.variables.server.php
 */
class InputServer extends PicoRequestBase {
    
    /**
     * Constructor for the InputServer class.
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadData($_SERVER, true);
    }

    /**
     * Get a specific request header by key.
     *
     * @param string $key Header key to retrieve.
     * @return string|null Returns the header value or null if not found.
     */
    public function getHeader($key)
    {
        return PicoRequest::getRequestHeader($key);
    }

    /**
     * Parse the 'Accept-Language' HTTP header.
     *
     * @param string $acceptLanguage The 'Accept-Language' header value.
     * @return array An associative array of languages with their quality values.
     */
    public function parseLanguages($acceptLanguage) {
        $langs = explode(',', $acceptLanguage);
        $languageList = array();
        foreach ($langs as $lang) {
            $parts = explode(';q=', $lang);
            $language = $parts[0];
            $quality = isset($parts[1]) ? (float)$parts[1] : 1.0;
            $languageList[$language] = $quality;
        }
        arsort($languageList);
        return $languageList;
    }

    /**
     * Get the user's preferred language.
     *
     * @param bool $general Flag to return the general language (default is false).
     * @return string|null Returns the user's preferred language or null if not set.
     */
    public function userLanguage($general = false)
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $languages = self::parseLanguages($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if (!empty($languages)) {
                $langs = array_keys($languages);
                if ($general) {
                    $arr = explode('-', $langs[0]);
                    return $arr[0];
                }
                return $langs[0];
            }
        }
        return null;
    }
}
