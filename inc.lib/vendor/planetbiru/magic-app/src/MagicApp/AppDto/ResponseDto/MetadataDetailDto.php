<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Data Transfer Object (DTO) representing detailed metadata information.
 * 
 * The MetadataDetailDto class extends the MetadataDto class to provide 
 * specific details related to metadata associated with data operations. 
 * It includes properties that indicate the status of operations, the 
 * primary key associated with the metadata, and its active status. 
 * This class is particularly useful for scenarios requiring detailed 
 * tracking of metadata operations, such as in data approval processes, 
 * updates, or state changes.
 * 
 * The class provides methods for accessing and manipulating these properties 
 * while leveraging inheritance from the MetadataDto class for common metadata 
 * functionalities.
 * 
 * The class also extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class MetadataDetailDto extends MetadataDto
{
    /**
     * Associated array key value primary key.
     *
     * @var array
     */
    public $primaryKey;

    /**
     * Indicates whether the metadata is active.
     *
     * @var bool
     */
    public $active;

    /**
     * Represents the status of the operation.
     * 
     * Possible values:
     * - 1: approval for new data
     * - 2: approval for updating data
     * - 3: approval for activate
     * - 4: approval for deactivate
     * - 5: approval for delete
     * - 6: approval for sort order
     *
     * @var int
     */
    public $waitingFor;

    /**
     * Code representing the waiting status.
     *
     * @var string
     */
    public $waitingForCode;

    /**
     * Message associated with the waiting status.
     *
     * @var string
     */
    public $waitingForMessage;

    /**
     * Constructor to initialize properties of the MetadataDetailDto class.
     *
     * @param array $primaryKey An array representing the primary key.
     * @param bool $active A boolean indicating if the metadata is active.
     * @param int $waitingFor An integer representing the operation status.
     * @param string $waitingForCode A code representing the waiting status.
     * @param string $waitingForMessage A message associated with the waiting status.
     */
    public function __construct($primaryKey, $active, $waitingFor, $waitingForCode, $waitingForMessage)
    {
        parent::__construct($primaryKey, $active, $waitingFor); // Call parent constructor
        $this->waitingForCode = $waitingForCode;
        $this->waitingForMessage = $waitingForMessage;
    }

    /**
     * Get the primary key associated with the metadata.
     *
     * @return array The primary key.
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key associated with the metadata.
     *
     * @param array $primaryKey The primary key to set.
     * @return self The current instance for method chaining.
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the active status of the metadata.
     *
     * @return bool The active status.
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set the active status of the metadata.
     *
     * @param bool $active The active status to set.
     * @return self The current instance for method chaining.
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the operation status represented by the waitingFor property.
     *
     * @return int The operation status.
     */
    public function getWaitingFor()
    {
        return $this->waitingFor;
    }

    /**
     * Set the operation status represented by the waitingFor property.
     *
     * @param int $waitingFor The operation status to set.
     * @return self The current instance for method chaining.
     */
    public function setWaitingFor($waitingFor)
    {
        $this->waitingFor = $waitingFor;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the code representing the waiting status.
     *
     * @return string The waiting status code.
     */
    public function getWaitingForCode()
    {
        return $this->waitingForCode;
    }

    /**
     * Set the code representing the waiting status.
     *
     * @param string $waitingForCode The waiting status code to set.
     * @return self The current instance for method chaining.
     */
    public function setWaitingForCode($waitingForCode)
    {
        $this->waitingForCode = $waitingForCode;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the message associated with the waiting status.
     *
     * @return string The waiting status message.
     */
    public function getWaitingForMessage()
    {
        return $this->waitingForMessage;
    }

    /**
     * Set the message associated with the waiting status.
     *
     * @param string $waitingForMessage The waiting status message to set.
     * @return self The current instance for method chaining.
     */
    public function setWaitingForMessage($waitingForMessage)
    {
        $this->waitingForMessage = $waitingForMessage;
        return $this; // Return current instance for method chaining.
    }
}
