<?php

namespace MagicApp;
use MagicApp\Utility\CloudflareUtil;
use MagicObject\MagicObject;
use MagicObject\SecretObject;

class CurrentAction
{
    /**
     * Current user set by constructor
     *
     * @var string
     */
    private $user = "";

    /**
     * Current IP
     *
     * @var string
     */
    private $ip = "";

    /**
     * Constructor
     *
     * @param MagicObject|SecretObject $cfg
     * @param string $user
     */
    public function __construct($cfg, $user)
    {
        $this->ip = $this->getRemoteAddress($cfg);
        $this->user = $user;
    }

    /**
     * Get remote address
     *
     * @param MagicObject|SecretObject $cfg
     * @return string
     */
    public function getRemoteAddress($cfg = null)
    {
        if($cfg != null && $cfg->getProxyProvider() != null && $cfg->getProxyProvider() == 'cloudflare')
        {
            // get remote address from header sent by Cloudflare
            return CloudflareUtil::getClientIp(false);
        }
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get the value of time
     * @return string
     */ 
    public function getTime()
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get current user set by constructor
     *
     * @return string
     */ 
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get current IP
     *
     * @return string
     */ 
    public function getIp()
    {
        return $this->ip;
    }
}