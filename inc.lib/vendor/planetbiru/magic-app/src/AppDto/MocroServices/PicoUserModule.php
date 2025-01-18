<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoUserModule
 *
 * Represents a module that is allowed to be accessed by the current user.
 * This class contains information about the module, such as its name, 
 * code, and path. It also includes the allowed actions that can be 
 * performed within the module and any child modules that may be nested within it.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoUserModule
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
     * The namespace where the module is located, such as "/", "/admin", "/supervisor", etc.
     *
     * @var string
     */
    protected $namespace;
    
    /**
     * The path or URL where the module can be accessed within the application.
     *
     * @var string
     */
    protected $path;
    
    /**
     * A list of child modules that are nested within the current module.
     * Each child module is represented by a `PicoUserModule` object.
     *
     * @var self[]
     */
    protected $childs;
    
}
