<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoEntityInfo
 * 
 * This class contains the mapping for column information related to an entity.
 * It holds the properties for various column values such as active status, draft status, waiting status, and approval ID.
 */
class PicoEntityInfo extends PicoObjectToString
{
    /**
     * Column name to store status active or inactive
     *
     * @var string
     */
    protected $active;

    /**
     * Column name to store draft status 
     *
     * @var string
     */
    protected $draft;

    /**
     * Column name to store waitingFor value 
     *
     * @var string
     */
    protected $waitingFor;

    /**
     * Column name to store approvalId value 
     *
     * @var string
     */
    protected $approvalId;

    /**
     * PicoEntityInfo constructor.
     * Initializes the properties with the given values from the $info array.
     * 
     * This constructor checks for the existence of specific keys in the provided 
     * associative array ($info) and assigns their values to the corresponding 
     * properties of the class if they exist.
     *
     * @param array $info An associative array containing the entity information.
     *                    Expected keys: 'active', 'draft', 'waitingFor', 'approvalId'.
     */
    public function __construct($info)
    {
        if(isset($info['active']))
        {
            $this->active = $info['active'];
        }
        if(isset($info['draft']))
        {
            $this->draft = $info['draft'];
        }
        if(isset($info['waitingFor']))
        {
            $this->waitingFor = $info['waitingFor'];
        }
        if(isset($info['approvalId']))
        {
            $this->approvalId = $info['approvalId'];
        }
    }


    /**
     * Get the active status of the entity.
     *
     * @return string The active status of the entity.
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the active status of the entity.
     * Returns the current instance for method chaining.
     *
     * @param string $active The active status to set for the entity.
     * @return self Returns the current instance for method chaining.
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this; // return the current instance
    }

    /**
     * Get the draft status of the entity.
     *
     * @return string The draft status of the entity.
     */
    public function getDraft()
    {
        return $this->draft;
    }

    /**
     * Set the draft status of the entity.
     * Returns the current instance for method chaining.
     *
     * @param string $draft The draft status to set for the entity.
     * @return self Returns the current instance for method chaining.
     */
    public function setDraft($draft)
    {
        $this->draft = $draft;
        return $this; // return the current instance
    }

    /**
     * Get the waitingFor value of the entity.
     *
     * @return string The waitingFor value of the entity.
     */
    public function getWaitingFor()
    {
        return $this->waitingFor;
    }

    /**
     * Set the waitingFor value of the entity.
     * Returns the current instance for method chaining.
     *
     * @param string $waitingFor The waitingFor value to set for the entity.
     * @return self Returns the current instance for method chaining.
     */
    public function setWaitingFor($waitingFor)
    {
        $this->waitingFor = $waitingFor;
        return $this; // return the current instance
    }

    /**
     * Get the approval ID of the entity.
     *
     * @return string The approval ID of the entity.
     */
    public function getApprovalId()
    {
        return $this->approvalId;
    }

    /**
     * Set the approval ID of the entity.
     * Returns the current instance for method chaining.
     *
     * @param string $approvalId The approval ID to set for the entity.
     * @return self Returns the current instance for method chaining.
     */
    public function setApprovalId($approvalId)
    {
        $this->approvalId = $approvalId;
        return $this; // return the current instance
    }
}
