<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoUserFormOutputDetail
 *
 * Represents a collection of input fields for a user form during an insert operation. 
 * This class manages multiple `OutputFieldDetail` objects, each representing a field 
 * to be inserted into the form. It also includes an array of allowed actions (e.g., 
 * `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`) that define 
 * the possible operations that can be performed on the form fields.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoUserFormOutputDetail extends PicoEntityData
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
     * @var FieldWaitingFor
     */
    protected $waitingFor;
    
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
     * Get examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @return  FieldWaitingFor
     */ 
    public function getWaitingfor()
    {
        return $this->waitingFor;
    }

    /**
     * Set examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @param FieldWaitingFor  $waitingFor  Examples include `update`, `activate`, `deactivate`, `delete`, `approve`, `reject`.
     *
     * @return self Returns the current instance for method chaining.
     */ 
    public function setWaitingfor($waitingFor)
    {
        $this->waitingFor = $waitingFor;

        return $this;
    }
}
