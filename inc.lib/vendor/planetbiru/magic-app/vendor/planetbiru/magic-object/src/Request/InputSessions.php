<?php

namespace MagicObject\Request;

use MagicObject\Session\PicoSession;

/**
 * Class InputSessions
 *
 * This class extends the PicoSession class to handle input sessions.
 * It is designed to manage session data and provide methods for session handling.
 *
 * @package MagicObject\Request
 */
class InputSessions extends PicoSession
{
    /**
     * Returns the instance of PicoSession.
     * The session is automatically initialized if it wasn't already.
     *
     * This method ensures that only one instance of PicoSession is created (Singleton pattern).
     *
     * @param string|null $name Session name.
     * @param int $maxLifeTime Maximum lifetime of the session.
     * @return self The instance of PicoSession.
     */
    public static function getInstance($name = null, $maxLifeTime = 0)
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self;
            if (isset($name)) {
                self::$_instance->setSessionName($name);
            }
            if ($maxLifeTime > 0) {
                self::$_instance->setSessionMaxLifeTime($maxLifeTime);
            }
        }
        self::$_instance->startSession();
        return self::$_instance;
    }
}