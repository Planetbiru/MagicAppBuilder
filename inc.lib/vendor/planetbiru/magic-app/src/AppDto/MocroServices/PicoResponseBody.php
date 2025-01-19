<?php

namespace MagicApp\AppDto\MocroServices;

use MagicApp\AppUserPermission;
use MagicApp\PicoModule;
use MagicObject\MagicObject;

/**
 * Class PicoResponseBody
 *
 * This class represents the response body of an API or service response. It encapsulates
 * information such as the response code, response text, and the data returned by the service. 
 * The `toArray()` and `__toString()` methods, inherited from the parent class `ObjectToString`, 
 * are used to convert this response object into a JSON string or an associative array. 
 * This class is designed to help format the response into a structured and readable format.
 * 
 * @package MagicApp\AppDto\MocroServices
 */
class PicoResponseBody extends PicoObjectToString
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
     * Module
     *
     * @var ModuleInfo
     */
    protected $module;
    
    /**
     * The data returned in the response, which can be of any type.
     *
     * @var EntityData
     */
    protected $data;

    /**
     * Constructor for PicoResponseBody.
     *
     * Initializes the response code, response text, data, and an optional primary key.
     * This constructor allows setting the values of these properties when creating a new instance of the class.
     * 
     * @param string $responseCode The response code from the service.
     * @param string $responseText The response text or message.
     * @param mixed $data The data returned in the response (optional).
     * @param string|null $primaryKey The primary key associated with the response (optional).
     */
    public function __construct(
        $responseCode,
        $responseText,
        $data = null,
        $primaryKey = null
    ) {
        $this->responseCode = $responseCode;
        $this->responseText = $responseText;
        $this->data = $data;
        if (isset($primaryKey)) {
            $this->setPrimaryKey($primaryKey);
        }
    }

    /**
     * Get a new instance of PicoResponseBody.
     *
     * This static method provides a way to create an instance of PicoResponseBody
     * without setting any values initially.
     * 
     * @return PicoResponseBody A new instance of PicoResponseBody with default values.
     */
    public static function getInstance()
    {
        return new self(null, null);
    }
    
    /**
     * Factory method to create an instance of PicoResponseBody with specific values.
     *
     * This static method provides an alternative way to instantiate the PicoResponseBody class,
     * allowing you to set the properties directly via parameters.
     * 
     * @param mixed $data The data returned in the response (optional).
     * @param string $responseCode The response code from the service.
     * @param string $responseText The response text or message.
     * 
     * @return PicoResponseBody A new instance of PicoResponseBody with the specified values.
     */
    public static function instanceOf(
        $data = null,
        $responseCode = "000",
        $responseText = "Success"
    ) {
        return new self($responseCode, $responseText, $data);
    }

    /**
     * Get the primary key associated with the response.
     *
     * This method returns the primary key that was set during instantiation or via setter.
     * 
     * @return string[]|null The primary key, or null if not set.
     */
    public function getPrimaryKey()
    {
        return $this->getData() != null ? $this->getData()->getPrimaryKey() : null;
    }

    /**
     * Set the primary key associated with the response.
     *
     * This method sets the primary key, which can be used to identify or link this response.
     * 
     * @param string[] $primaryKey The primary key to associate with the response.
     * 
     * @return self The current instance for method chaining.
     */
    public function setPrimaryKey($primaryKey)
    {
        if($this->getData() != null)
        {
            $this->getData()->setPrimaryKey($primaryKey);
        }
        return $this;
    }

    /**
     * Adds a primary key to the associated data.
     *
     * This method checks if the data object is not null, and if so, resets the primary key collection
     * and adds the provided primary key to it.
     *
     * @param string $primaryKey The primary key to add
     * @return self Returns the current instance for method chaining
     */
    public function addPrimaryKey($primaryKey)
    {
        if ($this->data != null) {
            if($this->data->getPrimaryKey() == null)
            {
                $this->getData()->setPrimaryKey([]);
            }
            $this->getData()->addPrimaryKey($primaryKey);
        }
        return $this;
    }

    /**
     * Adds a primary key value to the associated data.
     *
     * This method checks if the data object is not null, and if so, resets the primary key value collection
     * and adds a new `PrimaryKeyValue` object with the provided primary key and value.
     *
     * @param string $primaryKey The primary key to add
     * @param mixed $value The value associated with the primary key
     * @return self Returns the current instance for method chaining
     */
    public function addPrimaryKeyValue($primaryKey, $value)
    {
        if ($this->data != null) {
            if($this->data->getPrimaryKeyValue() == null)
            {
                $this->data->setPrimaryKeyValue([]);
            }
            $this->data->addPrimaryKeyValue($primaryKey, $value);
        }
        return $this;
    }

    /**
     * Set the entity for the response and automatically determine the primary key.
     *
     * This method sets the primary key based on the table information from the provided entity.
     *
     * @param MagicObject $entity The entity object that contains table information.
     * @param bool $setValue
     * @return self The current instance for method chaining.
     */
    public function setEntity($entity, $setValue = false)
    {
        $tableInfo = $entity->tableInfo();
        $primaryKeys = array_keys($tableInfo->getPrimaryKeys());
        foreach($primaryKeys as $primaryKey)
        {
            $this->addPrimaryKey($primaryKey);
            if($setValue)
            {
                $this->addPrimaryKeyValue($primaryKey, $entity->get($primaryKey));
            }
        }
        return $this;
    }

    /**
     * Get the data returned in the response.
     *
     * This method returns the data associated with the response, which can be of any type.
     * 
     * @return EntityData The data returned in the response.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the data returned in the response.
     *
     * This method sets the data associated with the response, which can be of any type.
     * 
     * @param EntityData $data The data to set for the response.
     * 
     * @return self The current instance for method chaining.
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the response code from the service or API.
     *
     * This method returns the response code associated with the service or API response.
     * 
     * @return string The response code.
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set the response code from the service or API.
     *
     * This method sets the response code associated with the service or API response.
     * 
     * @param string $responseCode The response code to set.
     * 
     * @return self The current instance for method chaining.
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Get the response message or text from the service or API.
     *
     * This method returns the response message or text associated with the service or API response.
     * 
     * @return string The response message or text.
     */
    public function getResponseText()
    {
        return $this->responseText;
    }

    /**
     * Set the response message or text from the service or API.
     *
     * This method sets the response message or text associated with the service or API response.
     * 
     * @param string $responseText The response message or text to set.
     * 
     * @return self The current instance for method chaining.
     */
    public function setResponseText($responseText)
    {
        $this->responseText = $responseText;

        return $this;
    }

    /**
     * Sets the case format to specified format.
     * This method allows switching the format for property names to specified format.
     * @return self
     */
    public function switchCaseTo($caseFormat)
    {
        parent::switchCaseTo($caseFormat);
        return $this;
    }

    /**
     * Get module
     *
     * @return  ModuleInfo
     */ 
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set module
     *
     * @param ModuleInfo|PicoModule  $module Module
     * @param string $section Module section
     *
     * @return  self
     */ 
    public function setModule($module, $section = null)
    {
        if(isset($module) && $module instanceof PicoModule)
        {
            $this->module = new PicoModuleInfo($module->getModuleName(), $module->getModuleTitle(), $section);
        }
        else
        {
            $this->module = $module;
        }

        return $this;
    }
    
    /**
     * Undocumented function
     *
     * @param AppUserPermission $appUserPermission
     * @return self
     */
    public function setPermission($appUserPermission)
    {
        return $this;
    }

}
