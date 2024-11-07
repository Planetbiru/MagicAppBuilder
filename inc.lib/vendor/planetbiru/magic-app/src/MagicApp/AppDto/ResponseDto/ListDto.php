<?php

namespace MagicApp\AppDto\ResponseDto;

use MagicObject\MagicObject;
use MagicObject\SetterGetter;
use MagicObject\Util\PicoGenericObject;
use stdClass;

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
class ListDto extends ToString
{
    /**
     * The namespace where the module is located, such as "/", "/admin", "/supervisor", etc.
     *
     * @var string
     */
    protected $namespace;
    
    /**
     * The ID of the module associated with the data.
     *
     * @var string
     */
    protected $moduleId;

    /**
     * The name of the module associated with the data.
     *
     * @var string
     */
    protected $moduleName;

    /**
     * The title of the module associated with the data.
     *
     * @var string
     */
    protected $moduleTitle;

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
     * The main data structure containing the list of items.
     *
     * @var ListDataDto|null
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
     * Sets the pagination details for the current object.
     *
     * This method assigns a `PageDto` instance to the object. It accepts various types 
     * of input to initialize the pagination details:
     * - A `PicoPageable` object, which contains the page and page size details.
     * - A `PicoPage` object, which provides the page number and page size.
     * - A `PageDto` object, which copies pagination information from another `PageDto`.
     * - An `array` containing the page number and page size (array format: [page_number, page_size]).
     *
     * If no input is provided, the pagination is set to the default values from the 
     * constructor of `PageDto` (page 1, page size 10).
     *
     * @param PicoPageable|PicoPage|PageDto|array|null $pageable A pagination object or array providing
     *                                      details (e.g., page number, page size).
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function setPage($pageable)
    {
        $this->data->setPage(new PageDto($pageable));
        return $this;
    }

    /**
     * Append a column title to the table.
     *
     * @param ListDataTitleDto $title The title to append.
     * @return self The current instance for method chaining.
     */
    public function addTitle($title)
    {
        if($this->data->getTitle() == null)
        {
            $this->data->resetTitle();
        }
        
        $this->data->addTitle($title);
        
        return $this;
    }
    
    /**
     * Add a data map to the collection.
     *
     * This method appends a DataMap instance to the internal data map collection.
     * If the collection does not exist, it initializes it first. Each DataMap is 
     * stored in the data structure.
     *
     * @param DataMap $dataMap The DataMap instance to add.
     * @return self The current instance for method chaining.
     */
    public function addDataMap($dataMap)
    {
        // Check if the data map is initialized; if not, reset it
        if ($this->data->getDataMap() == null) {
            $this->data->resetDataMap();
        }
        
        // Append the DataMap instance to the data structure
        $this->data->addDataMap($dataMap);
        
        return $this; // Return the current instance for method chaining
    }
    
    /**
     * Add a column title to the table.
     *
     * This method accepts various types of input to create a column title,
     * including associative arrays, stdClass objects, and instances of
     * MagicObject, SetterGetter, or PicoGenericObject. It extracts the name
     * and value for the title and appends it to the data structure.
     *
     * @param array|stdClass|MagicObject|SetterGetter|PicoGenericObject $title The title to add, which can be:
     * - An associative array with 'name' and 'value' elements.
     * - A stdClass object with 'name' and 'value' properties.
     * - An instance of MagicObject, SetterGetter, or PicoGenericObject 
     *   that has methods `getKey()` and `getValue()`.
     *
     * @return self Returns the current instance for method chaining.
     */
    public function add($title)
    {
        if($this->data->getTitle() == null)
        {
            $this->data->resetTitle();
        }     
        $finalTitle = null;     
        if($title instanceof stdClass && isset($title->name) && isset($title->value))
        {
            $finalTitle = new ListDataTitleDto($title->name, $title->value);
        }
        else if(is_array($title) && isset($title[ConstantDto::NAME]) && isset($title[ConstantDto::VALUE]))
        {
            $finalTitle = new ListDataTitleDto($title[ConstantDto::NAME], $title[ConstantDto::VALUE]);
        }
        else if(($title instanceof MagicObject || $title instanceof SetterGetter || $title instanceof PicoGenericObject) && $title->issetKey() && $title->issetValue())
        {
            $finalTitle = new ListDataTitleDto($title->getKey(), $title->getValue());
        }
        if(isset($finalTitle))
        {
            $this->data->addTitle($finalTitle);
        }
        return $this;
    }

    /**
     * Add a primary key name and its data type to the list of primary keys.
     *
     * This method initializes the primary key name and data type properties as arrays if they haven't been set,
     * then appends the new primary key name and its corresponding data type to the lists.
     *
     * @param string $primaryKeyName The primary key name to add.
     * @param string $primaryKeyDataType The primary key data type to add.
     * @return self The instance of this class.
     */
    public function addPrimaryKeyName($primaryKeyName, $primaryKeyDataType)
    {
        if (!isset($this->data->primaryKeyName)) {
            $this->data->setPrimaryKeyName([]); // Initialize as an array if not set
            $this->data->setPrimaryKeyDataType([]); // Initialize as an array if not set
        }   
        $this->data->addPrimaryKeyName($primaryKeyName, $primaryKeyDataType); // Append the primary key name
        return $this;
    }
    
    /**
     * Append a row of data to the table.
     *
     * This method adds a new row of data to the internal data collection,
     * creating a ListDataDto instance if it doesn't already exist.
     *
     * @param MagicObject $data The row data to append.
     * @param MetadataDto $metadata The metadata associated with the row data.
     * @return self The current instance for method chaining.
     */
    public function addData($data, $metadata)
    {
        if(!isset($this->data))
        {
            $this->data = new ListDataDto();
        }   
        $this->data->addData($data, $metadata);
        return $this;
    }

    /**
     * Get the response code indicating the status of the request.
     *
     * @return string|null
     */ 
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set the response code indicating the status of the request.
     *
     * @param string|null $responseCode The response code indicating the status of the request.
     *
     * @return self The current instance for method chaining.
     */ 
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;

        return $this;
    }

    /**
     * Get a message providing additional information about the response.
     *
     * @return string|null
     */ 
    public function getResponseMessage()
    {
        return $this->responseMessage;
    }

    /**
     * Set a message providing additional information about the response.
     *
     * @param string|null  $responseMessage  A message providing additional information about the response.
     *
     * @return self The current instance for method chaining.
     */ 
    public function setResponseMessage($responseMessage)
    {
        $this->responseMessage = $responseMessage;

        return $this;
    }

    /**
     * Get the main data structure containing the list of items.
     *
     * @return ListDataDto|null The main data structure.
     */ 
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * Adds a data control to the data object.
     * This function allows for the addition of a data control to be managed by the current object.
     *
     * @param ButtonFormData $dataControl The ButtonFormData object containing the data control to be added.
     * @return self Returns the current object instance for method chaining.
     */
    public function addDataControl($dataControl)
    {
        $this->data->addDataControl($dataControl);
        return $this;
    }

}
