<?php

namespace MagicObject\Session;

use MagicObject\SecretObject;
use stdClass;

/**
 * Class PicoSession
 * This class manages session handling, providing methods to configure and manipulate sessions.
 * 
 * The class provides an interface for session management, including handling session creation, destruction,
 * configuration, and the ability to store/retrieve session data.
 * 
 * @author Kamshory
 * @package MagicObject\Session
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSession // NOSONAR
{
    const SESSION_STARTED = true;
    const SESSION_NOT_STARTED = false;
    const SAME_SITE_LAX = "Lax";
    const SAME_SITE_STRICT = "Strict";
    const SAME_SITE_NONE = "None";

    /**
     * The state of the session.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var bool
     */
    protected $_sessionState = self::SESSION_NOT_STARTED; // NOSONAR

    /**
     * The instance of the object.
     * 
     * The property name starts with an underscore to prevent child classes 
     * from overriding its value.
     *
     * @var self
     */
    protected static $_instance; // NOSONAR


    /**
     * Constructor to initialize session configuration.
     *
     * This constructor accepts a session configuration object and applies settings such as
     * session name, max lifetime, and save handler (Redis or file system).
     *
     * @param SecretObject|null $sessConf Configuration for the session.
     */
    public function __construct($sessConf = null)
    {
        if(isset($sessConf))
        {
            if ($sessConf->getName() != "") {
                $this->setSessionName($sessConf->getName());
            }
            if ($sessConf->getMaxLifetime() > 0) {
                $this->setSessionMaxLifetime($sessConf->getMaxLifetime());
            }
            if ($sessConf->getSaveHandler() == "redis") {
                $redisParams = $this->getRedisParams($sessConf);
                $this->saveToRedis($redisParams->host, $redisParams->port, $redisParams->auth, $redisParams->db);
            } elseif ($sessConf->getSaveHandler() == "files" && $sessConf->getSavePath() != "") {
                $this->saveToFiles($sessConf->getSavePath());
            } elseif ($sessConf->getSaveHandler() == "sqlite" && $sessConf->getSavePath() != "") {
                $handler = new SqliteSessionHandler($sessConf->getSavePath());
                session_set_save_handler(
                    [$handler, 'open'],
                    [$handler, 'close'],
                    [$handler, 'read'],
                    [$handler, 'write'],
                    [$handler, 'destroy'],
                    [$handler, 'gc']
                );
                register_shutdown_function('session_write_close');
            }
        }
    }

    /**
     * Extracts Redis connection parameters from a session configuration object.
     *
     * Parses the Redis `save_path` (in URL format) from the given SecretObject instance
     * and returns a stdClass object containing the Redis host, port, and optional authentication.
     *
     * Example save path formats:
     * - tcp://127.0.0.1:6379
     * - tcp://[::1]:6379
     * - tcp://localhost:6379?auth=yourpassword
     *
     * @param SecretObject $sessConf Session configuration object containing the Redis save path.
     * @return stdClass An object with the properties: `host` (string), `port` (int), and `auth` (string|null).
     */
    private function getRedisParams($sessConf) // NOSONAR
    {
        $path = $sessConf->getSavePath();

        // Ensure the URI has a scheme so parse_url works properly
        if (strpos($path, '://') === false) {
            $path = 'tcp://' . $path;
        }

        // Special handling for "::1" without brackets and port (common IPv6 localhost)
        if (strpos($path, '://::1:') !== false && substr_count($path, ':') == 4) {
            $path = str_replace('://::1:', '://[::1]:', $path);
        }

        // Wrap unbracketed IPv6 addresses with []
        // This regex captures tcp:// followed by IPv6 without brackets
        $path = preg_replace_callback(
            '#tcp://([a-fA-F0-9:]+)(:[0-9]+)?#', // NOSONAR
            function ($matches) {
                $host = $matches[1];
                $port = isset($matches[2]) ? $matches[2] : '';
                // Add brackets if host contains ':' and is not already bracketed
                if (strpos($host, ':') !== false && strpos($host, '[') === false) {
                    return 'tcp://[' . $host . ']' . $port;
                }
                return $matches[0];
            },
            $path
        );

        $parsed = parse_url($path);

        $host = isset($parsed['host']) ? $parsed['host'] : '';
        $port = isset($parsed['port']) ? $parsed['port'] : 0;
        $auth = null;
        $db   = 0;   // default Redis DB
        $opts = [];

        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $parsedStr);

            if (isset($parsedStr['auth'])) {
                $auth = $parsedStr['auth'];
            } elseif (isset($parsedStr['password'])) {
                $auth = $parsedStr['password'];
            }
            if (isset($parsedStr['db'])) {
                $db = (int)$parsedStr['db'];
            } elseif (isset($parsedStr['dbindex'])) {
                $db = (int)$parsedStr['dbindex'];
            } elseif (isset($parsedStr['database'])) {
                $db = (int)$parsedStr['database'];
            }

            $opts = $parsedStr;
        }

        if (!empty($host) && $port == 0) {
            $port = 6379;
        }

        $params = new stdClass;
        $params->host = $host;
        $params->port = $port;
        $params->auth = $auth;
        $params->db   = $db;
        $params->options = $opts;

        return $params;
    }

    /**
     * Returns the instance of PicoSession.
     * The session is automatically initialized if it wasn't already.
     *
     * This method ensures that only one instance of PicoSession is created (Singleton pattern).
     *
     * @param string|null $name Session name.
     * @param int $maxLifetime Maximum lifetime of the session.
     * @return self The instance of PicoSession.
     */
    public static function getInstance($name = null, $maxLifetime = 0)
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
            if (isset($name)) {
                self::$_instance->setSessionName($name);
            }
            if ($maxLifetime > 0) {
                self::$_instance->setSessionMaxLifetime($maxLifetime);
            }
        }
        self::$_instance->startSession();
        return self::$_instance;
    }

    /**
     * (Re)starts the session and update cookie.
     *
     * This method starts a session if it hasn't been started already.
     *
     * @return bool true if the session has been initialized, false otherwise.
     */
    public function startSession()
    {
        if ($this->_sessionState == self::SESSION_NOT_STARTED) {
            $this->_sessionState = @session_start();
            $this->refreshSessionCookie();
        }
        return $this->_sessionState;
    }

    /**
     * Checks if the session has been started.
     *
     * This method checks whether the current session has been started.
     *
     * @return bool true if the session has started, false otherwise.
     */
    public function isSessionStarted()
    {
        return $this->_sessionState;
    }

    /**
     * Stores data in the session.
     * 
     * **Example:** 
     * ```php
     * <?php
     * $sessions->foo = 'bar';
     * ```
     *
     * This magic method stores data in the PHP session.
     *
     * @param string $name Name of the data.
     * @param mixed $value The data to store.
     * @return void
     */
    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Retrieves data from the session.
     * 
     * **Example:** 
     * ```php
     * <?php
     * echo $sessions->foo;
     * ```
     *
     * This magic method retrieves data from the PHP session.
     *
     * @param string $name Name of the data to retrieve.
     * @return mixed The data stored in session, or null if not set.
     */
    public function __get($name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    /**
     * Checks if a value is set in the session.
     *
     * This magic method checks whether a value is set in the session.
     *
     * @param string $name Name of the data to check.
     * @return bool true if the data is set, false otherwise.
     */
    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Unsets a value in the session.
     *
     * This magic method unsets data in the session.
     *
     * @param string $name Name of the data to unset.
     * @return void
     */
    public function __unset($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * Destroys the current session.
     *
     * This method destroys the session and clears all session data.
     *
     * @return bool true if the session has been deleted, else false.
     */
    public function destroy()
    {
        if ($this->_sessionState == self::SESSION_STARTED) {
            $this->_sessionState = !session_destroy();
            unset($_SESSION);
            return !$this->_sessionState;
        }
        return false;
    }

    /**
     * Sets the session cookie parameters, including lifetime, path, domain, security attributes, and SameSite settings.
     *
     * This method configures the session cookie parameters such as maximum lifetime, path, domain, and security settings
     * like whether the cookie should be accessible only over HTTPS or only via HTTP. It also sets the SameSite attribute
     * for compatibility with different browsers and PHP versions.
     *
     * @param int $maxlifetime Maximum lifetime of the session cookie in seconds.
     * @param string $path The path where the cookie will be available on the server.
     * @param string $domain The domain to which the cookie is available.
     * @param bool $secure Indicates if the cookie should only be transmitted over a secure HTTPS connection.
     * @param bool $httponly Indicates if the cookie should be accessible only through the HTTP protocol.
     * @param string $samesite The SameSite attribute of the cookie (Lax, Strict, None). Default is 'Strict'.
     * @return self Returns the current instance for method chaining.
     */
    public function setSessionCookieParams($maxlifetime, $path, $domain, $secure, $httponly, $samesite = self::SAME_SITE_STRICT)
    {
        if (PHP_VERSION_ID < 70300) {
            session_set_cookie_params($maxlifetime, $path.'; samesite=' . $samesite, $domain, $secure, $httponly);
        } else {
            session_set_cookie_params(array(
                'lifetime' => $maxlifetime,
                'path' => $path,
                'domain' => $domain,
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ));
        }
        return $this;
    }

    /**
     * Sets a cookie with the SameSite attribute, supporting both older and newer PHP versions.
     *
     * This method sets a cookie with a specified SameSite attribute, ensuring compatibility with both PHP versions
     * prior to and after PHP 7.3. It supports cookies with the 'Lax', 'Strict', or 'None' SameSite attributes.
     *
     * @param string $name The name of the cookie.
     * @param string $value The value of the cookie.
     * @param int $expire The expiration time of the cookie as a Unix timestamp.
     * @param string $path The path on the server where the cookie is available.
     * @param string $domain The domain to which the cookie is available.
     * @param bool $secure Indicates if the cookie should only be transmitted over a secure HTTPS connection.
     * @param bool $httponly Indicates if the cookie is accessible only through the HTTP protocol.
     * @param string $samesite The SameSite attribute of the cookie (Lax, Strict, None). Default is 'Strict'.
     * @return self Returns the current instance for method chaining.
     */
    public function setSessionCookieSameSite($name, $value, $expire, $path, $domain, $secure, $httponly, $samesite = self::SAME_SITE_STRICT) // NOSONAR
    {
        if (PHP_VERSION_ID < 70300) {
            setcookie($name, $value, $expire, $path . '; samesite=' . $samesite, $domain, $secure, $httponly);
        } else {
            setcookie($name, $value, array(
                'expires' => $expire,
                'path' => $path,
                'domain' => $domain,
                'samesite' => $samesite,
                'secure' => $secure,
                'httponly' => $httponly,
            ));
        }
        return $this;
    }

    /**
     * Sets the session name.
     *
     * This method sets the session name for the current session.
     *
     * @param string $name The name of the session.
     * @return self Returns the current instance for method chaining.
     */
    public function setSessionName($name)
    {
        session_name($name);
        return $this;
    }

    /**
     * Sets the session save path.
     *
     * This method sets the path where session files will be stored.
     *
     * @param string $path The session save path.
     * @return string|false The session save path on success, false on failure.
     */
    public function setSessionSavePath($path)
    {
        return session_save_path($path);
    }

    /**
     * Sets the maximum lifetime for the session.
     *
     * This method sets the maximum lifetime of the session, affecting both garbage collection and cookie expiration.
     *
     * @param int $lifeTime Maximum lifetime for the session in seconds.
     * @return self Returns the current instance for method chaining.
     */
    public function setSessionMaxLifetime($lifeTime)
    {
        ini_set("session.gc_maxlifetime", $lifeTime);
        ini_set("session.cookie_lifetime", $lifeTime);
        return $this;
    }

    /**
     * Configures PHP session storage to use Redis.
     *
     * This method sets the session handler and save path so that session data
     * is stored in a Redis server. It supports optional authentication.
     *
     * @param string      $host Redis server hostname or IP address.
     * @param int         $port Redis server port number.
     * @param string|null $auth Optional authentication password for Redis.
     * @return self Returns the current instance to allow method chaining.
     */
    public function saveToRedis($host, $port, $auth = null, $db = null)
    {
        $params = array();
        if(isset($db) && is_int($db))
        {
            $params['database'] = (int)$db;
        }
        if(isset($auth) && !empty($auth))
        {
            $params['auth'] = $auth;
        }
        $path = sprintf("tcp://%s:%d", $host, $port);
        if(!empty($params))
        {
            $path .= "?" . $this->httpBuildQuery($params);
        }
        ini_set("session.save_handler", "redis");
        ini_set("session.save_path", $path);
        return $this;
    }

    /**
     * Builds a URL-encoded query string from an associative array.
     *
     * This method constructs a query string suitable for use in URLs or HTTP requests
     * by encoding the keys and values of the provided associative array.
     *
     * @param array $params An associative array of key-value pairs to be converted into a query string.
     * @return string A URL-encoded query string.
     */
    private function httpBuildQuery($params)
    {
        $pairs = [];
        foreach ($params as $key => $value) {
            $pairs[] = urlencode($key) . '=' . urlencode($value);
        }
        return implode('&', $pairs);
    }

    /**
     * Saves the session to files.
     *
     * This method configures the session to be stored in files.
     *
     * @param string $path The directory where session files will be stored.
     * @return self Returns the current instance for method chaining.
     */
    public function saveToFiles($path)
    {
        ini_set("session.save_handler", "files");
        ini_set("session.save_path", $path);
        return $this;
    }

    /**
     * Retrieves the current session ID.
     *
     * This method retrieves the current session ID, if any.
     *
     * @return string The current session ID.
     */
    public function getSessionId()
    {
        return @session_id();
    }

    /**
     * Sets a new session ID.
     *
     * This method sets a new session ID for the current session.
     *
     * @param string $id The new session ID.
     * @return self Returns the current instance for method chaining.
     */
    public function setSessionId($id)
    {
        @session_id($id);
        return $this;
    }

    /**
     * Refreshes the session cookie to extend its lifetime.
     *
     * Call this to implement sliding expiration (extend session expiry each request).
     *
     * @return void
     */
    public function refreshSessionCookie()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $params = session_get_cookie_params();
            
            $name  = session_name();
            $value = session_id();
            $path  = isset($params['path']) ? $params['path'] : '/';
            $secure = $params['secure'] ? true : false;
            $httponly = $params['httponly'] ? true : false;

            if (PHP_VERSION_ID >= 70300) {
                $options = [
                    'path'     => $path,
                    'secure'   => $secure,
                    'httponly' => $httponly,
                    'samesite' => isset($params['samesite']) ? $params['samesite'] : self::SAME_SITE_STRICT
                ];

                // hanya tambahkan expires kalau lifetime > 0
                if ($params['lifetime'] > 0) {
                    $options['expires'] = time() + $params['lifetime'];
                }

                // hanya tambahkan domain kalau tidak kosong
                if (!empty($params['domain'])) {
                    $options['domain'] = $params['domain'];
                }

                setcookie($name, $value, $options);

            } else {
                $expires = $params['lifetime'] > 0 ? time() + $params['lifetime'] : 0;
                setcookie($name, $value, $expires, $path, !empty($params['domain']) ? $params['domain'] : '', $secure, $httponly);
            }
        }
    }

    /**
     * Converts the session data to a string representation.
     *
     * This method returns the session data as a JSON-encoded string, which can be useful for debugging
     * or logging the contents of the session. It encodes the global `$_SESSION` array into a JSON string.
     *
     * @return string The JSON-encoded string representation of the session data.
     */
    public function __toString()
    {
        return json_encode($_SESSION, JSON_PRETTY_PRINT);
    }

}
