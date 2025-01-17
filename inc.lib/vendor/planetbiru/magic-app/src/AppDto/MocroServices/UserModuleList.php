<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class UserModuleList
 *
 * Represents a collection of user modules that are accessible by the current user.
 * This class manages a list of `UserModule` objects, each of which represents a 
 * module that the user has access to. It provides a structure for storing and 
 * managing multiple modules.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class UserModuleList
{
    /**
     * An array of `UserModule` objects representing the modules that the user has access to.
     * Each module contains information about the module's name, code, path, allowed actions, 
     * and potential child modules.
     *
     * @var UserModule[]
     */
    protected $userModules;
    
    /**
     * Constructor for initializing the user module list.
     *
     * This constructor initializes an empty user module list and optionally adds a 
     * user module if provided. The user module is an instance of the `UserModule` class.
     *
     * @param UserModule|null $userModule An optional `UserModule` to add to the list.
     */
    public function __construct($userModule = null)
    {
        $this->userModules = [];
        if (isset($userModule)) {
            $this->userModules[] = $userModule;
        }
    }
    
    /**
     * Add a user module to the user module list.
     *
     * This method allows adding an `UserModule` object to the list of user modules.
     *
     * @param UserModule $userModule The `UserModule` to be added to the list.
     */
    public function addUserModule($userModule)
    {
        $this->userModules[] = $userModule;
    }
}
