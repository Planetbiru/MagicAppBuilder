<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Data Transfer Object (DTO) for displaying records in a table format.
 * 
 * The class extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class DetailDto extends ToString
{
    /**
     * The namespace where the module is located, such as "/", "/admin", "/supervisor", etc.
     *
     * @var string
     */
    public $namespace;
    
    /**
     * The ID of the module associated with the data.
     *
     * @var string
     */
    public $moduleId;

    /**
     * The name of the module associated with the data.
     *
     * @var string
     */
    public $moduleName;

    /**
     * The title of the module associated with the data.
     *
     * @var string
     */
    public $moduleTitle;

    /**
     * The response code indicating the status of the request.
     *
     * @var string|null
     */
    public $responseCode;

    /**
     * A message providing additional information about the response.
     *
     * @var string|null
     */
    public $responseMessage;

    /**
     * The main data structure containing the list of items.
     *
     * @var DetailDataDto
     */
    public $data;

    /**
     * Constructor for initializing the DetailDto instance.
     *
     * @param string|null $responseCode The response code.
     * @param string|null $responseMessage The response message.
     * @param DetailDataDto $data The main data structure.
     */
    public function __construct($responseCode, $responseMessage, $data)
    {
        $this->responseCode = $responseCode;    
        $this->responseMessage = $responseMessage;    
        $this->data = $data;    
    }

    /**
     * Get the namespace where the module is located.
     *
     * @return string The namespace.
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Set the namespace where the module is located.
     *
     * @param string $namespace The namespace to set.
     * @return self The current instance for method chaining.
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get the main data structure containing the detail columns.
     *
     * @return DetailDataDto|null The detail data structure.
     */ 
    public function getData()
    {
        return $this->data;
    }

    /**
     * Add a new detail column.
     *
     * This method creates a new DetailColumnDto and appends it to the detail data structure.
     *
     * @param string $field The field associated with the detail.
     * @param ValueDto $value The value associated with the detail.
     * @param string|null $type The type of the value.
     * @param string|null $label The label describing the detail.
     * @param bool $readonly Whether the detail is readonly.
     * @param bool $hidden Whether the detail is hidden.
     * @param ValueDto|null $valueDraft The value associated with the draft data.
     * @return self The instance of this class for method chaining.
     */
    public function addData($field, $value, $type = null, $label = null, $readonly = false, $hidden = false, $valueDraft = null)
    {
        $this->data->appendData($field, $value, $type, $label, $readonly, $hidden, $valueDraft);
        return $this; // Return current instance for method chaining.
    }

    /**
     * Add a primary key name and its data type to the list of primary keys.
     *
     * This method initializes the primary key name and data type properties as arrays if they haven't been set,
     * then appends the new primary key name and its corresponding data type to the lists.
     *
     * @param string $primaryKeyName The primary key name to add.
     * @param string $primaryKeyDataType The primary key data type to add.
     * @return self The instance of this class for method chaining.
     */
    public function addPrimaryKeyName($primaryKeyName, $primaryKeyDataType)
    {
        if (!isset($this->data->primaryKeyName)) {
            $this->data->primaryKeyName = array(); // Initialize as an array if not set
            $this->data->primaryKeyDataType = array(); // Initialize as an array if not set
        }   
        $this->data->primaryKeyName[] = $primaryKeyName; // Append the primary key name
        $this->data->primaryKeyDataType[$primaryKeyName] = $primaryKeyDataType; // Append the primary key data type
        return $this; // Return current instance for method chaining.
    }

    /**
     * Set metadata associated with the row.
     *
     * @param MetadataDto $metadata Metadata associated with the row.
     * @return self The current instance for method chaining.
     */ 
    public function setMetadata($metadata)
    {
        $this->data->column->metadata = $metadata;
        return $this; // Return current instance for method chaining.
    }
}
