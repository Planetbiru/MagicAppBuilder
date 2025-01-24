<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoFieldWaitingFor
 *
 * Represents the status of a field waiting for an action to be performed.
 * This class is used to manage the actions that are pending on a field. 
 * Examples of actions include `create`, `update`, `activate`, `deactivate`, 
 * `delete`, and `sort-order`, each associated with a specific integer value.
 *
 * Action codes:
 * - `create` = 1
 * - `update` = 2
 * - `activate` = 3
 * - `deactivate` = 4
 * - `delete` = 5
 * - `sort-order` = 6
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoFieldWaitingFor extends PicoObjectToString
{
    /**
     * The numeric value representing the action waiting for the field.
     * This value corresponds to a specific action (e.g., 1 for create, 2 for update).
     *
     * @var integer
     */
    protected $value;

    /**
     * The human-readable code for the action.
     * For example, "create", "update", "activate", etc.
     *
     * @var string
     */
    protected $code;

    /**
     * The label describing the action for user-friendly display.
     * This will be localized according to the language used.
     * For example, "Create", "Update", "Activate", etc.
     *
     * @var string
     */
    protected $label;

    /**
     * Constructor to initialize the properties of the class.
     *
     * @param integer $value The numeric value representing the action code.
     * @param string $code The human-readable code for the action.
     * @param string $label The user-friendly label describing the action.
     */
    public function __construct($value, $code, $label)
    {
        $this->value = $value;
        $this->code = $code;
        $this->label = $label;
    }

    /**
     * Get the numeric value representing the action.
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the numeric value representing the action.
     *
     * @param integer $value The action code.
     * @return self Returns the current instance for method chaining.
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get the human-readable code for the action.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the human-readable code for the action.
     *
     * @param string $code The action code.
     * @return self Returns the current instance for method chaining.
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get the label describing the action for user-friendly display.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set the label describing the action for user-friendly display.
     *
     * @param string $label The action label.
     * @return self Returns the current instance for method chaining.
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }
}
