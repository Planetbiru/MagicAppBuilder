<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoUserFormOutputApproval
 *
 * Represents a collection of output fields for a user form during an approval operation.
 * This class manages multiple `OutputFieldApproval` objects, each representing a field 
 * to be displayed in the form for approval or rejection. It also includes an array of allowed actions 
 * (e.g., `approve`, `reject`, `update`) that define the possible operations that can be performed on the form fields, 
 * as well as the current status of the field, such as whether it is waiting for an action like approval.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoUserFormOutputApproval extends PicoEntityData
{
    /**
     * An array of output fields to be displayed in the form for approval.
     * Each field is represented by an `OutputFieldApproval` object.
     *
     * @var OutputFieldApproval[]
     */
    protected $output;

    /**
     * The current status of the field, typically used to indicate whether the field
     * is waiting for a specific action, such as approval or rejection.
     * This is represented by a `FieldWaitingFor` object.
     *
     * @var FieldWaitingFor
     */
    protected $waitingfor;
    
    /**
     * Add an allowed action to the output approval.
     *
     * This method adds an `OutputFieldApproval` object to the list of output that can be performed on the form fields. 
     *
     * @param OutputFieldApproval $output The `OutputFieldApproval` object to be added.
     */
    public function addOutput($output)
    {
        if (!isset($this->output)) {
            $this->output = [];
        }
        $this->output[] = $output;
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
     * @param FieldWaitingFor  $waitingfor  Examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @return self The current instance for method chaining.
     */ 
    public function setWaitingfor($waitingfor)
    {
        $this->waitingfor = $waitingfor;

        return $this;
    }
}
