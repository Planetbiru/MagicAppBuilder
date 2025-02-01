<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoModuleInfo
 *
 * This class represents information about a module within the application.
 * It stores the module's name, title, path, section, and allowed actions.
 * 
 * - The `name` refers to the unique identifier of the module.
 * - The `title` is a human-readable label for the module.
 * - The `path` refers to the location or URL of the module.
 * - The `section` indicates the specific part or view of the module (e.g., "list" for listing data, "detail" for displaying detailed information).
 * 
 * This class allows setting and getting these properties, as well as managing allowed actions that can be performed on the module.
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
     * The path of the module.
     *
     * This property defines the file path or URL path associated with the
     * module, used for routing or linking to the module's content.
     *
     * @var string
     */
    protected $path;

    /**
     * The section to which the module belongs.
     *
     * This property defines the section or category in which the module is organized.
     * The section can represent the type of view the module should display, for example:
     * - "list" for a list view to display multiple items.
     * - "detail" for a detail view to display detailed information about a single item.
     *
     * @var string
     */
    protected $section;

    /**
     * A list of allowed actions that can be performed on the module.
     *
     * This property stores the allowed actions for the module. These actions could include
     * operations like updating, activating, or deleting records, and are represented by
     * `AllowedAction` objects.
     *
     * @var AllowedAction[]
     */
    protected $allowedActions;

    /**
     * Constructor for PicoModuleInfo.
     * Initializes the module's name, title, path, and section properties.
     *
     * @param string $name The name of the module.
     * @param string $title The title of the module.
     * @param string $path The path of the module.
     * @param string $section The section the module belongs to (e.g., "list", "detail").
     */
    public function __construct($name, $title, $path, $section = null)
    {
        $this->name = $name;
        $this->title = $title;
        $this->path = $path;
        $this->section = $section;
        $this->allowedActions = array();  // Initialize the allowed actions as an empty array.
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
     * Get the path of the module.
     *
     * @return string The path of the module.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path of the module.
     *
     * @param string $path The path of the module.
     * @return self Returns the current instance for method chaining.
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Get the section the module belongs to.
     *
     * The section represents the view type or purpose of the module (e.g., "list" or "detail").
     *
     * @return string The section the module belongs to.
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set the section the module belongs to.
     *
     * The section defines the view type or category for the module.
     * For example, "list" for listing multiple items, or "detail" for displaying details of a single item.
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
     * Add an allowed action to the module.
     *
     * This method adds an `AllowedAction` object to the list of actions that can be performed
     * on the module. Allowed actions might include operations like updating, activating, or 
     * deleting records.
     *
     * @param AllowedAction $allowedAction The `AllowedAction` object to be added.
     * @return self Returns the current instance for method chaining.
     */
    public function addAllowedAction($allowedAction)
    {
        if (!isset($this->allowedActions)) {
            $this->allowedActions = array();
        }
        $this->allowedActions[] = $allowedAction;
        return $this;
    }

    /**
     * Get the list of allowed actions for the module.
     *
     * @return array The list of `AllowedAction` objects for the module.
     */
    public function getAllowedActions()
    {
        return $this->allowedActions;
    }
}
