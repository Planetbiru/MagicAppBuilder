<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class PicoResponseStatus
 *
 * Represents a response status with a response code and message.
 * Useful for standardizing service responses in a structured format.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoResponseStatus extends PicoObjectToString
{
    /**
     * The response code (e.g., "000" for success, other codes for errors).
     *
     * @var string
     */
    protected $responseCode;

    /**
     * The response text or message associated with the response code.
     *
     * @var string
     */
    protected $responseText;

    /**
     * Constructor for PicoResponseStatus.
     *
     * @param string $responseCode The response code (e.g., "000").
     * @param string $responseText The response message (e.g., "Success").
     */
    public function __construct($responseCode, $responseText = null)
    {
        $this->responseCode = $responseCode;
        if(!isset($responseText))
        {
            $responseText = PicoStatusCode::getMessage($responseCode);
        }
        $this->responseText = $responseText;
    }

    /**
     * Get the response code (e.g., "000" for success, other codes for errors).
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set the response code (e.g., "000" for success, other codes for errors).
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Get the response text or message associated with the response code.
     */
    public function getResponseText()
    {
        return $this->responseText;
    }

    /**
     * Set the response text or message associated with the response code.
     */
    public function setResponseText($responseText)
    {
        $this->responseText = $responseText;

        return $this;
    }
}
