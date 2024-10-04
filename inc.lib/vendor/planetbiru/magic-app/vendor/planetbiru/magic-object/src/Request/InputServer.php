<?php

namespace MagicObject\Request;

/**
 * InputServer
 *
 * Available methods:
 * getPhpSelf() return $_SERVER['PHP_SELF']
 * getGatewayInterface() return $_SERVER['GATEWAY_INTERFACE']
 * getServerAddr() return $_SERVER['SERVER_ADDR']
 * getScriptName() return $_SERVER['SERVER_NAME']
 * getServerSoftware() return $_SERVER['SERVER_SOFTWARE']
 * getServerProtocol() return $_SERVER['SERVER_PROTOCOL']
 * getRequestMethod() return $_SERVER['REQUEST_METHOD']
 * getRequestTime() return $_SERVER['REQUEST_TIME']
 * getRequestTimeFloat() return $_SERVER['REQUEST_TIME_FLOAT']
 * getQueryString() return $_SERVER['QUERY_STRING']
 * getDocumentRoot() return $_SERVER['DOCUMENT_ROOT']
 * getHttps() return $_SERVER['HTTPS']
 * getRemoteAddr() return $_SERVER['REMOTE_ADDR']
 * getRemotePort() return $_SERVER['REMOTE_PORT']
 * getRemoteUser() return $_SERVER['REMOTE_USER']
 * getRedirectRemoteUser() return $_SERVER['REDIRECT_REMOTE_USER']
 * getScriptFilename() return $_SERVER['SCRIPT_FILENAME']
 * getServerAdmin() return $_SERVER['SERVER_ADMIN']
 * getServerPort() return $_SERVER['SERVER_PORT']
 * getServerSignature() return $_SERVER['SERVER_SIGNATURE']
 * getPathTranslated() return $_SERVER['PATH_TRANSLATED']
 * getScriptName() return $_SERVER['SCRIPT_NAME']
 * getRequestUri() return $_SERVER['REQUEST_URI']
 * getPhpAuthDigest() return $_SERVER['PHP_AUTH_DIGEST']
 * getPhpAuthUser() return $_SERVER['PHP_AUTH_USER']
 * getPhpAuthPw() return $_SERVER['PHP_AUTH_PW']
 * getAuthType() return $_SERVER['AUTH_TYPE']
 * getPathInfo() return $_SERVER['PATH_INFO']
 * getOrigPathInfo() return $_SERVER['ORIG_PATH_INFO']
 *
 * @link https://www.php.net/manual/en/reserved.variables.server.php
 */
class  InputServer extends PicoRequestBase {
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadData($_SERVER, true);
    }

    /**
     * Get request header with specific key
     *
     * @param string $key Header key
     * @return string
     */
    public function getHeader($key)
    {
        return PicoRequest::getRequestHeader($key);
    }

    /**
     * Parse language
     *
     * @param string $acceptLanguage
     * @return array
     */
    public function parseLanguages($acceptLanguage) {
        $langs = explode(',', $acceptLanguage);
        $languageList = array();
        foreach($langs as $lang) 
        {
            $parts = explode(';q=', $lang);
            $language = $parts[0];
            $quality = isset($parts[1]) ? (float)$parts[1] : 1.0;
            $languageList[$language] = $quality;
        }
        arsort($languageList);
        return $languageList;
    }    

    /**
     * User language
     *
     * @return string
     */
    public function userLanguage($general = false)
    {
        if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
        {
            $languages = self::parseLanguages($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            if(!empty($languages))
            {
                $langs = array_keys($languages);
                if($general)
                {
                    $arr = explode('-', $langs[0]);
                    return $arr[0];
                }
                return $langs[0];
            }
        }
        return null;
    }
}