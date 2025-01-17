<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class FieldWaitingFor
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
class FieldWaitingFor extends ValueLabelConstructor
{
    /**
     * The numeric value representing the action waiting for the field.
     * This value corresponds to a specific action (e.g., 1 for create, 2 for update).
     *
     * @var integer
     */
    protected $value;
    
    /**
     * The label describing the action for user-friendly display.
     * For example, "Create", "Update", "Activate", etc.
     *
     * @var string
     */
    protected $label;
}
