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
    protected $primaryKeyName;

    /**
     * An associative array mapping primary key names to their data types.
     *
     * @var string[]
     */
    protected $primaryKeyDataType;

    /**
     * An array of column, each represented as a ColumnDto.
     *
     * @var ColumnDto
     */
    protected $column;
    
    /**
     * Data control
     *
     * @var ButtonFormData[]
     */
    protected $dataControl;

    /**
     * Initializes the object and sets up the necessary properties.
     * This constructor creates a new instance of ColumnDto for the column property
     * and initializes an empty array for the dataControl property.
     */
    public function __construct()
    {
        $this->column = new ColumnDto();
        $this->dataControl = [];
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
        $this->primaryKeyDataType[] = new PrimaryKeyValueDto($primaryKeyName, $primaryKeyDataType); // Append the primary key data type
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
    public function addData($field, $value, $type, $label, $readonly, $hidden, $valueDraft)
    {
        if (!isset($this->column)) {
            $this->column = new ColumnDto(); // Initialize as an array if not set
        }

        $this->column->addData(new ColumnDataDto($field, $value, $type, $label, $readonly, $hidden, $valueDraft));
        return $this; // Return current instance for method chaining.
    }

    /**
     * Get an array of column, each represented as a ColumnDto.
     *
     * @return ColumnDto The column in the data structure.
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Set an array of column, each represented as a ColumnDto.
     *
     * @param ColumnDto[] $column An array of column to set.
     * @return self The current instance for method chaining.
     */
    public function setColumn($column)
    {
        $this->column = $column;
        return $this; // Return current instance for method chaining.
    }

    /**
     * Retrieves the associative array mapping primary key names to their data types.
     *
     * This method returns the current mapping of primary key names to their associated data types
     * as an associative array, where the keys are the primary key names and the values are 
     * their respective data types.
     *
     * @return string[] An associative array where the keys are primary key names and the values are data types.
     */
    public function getPrimaryKeyDataType()
    {
        return $this->primaryKeyDataType;
    }

    /**
     * Sets an associative array mapping primary key names to their data types.
     *
     * This method allows setting or updating the mapping of primary key names to their respective
     * data types. The argument should be an associative array where each key represents a primary 
     * key name and the corresponding value is the data type.
     *
     * @param string[] $primaryKeyDataType An associative array mapping primary key names to their data types.
     *                                     The array should contain primary key names as keys and data types as values.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function setPrimaryKeyDataType($primaryKeyDataType)
    {
        $this->primaryKeyDataType = $primaryKeyDataType;

        return $this;
    }

    /**
     * Appends or updates the data type for a specific primary key name.
     *
     * This method allows appending a new primary key name and its associated data type to the existing 
     * mapping or updating the data type of an existing primary key name. The primary key name is 
     * specified as the `$name` argument, and the data type is specified as the `$type` argument.
     *
     * @param string $name The primary key name to be added or updated in the mapping.
     * @param string $type The data type to be associated with the primary key name.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function addPrimaryKeyDataType($name, $type)
    {
        $this->primaryKeyDataType[$name] = $type;
        return $this;
    }

    /**
     * Adds a ButtonFormData object to the internal collection of data controls.
     * This method stores the given data control for further use or processing.
     *
     * @param ButtonFormData $dataControl The ButtonFormData object to be added to the collection.
     * @return self Returns the current object instance for method chaining.
     */
    public function addDataControl($dataControl)
    {
        $this->dataControl[] = $dataControl;
        return $this;
    }

}
