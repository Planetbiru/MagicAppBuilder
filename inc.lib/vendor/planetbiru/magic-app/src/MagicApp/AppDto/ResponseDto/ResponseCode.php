<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class ResponseCode
 *
 * This class represents the structure for a response code and its associated message.
 * It encapsulates the response code that indicates the status of a request,
 * along with a message that provides additional context about the response.
 * 
 * The class extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ResponseCode extends ToString
{
    /**
     * The response code indicating the status of the request.
     *
     * @var string
     */
    public $responseCode;

    /**
     * A message providing additional information about the response.
     *
     * @var string
     */
    public $responseMessage;

    /**
     * Constructor for initializing a ResponseCode instance.
     *
     * @param string $responseCode The response code.
     * @param string $responseMessage The response message.
     */
    public function __construct($responseCode = '', $responseMessage = '')
    {
        $this->responseCode = $responseCode;
        $this->responseMessage = $responseMessage;
    }

    /**
     * Get the response code.
     *
     * @return string The response code.
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set the response code.
     *
     * @param string $responseCode The response code.
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
     * @return string The response message.
     */
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Set the response message.
     *
     * @param string $responseMessage The response message.
     * @return self The current instance for method chaining.
     */
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;
        return $this;
    }
}
