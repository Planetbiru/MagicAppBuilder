<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoModuleInfo
 *
 * This class represents information about a module within the application.
 * It stores the module's name, title, and section, which are commonly used 
 * to categorize or display the module's details in various parts of the system.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoModuleInfo extends PicoObjectToString
{
    /**
     * The name of the module.
     *
     * This property stores the unique name identifier of the module, used for
     * referencing or accessing the module within the system.
     *
     * @var string
     */
    protected $name;
    
    /**
     * The title of the module.
     *
     * This property holds the title or label of the module, typically used
     * for display purposes in the user interface.
     *
     * @var string
     */
    protected $title;
    
    /**
     * The section to which the module belongs.
     *
     * This property defines the section or category in which the module is organized,
     * allowing for better organization and grouping of modules.
     *
     * @var string
     */
    protected $section;

    /**
     * Constructor for PicoModuleInfo.
     * Initializes the module's name, title, and section properties.
     *
     * @param string $name The name of the module.
     * @param string $title The title of the module.
     * @param string $section The section the module belongs to.
     */
    public function __construct($name, $title, $section)
    {
        $this->name = $name;
        $this->title = $title;
        $this->section = $section;
    }

    /**
     * Get the name of the module.
     *
     * @return string The name of the module.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the name of the module.
     *
     * @param string $name The name of the module.
     * @return self Returns the current instance for method chaining.
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the title of the module.
     *
     * @return string The title of the module.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the title of the module.
     *
     * @param string $title The title of the module.
     * @return self Returns the current instance for method chaining.
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the section the module belongs to.
     *
     * @return string The section the module belongs to.
     */
    public function getSection()
    {
        return $this->section;
    }
    
    /**
     * A list of allowed actions that can be performed on the form fields.
     * Each action is represented by an `AllowedAction` object.
     *
     * @var AllowedAction[]
     */
    protected $allowedActions;

    /**
     * Set the section the module belongs to.
     *
     * @param string $section The section the module belongs to.
     * @return self Returns the current instance for method chaining.
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }
    
    /**
     * Add an allowed action to the output detail.
     *
     * This method adds an `AllowedAction` object to the list of actions that can be performed on the form fields. 
     * These actions could include operations like updating, activating, or deleting records.
     *
     * @param AllowedAction $allowedAction The `AllowedAction` object to be added.
     * @return self Returns the current instance for method chaining.
     */
    public function addAllowedAction($allowedAction)
    {
        if (!isset($this->allowedActions)) {
            $this->allowedActions = [];
        }
        $this->allowedActions[] = $allowedAction;
        return $this;
    }
}
