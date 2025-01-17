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
class UserFormOutputList extends ObjectToString
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
     * An array of `OutputDataItem` objects representing the items in the output list.
     * Each item contains associated data for the fields in the list.
     *
     * @var OutputDataItem[]
     */
    protected $list;
    
    /**
     * A list of allowed actions that can be performed on the form fields.
     * Examples include `update`, `activate`, `deactivate`, `delete`, `approve`, and `reject`.
     * These actions are represented by `AllowedAction` objects.
     *
     * @var AllowedAction[]
     */
    protected $allowedActions;
    
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
     * This method adds an `OutputDataItem` object to the list of data items. Each data item represents an individual 
     * item in the list or table, containing the data for each field.
     *
     * @param OutputDataItem $dataItem The `OutputDataItem` object to be added.
     */
    public function addDataItem($dataItem)
    {
        if (!isset($this->list)) {
            $this->list = [];
        }
        $this->list[] = $dataItem;
    }
    
    /**
     * Add an allowed action to the output list.
     *
     * This method adds an `AllowedAction` object to the list of actions that can be performed on the form fields. 
     * These actions could include operations like updating, activating, or deleting records.
     *
     * @param AllowedAction $allowedAction The `AllowedAction` object to be added.
     */
    public function addAllowedAction($allowedAction)
    {
        if (!isset($this->allowedActions)) {
            $this->allowedActions = [];
        }
        $this->allowedActions[] = $allowedAction;
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
     * @param  string[]  $primaryKey  Primary key
     *
     * @return  self
     */ 
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;

        return $this;
    }
}
