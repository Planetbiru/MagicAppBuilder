<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class ResponseDto
 *
 * Represents a data transfer object for API responses.
 * This class holds the response code, message, and associated data.
 * 
 * The class extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ResponseDto extends ToString
{
    /**
     * The response code indicating the status of the request.
     *
     * @var string|null
     */
    protected $responseCode;

    /**
     * A message providing additional information about the response.
     *
     * @var string|null
     */
    protected $responseMessage;

    /**
     * The data associated with the response.
     *
     * @var mixed
     */
    protected $data;

    /**
     * Constructor to initialize properties.
     *
     * @param string|null $responseCode The response code.
     * @param string|null $responseMessage The response message.
     * @param mixed $data The associated data.
     */
    public function __construct($responseCode = null, $responseMessage = null, $data = null)
    {
        $this->responseCode = $responseCode;
        $this->responseMessage = $responseMessage;
        $this->data = $data;
    }

    /**
     * Set the metadata for the data object and return the current instance for method chaining.
     *
     * This method delegates the setting of metadata to the data object's setMetadata method.
     *
     * @param MetadataDto $metadata The metadata to associate with the data object.
     * @return self The current instance for method chaining. The current instance for method chaining.
     */
    public function setMetadata($metadata)
    {
        if ($this->data && method_exists($this->data, 'setMetadata')) {
            $this->data->setMetadata($metadata);
        }
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the response code.
     *
     * @return string|null
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set the response code.
     *
     * @param string|null $responseCode The response code to set.
     * @return self The current instance for method chaining.
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    /**
     * Get the response message.
     *
     * @return string|null
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Set the response message.
     *
     * @param string|null $responseMessage The response message to set.
     * @return self The current instance for method chaining.
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
        return $this;
    }

    /**
     * Get the associated data.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the associated data.
     *
     * @param mixed $data The data to set.
     * @return self The current instance for method chaining.
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }
}
