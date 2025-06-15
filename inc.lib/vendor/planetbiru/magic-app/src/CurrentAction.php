<?php

namespace MagicApp;

use MagicApp\Utility\CloudflareUtil;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

/**
 * Class CurrentAction
 *
 * Captures the current user action details, including user information and IP address.
 *
 * This class is responsible for capturing and providing the details of the current user's action,
 * including the user's identity and IP address. It handles retrieving the user's IP address, 
 * accounting for the use of proxy services like Cloudflare, and formatting the current timestamp.
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
     * Initializes the `CurrentAction` object with the user's information and their IP address.
     * The constructor expects a configuration object for retrieving proxy settings and a string 
     * representing the current user.
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
     * Retrieves the user's remote IP address. If a proxy service like Cloudflare is used,
     * it fetches the client's IP address from the Cloudflare header. Otherwise, it returns the 
     * address from the `REMOTE_ADDR` server variable.
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
     * @return string Current time in 'Y-m-d H:i:s' format.
     */
    public function getTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get the current user.
     *
     * @return string Current user.
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the current IP address.
     *
     * @return string Current IP address.
     */
    public function getIp()
    {
        return $this->ip;
    }
}
