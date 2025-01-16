<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class UserModule
 *
 * Represents a module that is allowed to be accessed by the current user.
 * This class contains information about the module, such as its name, 
 * code, and path. It also includes the allowed actions that can be 
 * performed within the module and any child modules that may be nested within it.
 *
 * @package AppBuilder\Generator\MocroServices
 */
class UserModule
{
    /**
     * The name of the module, typically used for display or identification purposes.
     *
     * @var string
     */
    protected $name;
    
    /**
     * The unique code that identifies the module, used for referencing or linking purposes.
     *
     * @var string
     */
    protected $code;
    
    /**
     * The path or URL where the module can be accessed within the application.
     *
     * @var string
     */
    protected $path;
    
    /**
     * A list of allowed actions that can be performed within the module.
     * Each action is represented by an `AllowedAction` object.
     *
     * @var AllowedAction[]
     */
    protected $allowedActions;
    
    /**
     * A list of child modules that are nested within the current module.
     * Each child module is represented by a `UserModule` object.
     *
     * @var self[]
     */
    protected $childs;
    
    /**
     * Add an allowed action to the user module.
     *
     * This method adds an `AllowedAction` object to the list of actions that can be performed on the form fields. 
     * These actions could include operations like updating, activating, or deleting records.
     *
     * @param AllowedAction $allowedAction The `AllowedAction` object to be added.
     */
    public function addAllowedAction($allowedAction)
    {
        if (!isset($this->allowedActions)) {
            $this->allowedActions = [];
        }
        $this->allowedActions[] = $allowedAction;
    }
}
