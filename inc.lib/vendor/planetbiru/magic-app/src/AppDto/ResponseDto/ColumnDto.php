<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class ColumnDto
 *
 * Represents a data structure for a column in a table or grid-like layout, typically used for displaying
 * rows of data. Each `ColumnDto` object contains the data for the column (represented by an array of `ColumnDataDto` objects),
 * as well as associated metadata (represented by a `MetadataDto` object) which provides additional information 
 * about the column, such as its title, type, or other descriptive attributes.
 *
 * This class is primarily used for managing and structuring data and metadata related to a column in a
 * larger dataset, such as a form, table, or report.
 *
 * Properties:
 * - `data`: An array of `ColumnDataDto` objects, each representing a piece of data within the column.
 * - `metadata`: An instance of the `MetadataDto` class containing additional metadata related to the column.
 *
 * Methods:
 * - `getData()`: Retrieves the data associated with the column.
 * - `setData(array $data)`: Sets the data for the column and returns the current instance.
 * - `addData(ColumnDataDto $columnData)`: Adds a single `ColumnDataDto` to the `data` array and returns the current instance.
 * - `getMetadata()`: Retrieves the metadata associated with the column.
 * - `setMetadata(MetadataDto $metadata)`: Sets the metadata for the column and returns the current instance.
 *
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class ColumnDto extends ToString
{
    /**
     * Data associated with the row.
     *
     * @var ColumnDataDto[] Array of `ColumnDataDto` objects.
     */
    protected $data;

    /**
     * Metadata associated with the row.
     *
     * @var MetadataDto An instance of `MetadataDto`.
     */
    protected $metadata;

    /**
     * ColumnDto constructor.
     *
     * Initializes the `data` property as an empty array and creates a new `MetadataDto` instance for the
     * `metadata` property.
     */
    public function __construct()
    {
        $this->data = [];
        $this->metadata = new MetadataDto();
    }

    /**
     * Gets the data associated with the column.
     *
     * @return ColumnDataDto[] The array of `ColumnDataDto` objects.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data for the column.
     *
     * @param ColumnDataDto[] $data The array of `ColumnDataDto` objects to set.
     * @return self Returns the current instance for method chaining.
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Adds a single `ColumnDataDto` to the `data` array.
     *
     * @param ColumnDataDto $columnData The `ColumnDataDto` object to add.
     * @return self Returns the current instance for method chaining.
     */
    public function addData($columnData)
    {
        $this->data[] = $columnData;
        return $this;
    }

    /**
     * Gets the metadata associated with the row.
     *
     * @return MetadataDto The metadata associated with the row.
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Sets the metadata associated with the row.
     *
     * @param MetadataDto $metadata The metadata to set for the column.
     * @return self Returns the current instance for method chaining.
     */
    public function setMetadata($metadata)
    {
        $this->metadata = $metadata;
        return $this;
    }
}
