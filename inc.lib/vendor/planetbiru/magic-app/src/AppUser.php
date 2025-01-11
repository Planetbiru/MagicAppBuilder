<?php

namespace MagicApp;

use BadMethodCallException;
use MagicObject\MagicObject;

/**
 * Class AppUser
 *
 * Represents a user in the application, encapsulating user properties and behaviors.
 * This class provides access to user data through dynamic getter and setter methods.
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
     * User data object encapsulated in a MagicObject.
     *
     * @var MagicObject
     */
    private $user;

    /**
     * Constructor
     *
     * Initializes the AppUser object with the provided user data object.
     *
     * @param MagicObject $user The user data object to initialize the user.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Magic method to handle dynamic getter and setter methods.
     *
     * This method intercepts calls to getter and setter methods that follow the `get` and `set` conventions.
     * It delegates these calls to the underlying `MagicObject` to retrieve or set user properties.
     *
     * @param string $method The name of the method being called (either a getter or setter).
     * @param array $args The arguments passed to the method (if any).
     * @return mixed The value returned by the `MagicObject` getter/setter method.
     * @throws BadMethodCallException If the method being called is neither a getter nor setter, or if it does not exist in the `MagicObject`.
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

        // Throw an exception if the method doesn't exist or isn't a valid getter/setter
        throw new BadMethodCallException("Method {$method} does not exist.");
    }

    /**
     * String representation of the AppUser object.
     *
     * This method converts the `AppUser` object to a string by calling the `__toString()` method of the encapsulated `MagicObject`.
     * Typically used for debugging and logging purposes.
     *
     * @return string The string representation of the user data.
     */
    public function __toString()
    {
        return (string) $this->user;
    }
}
