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
     * An array of `DataHeader` objects representing the headers of the output list.
     * Each header defines the structure and sorting behavior for the list.
     *
     * @var DataHeader[]
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
     * This method adds a `DataHeader` object to the list of headers. The header defines the structure and sorting
     * behavior for the fields in the list or table.
     *
     * @param DataHeader $header The `DataHeader` object to be added.
     */
    public function addHeader($header)
    {
        if (!isset($this->header)) {
            $this->header = [];
        }
        $this->header[] = $header;
    }
    
    /**
     * Add a data item to the output list.
     *
     * This method adds an `PicoOutputDataItem` object to the list of data items. Each data item represents an individual 
     * item in the list or table, containing the data for each field.
     *
     * @param PicoOutputDataItem $dataItem The `PicoOutputDataItem` object to be added.
     */
    public function addDataItem($dataItem)
    {
        if (!isset($this->row)) {
            $this->row = [];
        }
        $this->row[] = $dataItem;
    }

    /**
     * Get primary key
     *
     * @return  string[]
     */ 
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set primary key
     *
     * @param string[]  $primaryKey  Primary key
     *
     * @return self The current instance for method chaining.
     */ 
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }
}
