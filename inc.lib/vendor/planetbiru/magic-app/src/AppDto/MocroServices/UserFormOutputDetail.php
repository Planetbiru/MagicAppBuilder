<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class UserFormOutputDetail
 *
 * Represents a collection of input fields for a user form during an insert operation. 
 * This class manages multiple `OutputFieldDetail` objects, each representing a field 
 * to be inserted into the form. It also includes an array of allowed actions (e.g., 
 * `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`) that define 
 * the possible operations that can be performed on the form fields.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class UserFormOutputDetail extends EntityData
{

    /**
     * An array of input fields to be inserted into the form.
     * Each field is represented by an `OutputFieldDetail` object.
     *
     * @var OutputFieldDetail[]
     */
    protected $output;
    
    /**
     * A list of allowed actions that can be performed on the form fields.
     * Examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @var AllowedAction[]
     */
    protected $allowedActions;
    
    /**
     * A list of allowed actions that can be performed on the form fields.
     * Examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @var FieldWaitingFor
     */
    protected $waitingfor;
    
    /**
     * Add an allowed action to the output detail.
     *
     * This method adds an `OutputFieldDetail` object to the list of output that can be performed on the form fields. 
     *
     * @param OutputFieldDetail $output The `OutputFieldDetail` object to be added.
     */
    public function addOutput($output)
    {
        if (!isset($this->output)) {
            $this->output = [];
        }
        $this->output[] = $output;
    }
    
    /**
     * Add an allowed action to the output detail.
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

    /**
     * Get examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @return  FieldWaitingFor
     */ 
    public function getWaitingfor()
    {
        return $this->waitingfor;
    }

    /**
     * Set examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @param  FieldWaitingFor  $waitingfor  Examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @return  self
     */ 
    public function setWaitingfor($waitingfor)
    {
        $this->waitingfor = $waitingfor;

        return $this;
    }
}
