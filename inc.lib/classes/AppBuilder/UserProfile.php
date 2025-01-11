<?php

namespace AppBuilder;

use AppBuilder\Entity\EntityAdmin;
use Exception;
use MagicObject\Database\PicoDatabase;
use MagicObject\SecretObject;

class UserProfile
{
    /**
     * Undocumented variable
     *
     * @var PicoDatabase
     */
    private $database;
    
    /**
     * Undocumented variable
     *
     * @var SecretObject
     */
    private $coreConfig;
    
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $coreConfigPath;
    
    /**
     * Undocumented variable
     *
     * @var boolean
     */
    private $loggedIn;
    
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $adminId;
    
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $username;
    
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $name;
    
    /**
     * Undocumented variable
     *
     * @var string
     */
    private $userLevel;
    
    public function __construct($database, $coreConfig, $coreConfigPath)
    {
        $this->database = $database;
        $this->coreConfig = $coreConfig;
        $this->coreConfigPath = $coreConfigPath;
    }
    
    public function auth($username, $password)
    {
        $entityAdmin = new EntityAdmin(null, $this->database);
        try
        {
            $entityAdmin->findOneByUsername($username);
            if($entityAdmin->getPassword() == sha1($password))
            {
                $this->adminId = $entityAdmin->getUserId();
                $this->username = $entityAdmin->getUsername();
                $this->name = $entityAdmin->getName();
                $this->userLevel = $entityAdmin->getUserLevel();
            }
        }
        catch(Exception $e)
        {
            // Do nothing
        }
    }

    /**
     * Get undocumented variable
     *
     * @return  boolean
     */ 
    public function getLoggedIn()
    {
        return $this->loggedIn;
    }

    /**
     * Set undocumented variable
     *
     * @param  boolean  $loggedIn  Undocumented variable
     *
     * @return  self
     */ 
    public function setLoggedIn($loggedIn)
    {
        $this->loggedIn = $loggedIn;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */ 
    public function getUserId()
    {
        return $this->adminId;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $adminId  Undocumented variable
     *
     * @return  self
     */ 
    public function setUserId($adminId)
    {
        $this->adminId = $adminId;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */ 
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $username  Undocumented variable
     *
     * @return  self
     */ 
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $name  Undocumented variable
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get undocumented variable
     *
     * @return  string
     */ 
    public function getUserLevel()
    {
        return $this->userLevel;
    }

    /**
     * Set undocumented variable
     *
     * @param  string  $userLevel  Undocumented variable
     *
     * @return  self
     */ 
    public function setUserLevel($userLevel)
    {
        $this->userLevel = $userLevel;

        return $this;
    }
}