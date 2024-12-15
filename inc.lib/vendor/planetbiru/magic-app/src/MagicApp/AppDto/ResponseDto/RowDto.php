<?php

namespace MagicApp\AppDto\ResponseDto;

use MagicObject\MagicObject;

/**
 * Data Transfer Object (DTO) representing a row of data with associated metadata.
 * 
 * The RowDto class encapsulates a single row of data within a dataset, 
 * along with its corresponding metadata. This class is designed to hold 
 * both the data object, which can be dynamically structured through the 
 * MagicObject class, and the metadata that provides additional context 
 * about the row's status and attributes.
 * 
 * The class extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class RowDto extends ToString
{
    /**
     * Data associated with the row.
     *
     * @var MagicObject
     */
    protected $data;

    /**
     * Metadata associated with the row.
     *
     * @var MetadataDto
     */
    protected $metadata;

    /**
     * Constructor to initialize properties of the RowDto class.
     *
     * @param MagicObject $data The data object for the row.
     * @param MetadataDto $metadata The metadata associated with the data object.
     */
    public function __construct($data, $metadata)
    {
        $this->data = $data;
        $this->metadata = $metadata;
    }

    /**
     * Get the data object.
     *
     * @return MagicObject The data object associated with the row.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the data object and return the current instance for method chaining.
     *
     * @param MagicObject $data The new data object.
     * @return self The current instance for method chaining.
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this; // Returns the current instance for method chaining.
    }

    /**
     * Get the metadata object.
     *
     * @return MetadataDto The metadata object associated with the row.
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Set the metadata object and return the current instance for method chaining.
     *
     * @param MetadataDto $metadata The new metadata object.
     * @return self The current instance for method chaining.
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this; // Returns the current instance for method chaining.
    }
}
