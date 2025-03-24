<?php

namespace AppBuilder;

use AppBuilder\EntityInstaller\EntityAdmin;
use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;

/**
 * UserProfile class handles user authentication and profile management.
 */
class UserProfile
{
    /**
     * Database instance to interact with user data.
     *
     * @var PicoDatabase
     */
    private $database;
    
    /**
     * Core configuration object for the application.
     *
     * @var SecretObject
     */
    private $coreConfig;
    
    /**
     * Path to the core configuration file.
     *
     * @var string
     */
    private $coreConfigPath;
    
    /**
     * Boolean flag indicating whether the user is logged in.
     *
     * @var bool
     */
    private $loggedIn;
    
    /**
     * ID of the authenticated admin.
     *
     * @var string
     */
    private $adminId;
    
    /**
     * Username of the authenticated admin.
     *
     * @var string
     */
    private $username;
    
    /**
     * Full name of the authenticated admin.
     *
     * @var string
     */
    private $name;
    
    /**
     * The user level (e.g., admin, user) of the authenticated admin.
     *
     * @var string
     */
    private $userLevel;
    
    /**
     * Constructor to initialize the UserProfile instance.
     *
     * @param PicoDatabase $database      The database instance for user data.
     * @param SecretObject $coreConfig    The core configuration object.
     * @param string       $coreConfigPath The path to the core configuration file.
     */
    public function __construct($database, $coreConfig, $coreConfigPath)
    {
        $this->database = $database;
        $this->coreConfig = $coreConfig;
        $this->coreConfigPath = $coreConfigPath;
    }
    
    /**
     * Authenticates a user by checking the username and password.
     *
     * @param string $username The username to authenticate.
     * @param string $password The password to authenticate.
     *
     * @return void
     */
    public function auth($username, $password)
    {
        $entityAdmin = new EntityAdmin(null, $this->database);
        try
        {
            $entityAdmin->findOneByUsername($username);
            if ($entityAdmin->getPassword() == sha1($password))
            {
                $this->adminId = $entityAdmin->getUserId();
                $this->username = $entityAdmin->getUsername();
                $this->name = $entityAdmin->getName();
                $this->userLevel = $entityAdmin->getUserLevel();
            }
        }
        catch (Exception $e)
        {
            // Do nothing
        }
    }

    /**
     * Gets the logged-in status of the user.
     *
     * @return boolean True if logged in, false otherwise.
     */
    public function getLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * Sets the logged-in status of the user.
     *
     * @param boolean $loggedIn The logged-in status to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setLoggedIn($loggedIn)
    {
        $this->loggedIn = $loggedIn;

        return $this;
    }

    /**
     * Gets the ID of the authenticated user.
     *
     * @return string The authenticated user's ID.
     */
    public function getUserId()
    {
        return $this->adminId;
    }

    /**
     * Sets the ID of the authenticated user.
     *
     * @param string $adminId The admin ID to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setUserId($adminId)
    {
        $this->adminId = $adminId;

        return $this;
    }

    /**
     * Gets the username of the authenticated user.
     *
     * @return string The authenticated user's username.
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the username of the authenticated user.
     *
     * @param string $username The username to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Gets the name of the authenticated user.
     *
     * @return string The authenticated user's full name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name of the authenticated user.
     *
     * @param string $name The name to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets the user level of the authenticated user.
     *
     * @return string The user level (e.g., admin, user).
     */
    public function getUserLevel()
    {
        return $this->userLevel;
    }

    /**
     * Sets the user level of the authenticated user.
     *
     * @param string $userLevel The user level to set.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function setUserLevel($userLevel)
    {
        $this->userLevel = $userLevel;

        return $this;
    }
}
