<?php

namespace AppBuilder\Generator\MocroServices;

/**
 * Class ResponseBody
 *
 * This class represents the response body of an API or service response. It contains 
 * information about the response code, response text, and the data returned by the service. 
 * The `toArray()` and `__toString()` methods, inherited from the parent class `ObjectToString`, 
 * are used to convert this response object into a JSON string or an associative array. 
 * This class can be used to format the response into a structured, readable format.
 * 
 * @package AppBuilder\Generator\MocroServices
 */
class ResponseBody extends ObjectToString
{
    /**
     * The response code from the service or API.
     *
     * @var string
     */
    protected $responseCode;
    
    /**
     * The response message or text from the service or API.
     *
     * @var string
     */
    protected $responseText;
    
    /**
     * The data returned in the response, which can be of any type.
     *
     * @var mixed
     */
    protected $data;
    
    /**
     * Constructor for ResponeBody.
     *
     * Initializes the response code, response text, and data properties. 
     * This constructor allows setting the values of these properties when creating a new instance of the class.
     * 
     * @param string $responseCode The response code from the service.
     * @param string $responseText The response text or message.
     * @param mixed $data The data returned in the response (optional).
     */
    public function __construct(
        $responseCode,
        $responseText,
        $data = null
    ) {
        $this->responseCode = $responseCode;
        $this->responseText = $responseText;
        $this->data = $data;
    }
    
    /**
     * Factory method to create an instance of ResponeBody.
     *
     * This static method provides an alternative way to instantiate the ResponeBody class,
     * allowing you to set the properties directly via parameters.
     * 
     * @param mixed $data The data returned in the response (optional).
     * @param string $responseCode The response code from the service.
     * @param string $responseText The response text or message.
     * 
     * @return ResponeBody
     */
    public static function instanceOf(
        $data = null,
        $responseCode = "000",
        $responseText = "Success"
    )
    {
        return new self($responseCode, $responseText, $data);
    }
}
