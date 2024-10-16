<?php

namespace MagicApp;

use MagicApp\Utility\CloudflareUtil;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

/**
 * Class CurrentAction
 *
 * Captures the current user action details, including user information and IP address.
 */
class CurrentAction
{
    /**
     * Current user set by constructor.
     *
     * @var string
     */
    private $user;

    /**
     * Current IP address.
     *
     * @var string
     */
    private $ip;

    /**
     * Constructor
     *
     * @param MagicObject|SecretObject $cfg Configuration object for getting IP settings.
     * @param string $user Current user.
     */
    public function __construct($cfg, $user)
    {
        $this->user = $user;
        $this->ip = $this->getRemoteAddress($cfg);
    }

    /**
     * Get remote address.
     *
     * @param MagicObject|SecretObject|null $cfg Configuration object for proxy settings.
     * @return string The remote address of the user.
     */
    public function getRemoteAddress($cfg = null)
    {
        if ($cfg !== null && $cfg->getProxyProvider() === 'cloudflare') {
            // Get remote address from header sent by Cloudflare.
            return CloudflareUtil::getClientIp(false);
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get the current timestamp.
     *
     * @return string Formatted current time.
     */
    public function getTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get the current user.
     *
     * @return string The current user.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the current IP address.
     *
     * @return string The current IP address.
     */
    public function getIp()
    {
        return $this->ip;
    }
}
