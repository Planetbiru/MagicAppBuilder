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
     * Initializes the properties with the given values.
     *
     * @param string $active The status of the entity, either active or inactive.
     * @param string $draft The draft status of the entity.
     * @param string $waitingFor The waitingFor value.
     * @param string $approvalId The approval ID for the entity.
     */
    public function __construct($active = null, $draft = null, $waitingFor = null, $approvalId = null)
    {
        $this->active = $active;
        $this->draft = $draft;
        $this->waitingFor = $waitingFor;
        $this->approvalId = $approvalId;
    }

    /**
     * Get the active status of the entity.
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the active status of the entity.
     * Returns the current instance for method chaining.
     *
     * @param string $active
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
     * @return string
     */
    public function getDraft()
    {
        return $this->draft;
    }

    /**
     * Set the draft status of the entity.
     * Returns the current instance for method chaining.
     *
     * @param string $draft
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
     * @return string
     */
    public function getWaitingFor()
    {
        return $this->waitingFor;
    }

    /**
     * Set the waitingFor value of the entity.
     * Returns the current instance for method chaining.
     *
     * @param string $waitingFor
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
     * @return string
     */
    public function getApprovalId()
    {
        return $this->approvalId;
    }

    /**
     * Set the approval ID of the entity.
     * Returns the current instance for method chaining.
     *
     * @param string $approvalId
     * @return self Returns the current instance for method chaining.
     */
    public function setApprovalId($approvalId)
    {
        $this->approvalId = $approvalId;
        return $this; // return the current instance
    }
}
