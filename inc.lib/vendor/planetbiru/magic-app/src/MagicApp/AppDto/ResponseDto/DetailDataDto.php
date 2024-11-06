<?php

namespace MagicApp\AppDto\ResponseDto;

/**
 * Class DetailDataDto
 *
 * Represents the data structure for a table, including column titles and rows.
 * This class manages the titles of column, a data map, and the rows of data 
 * represented as RowDto instances. It provides methods for appending 
 * titles, data maps, and rows, as well as resetting these structures.
 * 
 * The class extends the ToString base class, enabling string representation based on 
 * the specified property naming strategy.
 * 
 * @package MagicApp\AppDto\ResponseDto
 * @author Kamshory
 * @link https://github.com/Planetbiru/MagicApp
 */
class DetailDataDto extends ToString
{
    /**
     * The name of the primary key in the data structure.
     *
     * @var string[]|null
     */
    public $primaryKeyName;

    /**
     * An associative array mapping primary key names to their data types.
     *
     * @var string[]
     */
    public $primaryKeyDataType;

    /**
     * An array of column, each represented as a ColumnDto.
     *
     * @var ColumnDto
     */
    public $column;

    public function __construct()
    {
        $this->column = new ColumnDto();
    }

    /**
     * Get the name of the primary key in the data structure.
     *
     * @return string[]|null The name of the primary key.
     */
    public function getPrimaryKeyName()
    {
        return $this->primaryKeyName;
    }

    /**
     * Set the name of the primary key in the data structure.
     *
     * @param string[]|null $primaryKeyName The name of the primary key.
     * @return self The current instance for method chaining.
     */
    public function setPrimaryKeyName($primaryKeyName)
    {
        $this->primaryKeyName = $primaryKeyName;
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
        if (!isset($this->primaryKeyName)) {
            $this->primaryKeyName = array(); // Initialize as an array if not set
            $this->primaryKeyDataType = array(); // Initialize as an array if not set
        }
        $this->primaryKeyName[] = $primaryKeyName; // Append the primary key name
        $this->primaryKeyDataType[$primaryKeyName] = $primaryKeyDataType; // Append the primary key data type
        return $this; // Return current instance for method chaining.
    }

    /**
     * Append a row of data to the table.
     *
     * This method adds a new row to the internal column collection using the provided
     * parameters to create a ColumnDto instance.
     *
     * @param string $field The name of the field.
     * @param mixed $value The value associated with the field.
     * @param string $type The type of the field.
     * @param string $label The label for the field.
     * @param bool $readonly Indicates if the field is read-only.
     * @param bool $hidden Indicates if the field is hidden.
     * @param mixed $valueDraft The draft value associated with the field.
     * @return self The current instance for method chaining.
     */
    public function appendData($field, $value, $type, $label, $readonly, $hidden, $valueDraft)
    {
        if (!isset($this->column)) {
            $this->column = new ColumnDto(); // Initialize as an array if not set
        }
        if (!isset($this->column->data)) {
            $this->column->data = []; // Initialize as an array if not set
        }


        $this->column->data[] = new ColumnDataDto($field, $value, $type, $label, $readonly, $hidden, $valueDraft);
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get an array of column, each represented as a ColumnDto.
     *
     * @return ColumnDto[] The column in the data structure.
     */
    public function getcolumn()
    {
        return $this->column;
    }

    /**
     * Set an array of column, each represented as a ColumnDto.
     *
     * @param ColumnDto[] $column An array of column to set.
     * @return self The current instance for method chaining.
     */
    public function setcolumn($column)
    {
        $this->column = $column;
        return $this; // Return current instance for method chaining.
    }
}
