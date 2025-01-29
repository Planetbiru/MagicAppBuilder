<?php

namespace MagicApp\AppDto\MocroServices;

/**
 * Class UserFormOutputList
 *
 * Represents a list of output data for a user form, typically used for displaying data in a list or table format.
 * This class manages the headers, which define the structure of the table, and the data items that are part of the list.
 * It also includes a list of allowed actions that can be performed on the fields within the form, such as updating, 
 * activating, or deleting records.
 *
 * @package MagicApp\AppDto\MocroServices
 */
class PicoUserFormOutputList extends PicoEntityData
{
    /**
     * An array of `PicoDataHeader` objects representing the headers of the output list.
     * Each header defines the structure and sorting behavior for the list.
     *
     * @var PicoDataHeader[]
     */
    protected $header;

    /**
     * Primary key
     *
     * @var string[]
     */
    protected $primaryKey;
    
    /**
     * An array of `PicoOutputDataItem` objects representing the items in the output row.
     * Each item contains associated data for the fields in the row.
     *
     * @var PicoOutputDataItem[]
     */
    protected $row;
    
    /**
     * Add a header to the output list.
     *
     * This method adds a `PicoDataHeader` object to the list of headers. The header defines the structure and sorting
     * behavior for the fields in the list or table.
     *
     * @param PicoDataHeader $header The `PicoDataHeader` object to be added.
     * @return self Returns the current instance for method chaining.
     */
    public function addHeader($header)
    {
        if (!isset($this->header)) {
            $this->header = [];
        }
        $this->header[] = $header;
        return $this;
    }
    
    /**
     * Add a data item to the output list.
     *
     * This method adds an `PicoOutputDataItem` object to the list of data items. Each data item represents an individual 
     * item in the list or table, containing the data for each field.
     *
     * @param PicoOutputDataItem $dataItem The `PicoOutputDataItem` object to be added.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function addDataItem($dataItem)
    {
        if (!isset($this->row)) {
            $this->row = [];
        }
        $this->row[] = $dataItem;
        return $this;
    }

    /**
     * Get the primary key.
     *
     * This method returns the primary key array which is used to identify the unique records in the output list.
     *
     * @return string[] The primary key array.
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key.
     *
     * This method sets the primary key for the output list, which is used for identifying unique records.
     *
     * @param string[] $primaryKey The primary key array.
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }
    
    /**
     * Set the sorting behavior for a header column.
     *
     * This method sets the sorting order for a given column header in the list. The sort type can be ascending or descending.
     *
     * @param string $column The column name to set the sorting for.
     * @param string $sortType The type of sorting ('ASC' for ascending, 'DESC' for descending).
     * 
     * @return self Returns the current instance for method chaining.
     */
    public function setSortHeader($column, $sortType)
    {
        if(isset($this->header) && is_array($this->header))
        {
            foreach($this->header as $index=>$header)
            {
                if($this->header[$index]->getValue() == $column)
                {
                    $this->header[$index]->setSort($sortType);
                }
            }
        }
        return $this;
    }
}
