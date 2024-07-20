<?php

namespace MagicApp;

use MagicObject\MagicObject;

class AppUser
{
    /**
     * User ID
     *
     * @var string
     */
    protected $userId;
    
    /**
     * User level ID
     *
     * @var string
     */
    protected $userLevelId;

    /**
     * Language ID
     * @var string
     */
    protected $languageId;

    /**
     * User
     * @var MagicObject
     */
    private $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function __call($method, $args)
    {
        if(stripos($method, 'get') === 0)
        {
            return $this->user->get(lcfirst(substr($method, 3)));
        }
        if(stripos($method, 'set') === 0 && isset($args))
        {
            return $this->user->set(lcfirst(substr($method, 3)), isset($args[0]) ? $args[0] : null);
        }
    }

    public function __tostring()
    {
        return $this->user."";
    }
}