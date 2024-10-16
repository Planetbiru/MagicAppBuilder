<?php

namespace MagicApp;

use MagicObject\MagicObject;

/**
 * Class AppUser
 *
 * Represents a user in the application, encapsulating user properties and behaviors.
 */
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
     *
     * @var string
     */
    protected $languageId;

    /**
     * User data
     *
     * @var MagicObject
     */
    private $user;

    /**
     * Constructor
     *
     * @param MagicObject $user The user data object.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Magic method to handle dynamic getter and setter methods.
     *
     * @param string $method The name of the method being called.
     * @param array $args The arguments passed to the method.
     * @return mixed
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        // Handle getter methods
        if (stripos($method, 'get') === 0) {
            $property = lcfirst(substr($method, 3));
            return $this->user->get($property);
        }

        // Handle setter methods
        if (stripos($method, 'set') === 0 && !empty($args)) {
            $property = lcfirst(substr($method, 3));
            return $this->user->set($property, isset($args[0]) ? $args[0] : null);
        }

        throw new \BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * String representation of the AppUser object.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->user;
    }
}
