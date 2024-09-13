<?php

namespace MagicObject\Session;

use MagicObject\SecretObject;

/**
 * Session
 * @link https://github.com/Planetbiru/MagicObject
 */
class PicoSession
{
    const SESSION_STARTED = true;
    const SESSION_NOT_STARTED = false;
    const SAME_SITE_LAX = "Lax";
    const SAME_SITE_STRICT = "Strict";
    const SAME_SIZE_NONE = "None";

    /**
     * The state of the session
     *
     * @var boolean
     */
    private $_sessionState = self::SESSION_NOT_STARTED; //NOSONAR

    /**
     * The instance of the object
     *
     * @var self
     */
    private static $_instance; //NOSONAR


    /**
     * Use this constructor if you want set other parameter before start sessiion
     * @param SecretObject $sessConf
     */
    public function __construct($sessConf = null)
    {
        if($sessConf->getName() != "")
        {
            $this->setSessionName($sessConf->getName());
        }
        if($sessConf->getMaxLifeTime() > 0)
        {
            $this->setSessionMaxLifeTime($sessConf->getMaxLifeTime());
        }
        if($sessConf->getSaveHandler() == "redis")
        {
            $path = $sessConf->getSaveHandler();
            $parsed = parse_url($path);
            parse_str($parsed['query'], $parsedStr);
            $this->saveToRedis($parsed['host'], $parsed['port'], $parsedStr['auth']);
        }
        else if($sessConf->getSaveHandler() == "files" && $sessConf->getSavePath() != "")
        {
            $this->saveToFiles($sessConf->getSavePath());
        }
    }

    /**
     * Returns the instance of 'PicoSession'.
     * The session is automatically initialized if it wasn't.
     *
     * @param string $name
     * @param integer $maxLifeTime
     * @return self
     **/
    public static function getInstance($name = null, $maxLifeTime = 0)
    {
        if (!isset(self::$_instance))
        {
            self::$_instance = new self;
            if(isset($name))
            {
                self::$_instance->setSessionName($name);
            }
            if($maxLifeTime > 0)
            {
                self::$_instance->setSessionMaxLifeTime($maxLifeTime);
            }
        }
        self::$_instance->startSession();
        return self::$_instance;
    }


    /**
     * (Re)starts the session.
     *
     * @return boolean true if the session has been initialized, else false.
     **/
    public function startSession()
    {
        if ($this->_sessionState == self::SESSION_NOT_STARTED)
        {
            $this->_sessionState = session_start();
        }
        $this->sessionStarted = true;
        return $this->_sessionState;
    }

    /**
     * Check if session has been started or not
     *
     * @return boolean
     */
    public function isSessionStarted()
    {
        return $this->_sessionState;
    }

    /**
     * Stores datas in the session.
     * Example: $_instance->foo = 'bar';
     *
     * @param string $name Name of the datas.
     * @param string $value Your datas.
     * @return void
     **/
    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Gets datas from the session.
     * Example: echo $_instance->foo;
     *
     * @param string $name Name of the datas to get.
     * @return mixed Datas stored in session.
     **/
    public function __get($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
    }

    /**
     * Check if value is set or not
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }

    /**
     * Unset value
     *
     * @param string $name
     */
    public function __unset($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * Destroys the current session.
     *
     * @return boolean true is session has been deleted, else false.
     **/
    public function destroy()
    {
        if ($this->_sessionState == self::SESSION_STARTED)
        {
            $this->_sessionState = !session_destroy();
            unset($_SESSION);
            return !$this->_sessionState;
        }
        return false;
    }

    /**
     * Set cookie params
     *
     * @param integer $maxlifetime
     * @param boolean $secure
     * @param boolean $httponly
     * @param string $samesite
     * @return self
     */
    public function setSessionCookieParams($maxlifetime, $secure, $httponly, $samesite = self::SAME_SIZE_NONE)
    {
        if (PHP_VERSION_ID < 70300) {
            session_set_cookie_params($maxlifetime, '/; samesite=' . $samesite, $_SERVER['HTTP_HOST'], $secure, $httponly);
        } else {
            session_set_cookie_params(array(
                'lifetime' => $maxlifetime,
                'path' => '/',
                'domain' => $_SERVER['HTTP_HOST'],
                'secure' => $secure,
                'httponly' => $httponly,
                'samesite' => $samesite
            ));
        }
        return $this;
    }

    /**
     * Support samesite cookie flag in both php 7.2 (current production) and php >= 7.3 (when we get there)
     * From: https://github.com/GoogleChromeLabs/samesite-examples/blob/master/php.md and https://stackoverflow.com/a/46971326/2308553
     *
     * @see https://www.php.net/manual/en/function.setcookie.php
     *
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param boolean $secure
     * @param boolean $httponly
     * @param string $samesite
     * @return self
     */
    function setSessionCookieSameSite($name, $value, $expire, $path, $domain, $secure, $httponly, $samesite = self::SAME_SIZE_NONE) //NOSONAR
    {
        if (PHP_VERSION_ID < 70300) {
            setcookie($name, $value, $expire, $path . '; samesite=' . $samesite, $domain, $secure, $httponly);
        }
        else {
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
     * Set session name
     *
     * @param string $ame
     * @return self
     */
    public function setSessionName($ame)
    {
        session_name($ame);
        return $this;
    }

    /**
     * Set path
     *
     * @param string $path Session save path. If sassion save handler is file, session save path is directory of the sesion files. If session save handler is redis, session save path is redis connection string include its key if any.
     * @return string|false
     */
    public function setSessionSavePath($path)
    {
        return session_save_path($path);
    }

    /**
     * Set maximum lifetime
     *
     * @param integer $lifeTime Maximum lifetime
     * @return self
     */
    public function setSessionMaxLifeTime($lifeTime)
    {
        ini_set("session.gc_maxlifetime", $lifeTime);
        ini_set("session.cookie_lifetime", $lifeTime);
        return $this;
    }

    /**
     * Save session to redis
     *
     * @param string $host Redis host
     * @param integer $port Redis port
     * @param string $auth Redis auth
     * @return self
     */
    public function saveToRedis($host, $port, $auth)
    {
        $path = sprintf("tcp://%s:%d?auth=%s", $host, $port, $auth);
        ini_set("session.save_handler ", "redis");
        ini_set("session.save_path ", $path);
        return $this;
    }

    /**
     * Save session to files
     *
     * @param string $path Directory
     * @return self
     */
    public function saveToFiles($path)
    {
        ini_set("session.save_handler ", "files");
        ini_set("session.save_path ", $path);
        return $this;
    }

    /**
     * Get current session ID
     *
     * @return string
     */
    public function getSessionId()
    {
        return @session_id();
    }

    /**
     * Set session ID
     *
     * @param string $id New session ID
     * @return self
     */
    public function setSessionId($id)
    {
        @session_id($id);
        return $this;
    }
}
